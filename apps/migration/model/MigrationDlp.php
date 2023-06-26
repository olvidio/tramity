<?php

namespace migration\model;


// Archivos requeridos por esta url **********************************************
use core\ConverterJson;
use PDO;
use PDOException;
use web\Protocolo;
use function core\any_2;
use function core\array_php2pg;
use function core\is_true;

require_once("/usr/share/awl/inc/iCalendar.php");

class MigrationDlp
{

    private $oDBT;

    /* CONSTRUCTOR ------------------------------ */
    function __construct()
    {
        $this->oDBT = $GLOBALS['oDBT'];

        // CREATE SCHEMA IF NOT EXISTS reg
        // GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA reg TO tramity;
        // GRANT USAGE ON SCHEMA reg TO tramity;
        //ALTER SCHEMA reg OWNER TO tramity;
        // REASSIGN OWNED BY dani TO tramity;
    }

    public function update_cancilleria(){
        // cambiar a origen Cancillería
        $sql = "UPDATE prodel.registro e 
                SET origen = 'Cancillería'
                WHERE e.origen = 'Cancilleria'";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // cambiar a destino Cancillería
        $sql = "UPDATE prodel.registro e 
                SET destino = 'Cancillería'
                WHERE e.destino = 'Cancilleria'";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

    }

    public function copiar_aprobaciones()
    {
        // aprobaciones: id_salida 	id_reg 	f_aprobacion 	f_salida 	id_modo_envio
        // escritos: id_escrito	json_prot_local json_prot_destino json_prot_ref id_grupos destinos entradilla
        //      asunto detalle creador resto_oficinas comentarios f_aprobacion f_escrito f_contestar categoria visibilidad
        //      accion modo_envio f_salida ok tipo_doc anulado

        // crear tabla
        $sql = "CREATE TABLE IF NOT EXISTS prodel.escritos_tmp (LIKE dlp.escritos INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna referencias y uuid, persistenceid
        $sql = "ALTER TABLE prodel.escritos_tmp ADD COLUMN IF NOT EXISTS referencias character varying(255),
        ADD COLUMN IF NOT EXISTS uuid character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        /*
        // añadir columna id_lugar_origen
        $sql = "ALTER TABLE prodel.entradas_tmp ADD COLUMN IF NOT EXISTS id_lugar_origen integer;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        */
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE prodel.escritos_tmp RESTART IDENTITY";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar (también las de cancillería)
        /*
        //categoria
        const CAT_E12 = 1;
        const CAT_NORMAL = 2;
        const CAT_PERMANENTE = 3;
        // visibilidad
        const V_TODOS = 1;  // cualquiera
        const V_PERSONAL = 2;  // oficina y directores
        const V_DIRECTORES = 3;  // sólo directores
        const V_RESERVADO = 4;  // sólo directores, añade no ver a los directores de otras oficinas no implicadas
        const V_RESERVADO_VCD = 5;  // sólo vcd + quien señale
        // Acción
        public const ACCION_PROPUESTA = 1;
        public const ACCION_ESCRITO = 2;
        public const ACCION_PLANTILLA = 3;
        // modo envío
        public const MODO_MANUAL = 1;
        public const MODO_XML = 2;
        // tipo documento (igual que entradadocdb)
        public const TIPO_ETHERPAD = 1;
        public const TIPO_ETHERCALC = 2;
        public const TIPO_OTRO = 3;
        // ok
        public const OK_NO = 1;
        public const OK_OFICINA = 2;
        public const OK_SECRETARIA = 3;

        */
        $sql = "INSERT INTO prodel.escritos_tmp (
                       asunto, detalle, comentarios, f_aprobacion, f_escrito, f_contestar, categoria, visibilidad, visibilidad_dst,
                                 accion, modo_envio, f_salida, ok, tipo_doc, anulado, referencias, uuid,persistenceid 
                       )
                (SELECT 
                     asunto, asuntooficinas, observaciones, fechacreacion, fechacreacion, fechatopecontestacion, 2, 2, 1, 
                            2, 1, fechacreacion, 3, 3, 'f', referencias, uuid, persistenceid 
                FROM prodel.registro WHERE (origen='dlp' OR destino='Rectorado') AND esanexo='f') ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        //-----------
        // buscar id_lugar_dl
        $id_lugar_dl = $this->getId_local('dlp');

        // rellenar json_prot_local
        // pasar l'any a dues xifres:
        $sql = "UPDATE prodel.escritos_tmp e 
                SET json_prot_local = (SELECT json_build_object('any', substring(r.anno::text from '..$'), 'mas', '', 'num', r.numprotocolo, 'id_lugar', $id_lugar_dl))
                FROM prodel.registro r WHERE e.persistenceid = r.persistenceid AND origen = 'dlp' ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        //-----------  Para Cancillería
        // buscar id_lugar_dl
        $sql = "SELECT id_lugar
            FROM public.lugares 
            WHERE sigla = 'Cancillería'";

        $id_lugar_cancilleria = $this->oDBT->query($sql)->fetchColumn();

        // rellenar json_prot_local
        // pasar l'any a dues xifres:
        $sql = "UPDATE prodel.escritos_tmp e 
                SET json_prot_local = (SELECT json_build_object('any', substring(r.anno::text from '..$'), 'mas', '', 'num', r.numprotocolo, 'id_lugar', $id_lugar_cancilleria))
                FROM prodel.registro r WHERE e.persistenceid = r.persistenceid AND destino = 'Rectorado' ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        //------- CREADOR, hace referencia a cargos, no a oficinas
        // cambiar nombres de oficinas conocidos:
        // OJO!!! Para que también cambie en el reto oficinas

        $a_siglas_tramity = ['ofsvsm', 'des', 'agd'];
        $a_siglas_dlp = ['osvsm', 'dre', 'dagd'];

        // Creador
        $sql = "UPDATE prodel.escritos_tmp
            SET creador = c.id_cargo
            FROM prodel.registro r
            JOIN dlp.x_oficinas o ON r.departamentoponente = o.sigla,
                dlp.aux_cargos c
            WHERE r.persistenceid = escritos_tmp.persistenceid
                    AND o.id_oficina = c.id_oficina AND c.director='t'";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // errores:
        $sql = "SELECT DISTINCT creador, departamentoponente
                FROM prodel.escritos_tmp LEFT JOIN prodel.registro USING (persistenceid) 
                WHERE creador IS NULL ORDER BY creador";
        $msg = '';
        foreach ($this->oDBT->query($sql) as $row_error) {
            $creador = $row_error['creador'];
            $departamento_ponente = $row_error['departamentoponente'];

            $msg .= "No se encuentra el cargo (dtor) para la oficina: " . $departamento_ponente;
            $msg .= "<br>";
        }
        if (!empty($msg)) {
            $msg .= "No sigue...<br>";
            exit ($msg);
        }

        //----------- resto oficinas OJO: también son cargos
        $a_posibles_dtor_oficinas = [];

        $sQuery = "SELECT c.id_cargo, o.sigla FROM dlp.x_oficinas o JOIN dlp.aux_cargos c USING (id_oficina)
                       WHERE c.director = 't'
                 ORDER BY orden";
        if (($this->oDBT->query($sQuery)) === FALSE) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBT->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $a_posibles_dtor_oficinas[$val] = $clave;
        }

        $sql = "SELECT persistenceid, otrosdepartamentos 
            FROM prodel.registro
            WHERE otrosdepartamentos IS NOT NULL AND trim(otrosdepartamentos) != '';";
        foreach ($this->oDBT->query($sql) as $row) {
            $persistenceid = $row['persistenceid'];
            $departamentos = $row['otrosdepartamentos'];
            $a_oficinas = explode(',', $departamentos);
            $a_id_oficinas = [];
            foreach ($a_oficinas as $sigla) {
                // cambiar las siglas distintas conocidas
                $new_sigla = str_replace($a_siglas_dlp, $a_siglas_tramity, $sigla);
                // ojo a las oficinas que no existen
                if (empty($a_posibles_dtor_oficinas[$new_sigla])) {
                    continue;
                }
                $a_id_oficinas[] = $a_posibles_dtor_oficinas[$new_sigla];
            }
            $postgresArray = array_php2pg($a_id_oficinas);
            $sql_update = "UPDATE prodel.escritos_tmp SET resto_oficinas = '$postgresArray' WHERE persistenceid = $persistenceid";
            $this->oDBT->exec($sql_update);
        }


    }

    /*------------------------- METODES ESCRITOS-APROBACIONES ----------------------------- */

    private function getId_local($dl = 'dlb')
    {
        $sql = "SELECT id_lugar FROM lugares WHERE sigla = '$dl'";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBT->query($sql) as $row) {
            $id = $row['id_lugar'];
        }
        return $id;
    }

    public function destinos_aprobaciones()
    {
        // buscar lugares
        $a_lugares = $this->buscarLugares();

        $a_grupos = [];

        // DESTINOS
        // si solo uno => json_prot_destino
        // varios separados por comas:
        //      a) correspondencia con ctr => id
        //      b) correspondencia con grupo => grupo
        // para poner el id_lugar nuevo
        $sql = "SELECT r.destino, r.persistenceid 
            FROM prodel.escritos_tmp e JOIN prodel.registro r USING (persistenceid)
            ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        foreach ($this->oDBT->query($sql) as $row_destino) {
            $grupos_descripcion = '';
            $aProtDst = [];
            $a_id_grupos = [];
            $a_ids = [];
            $destinos_csv = $row_destino['destino'];
            $persistenceid = $row_destino['persistenceid'];
            $a_destinos = explode(',', $destinos_csv);
            foreach ($a_destinos as $destino) {
                if (!empty($a_lugares[$destino])) {
                    $a_ids[] = $a_lugares[$destino];
                }
                if (!empty($a_grupos[$destino])) {
                    $a_id_grupos[] = $a_grupos[$destino];
                    $grupos_descripcion .= empty($grupos_descripcion) ? '' : ', ';
                    $grupos_descripcion .= $destino;
                }
            }
            // UPDATE SQL
            if (!empty($a_ids)) {
                if (count($a_ids) === 1) {
                    $id_lugar = $a_ids[0];
                    $prot_num_destino = NULL;
                    $prot_any_destino = NULL;
                    $destino_mas = NULL;
                    $oProtDestino = new Protocolo($id_lugar, $prot_num_destino, $prot_any_destino, $destino_mas);
                    $aProtDst[] = $oProtDestino->getProt();
                    $json_prot_destino = (new ConverterJson($aProtDst, FALSE))->toPg(FALSE);

                    $sql = "UPDATE prodel.escritos_tmp SET json_prot_destino = '$json_prot_destino'
                            WHERE persistenceid = $persistenceid ";

                    if ($this->oDBT->exec($sql) === FALSE) {
                        exit ("algo falla");
                    }
                } else {
                    $postgresArray = array_php2pg($a_ids);
                    $sql_update = "UPDATE prodel.escritos_tmp SET destinos = '$postgresArray' WHERE persistenceid = $persistenceid";
                    $this->oDBT->exec($sql_update);
                }
            }
            if (!empty($a_id_grupos)) {
                $postgresArray = array_php2pg($a_id_grupos);
                $sql_update = "UPDATE prodel.escritos_tmp 
                                    SET id_grupos = '$postgresArray', descripcion = '$grupos_descripcion'
                                    WHERE persistenceid = $persistenceid";
                $this->oDBT->exec($sql_update);
            }
        }
    }



    public function referencias_aprobaciones()
    {
        // buscar lugares
        $a_lugares = $this->buscarLugares();


        // Seleccionar ref de entradas
        $sql = "SELECT id_escrito, referencias FROM  prodel.escritos_tmp 
                WHERE referencias IS NOT NULL AND referencias != '' ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $msg = "";
        foreach ($this->oDBT->query($sql) as $row_ref) {
            $id_escrito = $row_ref['id_escrito'];
            $ref_txt = $row_ref['referencias'];
            $ref_txt = preg_replace('#,(\s*\w+)#', ';\1', $ref_txt);
            $a_ref = explode(';', $ref_txt);
            $aProtRef = [];
            foreach ($a_ref as $ref) {
                // agdmontagut 12/22      dlb 3/22
                $pattern = "/^\s*([^\*\p{N}]+)*((\*|\s)+\d+\/\d{2})*,?(\s*[^\p{N}]*)\s*$/u";
                $coincide = preg_match($pattern, $ref, $matches);
                if ($coincide === 1) {
                    // quitar los '*' si tiene
                    $destino = trim($matches[1]);
                    $destino_prot = empty($matches[2]) ? '' : $matches[2];
                    $destino_prot = str_replace('*', '', $destino_prot);
                    $destino_mas = empty($matches[3]) ? '' : $matches[3];

                    if (empty($a_lugares[$destino])) {
                        $msg .= _("No sé encuentra: '" . $destino . "' en la tabla de lugares");
                        $msg .= "<br>";
                        $msg .= _("Para la referencia:") . " " . $ref;
                        $msg .= "<br>";
                        $msg .= _("id_escrito:") . " " . $id_escrito;
                        $msg .= "<br>";
                        continue 2;
                    } else {
                        $id_lugar = $a_lugares[$destino];

                        if (!empty($destino_prot)) {
                            $a_destino = explode('/', $destino_prot);
                            $prot_num_destino = empty($a_destino[0]) ? '' : trim($a_destino[0]);
                            $prot_any_destino = empty($a_destino[1]) ? '' : trim($a_destino[1]);
                        } else {
                            $prot_num_destino = '';
                            $prot_any_destino = '';
                        }
                        $oProtDestino = new Protocolo($id_lugar, $prot_num_destino, $prot_any_destino, $destino_mas);
                        $aProtRef[] = $oProtDestino->getProt();
                    }
                }
            }
            $json_prot_ref = (new ConverterJson($aProtRef, FALSE))->toPg(FALSE);

            $sql = "UPDATE prodel.escritos_tmp SET json_prot_ref = '$json_prot_ref'
                WHERE id_escrito = $id_escrito ";

            if ($this->oDBT->exec($sql) === FALSE) {
                exit ("algo falla escritos ref.");
            }
        }

        if (!empty($msg)) {
            exit ($msg);
        }
    }

    public function aprobaciones_anexos()
    {
        // crear tabla
        $sql = "CREATE TABLE IF NOT EXISTS prodel.escritos_anexos_tmp (LIKE dlp.escrito_adjuntos INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna uuid, persistenceid
        $sql = "ALTER TABLE prodel.escritos_anexos_tmp ADD COLUMN IF NOT EXISTS referencias character varying(255),
        ADD COLUMN IF NOT EXISTS uuid character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint,
        ADD COLUMN IF NOT EXISTS id_escrito int,
        ADD COLUMN IF NOT EXISTS id_lugar_local integer,
        ADD COLUMN IF NOT EXISTS json_prot_local jsonb;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE prodel.escritos_anexos_tmp RESTART IDENTITY";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar. También el propio escrito, que entra como adjunto. Quito: AND esanexo='t'
        $sql = "INSERT INTO prodel.escritos_anexos_tmp (id_escrito, nom, adjunto, tipo_doc, uuid, persistenceid )
                (SELECT 1, nombdocumento||tipomime, 'xxxx', 3, uuid, persistenceid 
                FROM prodel.registro WHERE origen='dlp' OR destino='Rectorado' ) ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar id_lugar_origen
        $sql = "UPDATE prodel.escritos_anexos_tmp
            SET id_lugar_local = lugares.id_lugar
            FROM prodel.registro
            JOIN public.lugares ON registro.origen = lugares.sigla
            WHERE registro.persistenceid = escritos_anexos_tmp.persistenceid";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar json_prot_local
        // pasar l'any a dues xifres:
        $sql = "UPDATE prodel.escritos_anexos_tmp e                                                                  
                SET json_prot_local = (SELECT json_build_object('any', substring(r.anno::text from '..$'), 'mas', '', 'num', r.numprotocolo, 'id_lugar', e.id_lugar_local)
                FROM prodel.registro r WHERE e.persistenceid = r.persistenceid) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar id_escrito
        $sql = "UPDATE prodel.escritos_anexos_tmp
            SET id_escrito = prodel.escritos_tmp.id_escrito
            FROM prodel.escritos_tmp
            WHERE escritos_tmp.json_prot_local = escritos_anexos_tmp.json_prot_local";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

    /*------------------------- METODES ENTRADAS ----------------------------- */

    /**
     * realmente es para todos los docs  exportados del openkm
     * @return false|void
     */
    public function docs_entradas()
    {
        // leer directorio: ficheros .okm
        // buscar uuid y esanexo en json
        // introducir en DB
        $directorio = '/home/dani/dlp/exrepository';
        $log_file = $directorio . '/errores.log';
        $err_txt_tot = '';
        $err_txt = '';

        $a_scan = scandir($directorio);
        $a_files = array_diff($a_scan, ['.', '..']);

        $pattern = "/(.*)\.okm/";
        $i = 0;
        foreach ($a_files as $filename) {
            $i++;
            set_time_limit(0);
            ob_start();
            if (!empty($err_txt)) {
                $err_txt_tot .= $err_txt;
                echo "$err_txt<br>";
                $err_txt = '';
            } else {
                $count = $i % 10;
                for($c = 0 ; $c < $count; $c++) {
                    echo "x";
                }
                echo "<br>";
            }
            ob_end_flush();
            $matches = [];
            if (preg_match($pattern, $filename, $matches)) {
                $fullfilename = $directorio . '/' . $filename;
                $json_object = file_get_contents($fullfilename);
                $json_data = json_decode($json_object, true);

                $uuid = $json_data['uuid'];
                $name = $json_data['name'];
                $path_parts = pathinfo($name);
                $extension_filename = $path_parts['extension'];

                $destino = '';
                $dst_prot_num = '';
                $dst_prot_any = '';
                $esanexo = FALSE;
                $origen = '';
                $org_prot_num = '';
                $org_prot_any = '';

                $properties = $json_data['propertyGroups'][0]['properties'];
                foreach ($properties as $property) {
                    $property_name = $property['name'];
                    $property_val = $property['value'] ?? '';

                    switch ($property_name) {
                        case 'okp:registro.destino':
                            $destino = $property_val;
                            break;
                        case 'okp:registro.registro':
                            $dst_prot_num = $property_val;
                            break;
                        case 'okp:registro.annoregistro':
                            $dst_prot_any = any_2($property_val);
                            break;
                        case 'okp:registro.esanexo':
                            $esanexo = is_true($property_val);
                            break;
                        case 'okp:registro.origen':
                            $origen = $property_val;
                            break;
                        case 'okp:registro.numprotocolo':
                            $org_prot_num = $property_val;
                            break;
                        case 'okp:registro.anno':
                            $org_prot_any = any_2($property_val);
                            break;
                        default:
                            break;
                    }
                }
                /*
                echo "uuid: $uuid<br>";
                echo "name: $name<br>";
                echo "esanexo: $esanexo<br>";
                echo "origen: $origen<br>";
                */

                // por el uuid buscar que és.
                // entradas:
                if ($destino === 'dlp' || $destino === 'Cancilleria') {
                    // generar nombre file
                    $str_anexo = '';
                    if ($esanexo) {
                        $str_anexo = 'a1';
                    }
                    $nombre_doc = $origen . $org_prot_num . $str_anexo . '_' . $org_prot_any . '.' . $extension_filename;

                    $sql = "SELECT id_item FROM prodel.entradas_anexos_tmp WHERE uuid = '$uuid' ";
                    if (($oDblSt = $this->oDBT->query($sql)) === FALSE) {
                        $err_txt .= "Error select entrada adjuntos\n";
                        continue;
                    }
                    $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
                    if ($aDades === FALSE) {
                        $err_txt .= "No se encuentra el anexo para entrada con uuid: $uuid\n";
                        continue;
                    }
                    $id_item = $aDades['id_item'];

                    $nombre_fichero = $directorio . '/' . $name;
                    if (!file_exists($nombre_fichero)) {
                        $err_txt .= "No se existe el fichero: $nombre_fichero\n";
                        continue;
                    }
                    $fp = fopen($nombre_fichero, 'rb');
                    $contenido_doc = fread($fp, filesize($nombre_fichero));
                    // Escape the binary data
                    $adjunto_escaped = bin2hex($contenido_doc);

                    $update = "
					nom                   = :nom,
					adjunto               = :adjunto";
                    if (($oDblSt = $this->oDBT->prepare("UPDATE prodel.entradas_anexos_tmp SET $update WHERE id_item='$id_item'")) === FALSE) {
                        $err_txt .= "Error prepare update entrada adjuntos\n";
                        continue;
                    } else {
                        $oDblSt->bindParam(1, $nombre_doc, PDO::PARAM_STR);
                        $oDblSt->bindParam(2, $adjunto_escaped, PDO::PARAM_STR);
                        try {
                            $oDblSt->execute();
                        } catch (PDOException $e) {
                            $err_txt .= $e->errorInfo[2] . "\n";
                            continue;
                        }
                    }

                }

                // salidas
                if ($origen === 'dlp' || $destino === 'Rectorado') {
                    // generar nombre file
                    if ($origen === 'dlp') {
                        $matches1 = [];
                        $pattern1 = "/^\d+-dlp-(.*)-/";
                        if (preg_match($pattern1, $filename, $matches1)) {
                            $org_prot_num = $matches1[1];
                        }
                        $nombre_doc = $origen . $org_prot_num . '_' . $org_prot_any . '.' . $extension_filename;
                    }
                    if ($destino === 'Rectorado') {
                        $str_anexo = '';
                        if ($esanexo) {
                            $str_anexo = 'a1';
                        }
                        $nombre_doc = $origen . $org_prot_num . $str_anexo . '_' . $org_prot_any . '.' . $extension_filename;
                    }

                    $sql = "SELECT id_item FROM prodel.escritos_anexos_tmp WHERE uuid = '$uuid' ";
                    if (($oDblSt = $this->oDBT->query($sql)) === FALSE) {
                        $err_txt .= "Error select escrito adjunto\n";
                        continue;
                    }
                    $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
                    if ($aDades === FALSE) {
                        $err_txt .= "No se encuentra el anexo para escrito con uuid: $uuid\n";
                        continue;
                    }
                    $id_item = $aDades['id_item'];

                    $nombre_fichero = $directorio . '/' . $name;
                    if (!file_exists($nombre_fichero)) {
                        $err_txt .= "No se existe el fichero: $nombre_fichero\n";
                        continue;
                    }
                    $fp = fopen($nombre_fichero, 'rb');
                    $contenido_doc = fread($fp, filesize($nombre_fichero));
                    // Escape the binary data
                    $adjunto_escaped = bin2hex($contenido_doc);

                    $update = "
					nom                   = :nom,
					adjunto               = :adjunto";
                    if (($oDblSt = $this->oDBT->prepare("UPDATE prodel.escritos_anexos_tmp SET $update WHERE id_item='$id_item'")) === FALSE) {
                        $err_txt .= "Error prepare update escrito adjunto\n";
                        continue;
                    } else {
                        $oDblSt->bindParam(1, $nombre_doc, PDO::PARAM_STR);
                        $oDblSt->bindParam(2, $adjunto_escaped, PDO::PARAM_STR);
                        try {
                            $oDblSt->execute();
                        } catch (PDOException $e) {
                            $err_txt .= $e->errorInfo[2] . "\n";
                            continue;
                        }
                    }

                }
                // si todo va bien borrar los dos archivos: el doc y el okm
                // si hay algún error se escribe arriba, porque hay el continue.
                unlink($fullfilename); // okm
                unlink($nombre_fichero); // el doc
            }
        }
        // escribir todos los errores en el log
        if (!empty($err_txt_tot)) {
            file_put_contents($log_file, $err_txt_tot);
        }

    }

    public
    function entradas_ref()
    {
        // buscar lugares
        $a_lugares = $this->buscarLugares();


        // Seleccionar ref de entradas
        $sql = "SELECT id_entrada, referencias FROM  prodel.entradas_tmp 
                WHERE referencias IS NOT NULL AND referencias != '' ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $msg = "";
        foreach ($this->oDBT->query($sql) as $row_ref) {
            $id_entrada = $row_ref['id_entrada'];
            $ref_txt = $row_ref['referencias'];
            $ref_txt = preg_replace('#,(\s*\w+)#', ';\1', $ref_txt);
            $a_ref = explode(';', $ref_txt);
            $aProtRef = [];
            foreach ($a_ref as $ref) {
                // agdmontagut 12/22      dlb 3/22
                $pattern = "/^\s*([^\*\p{N}]+)*((\*|\s)+\d+\/\d{2})*,?(\s*[^\p{N}]*)\s*$/u";
                $coincide = preg_match($pattern, $ref, $matches);
                if ($coincide === 1) {
                    // quitar los '*' si tiene
                    $destino = trim($matches[1]);
                    $destino_prot = empty($matches[2]) ? '' : $matches[2];
                    $destino_prot = str_replace('*', '', $destino_prot);
                    $destino_mas = empty($matches[3]) ? '' : $matches[3];

                    if (empty($a_lugares[$destino])) {
                        $msg .= _("No sé encuentra: '" . $destino . "' en la tabla de lugares");
                        $msg .= "<br>";
                        $msg .= _("Para la referencia:") . " " . $ref;
                        $msg .= "<br>";
                        $msg .= _("id_entrada:") . " " . $id_entrada;
                        $msg .= "<br>";
                        continue 2;
                    } else {
                        $id_lugar = $a_lugares[$destino];

                        if (!empty($destino_prot)) {
                            $a_destino = explode('/', $destino_prot);
                            $prot_num_destino = empty($a_destino[0]) ? '' : trim($a_destino[0]);
                            $prot_any_destino = empty($a_destino[1]) ? '' : trim($a_destino[1]);
                        } else {
                            $prot_num_destino = '';
                            $prot_any_destino = '';
                        }
                        $oProtDestino = new Protocolo($id_lugar, $prot_num_destino, $prot_any_destino, $destino_mas);
                        $aProtRef[] = $oProtDestino->getProt();
                    }
                }
            }
            $json_prot_ref = (new ConverterJson($aProtRef, FALSE))->toPg(FALSE);

            $sql = "UPDATE prodel.entradas_tmp SET json_prot_ref = '$json_prot_ref'
                WHERE id_entrada = $id_entrada ";

            if ($this->oDBT->exec($sql) === FALSE) {
                exit ("algo falla");
            }
        }

        if (!empty($msg)) {
            exit ($msg);
        }
    }


    public
    function entradas_anexos()
    {
        // crear tabla
        $sql = "CREATE TABLE IF NOT EXISTS prodel.entradas_anexos_tmp (LIKE dlp.entrada_adjuntos INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna uuid, persistenceid
        $sql = "ALTER TABLE prodel.entradas_anexos_tmp ADD COLUMN IF NOT EXISTS referencias character varying(255),
        ADD COLUMN IF NOT EXISTS uuid character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint,
        ADD COLUMN IF NOT EXISTS id_entrada int,
        ADD COLUMN IF NOT EXISTS id_lugar_origen integer,
        ADD COLUMN IF NOT EXISTS json_prot_origen jsonb;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE prodel.entradas_anexos_tmp RESTART IDENTITY";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar. Como el escrito se va a poner como un adjunto, hay que introducir todos, no
        // solo las: AND esanexo='t' (como hacía inicialmente).
        $sql = "INSERT INTO prodel.entradas_anexos_tmp (id_entrada, nom, adjunto, uuid, persistenceid )
                (SELECT 1, nombdocumento||tipomime, 'xxxx', uuid, persistenceid 
                FROM prodel.registro WHERE destino='dlp' OR destino='Cancillería' ) ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar id_lugar_origen
        $sql = "UPDATE prodel.entradas_anexos_tmp
            SET id_lugar_origen = lugares.id_lugar
            FROM prodel.registro
            JOIN public.lugares ON registro.origen = lugares.sigla
            WHERE registro.persistenceid = entradas_anexos_tmp.persistenceid";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar json_prot_origen
        // pasar l'any a dues xifres:
        $sql = "UPDATE prodel.entradas_anexos_tmp e                                                                  
                SET json_prot_origen = (SELECT json_build_object('any', substring(r.anno::text from '..$'), 'mas', '', 'num', r.numprotocolo, 'id_lugar', e.id_lugar_origen)
                FROM prodel.registro r WHERE e.persistenceid = r.persistenceid) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar id_entrada
        $sql = "UPDATE prodel.entradas_anexos_tmp
            SET id_entrada = prodel.entradas_tmp.id_entrada
            FROM prodel.entradas_tmp
            WHERE entradas_tmp.json_prot_origen = entradas_anexos_tmp.json_prot_origen";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }


    }

    public
    function entradas_cancilleria()
    {
        // sobreescribir el detalle:
        // '['+substring (prodel.registro.numregistro,2,4)+’/’+substring(prodel.registro.anno,3,2) +’]’+ asuntooficinas
        // (Ref. Reg. 545/21)

        $sql = "UPDATE prodel.entradas_tmp e
            SET asunto = r.asunto || ' (Ref. Reg. '||substring (r.numregistro::text,2,4)||'/'||substring(r.anno::text,3,2)||')'
            , asunto_entrada = asunto_entrada || ' (Ref. Reg. '||substring (r.numregistro::text,2,4)||'/'||substring(r.anno::text,3,2)||')'
            FROM prodel.registro r
            WHERE r.persistenceid = e.persistenceid
            AND r.destino='Cancillería' AND r.esanexo='f'";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

    }

    public
    function copiar_entradas()
    {
        // crear tabla
        $sql = "CREATE TABLE IF NOT EXISTS prodel.entradas_tmp (LIKE dlp.entradas INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna referencias y uuid, persistenceid
        $sql = "ALTER TABLE prodel.entradas_tmp ADD COLUMN IF NOT EXISTS referencias character varying(255),
    ADD COLUMN IF NOT EXISTS uuid character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // añadir columna id_lugar_origen
        $sql = "ALTER TABLE prodel.entradas_tmp ADD COLUMN IF NOT EXISTS id_lugar_origen integer;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE prodel.entradas_tmp RESTART IDENTITY";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar (también las de cancillería)
        // estado
        /*
         - Ingresa (secretaría introduce los datos de la entrada)
         - Admitir (vcd los mira y da el ok)
         - Asignar (secretaría añade datos tipo: ponente... Puede que no se haya hecho el paso de ingresar)
         - Aceptar (scdl ok)
         - Oficinas (Las oficinas puede ver lo suyo)
         - Archivado (Ya no sale en las listas de la oficina)
         - Enviado cr (Cuando se han enviado los bypass)
        public const ESTADO_INGRESADO = 1;
        public const ESTADO_ADMITIDO = 2;
        public const ESTADO_ASIGNADO = 3;
        public const ESTADO_ACEPTADO = 4;
        //const ESTADO_OFICINAS           = 5;
        public const ESTADO_ARCHIVADO = 6;
        public const ESTADO_ENVIADO_CR = 10;
         */
        $sql = "INSERT INTO prodel.entradas_tmp (modo_entrada, f_entrada, asunto_entrada, asunto, detalle,
                         categoria, visibilidad, f_contestar, bypass, estado, referencias, uuid, persistenceid )
                (SELECT 5, fechacreacion, asunto, asunto, asuntooficinas, 
                        2, 1, fechatopecontestacion, 'f', 6, referencias, uuid, persistenceid 
                FROM prodel.registro WHERE (destino='dlp' OR destino='Cancillería') AND esanexo='f') ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // rellenar id_lugar_origen
        $sql = "UPDATE prodel.entradas_tmp
            SET id_lugar_origen = lugares.id_lugar
            FROM prodel.registro
            JOIN public.lugares ON registro.origen = lugares.sigla
            WHERE registro.persistenceid = entradas_tmp.persistenceid";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // comprobar que el id_lugar existe:
        $sql = "SELECT DISTINCT r.origen FROM prodel.entradas_tmp e JOIN prodel.registro r USING (persistenceid) WHERE id_lugar_origen IS NULL";
        $msg = '';
        foreach ($this->oDBT->query($sql) as $row_error) {
            $origen = $row_error['origen'];

            $msg .= "No se encuentra '$origen' en la tabla de lugares";
            $msg .= "<br>";
        }
        if (!empty($msg)) {
            exit ($msg);
        }

        // rellenar json_prot_origen
        // pasar l'any a dues xifres:
        $sql = "UPDATE prodel.entradas_tmp e                                                                  
                SET json_prot_origen = (SELECT json_build_object('any', substring(r.anno::text from '..$'), 'mas', '', 'num', r.numprotocolo, 'id_lugar', e.id_lugar_origen)
                FROM prodel.registro r WHERE e.persistenceid = r.persistenceid) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // LAS ENTRADAS VAN A OFICINAS
        // cambiar nombres de oficinas conocidos:
        // OJO!!! Para que también cambie en el reto oficinas
        $a_siglas_tramity = ['ofsvsm', 'des', 'agd'];
        $a_siglas_dlp = ['osvsm', 'dre', 'dagd'];

        $sql = "UPDATE prodel.registro SET departamentoponente = 'ofsvsm' WHERE departamentoponente = 'osvsm'";
        $this->oDBT->exec($sql);
        $sql = "UPDATE prodel.registro SET departamentoponente = 'des' WHERE departamentoponente = 'dre'";
        $this->oDBT->exec($sql);
        $sql = "UPDATE prodel.registro SET departamentoponente = 'agd' WHERE departamentoponente = 'dagd'";
        $this->oDBT->exec($sql);

        // comprobar que existen todas las oficinas:
        $sql = "SELECT DISTINCT departamentoponente
                FROM prodel.registro LEFT JOIN dlp.x_oficinas ON (registro.departamentoponente = x_oficinas.sigla)
                WHERE x_oficinas.sigla IS NULL;";
        $a_oficinas = [];
        foreach ($this->oDBT->query($sql) as $row_oficina) {
            $a_oficinas[] = $row_oficina['departamentoponente'];
        }
        if (!empty($a_oficinas)) {
            echo "Oficinas no definidas en tramity:";
            echo "<pre>";
            print_r($a_oficinas);
            echo "<pre>";
        }
        // Ponente
        $sql = "UPDATE prodel.entradas_tmp
            SET ponente = dlp.x_oficinas.id_oficina
            FROM prodel.registro
            JOIN dlp.x_oficinas ON registro.departamentoponente = x_oficinas.sigla
            WHERE registro.persistenceid = entradas_tmp.persistenceid";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // resto oficinas
        $a_posibles_oficinas = [];

        $sQuery = "SELECT id_oficina, sigla FROM dlp.x_oficinas
                 ORDER BY orden";
        if (($this->oDBT->query($sQuery)) === FALSE) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBT->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $a_posibles_oficinas[$val] = $clave;
        }

        $sql = "SELECT persistenceid, otrosdepartamentos 
            FROM prodel.registro
            WHERE otrosdepartamentos IS NOT NULL AND trim(otrosdepartamentos) != '';";
        foreach ($this->oDBT->query($sql) as $row) {
            $persistenceid = $row['persistenceid'];
            $departamentos = $row['otrosdepartamentos'];
            $a_oficinas = explode(',', $departamentos);
            $a_id_oficinas = [];
            foreach ($a_oficinas as $sigla) {
                // cambiar las siglas distintas conocidas
                $new_sigla = str_replace($a_siglas_dlp, $a_siglas_tramity, $sigla);
                // ojo a las oficinas que no existen
                if (empty($a_posibles_oficinas[$new_sigla])) {
                    continue;
                }
                $a_id_oficinas[] = $a_posibles_oficinas[$new_sigla];
            }
            $postgresArray = array_php2pg($a_id_oficinas);
            $sql_update = "UPDATE prodel.entradas_tmp SET resto_oficinas = '$postgresArray' WHERE persistenceid = $persistenceid";
            $this->oDBT->exec($sql_update);
        }


    }

    public
    function crear_equivalencias_lugares()
    {
        // crear tabla  lugares_tmp
        // con el like, la sequencia del id_lugar sigue siendo la misma,
        // y empieza a contar desde donde está el public. Al pasar a producción no hay que modificar la secuencia
        $sql = "CREATE TABLE IF NOT EXISTS prodel.lugares_tmp (LIKE public.lugares INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna autorizacion y codigo, persistenceid
        $sql = "ALTER TABLE prodel.lugares_tmp ADD COLUMN IF NOT EXISTS autorizacion character varying(255),
    ADD COLUMN IF NOT EXISTS codigo character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $sql = "TRUNCATE TABLE prodel.lugares_tmp RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // actualizar la secuencia
        $sql_update_sequence = "SELECT setval('lugares_id_lugar_seq', (SELECT MAX(id_lugar) FROM public.lugares));";
        $this->oDBT->exec($sql_update_sequence);

        // copiar los ctr
        $sql = "INSERT INTO prodel.lugares_tmp (sigla, dl, region, nombre, autorizacion, codigo, persistenceid)
                SELECT nombre, 'dlp', 'H', nombre, autorizacion, codigo, persistenceid 
                FROM prodel.lugares 
                WHERE 
                tipo != 'ctr-grupo' and 
                tipo != 'cr' and 
                substring(tipo,1,2) !=  'dl' and 
                tipo!='com' ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // update tipo ctr
        $sql = "UPDATE prodel.lugares_tmp SET tipo_ctr = regexp_replace(p.tipo, 'ctr-(.*)','\\1')
                    FROM prodel.lugares p 
                    WHERE prodel.lugares_tmp.persistenceid = p.persistenceid 
                    AND p.tipo IN ('ctr-am','ctr-aj','ctr-a', 'ctr-nm', 'ctr-nj', 'ctr-sg', 'ctr-sss+', 'ctr-oc' )";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // grupos
        // crear tabla  lugares_grupos_tmp
        $sql = "CREATE TABLE IF NOT EXISTS prodel.lugares_grupos_tmp (LIKE dlp.lugares_grupos INCLUDING ALL)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // añadir columna autorizacion y codigo
        $sql = "ALTER TABLE prodel.lugares_grupos_tmp ADD COLUMN IF NOT EXISTS autorizacion character varying(255),
    ADD COLUMN IF NOT EXISTS codigo character varying(255),
        ADD COLUMN IF NOT EXISTS persistenceid bigint;";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // copiar los nombres de grupos
        $sql = "INSERT INTO prodel.lugares_grupos_tmp ( descripcion, autorizacion, codigo, persistenceid)
                SELECT nombre, autorizacion, codigo, persistenceid FROM prodel.lugares WHERE 
                tipo ='ctr-grupo' ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // añadir cada centro a su grupo
        $sql = "SELECT id_grupo, codigo FROM prodel.lugares_grupos_tmp ORDER BY codigo";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBT->query($sql) as $row_grupo) {
            $id_grupo = $row_grupo['id_grupo'];
            $codigo = $row_grupo['codigo'];

            $sql_buscar = "SELECT id_lugar FROM prodel.lugares_tmp WHERE substring(codigo,position('1' in '$codigo'),1)= '1';";
            $a_lugares = [];
            foreach ($this->oDBT->query($sql_buscar) as $row_ctr) {
                $a_lugares[] = $row_ctr['id_lugar'];
            }
            $postgresArray = array_php2pg($a_lugares);
            $sql_update = "UPDATE prodel.lugares_grupos_tmp SET miembros = '$postgresArray' WHERE id_grupo = $id_grupo";
            $this->oDBT->exec($sql_update);
        }
    }

    public
    function pasar_lugares_a_produccion()
    {
        // añadir a producción los de la tabla temporal:
        $sql = "INSERT INTO public.lugares SELECT id_lugar, sigla, dl, region, nombre, tipo_ctr, modo_envio, pub_key, e_mail, anulado, plataforma
                FROM prodel.lugares_tmp ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        /* NO hace falta
        // actualizar la secuencia
        $sql_update_sequence = "SELECT setval('lugares_id_lugar_seq', (SELECT MAX(id_lugar) FROM public.lugares));";
        $this->oDBT->exec($sql_update_sequence);
        */
    }

    /**
     * @return array
     */
    public
    function buscarLugares(): array
    {
        $sql = "SELECT sigla, id_lugar 
            FROM public.lugares 
            ORDER BY sigla";

        $a_lugares = [];
        foreach ($this->oDBT->query($sql) as $row_lugares) {
            $sigla = $row_lugares['sigla'];
            $id_lugar = $row_lugares['id_lugar'];
            $a_lugares[$sigla] = $id_lugar;
        }
        return $a_lugares;
    }

    public
    function escritos_cancilleria()
    {
        // sobreescribir el detalle:
        // '['+substring (prodel.registro.numregistro,2,4)+’/’+substring(prodel.registro.anno,3,2) +’]’+ asuntooficinas
        // (Ref. Reg. 545/21)

        $sql = "UPDATE prodel.escritos_tmp e
            SET asunto = r.asunto || ' (Ref. Reg. '||substring (r.numregistro::text,2,4)||'/'||substring(r.anno::text,3,2)||')'
            FROM prodel.registro r
            WHERE r.persistenceid = e.persistenceid
            AND r.destino='Cancillería' AND r.esanexo='f'";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

    }

    public
    function pasar_a_dlp()
    {
        // lugares grupos
        $sql = "TRUNCATE TABLE dlp.lugares_grupos RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "INSERT INTO dlp.lugares_grupos (id_grupo, descripcion, miembros )
                (SELECT id_grupo, descripcion, miembros
                FROM prodel.lugares_grupos_tmp ORDER BY id_grupo) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // ajustar secuencia:
        $sql_update_sequence = "SELECT setval('dlp.lugares_grupos_id_grupo_seq', (SELECT MAX(id_grupo) FROM dlp.lugares_grupos));";
        $this->oDBT->exec($sql_update_sequence);


        // entradas:
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE dlp.entradas RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "INSERT INTO dlp.entradas (id_entrada, id_entrada_compartida, modo_entrada, json_prot_origen,
                    asunto_entrada, json_prot_ref, ponente, resto_oficinas, asunto, f_entrada, detalle, categoria,
                    visibilidad, f_contestar, bypass, estado, anulado, encargado, json_visto
                    )
                (SELECT id_entrada, id_entrada_compartida, modo_entrada, json_prot_origen,
                    asunto_entrada, json_prot_ref, ponente, resto_oficinas, asunto, f_entrada, detalle, categoria,
                    visibilidad, f_contestar, bypass, estado, anulado, encargado, json_visto
                FROM prodel.entradas_tmp ORDER BY id_entrada) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // ajustar secuencia:
        $sql_update_sequence = "SELECT setval('dlp.entradas_id_entrada_seq', (SELECT MAX(id_entrada) FROM dlp.entradas));";
        $this->oDBT->exec($sql_update_sequence);

        // escritos:
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE dlp.escritos RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "INSERT INTO dlp.escritos (id_escrito, json_prot_local, json_prot_destino, json_prot_ref, id_grupos, destinos, 
                      asunto, detalle, creador, resto_oficinas, comentarios, f_aprobacion, f_escrito, f_contestar, categoria,
                      visibilidad, visibilidad_dst, accion, modo_envio, f_salida, ok, tipo_doc, anulado, descripcion
                       )
                (SELECT id_escrito, json_prot_local, json_prot_destino, json_prot_ref, id_grupos, destinos,
                asunto, detalle, creador, resto_oficinas, comentarios, f_aprobacion, f_escrito, f_contestar, categoria,
                visibilidad, visibilidad_dst, accion, modo_envio, f_salida, ok, tipo_doc, anulado, descripcion
                FROM prodel.escritos_tmp ORDER BY id_escrito) ";

        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // ajustar secuencia:
        $sql_update_sequence = "SELECT setval('dlp.escritos_id_escrito_seq', (SELECT MAX(id_escrito) FROM dlp.escritos));";
        $this->oDBT->exec($sql_update_sequence);
    }

    public
    function pasar_a_dlp_anexos()
    {
        // anexos:
        // entradas
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE dlp.entrada_adjuntos RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "INSERT INTO dlp.entrada_adjuntos (id_entrada, nom, adjunto)
                (SELECT id_entrada, nom, adjunto
                FROM prodel.entradas_anexos_tmp ORDER BY id_item)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // ajustar secuencia:
        $sql_update_sequence = "SELECT setval('dlp.entrada_adjuntos_id_item_seq', (SELECT MAX(id_item) FROM dlp.entrada_adjuntos));";
        $this->oDBT->exec($sql_update_sequence);

        //salidas
        // vaciar ya debe existir
        $sql = "TRUNCATE TABLE dlp.escrito_adjuntos RESTART IDENTITY CASCADE";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "INSERT INTO dlp.escrito_adjuntos (id_escrito, nom, adjunto, tipo_doc)
                (SELECT id_escrito, nom, adjunto, tipo_doc
                FROM prodel.escritos_anexos_tmp ORDER BY id_item)";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // ajustar secuencia:
        $sql_update_sequence = "SELECT setval('dlp.escrito_adjuntos_id_item_seq', (SELECT MAX(id_item) FROM dlp.escrito_adjuntos));";
        $this->oDBT->exec($sql_update_sequence);

    }

}