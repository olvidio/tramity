<?php

namespace migration_ctr\model;


// Archivos requeridos por esta url **********************************************
use core\ConfigDB;
use core\DBConnection;
use entradas\model\Entrada;
use etherpad\model\Etherpad;
use PDO;
use PDOException;
use usuarios\model\Categoria;
use function core\is_true;

class MigrationCtr
{

    private $sigla_dl;
    private $schema;
    private $oDBPublic;
    private $oDBDl;
    private $oDBCtr;
    private $f_limite;

    /* CONSTRUCTOR ------------------------------ */
    function __construct($schema)
    {
        $this->schema = $schema;
        $this->oDBPublic = $GLOBALS['oDBT'];
        // El esquema para gestionar esto es el de admin.
        // Quiero conectar con la dl
        $oConfigDB = new ConfigDB('tramity');
        $config = $oConfigDB->getEsquema('dlb');
        $oConexion = new DBConnection($config);
        $this->oDBDl = $oConexion->getPDO();

        // para el ctr
        $oConfigDB = new ConfigDB('tramity_ctr');
        $config = $oConfigDB->getEsquema($schema);
        $oConexion = new DBConnection($config);

        $this->oDBCtr = $oConexion->getPDO();

        $this->f_limite = '2022-01-01';
        $this->sigla_dl = 'dlb';
    }

    public function entradas_compartidas()
    {
        // SALIDAS CON DESTINOS MÚLTIPLES
        $sql = "SELECT * FROM escritos WHERE f_salida > '$this->f_limite' AND destinos != '{}' ORDER BY f_salida";
        if ($this->oDBDl->query($sql) === FALSE) {
            $sClauError = 'dl: select escritos multiples destinos';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBDl->query($sql) as $a_row_escrito) {
            $json_prot_local = $a_row_escrito['json_prot_local'];
            // Saltar si ya existe la entrada en el ctr
            list($existe, $sClauError) = $this->comprobar_si_existe_entrada_compartida($json_prot_local);
            $this->exit_si_hay_error($sClauError);
            if (is_true($existe)) {
                continue;
            }

            $id_escrito = $a_row_escrito['id_escrito'];

            list($id_entrada_compartida, $sClauError) = $this->insertar_escrito_dl_como_entrada_compartida_ctr($a_row_escrito);
            $this->exit_si_hay_error($sClauError);

            // copiar el etherpad
            $sClauError = $this->copiar_etherpad_escrito_dl_en_tabla_public_entrada_doc_txt_de_ctr($id_escrito, $id_entrada_compartida);
            $this->exit_si_hay_error($sClauError);

            // Buscar anexos
            $sClauError = $this->insertar_adjuntos_de_escrito_dl_como_entrada_compartida_ctr($id_escrito, $id_entrada_compartida);
            $this->exit_si_hay_error($sClauError);
        }
    }

    public function crear_entradas_individuales($id_ctr)
    {
        // buscar entradas compartidas que sean para el ctr.
        $sql = "SELECT * FROM public.entradas_compartidas WHERE f_entrada > '$this->f_limite' AND $id_ctr = ANY (destinos) ORDER BY f_entrada";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = "ctr: select entradas compartidas para ctr ($id_ctr)";
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBCtr->query($sql) as $a_row_entrada_compartida) {
            $json_prot_origen = $a_row_entrada_compartida['json_prot_origen'];

            // test si ya existe?¿
            list($existe, $sClauError) = $this->comprobar_si_existe_entrada($json_prot_origen);
            $this->exit_si_hay_error($sClauError);
            if (is_true($existe)) {
                continue;
            }

            list($id_entrada, $sClauError) = $this->insertar_entrada_ctr_desde_entrada_compartida($a_row_entrada_compartida);
            $this->exit_si_hay_error($sClauError);

        }
    }

    public
    function entradas($id_lugar)
    {
        $sClauError = '';
        // Buscar las entradas del ctr en la dl, y copiarlas a la DB de ctr como escritos aprobados por el ctr
        $id_lugar_dl = $this->getId_local($this->sigla_dl);
        $sql = "SELECT * FROM entradas WHERE f_entrada > '$this->f_limite' AND json_prot_origen @> '{\"id_lugar\":$id_lugar}' ORDER BY f_entrada";
        if ($this->oDBDl->query($sql) === FALSE) {
            $sClauError = "dl: select entradas de ($id_lugar)";
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        $this->exit_si_hay_error($sClauError);

        foreach ($this->oDBDl->query($sql) as $a_row_entrada) {
            $json_prot_origen = $a_row_entrada['json_prot_origen'];
            // Saltar si ya existe el escrito en el ctr
            list($existe, $sClauError) = $this->comprobar_si_existe_escrito($json_prot_origen);
            $this->exit_si_hay_error($sClauError);
            if (is_true($existe)) {
                continue;
            }

            list($id_escrito, $sClauError) = $this->insertar_escrito_ctr_desde_entrada_dl($a_row_entrada, $id_lugar_dl);
            $this->exit_si_hay_error($sClauError);

            $id_entrada = $a_row_entrada['id_entrada'];
            // copiar el etherpad
            $sClauError = $this->copiar_etherpad_entrada_dl_a_tabla_entrada_doc_bytea_de_ctr($id_entrada, $id_escrito);
            $this->exit_si_hay_error($sClauError);

            // Buscar anexos
            $sClauError = $this->insertar_adjuntos_de_entrada_dl_como_escrito_ctr($id_entrada, $id_escrito);
            $this->exit_si_hay_error($sClauError);

            // Con la fecha del documento de entrada no hago nada.
        }
        return TRUE;
    }

    /**
     * exportar escritos con destino individual de la dl al ctr, como entradas en el ctr
     * @throws \JsonException
     */
    public
    function salidas($id_lugar)
    {
        // SALIDAS
        $sClauError = '';
        $sql = "SELECT * FROM escritos WHERE f_salida > '$this->f_limite' AND json_prot_destino @> '[{\"id_lugar\":$id_lugar}]' ORDER BY f_salida";
        if ($this->oDBDl->query($sql) === FALSE) {
            $sClauError = "dl: select escritos para ($id_lugar)";
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        $this->exit_si_hay_error($sClauError);

        foreach ($this->oDBDl->query($sql) as $a_row_escrito) {
            $json_prot_local = $a_row_escrito['json_prot_local'];
            // Saltar si ya existe la entrada en el ctr
            list($existe, $sClauError) = $this->comprobar_si_existe_entrada($json_prot_local);
            $this->exit_si_hay_error($sClauError);
            if (is_true($existe)) {
                continue;
            }

            $id_escrito = $a_row_escrito['id_escrito'];

            list($id_entrada, $sClauError) = $this->insertar_escrito_dl_como_entrada_ctr($a_row_escrito, $id_lugar);
            $this->exit_si_hay_error($sClauError);

            $sClauError = $this->copiar_etherpad_escrito_dl_en_tabla_entrada_doc_txt_de_ctr($id_escrito, $id_entrada);
            $this->exit_si_hay_error($sClauError);

            // Buscar anexos
            $sClauError = $this->insertar_escrito_adjuntos_dl_como_entrada_adjuntos_ctr($id_escrito, $id_entrada);
            $this->exit_si_hay_error($sClauError);

            // Con la fecha del documento de entrada no hago nada.
        }
    }

    /**
     * Lee los etherpads guardados en 'entrada_doc_bytea' provinientes de las entradas de la dl
     * y crea el etherpad correspondiente para el escrito.
     * @return false|void
     */
    public
    function crear_etherpad_como_escrito()
    {
        $sClauError = '';
        $sql = "SELECT * FROM entrada_doc_bytea ORDER BY id_doc";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = 'ctr: select entrada_doc_bytea';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBCtr->query($sql) as $a_row_doc) {
            $id_escrito = $a_row_doc['id_doc'];

            if (($oDblSt = $this->oDBCtr->prepare("SELECT id_doc, txt FROM entrada_doc_bytea WHERE id_doc=$id_escrito")) === FALSE) {
                $sClauError = "ctr: preparar select de entrada_doc_bytea para id_doc = $id_escrito";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_escrito, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $txt, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $siglaDestino = $this->schema; // creo que al final es lo mismo
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito, $siglaDestino);
            $pad_id = $oEtherpad->getPadId(); // Aquí crea el pad
            $oEtherpad->setHTML($pad_id, $txt);


            // borrar la fila
            $sql = "DELETE FROM entrada_doc_bytea WHERE id_doc = $id_escrito";
            if ($this->oDBCtr->query($sql) === FALSE) {
                $sClauError = "ctr: delete entrada_doc_bytea para id_doc = $id_escrito";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        }
    }

    /**
     * Lee los etherpads guardados en 'entrada_doc_txt' provinientes de los escritos de la dl
     * y crea el etherpad correspondiente para la entrada.
     * @return false|void
     */
    public
    function crear_etherpad_como_entrada()
    {
        $tabla = 'entrada_doc_txt'; // para que no se mezcle con las de 'entrada_doc_bytea';
        $sql = "SELECT id_doc FROM $tabla ORDER BY id_doc";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = "ctr: select de entrada_doc_txt";
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBCtr->query($sql) as $a_row_doc) {
            $id_entrada = $a_row_doc['id_doc'];

            if (($oDblSt = $this->oDBCtr->prepare("SELECT id_doc, txt FROM $tabla WHERE id_doc=$id_entrada")) === FALSE) {
                $sClauError = "ctr: preparar select de entrada_doc_txt para id_doc = $id_entrada";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_entrada, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $txt, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $siglaDestino = $this->schema; // creo que al final es lo mismo
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
            $pad_id = $oEtherpad->getPadId(); // Aquí crea el pad
            //$oEtherpad->grabarMD($txt);
            $oEtherpad->setHTML($pad_id, $txt);

            // borrar la fila
            $sql = "DELETE FROM $tabla WHERE id_doc = $id_entrada";
            if ($this->oDBCtr->query($sql) === FALSE) {
                $sClauError = "ctr: delete entrada_doc_txt para id_doc = $id_entrada";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        }
    }

    /**
     * Lee los etherpads guardados en 'entrada_doc_txt' provinientes de los escritos de la dl
     * y crea el etherpad correspondiente para la entrada.
     * @return false|void
     */
    public
    function crear_etherpad_como_entrada_compartida()
    {
        $tabla = 'public.entrada_doc_txt'; // para que no se mezcle con las de 'entrada_doc_bytea';
        $sql = "SELECT id_doc FROM $tabla ORDER BY id_doc";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = 'ctr: select de public.entrada_doc_txt';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBCtr->query($sql) as $a_row_doc) {
            $id_entrada = $a_row_doc['id_doc'];

            if (($oDblSt = $this->oDBCtr->prepare("SELECT id_doc, txt FROM $tabla WHERE id_doc=$id_entrada")) === FALSE) {
                $sClauError = "ctr: prepare de public.entrada_doc_txt para id_doc=$id_entrada";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_entrada, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $txt, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $siglaDestino = $this->schema; // creo que al final es lo mismo
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada, $siglaDestino);
            $pad_id = $oEtherpad->getPadId(); // Aquí crea el pad
            //$oEtherpad->grabarMD($txt);
            $oEtherpad->setHTML($pad_id, $txt);

            // borrar la fila
            $sql = "DELETE FROM $tabla WHERE id_doc = $id_entrada";
            if ($this->oDBCtr->query($sql) === FALSE) {
                $sClauError = "ctr: delete public.entrada_doc_txt para id_doc=$id_entrada";
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        }
    }


    /*-------------------------  ----------------------------- */

    private
    function getId_local($dl = 'dlb')
    {
        $sql = "SELECT id_lugar FROM lugares WHERE sigla = '$dl'";
        if ($this->oDBPublic->query($sql) === FALSE) {
            $sClauError = 'dl: select de lugares';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBPublic, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBPublic->query($sql) as $row) {
            $id = $row['id_lugar'];
        }
        return $id;
    }

    private function comprobar_si_existe_escrito($json_prot_origen): array
    {
        $sClauError = '';
        // test si ya existe?¿
        // select count(*) FROM daumar.escritos WHERE json_prot_local = '{"any": "23", "mas": "", "num": 34, "id_lugar": 98}';
        $sql_check_if_exist = " select count(*) FROM escritos WHERE json_prot_local = '$json_prot_origen' ";
        if ($this->oDBCtr->query($sql_check_if_exist) === FALSE) {
            $sClauError = 'ctr: comprobar si existe escrito';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        $existe = FALSE;
        if ($this->oDBCtr->query($sql_check_if_exist)->fetchColumn() > 0) {
            $existe = TRUE;
        }
        return array($existe, $sClauError);
    }

    private function insertar_escrito_ctr_desde_entrada_dl(mixed $a_row_entrada, mixed $id_lugar_dl): array
    {
        $json_prot_origen = $a_row_entrada['json_prot_origen'];
        // escapar los apóstrofes con doble ('') cosas del postgresql.
        $asunto = str_replace("'", "''", $a_row_entrada['asunto']);
        $detalle = str_replace("'", "''", $a_row_entrada['detalle']);
        $f_entrada = $a_row_entrada['f_entrada'];
        $categoria = $a_row_entrada['categoria'];

        $json_destino = $a_row_entrada['json_prot_ref'];
        $a_prot_destino = json_decode($json_destino, TRUE, 512, JSON_THROW_ON_ERROR);
        $primera_ref = $a_prot_destino[0];

        if (empty($a_prot_destino) || (!empty($primera_ref) && $primera_ref['id_lugar'] !== $id_lugar_dl)) {
            $any_2 = substr($f_entrada, 2, 2);
            $destino = '[{"any": "' . $any_2 . '", "mas": "", "num": 0, "id_lugar": ' . $id_lugar_dl . '}]';
            $json_ref = $a_row_entrada['json_prot_ref'];
        } else {
            $destino = '[' . json_encode($primera_ref) . ']';
            $a_prot_destino[0] = '';
            $json_ref = json_encode($a_prot_destino);
        }

        $sClauError = '';
        $sql_insert = "INSERT INTO escritos ( json_prot_local, json_prot_destino, json_prot_ref, id_grupos, destinos, asunto, detalle, creador,
                      resto_oficinas, comentarios, f_aprobacion, f_escrito, f_contestar, categoria, visibilidad, visibilidad_dst, accion, modo_envio,
                      f_salida, ok, tipo_doc, anulado, descripcion )
                VALUES ( NULLIF('$json_prot_origen','')::jsonb, NULLIF('$destino','')::jsonb, NULLIF('$json_ref','')::jsonb, '{}', '{}', '$asunto', NULLIF('$detalle','')::text, 10, '{}', '', NULLIF('$f_entrada','')::date, NULLIF('$f_entrada','')::date, NULL, NULLIF('$categoria','')::smallint,
                        1, 0, 2, 1, NULLIF('$f_entrada','')::date, 3, 1, 'f', '' )";
        if ($this->oDBCtr->query($sql_insert) === FALSE) {
            $sClauError = 'ctr: insertar escrito';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        // obtener el id_escrito recién creado:
        $id_escrito = $this->oDBCtr->lastInsertId('escritos_id_escrito_seq');
        return array($id_escrito, $sClauError);
    }

    private function copiar_etherpad_entrada_dl_a_tabla_entrada_doc_bytea_de_ctr(mixed $id_entrada, mixed $id_escrito): string
    {
        // COPIAR EL Etherpad !!!!!!!!!!!!!!!!!!
        // grabo el txt en entrada_doc_bytea, que es una tabla que no se usa. Después desde el servidor de ctr habrá que insertalo.
        // buscar el Etherpad
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $this->sigla_dl);
        $txt = $oEtherpad->generarHtml();
        $campos = "(id_doc,txt)";
        $valores = "(:id_escrito,:txt)";
        $sClauError = '';
        if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO entrada_doc_bytea $campos VALUES $valores")) === FALSE) {
            $sClauError = 'ctr: prepare insert entrada_doc_bytea';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
        } else {
            $oDblSt->bindParam(1, $id_escrito, PDO::PARAM_INT);
            $oDblSt->bindParam(2, $txt, PDO::PARAM_STR);
            try {
                $oDblSt->execute();
            } catch (PDOException $e) {
                //$err_txt = $e->errorInfo[2];
                //$this->setErrorTxt($err_txt);
                $sClauError = 'ctr: No se puede insertar en entrada_doc_bytea';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            }
        }
        return $sClauError;
    }

    private function insertar_adjuntos_de_entrada_dl_como_escrito_ctr(int $id_entrada, int $id_escrito): string
    {

        $sql_entrada_adjuntos = "SELECT * FROM entrada_adjuntos WHERE id_entrada = $id_entrada";

        $sClauError = '';
        if ($this->oDBDl->query($sql_entrada_adjuntos) === FALSE) {
            $sClauError = 'dl: select entrada_adjuntos';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        /* tipo documento (igual que entradadocdb)
                    public const TIPO_ETHERPAD = 1;
                    public const TIPO_ETHERCALC = 2;
                    public const TIPO_OTRO = 3;
                    */
        // El tipo ha de ser 3, pues son entradas antiguas que no son de tramity.
        foreach ($this->oDBDl->query($sql_entrada_adjuntos) as $a_row_entrada_adjuntos) {
            $id_item = $a_row_entrada_adjuntos['id_item'];

            if (($oDblSt = $this->oDBDl->prepare("SELECT id_entrada, nom, adjunto FROM entrada_adjuntos WHERE id_item=$id_item")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'dl: prepare select entrada_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_entrada, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $nom, PDO::PARAM_STR, 256);
            $oDblSt->bindColumn(3, $adjunto, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $campos = "(id_escrito,nom,adjunto,tipo_doc)";
            $valores = "(:id_escrito,:nom,:ajunto,:tipo_doc)";
            if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO escrito_adjuntos $campos VALUES $valores")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'ctr: prepare insert escrito_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
            } else {
                $tipo_doc = 3;

                $oDblSt->bindParam(1, $id_escrito, PDO::PARAM_INT);
                $oDblSt->bindParam(2, $nom, PDO::PARAM_STR);
                $oDblSt->bindParam(3, $adjunto, PDO::PARAM_STR);
                $oDblSt->bindParam(4, $tipo_doc, PDO::PARAM_INT);
                try {
                    $oDblSt->execute();
                } catch (PDOException $e) {
                    //$err_txt = $e->errorInfo[2];
                    //$this->setErrorTxt($err_txt);
                    $sClauError .= empty($sClauError) ? '' : '; ';
                    $sClauError .= 'ctr: insert adjuntos';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                }
            }
        }
        return $sClauError;
    }

    private function exit_si_hay_error(string $sClauError): void
    {
        if (!empty($sClauError)) {
            exit(_("Error:") . ' ' . $sClauError);
        }
    }

    private function comprobar_si_existe_entrada($json_prot_local): array
    {
        $sClauError = '';
        // test si ya existe?¿
        // select count(*) FROM daumar.entradas WHERE json_prot_origen = '{"any": "23", "mas": "", "num": 3350, "id_lugar": 73}';
        $sql_check_if_exist = " select count(*) FROM entradas WHERE json_prot_origen = '$json_prot_local' ";
        if ($this->oDBCtr->query($sql_check_if_exist) === FALSE) {
            $sClauError = 'ctr: select entradas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        $existe = FALSE;
        if ($this->oDBCtr->query($sql_check_if_exist)->fetchColumn() > 0) {
            $existe = TRUE;
        }
        return array($existe, $sClauError);
    }

    private function insertar_escrito_dl_como_entrada_ctr(array $a_row_escrito, int $id_lugar): array
    {
        $json_prot_local = $a_row_escrito['json_prot_local'];
        $json_prot_ref = $a_row_escrito['json_prot_ref'];
        $json_prot_destino = $a_row_escrito['json_prot_destino'];
        // buscar si en destinos hay un protocolo (numero != 0) para mi ctr, y lo añado a las ref.
        $a_destinos = json_decode($json_prot_destino, TRUE, 512, JSON_THROW_ON_ERROR);
        foreach ($a_destinos as $destino) {
            if ($destino['id_lugar'] === $id_lugar && !empty($destino['num'])) {
                // añadirlo a las ref
                $a_referencias = json_decode($json_prot_ref, TRUE, 512, JSON_THROW_ON_ERROR);
                array_unshift($a_referencias, $destino);
                $json_prot_ref = json_encode($a_referencias, JSON_THROW_ON_ERROR);
            }
        }

        // escapar los apóstrofes con doble ('') cosas del postgresql.
        $asunto = str_replace("'", "''", $a_row_escrito['asunto']);
        $detalle = str_replace("'", "''", $a_row_escrito['detalle']);
        $f_salida = $a_row_escrito['f_salida'];

        $sClauError = '';
        $sql = "INSERT INTO entradas ( modo_entrada, json_prot_origen, asunto_entrada, json_prot_ref, asunto, f_entrada, detalle, categoria,
                      visibilidad, bypass, estado)
                    VALUES ( 1, NULLIF('$json_prot_local','')::jsonb, '$asunto', NULLIF('$json_prot_ref','')::jsonb, '$asunto', NULLIF('$f_salida','')::date, NULLIF('$detalle','')::text, 2, 1, 'f', 6)";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = 'ctr: insert entradas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        // obtener el id_entrada recién creado:
        $id_entrada = $this->oDBCtr->lastInsertId('entradas_id_entrada_seq');

        return array($id_entrada, $sClauError);
    }

    private function copiar_etherpad_escrito_dl_en_tabla_public_entrada_doc_txt_de_ctr(mixed $id_escrito, mixed $id_entrada): string
    {
        $sClauError = '';
        // COPIAR EL Etherpad !!!!!!!!!!!!!!!!!!
        $tabla = 'public.entrada_doc_txt'; // para que no se mezcle con las de 'entrada_doc_bytea';
        // grabo el txt en entrada_doc_txt, que es una tabla que no se usa. Después desde el servidor de ctr habrá que insertalo.
        // buscar el Etherpad
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito, $this->sigla_dl);
        $txt = $oEtherpad->generarHtml();
        //$txt = $oEtherpad->generarMD();
        $campos = "(id_doc,txt)";
        $valores = "(:id_entrada,:txt)";
        if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO $tabla $campos VALUES $valores")) === FALSE) {
            $sClauError = 'ctr: preparar insert public.entrada_doc_txt';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
        } else {
            $oDblSt->bindParam(1, $id_entrada, PDO::PARAM_INT);
            $oDblSt->bindParam(2, $txt, PDO::PARAM_STR);
            try {
                $oDblSt->execute();
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $sClauError = 'ctr: insert public.entrada_doc_txt:: ' . $err_txt;
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            }
        }
        return $sClauError;
    }

    private function copiar_etherpad_escrito_dl_en_tabla_entrada_doc_txt_de_ctr(mixed $id_escrito, mixed $id_entrada): string
    {
        $sClauError = '';
        // COPIAR EL Etherpad !!!!!!!!!!!!!!!!!!
        $tabla = 'entrada_doc_txt'; // para que no se mezcle con las de 'entrada_doc_bytea';
        // grabo el txt en entrada_doc_txt, que es una tabla que no se usa. Después desde el servidor de ctr habrá que insertalo.
        // buscar el Etherpad
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito, $this->sigla_dl);
        $txt = $oEtherpad->generarHtml();
        //$txt = $oEtherpad->generarMD();
        $campos = "(id_doc,txt)";
        $valores = "(:id_entrada,:txt)";
        if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO $tabla $campos VALUES $valores")) === FALSE) {
            $sClauError = 'ctr: preparar insert entrada_doc_txt';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
        } else {
            $oDblSt->bindParam(1, $id_entrada, PDO::PARAM_INT);
            $oDblSt->bindParam(2, $txt, PDO::PARAM_STR);
            try {
                $oDblSt->execute();
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $sClauError = 'ctr: insert entrada_doc_txt:: ' . $err_txt;
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            }
        }
        return $sClauError;
    }

    private function insertar_escrito_adjuntos_dl_como_entrada_adjuntos_ctr(int $id_escrito, int $id_entrada): string
    {
        $sClauError = '';
        $sql_escrito_adjuntos = "SELECT * FROM escrito_adjuntos WHERE id_escrito = $id_escrito";

        if ($this->oDBDl->query($sql_escrito_adjuntos) === FALSE) {
            $sClauError .= 'dl: select escrito_adjuntos';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        /* tipo documento (igual que entradadocdb)
        public const TIPO_ETHERPAD = 1;
        public const TIPO_ETHERCALC = 2;
        public const TIPO_OTRO = 3;
        */
        // El tipo ha de ser 3, pues son entradas antiguas que no son de tramity.
        foreach ($this->oDBDl->query($sql_escrito_adjuntos) as $a_row_escrito_adjuntos) {
            $id_item = $a_row_escrito_adjuntos['id_item'];

            if (($oDblSt = $this->oDBDl->prepare("SELECT id_escrito, nom, adjunto, tipo_doc FROM escrito_adjuntos WHERE id_item=$id_item")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'dl: preparar select escrito_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_escrito, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $nom, PDO::PARAM_STR, 256);
            $oDblSt->bindColumn(3, $adjunto, PDO::PARAM_STR);
            $oDblSt->bindColumn(4, $tipo_doc, PDO::PARAM_INT);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $campos = "(id_entrada,nom,adjunto)";
            $valores = "(:id_entrada,:nom,:ajunto)";
            if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO entrada_adjuntos $campos VALUES $valores")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'ctr: preparar insert entrada_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
            } else {
                $oDblSt->bindParam(1, $id_entrada, PDO::PARAM_INT);
                $oDblSt->bindParam(2, $nom, PDO::PARAM_STR);
                $oDblSt->bindParam(3, $adjunto, PDO::PARAM_STR);
                try {
                    $oDblSt->execute();
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $sClauError .= empty($sClauError) ? '' : '; ';
                    $sClauError .= 'ctr: insert entrada_adjuntos:: ' . $err_txt;
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                }
            }
        }
        return $sClauError;
    }

    private function insertar_entrada_ctr_desde_entrada_compartida(array $a_row_entrada_compartida)
    {

        $id_entrada_compartida = $a_row_entrada_compartida['id_entrada_compartida'];
        $json_prot_origen = $a_row_entrada_compartida['json_prot_origen'];
        $json_prot_ref = $a_row_entrada_compartida['json_prot_ref'];
        $categoria = $a_row_entrada_compartida['categoria'];
        $f_entrada = $a_row_entrada_compartida['f_entrada'];
        $asunto_entrada = str_replace("'", "''", $a_row_entrada_compartida['asunto_entrada']);
        $anulado = $a_row_entrada_compartida['anulado'];

        $estado = Entrada::ESTADO_ARCHIVADO;
        $modo_entrada = Entrada::MODO_MANUAL;
        $categoria = empty($categoria)? Categoria::CAT_NORMAL : $categoria;

        $sClauError = '';
        $sql = "INSERT INTO entradas (id_entrada_compartida, modo_entrada, json_prot_origen, asunto_entrada, json_prot_ref,
                       asunto, f_entrada, categoria, visibilidad, bypass, estado, anulado )
                VALUES ($id_entrada_compartida, $modo_entrada, '$json_prot_origen', '$asunto_entrada', '$json_prot_ref',
                      '$asunto_entrada', '$f_entrada', $categoria, 1, 'f', $estado, '$anulado')";
        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = 'ctr: insert entradas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        // obtener el id_entrada recién creado:
        $id_entrada = $this->oDBCtr->lastInsertId('entradas_id_entrada_seq');

        return array($id_entrada, $sClauError);
    }

    private function comprobar_si_existe_entrada_compartida(mixed $json_prot_local)
    {
        $sClauError = '';
        // test si ya existe?¿
        $sql_check_if_exist = " select count(*) FROM public.entradas_compartidas WHERE json_prot_origen = '$json_prot_local' ";
        if ($this->oDBCtr->query($sql_check_if_exist) === FALSE) {
            $sClauError = 'ctr: select public.entradas_compartidas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }
        $existe = FALSE;
        if ($this->oDBCtr->query($sql_check_if_exist)->fetchColumn() > 0) {
            $existe = TRUE;
        }
        return array($existe, $sClauError);
    }

    private function insertar_escrito_dl_como_entrada_compartida_ctr(array $a_row_escrito): array
    {
        $sClauError = '';

        $json_prot_local = $a_row_escrito['json_prot_local'];
        $json_prot_ref = $a_row_escrito['json_prot_ref'];
        $json_prot_destino = $a_row_escrito['json_prot_destino'];

        // escapar los apóstrofes con doble ('') cosas del postgresql.
        $asunto_entrada = str_replace("'", "''", $a_row_escrito['asunto']);
        $descripcion = str_replace("'", "''", $a_row_escrito['descripcion']);
        $destinos = $a_row_escrito['destinos'];
        $anulado = $a_row_escrito['anulado'];
        $categoria = $a_row_escrito['categoria'];

        $f_documento = $a_row_escrito['f_escrito'];
        $f_entrada = $a_row_escrito['f_salida'];


        $sql = "INSERT INTO public.entradas_compartidas (descripcion, asunto_entrada, json_prot_destino, destinos,
                                         f_documento, json_prot_origen, json_prot_ref, categoria, f_entrada, anulado 
        ) VALUES ( '$descripcion', '$asunto_entrada', NULLIF('$json_prot_destino','')::jsonb, NULLIF('$destinos','')::integer[], NULLIF('$f_documento','')::date,
          NULLIF('$json_prot_local','')::jsonb, NULLIF('$json_prot_ref','')::jsonb, NULLIF('$categoria','')::smallint, NULLIF('$f_entrada','')::date, NULLIF('$anulado','')::text)";

        if ($this->oDBCtr->query($sql) === FALSE) {
            $sClauError = 'ctr: insert public.entradas_compartidas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        // obtener el id_entrada recién creado:
        $id_entrada_compartida = $this->oDBCtr->lastInsertId('public.entradas_compartidas_id_entrada_compartida_seq');

        return array($id_entrada_compartida, $sClauError);
    }

    private function insertar_adjuntos_de_escrito_dl_como_entrada_compartida_ctr(int $id_escrito, int $id_entrada_compartida): string
    {
        $sClauError = '';
        $sql_escrito_adjuntos = "SELECT * FROM escrito_adjuntos WHERE id_escrito = $id_escrito";

        if ($this->oDBDl->query($sql_escrito_adjuntos) === FALSE) {
            $sClauError .= 'dl: select escrito_adjuntos';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
        }

        /* tipo documento (igual que entradadocdb)
        public const TIPO_ETHERPAD = 1;
        public const TIPO_ETHERCALC = 2;
        public const TIPO_OTRO = 3;
        */
        // El tipo ha de ser 3, pues son entradas antiguas que no son de tramity.
        foreach ($this->oDBDl->query($sql_escrito_adjuntos) as $a_row_escrito_adjuntos) {
            $id_item = $a_row_escrito_adjuntos['id_item'];

            if (($oDblSt = $this->oDBDl->prepare("SELECT id_escrito, nom, adjunto, tipo_doc FROM escrito_adjuntos WHERE id_item=$id_item")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'dl: preparar select escrito_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBDl, $sClauError, __LINE__, __FILE__);
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_escrito, PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $nom, PDO::PARAM_STR, 256);
            $oDblSt->bindColumn(3, $adjunto, PDO::PARAM_STR);
            $oDblSt->bindColumn(4, $tipo_doc, PDO::PARAM_INT);
            $oDblSt->fetch(PDO::FETCH_BOUND);


            $campos = "(id_entrada_compartida,nom,adjunto)";
            $valores = "(:id_entrada_compartida,:nom,:ajunto)";
            if (($oDblSt = $this->oDBCtr->prepare("INSERT INTO public.entrada_compartida_adjuntos $campos VALUES $valores")) === FALSE) {
                $sClauError .= empty($sClauError) ? '' : '; ';
                $sClauError .= 'ctr: preparar insert public.entrada_compartida_adjuntos';
                $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBCtr, $sClauError, __LINE__, __FILE__);
            } else {
                $oDblSt->bindParam(1, $id_entrada_compartida, PDO::PARAM_INT);
                $oDblSt->bindParam(2, $nom, PDO::PARAM_STR);
                $oDblSt->bindParam(3, $adjunto, PDO::PARAM_STR);
                try {
                    $oDblSt->execute();
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $sClauError .= empty($sClauError) ? '' : '; ';
                    $sClauError .= 'ctr: insert public.entrada_compartida_adjuntos:: ' . $err_txt;
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                }
            }
        }
        return $sClauError;
    }

}