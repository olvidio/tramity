<?php

namespace expedientes\model\entity;

use core;
use core\ClasePropiedades;
use PDO;
use PDOException;
use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;

/**
 * Fitxer amb la Classe que accedeix a la taula expedientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat expedientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class ExpedienteDB extends ClasePropiedades
{

    // constantes:
    // visibilidad
    // USAR LAS DE ENTRADADB


    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de ExpedienteDB
     *
     * @var array
     */
    protected $aPrimary_key;

    /**
     * aDades de ExpedienteDB
     *
     * @var array
     */
    protected $aDades;

    /**
     * bLoaded de ExpedienteDB
     *
     * @var boolean
     */
    protected $bLoaded = FALSE;

    /**
     * Id_schema de ExpedienteDB
     *
     * @var integer
     */
    protected $iid_schema;

    /**
     * Id_expediente de ExpedienteDB
     *
     * @var integer
     */
    protected $iid_expediente;
    /**
     * Id_tramite de ExpedienteDB
     *
     * @var integer
     */
    protected $iid_tramite;
    /**
     * Ponente de ExpedienteDB
     *
     * @var integer
     */
    protected $iponente;
    /**
     * Resto_oficinas de ExpedienteDB
     *
     * @var array
     */
    protected $a_resto_oficinas;
    /**
     * Asunto de ExpedienteDB
     *
     * @var string
     */
    protected $sasunto;
    /**
     * Entradilla de ExpedienteDB
     *
     * @var string
     */
    protected $sentradilla;
    /**
     * Comentarios de ExpedienteDB
     *
     * @var string
     */
    protected $scomentarios;
    /**
     * Prioridad de ExpedienteDB
     *
     * @var integer
     */
    protected $iprioridad;
    /**
     * Json_antecedentes de ExpedienteDB
     *
     * @var object JSON
     */
    protected $json_antecedentes;
    /**
     * Json_acciones de ExpedienteDB
     *
     * @var object JSON
     */
    protected $json_acciones;
    /**
     * F_contestar de ExpedienteDB
     *
     * @var DateTimeLocal
     */
    protected $df_contestar;
    /**
     * Estado de ExpedienteDB
     *
     * @var integer
     */
    protected $iestado;
    /**
     * F_ini_circulacion de ExpedienteDB
     *
     * @var DateTimeLocal
     */
    protected $df_ini_circulacion;
    /**
     * F_reunion de ExpedienteDB
     *
     * @var DateTimeLocal
     */
    protected $df_reunion;
    /**
     * F_aprobacion de ExpedienteDB
     *
     * @var DateTimeLocal
     */
    protected $df_aprobacion;
    /**
     * Vida de ExpedienteDB
     *
     * @var integer
     */
    protected $ivida;
    /**
     * Json_preparar de ExpedienteDB
     *
     * @var object JSON
     */
    protected $json_preparar;
    /**
     * Firmas_oficina de ExpedienteDB
     *
     * @var array
     */
    protected $a_firmas_oficina;
    /**
     * Visibilidad de ExpedienteDB
     *
     * @var integer
     */
    protected $ivisibilidad;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de ExpedienteDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de ExpedienteDB
     *
     * @var string
     */
    protected $sNomTabla;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_expediente
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_expediente') && $val_id !== '') {
                    $this->iid_expediente = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_expediente = (int)$a_id;
                $this->aPrimary_key = array('iid_expediente' => $this->iid_expediente);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('expedientes');
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
        $aDades['id_tramite'] = $this->iid_tramite;
        $aDades['ponente'] = $this->iponente;
        $aDades['resto_oficinas'] = $this->a_resto_oficinas;
        $aDades['asunto'] = $this->sasunto;
        $aDades['entradilla'] = $this->sentradilla;
        $aDades['comentarios'] = $this->scomentarios;
        $aDades['prioridad'] = $this->iprioridad;
        $aDades['json_antecedentes'] = $this->json_antecedentes;
        $aDades['json_acciones'] = $this->json_acciones;
        $aDades['f_contestar'] = $this->df_contestar;
        $aDades['estado'] = $this->iestado;
        $aDades['f_ini_circulacion'] = $this->df_ini_circulacion;
        $aDades['f_reunion'] = $this->df_reunion;
        $aDades['f_aprobacion'] = $this->df_aprobacion;
        $aDades['vida'] = $this->ivida;
        $aDades['json_preparar'] = $this->json_preparar;
        $aDades['firmas_oficina'] = $this->a_firmas_oficina;
        $aDades['visibilidad'] = $this->ivisibilidad;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_tramite               = :id_tramite,
					ponente                  = :ponente,
					resto_oficinas           = :resto_oficinas,
					asunto                   = :asunto,
					entradilla               = :entradilla,
					comentarios              = :comentarios,
					prioridad                = :prioridad,
					json_antecedentes        = :json_antecedentes,
					json_acciones            = :json_acciones,
					f_contestar              = :f_contestar,
					estado                   = :estado,
					f_ini_circulacion        = :f_ini_circulacion,
					f_reunion                = :f_reunion,
					f_aprobacion             = :f_aprobacion,
					vida                     = :vida,
					json_preparar            = :json_preparar,
					firmas_oficina           = :firmas_oficina,
					visibilidad              = :visibilidad";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_expediente='$this->iid_expediente'")) === FALSE) {
                $sClauError = 'ExpedienteDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'ExpedienteDB.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_tramite,ponente,resto_oficinas,asunto,entradilla,comentarios,prioridad,json_antecedentes,json_acciones,f_contestar,estado,f_ini_circulacion,f_reunion,f_aprobacion,vida,json_preparar,firmas_oficina,visibilidad)";
            $valores = "(:id_tramite,:ponente,:resto_oficinas,:asunto,:entradilla,:comentarios,:prioridad,:json_antecedentes,:json_acciones,:f_contestar,:estado,:f_ini_circulacion,:f_reunion,:f_aprobacion,:vida,:json_preparar,:firmas_oficina,:visibilidad)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'ExpedienteDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'ExpedienteDB.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_expediente = $oDbl->lastInsertId('expedientes_id_expediente_seq');
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
        if (isset($this->iid_expediente)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_expediente='$this->iid_expediente'")) === FALSE) {
                $sClauError = 'ExpedienteDB.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->setAllAtributes($aDades);
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) return FALSE;
                    break;
                default:
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
        if (array_key_exists('id_expediente', $aDades)) {
            $this->setId_expediente($aDades['id_expediente']);
        }
        if (array_key_exists('id_tramite', $aDades)) {
            $this->setId_tramite($aDades['id_tramite']);
        }
        if (array_key_exists('ponente', $aDades)) {
            $this->setPonente($aDades['ponente']);
        }
        if (array_key_exists('resto_oficinas', $aDades)) {
            $this->setResto_oficinas($aDades['resto_oficinas'], TRUE);
        }
        if (array_key_exists('asunto', $aDades)) {
            $this->setAsunto($aDades['asunto']);
        }
        if (array_key_exists('entradilla', $aDades)) {
            $this->setEntradilla($aDades['entradilla']);
        }
        if (array_key_exists('comentarios', $aDades)) {
            $this->setComentarios($aDades['comentarios']);
        }
        if (array_key_exists('prioridad', $aDades)) {
            $this->setPrioridad($aDades['prioridad']);
        }
        if (array_key_exists('json_antecedentes', $aDades)) {
            $this->setJson_antecedentes($aDades['json_antecedentes'], TRUE);
        }
        if (array_key_exists('json_acciones', $aDades)) {
            $this->setJson_acciones($aDades['json_acciones'], TRUE);
        }
        if (array_key_exists('f_contestar', $aDades)) {
            $this->setF_contestar($aDades['f_contestar'], $convert);
        }
        if (array_key_exists('estado', $aDades)) {
            $this->setEstado($aDades['estado']);
        }
        if (array_key_exists('f_ini_circulacion', $aDades)) {
            $this->setF_ini_circulacion($aDades['f_ini_circulacion'], $convert);
        }
        if (array_key_exists('f_reunion', $aDades)) {
            $this->setF_reunion($aDades['f_reunion'], $convert);
        }
        if (array_key_exists('f_aprobacion', $aDades)) {
            $this->setF_aprobacion($aDades['f_aprobacion'], $convert);
        }
        if (array_key_exists('vida', $aDades)) {
            $this->setVida($aDades['vida']);
        }
        if (array_key_exists('json_preparar', $aDades)) {
            $this->setJson_preparar($aDades['json_preparar'], TRUE);
        }
        if (array_key_exists('firmas_oficina', $aDades)) {
            $this->setFirmas_oficina($aDades['firmas_oficina'], TRUE);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param integer iid_expediente
     */
    function setId_expediente($iid_expediente)
    {
        $this->iid_expediente = $iid_expediente;
    }

    /**
     * @param integer iid_tramite='' optional
     */
    function setId_tramite($iid_tramite = '')
    {
        $this->iid_tramite = $iid_tramite;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param integer iponente='' optional
     */
    function setPonente($iponente = '')
    {
        $this->iponente = $iponente;
    }

    /**
     * @param array a_resto_oficinas
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    function setResto_oficinas($a_resto_oficinas = '', $db = FALSE)
    {
        if ($db === FALSE) {
            $postgresArray = core\array_php2pg($a_resto_oficinas);
        } else {
            $postgresArray = $a_resto_oficinas;
        }
        $this->a_resto_oficinas = $postgresArray;
    }

    /**
     * @param string sasunto='' optional
     */
    function setAsunto($sasunto = '')
    {
        $this->sasunto = $sasunto;
    }

    /**
     * @param string sentradilla='' optional
     */
    function setEntradilla($sentradilla = '')
    {
        $this->sentradilla = $sentradilla;
    }

    /**
     * @param string scomentarios='' optional
     */
    function setComentarios($scomentarios = '')
    {
        $this->scomentarios = $scomentarios;
    }

    /**
     * @param integer iprioridad='' optional
     */
    function setPrioridad($iprioridad = '')
    {
        $this->iprioridad = (int)$iprioridad;
    }

    /**
     * Si df_contestar es string, y convert=TRUE se convierte usando el formato DateTimeLocal->getFormat().
     * Si convert es FALSE, df_contestar debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_contestar='' optional.
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
     * @param integer iestado='' optional
     */
    function setEstado($iestado = '')
    {
        $this->iestado = (int)$iestado;
    }

    /**
     * Si df_ini_circulacion es string, y convert=TRUE se convierte usando el formato DateTimeLocal->getFormat().
     * Si convert es FALSE, df_ini_circulacion debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_ini_circulacion='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_ini_circulacion($df_ini_circulacion = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_ini_circulacion)) {
            $oConverter = new core\ConverterDate('date', $df_ini_circulacion);
            $this->df_ini_circulacion = $oConverter->toPg();
        } else {
            $this->df_ini_circulacion = $df_ini_circulacion;
        }
    }

    /**
     * Si df_reunion es string, y convert=TRUE se convierte usando el formato DateTimeLocal->getFormat().
     * Si convert es FALSE, df_reunion debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_reunion='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_reunion($df_reunion = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_reunion)) {
            $oConverter = new core\ConverterDate('timestamp', $df_reunion);
            $this->df_reunion = $oConverter->toPg();
        } else {
            $this->df_reunion = $df_reunion;
        }
    }

    /**
     * Si df_aprobacion es string, y convert=TRUE se convierte usando el formato DateTimeLocal->getFormat().
     * Si convert es FALSE, df_aprobacion debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_aprobacion='' optional.
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
     * @param integer ivida='' optional
     */
    function setVida($ivida = '')
    {
        $this->ivida = $ivida;
    }

    /**
     * @param array a_firmas_oficina
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    function setFirmas_oficina($a_firmas_oficina = '', $db = FALSE)
    {
        if ($db === FALSE) {
            $postgresArray = core\array_php2pg($a_firmas_oficina);
        } else {
            $postgresArray = $a_firmas_oficina;
        }
        $this->a_firmas_oficina = $postgresArray;
    }

    /**
     * @param integer ivisibilidad='' optional
     */
    function setVisibilidad($ivisibilidad = '')
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    
    /**
     * Recupera las claus primàries de ExpedienteDB en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_expediente' => $this->iid_expediente);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de ExpedienteDB en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_expediente') && $val_id !== '') {
                    $this->iid_expediente = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_expediente = (int)$a_id;
                $this->aPrimary_key = array('iid_expediente' => $this->iid_expediente);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_expediente='$this->iid_expediente'")) === FALSE) {
            $sClauError = 'ExpedienteDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_expediente de ExpedienteDB
     *
     * @return integer iid_expediente
     */
    function getId_expediente()
    {
        if (!isset($this->iid_expediente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_expediente;
    }

    /**
     * Recupera l'atribut iid_tramite de ExpedienteDB
     *
     * @return integer iid_tramite
     */
    function getId_tramite()
    {
        if (!isset($this->iid_tramite) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_tramite;
    }

    /**
     * Recupera l'atribut iponente de ExpedienteDB
     *
     * @return integer|null iponente
     */
    public function getPonente(): ?int
    {
        if (!isset($this->iponente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iponente;
    }

    /**
     * Recupera l'atribut a_resto_oficinas de ExpedienteDB
     *
     * @return array a_resto_oficinas
     */
    function getResto_oficinas()
    {
        if (!isset($this->a_resto_oficinas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return core\array_pgInteger2php($this->a_resto_oficinas);
    }

    /**
     * Recupera l'atribut sasunto de ExpedienteDB
     *
     * @return string sasunto
     */
    function getAsunto()
    {
        if (!isset($this->sasunto) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sasunto;
    }

    /**
     * Recupera l'atribut sentradilla de ExpedienteDB
     *
     * @return string sentradilla
     */
    function getEntradilla()
    {
        if (!isset($this->sentradilla) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sentradilla;
    }

    /**
     * Recupera l'atribut scomentarios de ExpedienteDB
     *
     * @return string scomentarios
     */
    function getComentarios()
    {
        if (!isset($this->scomentarios) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->scomentarios;
    }

    /**
     * Recupera l'atribut iprioridad de ExpedienteDB
     *
     * @return integer|null iprioridad
     */
    function getPrioridad(): ?int
    {
        if (!isset($this->iprioridad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iprioridad;
    }

    /**
     * Recupera l'atribut json_antecedentes de ExpedienteDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return object JSON json_antecedentes
     */
    function getJson_antecedentes($bArray = FALSE)
    {
        if (!isset($this->json_antecedentes) && !$this->bLoaded) {
            $this->DBCargar();
        }
        $oJSON = json_decode($this->json_antecedentes, $bArray);
        if (empty($oJSON) || $oJSON == '[]') {
            if ($bArray) {
                $oJSON = [];
            } else {
                $oJSON = new stdClass;
            }
        }
        //$this->json_antecedentes = $oJSON;
        return $oJSON;
    }

    /**
     * @param object JSON json_antecedentes
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     */
    function setJson_antecedentes($oJSON, $db = FALSE)
    {
        if ($db === FALSE) {
            $json = json_encode($oJSON);
        } else {
            $json = $oJSON;
        }
        $this->json_antecedentes = $json;
    }

    /**
     * Recupera l'atribut json_acciones de ExpedienteDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return object JSON json_acciones
     */
    function getJson_acciones($bArray = FALSE)
    {
        if (!isset($this->json_acciones) && !$this->bLoaded) {
            $this->DBCargar();
        }
        $oJSON = json_decode($this->json_acciones, $bArray);
        if (empty($oJSON) || $oJSON == '[]') {
            if ($bArray) {
                $oJSON = [];
            } else {
                $oJSON = new stdClass;
            }
        }
        //$this->json_acciones = $oJSON;
        //return $this->json_acciones;
        return $oJSON;
    }

    /**
     * @param object JSON json_acciones
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     */
    function setJson_acciones($oJSON, $db = FALSE)
    {
        if ($db === FALSE) {
            $json = json_encode($oJSON);
        } else {
            $json = $oJSON;
        }
        $this->json_acciones = $json;
    }

    /**
     * Recupera l'atribut df_contestar de ExpedienteDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_contestar
     */
    function getF_contestar()
    {
        if (!isset($this->df_contestar) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_contestar)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_contestar);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut iestado de ExpedienteDB
     *
     * @return integer|null iestado
     */
    function getEstado(): ?int
    {
        if (!isset($this->iestado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iestado;
    }

    /**
     * Recupera l'atribut df_ini_circulacion de ExpedienteDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_ini_circulacion
     */
    function getF_ini_circulacion()
    {
        if (!isset($this->df_ini_circulacion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_ini_circulacion)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_ini_circulacion);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_reunion de ExpedienteDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_reunion
     */
    function getF_reunion()
    {
        if (!isset($this->df_reunion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_reunion)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_reunion);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_aprobacion de ExpedienteDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_aprobacion
     */
    function getF_aprobacion()
    {
        if (!isset($this->df_aprobacion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_aprobacion)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('date', $this->df_aprobacion);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ivida de ExpedienteDB
     *
     * @return integer ivida
     */
    function getVida()
    {
        if (!isset($this->ivida) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivida;
    }

    /**
     * Recupera l'atribut json_preparar de ExpedienteDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return object JSON json_preparar
     */
    function getJson_preparar($bArray = FALSE)
    {
        if (!isset($this->json_preparar) && !$this->bLoaded) {
            $this->DBCargar();
        }
        $oJSON = json_decode($this->json_preparar, $bArray);
        if (empty($oJSON) || $oJSON == '[]') {
            if ($bArray) {
                $oJSON = [];
            } else {
                $oJSON = new stdClass;
            }
        }
        //$this->json_preparar = $oJSON;
        //return $this->json_preparar;
        return $oJSON;
    }

    /**
     * @param object JSON json_preparar
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     */
    function setJson_preparar($oJSON, $db = FALSE)
    {
        if ($db === FALSE) {
            $json = json_encode($oJSON);
        } else {
            $json = $oJSON;
        }
        $this->json_preparar = $json;
    }

    /**
     * Recupera l'atribut a_firmas_oficina de ExpedienteDB
     *
     * @return array a_firmas_oficina
     */
    function getFirmas_oficina()
    {
        if (!isset($this->a_firmas_oficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return core\array_pgInteger2php($this->a_firmas_oficina);
    }

    /**
     * Recupera l'atribut ivisibilidad de ExpedienteDB
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
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oExpedienteDBSet = new core\Set();

        $oExpedienteDBSet->add($this->getDatosId_tramite());
        $oExpedienteDBSet->add($this->getDatosPonente());
        $oExpedienteDBSet->add($this->getDatosResto_oficinas());
        $oExpedienteDBSet->add($this->getDatosAsunto());
        $oExpedienteDBSet->add($this->getDatosEntradilla());
        $oExpedienteDBSet->add($this->getDatosComentarios());
        $oExpedienteDBSet->add($this->getDatosPrioridad());
        $oExpedienteDBSet->add($this->getDatosJson_antecedentes());
        $oExpedienteDBSet->add($this->getDatosJson_acciones());
        $oExpedienteDBSet->add($this->getDatosF_contestar());
        $oExpedienteDBSet->add($this->getDatosEstado());
        $oExpedienteDBSet->add($this->getDatosF_ini_circulacion());
        $oExpedienteDBSet->add($this->getDatosF_reunion());
        $oExpedienteDBSet->add($this->getDatosF_aprobacion());
        $oExpedienteDBSet->add($this->getDatosVida());
        $oExpedienteDBSet->add($this->getDatosJson_preparar());
        $oExpedienteDBSet->add($this->getDatosFirmas_oficina());
        $oExpedienteDBSet->add($this->getDatosVisibilidad());
        return $oExpedienteDBSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_tramite de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_tramite()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_tramite'));
        $oDatosCampo->setEtiqueta(_("id_tramite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iponente de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPonente()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'ponente'));
        $oDatosCampo->setEtiqueta(_("ponente"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_resto_oficinas de ExpedienteDB
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
     * Recupera les propietats de l'atribut sasunto de ExpedienteDB
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
     * Recupera les propietats de l'atribut sentradilla de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEntradilla()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'entradilla'));
        $oDatosCampo->setEtiqueta(_("entradilla"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut scomentarios de ExpedienteDB
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
     * Recupera les propietats de l'atribut iprioridad de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPrioridad()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'prioridad'));
        $oDatosCampo->setEtiqueta(_("prioridad"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_antecedentes de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_antecedentes()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_antecedentes'));
        $oDatosCampo->setEtiqueta(_("json_antecedentes"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_acciones de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_acciones()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_acciones'));
        $oDatosCampo->setEtiqueta(_("json_acciones"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_contestar de ExpedienteDB
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
     * Recupera les propietats de l'atribut iestado de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEstado()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'estado'));
        $oDatosCampo->setEtiqueta(_("estado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_ini_circulacion de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_ini_circulacion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_ini_circulacion'));
        $oDatosCampo->setEtiqueta(_("f_ini_circulacion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_reunion de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_reunion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_reunion'));
        $oDatosCampo->setEtiqueta(_("f_reunion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_aprobacion de ExpedienteDB
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
     * Recupera les propietats de l'atribut ivida de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosVida()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'vida'));
        $oDatosCampo->setEtiqueta(_("vida"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_preparar de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_preparar()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_preparar'));
        $oDatosCampo->setEtiqueta(_("json_preparar"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_firmas_oficina de ExpedienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosFirmas_oficina()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'firmas_oficina'));
        $oDatosCampo->setEtiqueta(_("firmas_oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivisibilidad de ExpedienteDB
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
     * Recupera tots els ATRIBUTOS de ExpedienteDB en un array
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
