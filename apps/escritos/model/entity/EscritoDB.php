<?php

namespace escritos\model\entity;

use core;
use core\ConverterJson;
use JsonException;
use PDO;
use PDOException;
use stdClass;
use web;
use function core\array_pgInteger2php;
use function core\array_php2pg;
use function core\is_true;

/**
 * Fitxer amb la Classe que accedeix a la taula escritos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat escritos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class EscritoDB extends core\ClasePropiedades
{

    // ok
    public const OK_NO = 1;
    public const OK_OFICINA = 2;
    public const OK_SECRETARIA = 3;

    // visibilidad
    // USAR LAS DE ENTRADADB


    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de EscritoDB
     *
     * @var array
     */
    protected $aPrimary_key;

    /**
     * aDades de EscritoDB
     *
     * @var array
     */
    protected $aDades;

    /**
     * bLoaded de EscritoDB
     *
     * @var boolean
     */
    protected $bLoaded = FALSE;

    /**
     * Id_schema de EscritoDB
     *
     * @var integer
     */
    protected $iid_schema;

    /**
     * Id_escrito de EscritoDB
     *
     * @var integer
     */
    protected $iid_escrito;
    /**
     * Json_prot_local de EscritoDB
     *
     * @var string|null
     */
    protected ?string $json_prot_local = null;
    /**
     * Json_prot_destino de EscritoDB
     *
     * @var string|null
     */
    protected ?string $json_prot_destino = null;
    /**
     * Json_prot_ref de EscritoDB
     *
     * @var string|null
     */
    protected ?string $json_prot_ref = null;
    /**
     * Id_grupos de EscritoDB
     *
     * @var string|null
     */
    protected ?string $a_id_grupos = null;
    /**
     * Destinos de EscritoDB
     *
     * @var string|null
     */
    protected ?string $a_destinos = null;
    /**
     * Asunto de EscritoDB
     *
     * @var string
     */
    protected $sasunto;
    /**
     * Detalle de EscritoDB
     *
     * @var string|null
     */
    protected $sdetalle = null;
    /**
     * Creador de EscritoDB
     *
     * @var integer|null
     */
    protected $icreador = null;
    /**
     * Resto_oficinas de EscritoDB
     *
     * @var string|null
     */
    protected $a_resto_oficinas = null;
    /**
     * Comentarios de EscritoDB
     *
     * @var string|null
     */
    protected $scomentarios = null;
    /**
     * F_aprobacion de EscritoDB
     *
     * @var web\DateTimeLocal|null
     */
    protected $df_aprobacion = null;
    /**
     * F_escrito de EscritoDB
     *
     * @var web\DateTimeLocal|null
     */
    protected $df_escrito = null;
    /**
     * F_contestar de EscritoDB
     *
     * @var web\DateTimeLocal|null
     */
    protected $df_contestar = null;
    /**
     * Categoria de EscritoDB
     *
     * @var integer|null
     */
    protected $icategoria = null;
    /**
     * Visibilidad de EscritoDB
     *
     * @var integer|null
     */
    protected $ivisibilidad = null;
    /**
     * Visibilidad para destino de EscritoDB
     *
     * @var integer|null
     */
    protected $ivisibilidad_dst = null;
    /**
     * Accion de EscritoDB
     *
     * @var integer
     */
    protected $iaccion;
    /**
     * Modo_envio de EscritoDB
     *
     * @var integer
     */
    protected $imodo_envio;
    /**
     * F_salida de EscritoDB
     *
     * @var web\DateTimeLocal
     */
    protected $df_salida = null;
    /**
     * Ok de EscritoDB
     *
     * @var integer|null
     */
    protected $iok = null;
    /**
     * Tipo_doc de EscritoDB
     *
     * @var integer|null
     */
    protected $itipo_doc = null;
    /**
     * anulado de EscritoDB
     *
     * @var boolean|null
     */
    protected $banulado = null;
    /**
     * descripcion de EscritoDB
     *
     * @var string|null
     */
    protected $sdescripcion = null;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de EscritoDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EscritoDB
     *
     * @var string
     */
    protected $sNomTabla;

    protected $clone = FALSE;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_escrito
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_escrito') && $val_id !== '') {
                    $this->iid_escrito = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_escrito = (int)$a_id;
                $this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('escritos');
    }

    public function __clone()
    {
        $this->clone = TRUE;
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi es fa el update.
     *
     */
    public function DBGuardar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if ($this->DBCargar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['json_prot_local'] = $this->json_prot_local;
        $aDades['json_prot_destino'] = $this->json_prot_destino;
        $aDades['json_prot_ref'] = $this->json_prot_ref;
        $aDades['id_grupos'] = $this->a_id_grupos;
        $aDades['destinos'] = $this->a_destinos;
        $aDades['asunto'] = $this->sasunto;
        $aDades['detalle'] = $this->sdetalle;
        $aDades['creador'] = $this->icreador;
        $aDades['resto_oficinas'] = $this->a_resto_oficinas;
        $aDades['comentarios'] = $this->scomentarios;
        $aDades['f_aprobacion'] = $this->df_aprobacion;
        $aDades['f_escrito'] = $this->df_escrito;
        $aDades['f_contestar'] = $this->df_contestar;
        $aDades['categoria'] = $this->icategoria;
        $aDades['visibilidad'] = $this->ivisibilidad;
        $aDades['visibilidad_dst'] = $this->ivisibilidad_dst;
        $aDades['accion'] = $this->iaccion;
        $aDades['modo_envio'] = $this->imodo_envio;
        $aDades['f_salida'] = $this->df_salida;
        $aDades['ok'] = $this->iok;
        $aDades['tipo_doc'] = $this->itipo_doc;
        $aDades['anulado'] = $this->banulado;
        $aDades['descripcion'] = $this->sdescripcion;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['anulado'])) {
            $aDades['anulado'] = 'true';
        } else {
            $aDades['anulado'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					json_prot_local          = :json_prot_local,
					json_prot_destino        = :json_prot_destino,
					json_prot_ref            = :json_prot_ref,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					asunto                   = :asunto,
					detalle                  = :detalle,
					creador                  = :creador,
					resto_oficinas           = :resto_oficinas,
					comentarios              = :comentarios,
					f_aprobacion             = :f_aprobacion,
					f_escrito                = :f_escrito,
					f_contestar              = :f_contestar,
					categoria                = :categoria,
					visibilidad              = :visibilidad,
					visibilidad_dst          = :visibilidad_dst,
					accion                   = :accion,
					modo_envio               = :modo_envio,
					f_salida                 = :f_salida,
					ok                       = :ok,
					tipo_doc                 = :tipo_doc,
					anulado                  = :anulado,
					descripcion              = :descripcion";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
                $sClauError = 'EscritoDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EscritoDB.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(json_prot_local,json_prot_destino,json_prot_ref,id_grupos,destinos,asunto,detalle,creador,resto_oficinas,comentarios,f_aprobacion,f_escrito,f_contestar,categoria,visibilidad,visibilidad_dst,accion,modo_envio,f_salida,ok,tipo_doc,anulado,descripcion)";
            $valores = "(:json_prot_local,:json_prot_destino,:json_prot_ref,:id_grupos,:destinos,:asunto,:detalle,:creador,:resto_oficinas,:comentarios,:f_aprobacion,:f_escrito,:f_contestar,:categoria,:visibilidad,:visibilidad_dst,:accion,:modo_envio,:f_salida,:ok,:tipo_doc,:anulado,:descripcion)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EscritoDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EscritoDB.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_escrito = $oDbl->lastInsertId('escritos_id_escrito_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     */
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_escrito) && $this->clone === FALSE) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
                $sClauError = 'EscritoDB.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $oDblSt->closeCursor();
                    $this->setAllAtributes($aDades);
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) {
                        return FALSE;
                    }
                    break;
                default:
                    $oDblSt->closeCursor();
                    // En el caso de no existir esta fila, $aDades = FALSE:
                    if ($aDades === FALSE) {
                        return FALSE;
                    }
                    $this->setAllAtributes($aDades);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     */
    private function setAllAtributes($aDades, $convert = FALSE)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_escrito', $aDades)) {
            $this->setId_escrito($aDades['id_escrito']);
        }
        if (array_key_exists('json_prot_local', $aDades)) {
            $this->setJson_prot_local($aDades['json_prot_local'], TRUE);
        }
        if (array_key_exists('json_prot_destino', $aDades)) {
            $this->setJson_prot_destino($aDades['json_prot_destino'], TRUE);
        }
        if (array_key_exists('json_prot_ref', $aDades)) {
            $this->setJson_prot_ref($aDades['json_prot_ref'], TRUE);
        }
        if (array_key_exists('id_grupos', $aDades)) {
            $this->setId_grupos($aDades['id_grupos'], TRUE);
        }
        if (array_key_exists('destinos', $aDades)) {
            $this->setDestinos($aDades['destinos'], TRUE);
        }
        if (array_key_exists('asunto', $aDades)) {
            $this->setAsunto($aDades['asunto']);
        }
        if (array_key_exists('detalle', $aDades)) {
            $this->setDetalle($aDades['detalle']);
        }
        if (array_key_exists('creador', $aDades)) {
            $this->setCreador($aDades['creador']);
        }
        if (array_key_exists('resto_oficinas', $aDades)) {
            $this->setResto_oficinas($aDades['resto_oficinas'], TRUE);
        }
        if (array_key_exists('comentarios', $aDades)) {
            $this->setComentarios($aDades['comentarios']);
        }
        if (array_key_exists('f_aprobacion', $aDades)) {
            $this->setF_aprobacion($aDades['f_aprobacion'], $convert);
        }
        if (array_key_exists('f_escrito', $aDades)) {
            $this->setF_escrito($aDades['f_escrito'], $convert);
        }
        if (array_key_exists('f_contestar', $aDades)) {
            $this->setF_contestar($aDades['f_contestar'], $convert);
        }
        if (array_key_exists('categoria', $aDades)) {
            $this->setCategoria($aDades['categoria']);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
        if (array_key_exists('visibilidad_dst', $aDades)) {
            $this->setVisibilidad_dst($aDades['visibilidad_dst']);
        }
        if (array_key_exists('accion', $aDades)) {
            $this->setAccion($aDades['accion']);
        }
        if (array_key_exists('modo_envio', $aDades)) {
            $this->setModo_envio($aDades['modo_envio']);
        }
        if (array_key_exists('f_salida', $aDades)) {
            $this->setF_salida($aDades['f_salida'], $convert);
        }
        if (array_key_exists('ok', $aDades)) {
            $this->setOk($aDades['ok']);
        }
        if (array_key_exists('tipo_doc', $aDades)) {
            $this->setTipo_doc($aDades['tipo_doc']);
        }
        if (array_key_exists('anulado', $aDades)) {
            $this->setAnulado(is_true($aDades['anulado']));
        }
        if (array_key_exists('descripcion', $aDades)) {
            $this->setDescripcion($aDades['descripcion']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/

    /**
     * @param integer iid_escrito
     */
    function setId_escrito($iid_escrito)
    {
        $this->iid_escrito = $iid_escrito;
    }

    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param array|string|null $a_id_grupos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setId_grupos(array|string|null $a_id_grupos = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_id_grupos);
        } else {
            $postgresArray = $a_id_grupos;
        }
        $this->a_id_grupos = $postgresArray;
    }

    /**
     * @param array|string|null $a_destinos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setDestinos(array|string $a_destinos = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_destinos);
        } else {
            $postgresArray = $a_destinos;
        }
        $this->a_destinos = $postgresArray;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string sasunto='' optional
     */
    function setAsunto($sasunto = '')
    {
        $this->sasunto = $sasunto;
    }

    /**
     * @param string sdetalle='' optional
     */
    function setDetalle($sdetalle = '')
    {
        $this->sdetalle = $sdetalle;
    }

    /**
     * @param integer icreador='' optional
     */
    function setCreador($icreador = '')
    {
        $this->icreador = $icreador;
    }

    /**
     * @param array|string|null $a_resto_oficinas a_resto_oficinas
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setResto_oficinas(array|string|null $a_resto_oficinas = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_resto_oficinas);
        } else {
            $postgresArray = $a_resto_oficinas;
        }
        $this->a_resto_oficinas = $postgresArray;
    }

    /**
     * @param string scomentarios='' optional
     */
    function setComentarios($scomentarios = '')
    {
        $this->scomentarios = $scomentarios;
    }

    /**
     * Si df_aprobacion es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_aprobacion debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_aprobacion='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_aprobacion($df_aprobacion = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_aprobacion)) {
            $oConverter = new core\ConverterDate('date', $df_aprobacion);
            $this->df_aprobacion = $oConverter->toPg();
        } else {
            $this->df_aprobacion = $df_aprobacion;
        }
    }

    /**
     * Si df_escrito es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_escrito debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_escrito='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_escrito($df_escrito = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_escrito)) {
            $oConverter = new core\ConverterDate('date', $df_escrito);
            $this->df_escrito = $oConverter->toPg();
        } else {
            $this->df_escrito = $df_escrito;
        }
    }

    /**
     * Si df_contestar es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_contestar debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_contestar='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_contestar($df_contestar = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_contestar)) {
            $oConverter = new core\ConverterDate('date', $df_contestar);
            $this->df_contestar = $oConverter->toPg();
        } else {
            $this->df_contestar = $df_contestar;
        }
    }

    /**
     * @param integer icategoria='' optional
     */
    function setCategoria($icategoria = '')
    {
        $this->icategoria = $icategoria;
    }

    /**
     * @param integer ivisibilidad='' optional
     */
    function setVisibilidad($ivisibilidad = '')
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    /**
     * @param integer ivisibilidad_dst='' optional
     */
    function setVisibilidad_dst($ivisibilidad_dst = '')
    {
        $this->ivisibilidad_dst = $ivisibilidad_dst;
    }

    /**
     * @param integer iaccion='' optional
     */
    function setAccion($iaccion = '')
    {
        $this->iaccion = $iaccion;
    }

    /**
     * @param integer imodo_envio='' optional
     */
    function setModo_envio($imodo_envio = '')
    {
        $this->imodo_envio = $imodo_envio;
    }

    /**
     * establece el valor del atributo df_salida de EscritoDB
     * Si df_salida es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_salida debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_salida='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_salida($df_salida = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_salida)) {
            $oConverter = new core\ConverterDate('date', $df_salida);
            $this->df_salida = $oConverter->toPg();
        } else {
            $this->df_salida = $df_salida;
        }
    }

    /**
     * @param integer iok
     */
    function setOk($iok)
    {
        $this->iok = $iok;
    }

    /**
     * @param integer itipo_doc='' optional
     */
    function setTipo_doc($itipo_doc = '')
    {
        $this->itipo_doc = $itipo_doc;
    }

    /**
     * @param boolean banulado='f' optional
     */
    function setAnulado($banulado = 'f')
    {
        $this->banulado = $banulado;
    }

    /**
     * @param string sdescripcion='' optional
     */
    function setDescripcion($sdescripcion = '')
    {
        $this->sdescripcion = $sdescripcion;
    }

    
    /**
     * Recupera las claus primàries de EscritoDB en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_escrito' => $this->iid_escrito);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EscritoDB en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_escrito') && $val_id !== '') {
                    $this->iid_escrito = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_escrito = (int)$a_id;
                $this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
            }
        }
    }

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
            $sClauError = 'EscritoDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Para que sea compatible com metodos de Entradas.
     * Recupera l'atribut icreador de EscritoDB
     *
     * @return integer icreador
     */
    function getPonente()
    {
        if (!isset($this->icreador) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icreador;
    }

    /**
     * Recupera l'atribut iid_escrito de EscritoDB
     *
     * @return integer iid_escrito
     */
    function getId_escrito()
    {
        if (!isset($this->iid_escrito) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_escrito;
    }

    /**
     * Recupera l'atribut json_prot_local de EscritoDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null
     * @throws JsonException
     */
    public function getJson_prot_local(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_prot_local) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return (new ConverterJson($this->json_prot_local, $bArray))->fromPg();
    }

    /**
     * @param string|stdClass|null $oJSON
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_local(string|stdClass|null $oJSON, bool $db = FALSE): void
    {
        $this->json_prot_local = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Recupera l'atribut json_prot_destino de EscritoDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null
     * @throws JsonException
     */
    public function getJson_prot_destino(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_prot_destino) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return (new ConverterJson($this->json_prot_destino, $bArray))->fromPg();
    }

    /**
     * @param string|array|null $oJSON json_prot_destino
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_destino(string|array|null $oJSON, bool $db = FALSE):void
    {
        $this->json_prot_destino = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Recupera l'atribut json_prot_ref de EscritoDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null JSON json_prot_ref
     * @throws JsonException
     */
    public function getJson_prot_ref(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_prot_ref) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return (new ConverterJson($this->json_prot_ref, $bArray))->fromPg();
    }

    /**
     * @param string|array|null $oJSON JSON json_prot_ref
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_ref(string|array|null $oJSON, bool $db = FALSE):void
    {
        $this->json_prot_ref = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Recupera l'atribut a_id_grupos de EscritoDB
     *
     * @return array|null $a_id_grupos
     */
    public function getId_grupos(): ?array
    {
        if (!isset($this->a_id_grupos) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pgInteger2php($this->a_id_grupos);
    }

    /**
     * Recupera l'atribut a_destinos de EscritoDB
     *
     * @return array|null a_destinos
     */
    public function getDestinos(): ?array
    {
        if (!isset($this->a_destinos) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pgInteger2php($this->a_destinos);
    }

    /**
     * Recupera l'atribut sasunto de EscritoDB
     *
     * @return string sasunto
     */
    public function getAsuntoDB(): string
    {
        if (!isset($this->sasunto) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sasunto;
    }

    /**
     * Recupera l'atribut sdetalle de EscritoDB
     *
     * @return string|null sdetalle
     */
    public function getDetalleDB(): ?string
    {
        if (!isset($this->sdetalle) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdetalle;
    }

    /**
     * Recupera l'atribut icreador de EscritoDB
     *
     * @return integer|null icreador
     */
    public function getCreador(): ?int
    {
        if (!isset($this->icreador) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icreador;
    }

    /**
     * Recupera l'atribut a_resto_oficinas de EscritoDB
     *
     * @return array|null $a_resto_oficinas
     */
    public function getResto_oficinas(): ?array
    {
        if (!isset($this->a_resto_oficinas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pgInteger2php($this->a_resto_oficinas);
    }

    /**
     * Recupera l'atribut scomentarios de EscritoDB
     *
     * @return string|null scomentarios
     */
    public function getComentarios(): ?string
    {
        if (!isset($this->scomentarios) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->scomentarios;
    }

    /**
     * Recupera l'atribut df_aprobacion de EscritoDB
     *
     * @return web\DateTimeLocal|web\NullDateTimeLocal
     */
    public function getF_aprobacion()
    {
        if (!isset($this->df_aprobacion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_aprobacion)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_aprobacion);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_escrito de EscritoDB
     *
     * @return web\DateTimeLocal|web\NullDateTimeLocal
     */
    public function getF_escrito()
    {
        if (!isset($this->df_escrito) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_escrito)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_escrito);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_contestar de EscritoDB
     *
     * @return web\DateTimeLocal|web\NullDateTimeLocal
     */
    public function getF_contestar()
    {
        if (!isset($this->df_contestar) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_contestar)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_contestar);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut icategoria de EscritoDB
     *
     * @return integer|null icategoria
     */
    public function getCategoria(): ?int
    {
        if (!isset($this->icategoria) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icategoria;
    }

    /**
     * Recupera l'atribut ivisibilidad de EscritoDB
     *
     * @return integer|null ivisibilidad
     */
    public function getVisibilidad(): ?int
    {
        if (!isset($this->ivisibilidad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivisibilidad;
    }

    /**
     * Recupera l'atribut ivisibilidad_dst de EscritoDB
     *
     * @return integer|null ivisibilidad_dst
     */
    public function getVisibilidad_dst(): ?int
    {
        if (!isset($this->ivisibilidad_dst) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivisibilidad_dst;
    }

    /**
     * Recupera l'atribut iaccion de EscritoDB
     *
     * @return integer iaccion
     */
    public function getAccion(): int
    {
        if (!isset($this->iaccion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iaccion;
    }

    /**
     * Recupera l'atribut imodo_envio de EscritoDB
     *
     * @return integer imodo_envio
     */
    public function getModo_envio(): int
    {
        if (!isset($this->imodo_envio) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->imodo_envio;
    }

    /**
     * Recupera l'atribut df_salida de EscritoDB
     *
     * @return web\DateTimeLocal|web\NullDateTimeLocal
     */
    public function getF_salida()
    {
        if (!isset($this->df_salida) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_salida)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_salida);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut iok de EscritoDB
     *
     * @return integer|null iok
     */
    public function getOk(): ?int
    {
        if (!isset($this->iok) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iok;
    }

    /**
     * Recupera l'atribut itipo_doc de EscritoDB
     *
     * @return integer|null itipo_doc
     */
    public function getTipo_doc(): ?int
    {
        if (!isset($this->itipo_doc) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->itipo_doc;
    }

    /**
     * Recupera l'atribut banulado de EscritoDB
     *
     * @return boolean banulado
     */
    public function getAnulado(): bool
    {
        if (!isset($this->banulado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->banulado;
    }

    /**
     * Recupera l'atribut sdescripcion de EscritoDB
     *
     * @return string|null sdescripcion
     */
    public function getDescripcion(): ?string
    {
        if (!isset($this->sdescripcion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdescripcion;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oEscritoDBSet = new core\Set();

        $oEscritoDBSet->add($this->getDatosJson_prot_local());
        $oEscritoDBSet->add($this->getDatosJson_prot_destino());
        $oEscritoDBSet->add($this->getDatosJson_prot_ref());
        $oEscritoDBSet->add($this->getDatosId_grupos());
        $oEscritoDBSet->add($this->getDatosDestinos());
        $oEscritoDBSet->add($this->getDatosAsunto());
        $oEscritoDBSet->add($this->getDatosDetalle());
        $oEscritoDBSet->add($this->getDatosCreador());
        $oEscritoDBSet->add($this->getDatosResto_oficinas());
        $oEscritoDBSet->add($this->getDatosComentarios());
        $oEscritoDBSet->add($this->getDatosF_aprobacion());
        $oEscritoDBSet->add($this->getDatosF_escrito());
        $oEscritoDBSet->add($this->getDatosF_contestar());
        $oEscritoDBSet->add($this->getDatosCategoria());
        $oEscritoDBSet->add($this->getDatosVisibilidad());
        $oEscritoDBSet->add($this->getDatosAccion());
        $oEscritoDBSet->add($this->getDatosModo_envio());
        $oEscritoDBSet->add($this->getDatosF_salida());
        $oEscritoDBSet->add($this->getDatosOk());
        $oEscritoDBSet->add($this->getDatosTipo_doc());
        $oEscritoDBSet->add($this->getDatosAnulado());
        $oEscritoDBSet->add($this->getDatosDescripcion());
        return $oEscritoDBSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut json_prot_local de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_prot_local()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_local'));
        $oDatosCampo->setEtiqueta(_("json_prot_local"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_prot_destino de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_prot_destino()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_destino'));
        $oDatosCampo->setEtiqueta(_("json_prot_destino"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_prot_ref de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_prot_ref()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_ref'));
        $oDatosCampo->setEtiqueta(_("json_prot_ref"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_id_grupos de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_grupos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_grupos'));
        $oDatosCampo->setEtiqueta(_("id_grupos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_destinos de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDestinos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'destinos'));
        $oDatosCampo->setEtiqueta(_("destinos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sasunto de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosAsunto()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'asunto'));
        $oDatosCampo->setEtiqueta(_("asunto"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdetalle de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDetalle()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'detalle'));
        $oDatosCampo->setEtiqueta(_("detalle"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut icreador de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCreador()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'creador'));
        $oDatosCampo->setEtiqueta(_("creador"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_resto_oficinas de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosResto_oficinas()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'resto_oficinas'));
        $oDatosCampo->setEtiqueta(_("resto_oficinas"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut scomentarios de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosComentarios()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'comentarios'));
        $oDatosCampo->setEtiqueta(_("comentarios"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_aprobacion de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_aprobacion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_aprobacion'));
        $oDatosCampo->setEtiqueta(_("f_aprobacion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_escrito de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_escrito()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_escrito'));
        $oDatosCampo->setEtiqueta(_("f_escrito"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_contestar de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_contestar()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_contestar'));
        $oDatosCampo->setEtiqueta(_("f_contestar"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut icategoria de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCategoria()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'categoria'));
        $oDatosCampo->setEtiqueta(_("categoria"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivisibilidad de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosVisibilidad()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'visibilidad'));
        $oDatosCampo->setEtiqueta(_("visibilidad"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iaccion de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosAccion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'accion'));
        $oDatosCampo->setEtiqueta(_("acción"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut imodo_envio de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosModo_envio()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'modo_envio'));
        $oDatosCampo->setEtiqueta(_("modo_envio"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_salida de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_salida()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_salida'));
        $oDatosCampo->setEtiqueta(_("f_salida"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iok de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOk()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'ok'));
        $oDatosCampo->setEtiqueta(_("ok"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut itipo_doc de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo_doc()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo_doc'));
        $oDatosCampo->setEtiqueta(_("tipo_doc"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut banulado de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosAnulado()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'anulado'));
        $oDatosCampo->setEtiqueta(_("anulado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescripcion de EscritoDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDescripcion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'descripcion'));
        $oDatosCampo->setEtiqueta(_("descripcion"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de EscritoDB en un array
     *
     * @return array aDades
     */
    function getTot()
    {
        if (!is_array($this->aDades)) {
            $this->DBCargar('tot');
        }
        return $this->aDades;
    }
}
