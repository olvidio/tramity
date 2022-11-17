<?php

namespace pendientes\model\entity;

use core;
use PDO;
use PDOException;
use web;

/**
 * Fitxer amb la Classe que accedeix a la taula pendientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/2/2021
 */

/**
 * Classe que implementa l'entitat pendientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/2/2021
 */
class PendienteDB extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de PendienteDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de PendienteDB
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de PendienteDB
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de PendienteDB
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de PendienteDB
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de PendienteDB
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_pendiente de PendienteDB
     *
     * @var integer
     */
    private $iid_pendiente;
    /**
     * Asunto de PendienteDB
     *
     * @var string
     */
    private $sasunto;
    /**
     * Status de PendienteDB
     *
     * @var string
     */
    private $sstatus;
    /**
     * F_acabado de PendienteDB
     *
     * @var web\DateTimeLocal
     */
    private $df_acabado;
    /**
     * F_plazo de PendienteDB
     *
     * @var web\DateTimeLocal
     */
    private $df_plazo;
    /**
     * Ref_mas de PendienteDB
     *
     * @var string
     */
    private $sref_mas;
    /**
     * Observ de PendienteDB
     *
     * @var string
     */
    private $sobserv;
    /**
     * Encargado de PendienteDB
     *
     * @var string
     */
    private $sencargado;
    /**
     * Cancilleria de PendienteDB
     *
     * @var boolean
     */
    private $bcancilleria;
    /**
     * Visibilidad de PendienteDB
     *
     * @var boolean
     */
    private $ivisibilidad;
    /**
     * Detalle de PendienteDB
     *
     * @var string
     */
    private $sdetalle;
    /**
     * Pendiente_con de PendienteDB
     *
     * @var string
     */
    private $spendiente_con;
    /**
     * Etiquetas de PendienteDB
     *
     * @var string
     */
    private $setiquetas;
    /**
     * Oficinas de PendienteDB
     *
     * @var string
     */
    private $soficinas;
    /**
     * Id_oficina de PendienteDB
     *
     * @var integer
     */
    private $iid_oficina;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Rrule de PendienteDB
     *
     * @var string
     */
    private $srrule;
    /**
     * F_inicio de PendienteDB
     *
     * @var web\DateTimeLocal
     */
    private $df_inicio;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_pendiente
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_pendiente') && $val_id !== '') {
                    $this->iid_pendiente = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_pendiente = (int)$a_id;
                $this->aPrimary_key = array('iid_pendiente' => $this->iid_pendiente);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('pendientes');
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
        $aDades['asunto'] = $this->sasunto;
        $aDades['status'] = $this->sstatus;
        $aDades['f_acabado'] = $this->df_acabado;
        $aDades['f_plazo'] = $this->df_plazo;
        $aDades['ref_mas'] = $this->sref_mas;
        $aDades['observ'] = $this->sobserv;
        $aDades['encargado'] = $this->sencargado;
        $aDades['cancilleria'] = $this->bcancilleria;
        $aDades['visibilidad'] = $this->ivisibilidad;
        $aDades['detalle'] = $this->sdetalle;
        $aDades['pendiente_con'] = $this->spendiente_con;
        $aDades['etiquetas'] = $this->setiquetas;
        $aDades['oficinas'] = $this->soficinas;
        $aDades['id_oficina'] = $this->iid_oficina;
        $aDades['rrule'] = $this->srrule;
        $aDades['f_inicio'] = $this->df_inicio;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['cancilleria'])) {
            $aDades['cancilleria'] = 'true';
        } else {
            $aDades['cancilleria'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					asunto                   = :asunto,
					status                   = :status,
					f_acabado                = :f_acabado,
					f_plazo                  = :f_plazo,
					ref_mas                  = :ref_mas,
					observ                   = :observ,
					encargado                = :encargado,
					cancilleria              = :cancilleria,
					visibilidad              = :visibilidad,
					detalle                  = :detalle,
					pendiente_con            = :pendiente_con,
					etiquetas                = :etiquetas,
					oficinas                 = :oficinas,
					id_oficina               = :id_oficina,
					rrule                    = :rrule,
					f_inicio                 = :f_inicio";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_pendiente='$this->iid_pendiente'")) === FALSE) {
                $sClauError = 'PendienteDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'PendienteDB.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(asunto,status,f_acabado,f_plazo,ref_mas,observ,encargado,cancilleria,visibilidad,detalle,pendiente_con,etiquetas,oficinas,id_oficina,rrule,f_inicio)";
            $valores = "(:asunto,:status,:f_acabado,:f_plazo,:ref_mas,:observ,:encargado,:cancilleria,:visibilidad,:detalle,:pendiente_con,:etiquetas,:oficinas,:id_oficina,:rrule,:f_inicio)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'PendienteDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'PendienteDB.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_pendiente = $oDbl->lastInsertId('pendientes_id_pendiente_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_pendiente)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_pendiente='$this->iid_pendiente'")) === FALSE) {
                $sClauError = 'PendienteDB.carregar';
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
                        $this->setNullAllAtributes();
                    } else {
                        $this->setAllAtributes($aDades);
                    }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Estableix el valor de tots els ATRIBUTOS
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
        if (array_key_exists('id_pendiente', $aDades)) {
            $this->setId_pendiente($aDades['id_pendiente']);
        }
        if (array_key_exists('asunto', $aDades)) {
            $this->setAsunto($aDades['asunto']);
        }
        if (array_key_exists('status', $aDades)) {
            $this->setStatus($aDades['status']);
        }
        if (array_key_exists('f_acabado', $aDades)) {
            $this->setF_acabado($aDades['f_acabado'], $convert);
        }
        if (array_key_exists('f_plazo', $aDades)) {
            $this->setF_plazo($aDades['f_plazo'], $convert);
        }
        if (array_key_exists('ref_mas', $aDades)) {
            $this->setRef_mas($aDades['ref_mas']);
        }
        if (array_key_exists('observ', $aDades)) {
            $this->setObserv($aDades['observ']);
        }
        if (array_key_exists('encargado', $aDades)) {
            $this->setEncargado($aDades['encargado']);
        }
        if (array_key_exists('cancilleria', $aDades)) {
            $this->setCancilleria($aDades['cancilleria']);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
        if (array_key_exists('detalle', $aDades)) {
            $this->setDetalle($aDades['detalle']);
        }
        if (array_key_exists('pendiente_con', $aDades)) {
            $this->setPendiente_con($aDades['pendiente_con']);
        }
        if (array_key_exists('etiquetas', $aDades)) {
            $this->setEtiquetas($aDades['etiquetas']);
        }
        if (array_key_exists('oficinas', $aDades)) {
            $this->setOficinas($aDades['oficinas']);
        }
        if (array_key_exists('id_oficina', $aDades)) {
            $this->setId_oficina($aDades['id_oficina']);
        }
        if (array_key_exists('rrule', $aDades)) {
            $this->setRrule($aDades['rrule']);
        }
        if (array_key_exists('f_inicio', $aDades)) {
            $this->setF_inicio($aDades['f_inicio'], $convert);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_pendiente de PendienteDB
     *
     * @param integer iid_pendiente
     */
    function setId_pendiente($iid_pendiente)
    {
        $this->iid_pendiente = $iid_pendiente;
    }

    /**
     * estableix el valor de l'atribut sasunto de PendienteDB
     *
     * @param string sasunto='' optional
     */
    function setAsunto($sasunto = '')
    {
        $this->sasunto = $sasunto;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut sstatus de PendienteDB
     *
     * @param string sstatus='' optional
     */
    function setStatus($sstatus = '')
    {
        $this->sstatus = $sstatus;
    }

    /**
     * estableix el valor de l'atribut df_acabado de PendienteDB
     * Si df_acabado es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_acabado debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_acabado='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_acabado($df_acabado = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_acabado)) {
            $oConverter = new core\Converter('date', $df_acabado);
            $this->df_acabado = $oConverter->toPg();
        } else {
            $this->df_acabado = $df_acabado;
        }
    }

    /**
     * estableix el valor de l'atribut df_plazo de PendienteDB
     * Si df_plazo es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_plazo debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_plazo='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_plazo($df_plazo = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_plazo)) {
            $oConverter = new core\Converter('date', $df_plazo);
            $this->df_plazo = $oConverter->toPg();
        } else {
            $this->df_plazo = $df_plazo;
        }
    }

    /**
     * estableix el valor de l'atribut sref_mas de PendienteDB
     *
     * @param string sref_mas='' optional
     */
    function setRef_mas($sref_mas = '')
    {
        $this->sref_mas = $sref_mas;
    }

    /**
     * estableix el valor de l'atribut sobserv de PendienteDB
     *
     * @param string sobserv='' optional
     */
    function setObserv($sobserv = '')
    {
        $this->sobserv = $sobserv;
    }

    /**
     * estableix el valor de l'atribut sencargado de PendienteDB
     *
     * @param string sencargado='' optional
     */
    function setEncargado($sencargado = '')
    {
        $this->sencargado = $sencargado;
    }

    /**
     * estableix el valor de l'atribut bcancilleria de PendienteDB
     *
     * @param boolean bcancilleria='f' optional
     */
    function setCancilleria($bcancilleria = 'f')
    {
        $this->bcancilleria = $bcancilleria;
    }

    /**
     * estableix el valor de l'atribut ivisibilidad de PendienteDB
     *
     * @param integer ivisibilidad=''
     */
    function setVisibilidad($ivisibilidad = '')
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    /**
     * estableix el valor de l'atribut sdetalle de PendienteDB
     *
     * @param string sdetalle='' optional
     */
    function setDetalle($sdetalle = '')
    {
        $this->sdetalle = $sdetalle;
    }

    /**
     * estableix el valor de l'atribut spendiente_con de PendienteDB
     *
     * @param string spendiente_con='' optional
     */
    function setPendiente_con($spendiente_con = '')
    {
        $this->spendiente_con = $spendiente_con;
    }

    /**
     * estableix el valor de l'atribut setiquetas de PendienteDB
     *
     * @param string setiquetas='' optional
     */
    function setEtiquetas($setiquetas = '')
    {
        $this->setiquetas = $setiquetas;
    }

    /**
     * estableix el valor de l'atribut soficinas de PendienteDB
     *
     * @param string soficinas='' optional
     */
    function setOficinas($soficinas = '')
    {
        $this->soficinas = $soficinas;
    }

    /**
     * estableix el valor de l'atribut iid_oficina de PendienteDB
     *
     * @param integer iid_oficina='' optional
     */
    function setId_oficina($iid_oficina = '')
    {
        $this->iid_oficina = $iid_oficina;
    }

    /**
     * estableix el valor de l'atribut srrule de PendienteDB
     *
     * @param string srrule='' optional
     */
    function setRrule($srrule = '')
    {
        $this->srrule = $srrule;
    }

    /**
     * estableix el valor de l'atribut df_inicio de PendienteDB
     * Si df_inicio es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_inicio debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_inicio='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_inicio($df_inicio = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_inicio)) {
            $oConverter = new core\Converter('date', $df_inicio);
            $this->df_inicio = $oConverter->toPg();
        } else {
            $this->df_inicio = $df_inicio;
        }
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_pendiente('');
        $this->setAsunto('');
        $this->setStatus('');
        $this->setF_acabado('');
        $this->setF_plazo('');
        $this->setRef_mas('');
        $this->setObserv('');
        $this->setEncargado('');
        $this->setCancilleria('');
        $this->setVisibilidad('');
        $this->setDetalle('');
        $this->setPendiente_con('');
        $this->setEtiquetas('');
        $this->setOficinas('');
        $this->setId_oficina('');
        $this->setRrule('');
        $this->setF_inicio('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de PendienteDB en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_pendiente' => $this->iid_pendiente);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de PendienteDB en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_pendiente') && $val_id !== '') {
                    $this->iid_pendiente = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_pendiente = (int)$a_id;
                $this->aPrimary_key = array('iid_pendiente' => $this->iid_pendiente);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_pendiente='$this->iid_pendiente'")) === FALSE) {
            $sClauError = 'PendienteDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_pendiente de PendienteDB
     *
     * @return integer iid_pendiente
     */
    function getId_pendiente()
    {
        if (!isset($this->iid_pendiente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_pendiente;
    }

    /**
     * Recupera l'atribut sasunto de PendienteDB
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
     * Recupera l'atribut sstatus de PendienteDB
     *
     * @return string sstatus
     */
    function getStatus()
    {
        if (!isset($this->sstatus) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sstatus;
    }

    /**
     * Recupera l'atribut df_acabado de PendienteDB
     *
     * @return web\DateTimeLocal df_acabado
     */
    function getF_acabado()
    {
        if (!isset($this->df_acabado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_acabado)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_acabado);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut df_plazo de PendienteDB
     *
     * @return web\DateTimeLocal df_plazo
     */
    function getF_plazo()
    {
        if (!isset($this->df_plazo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_plazo)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_plazo);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut sref_mas de PendienteDB
     *
     * @return string sref_mas
     */
    function getRef_mas()
    {
        if (!isset($this->sref_mas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sref_mas;
    }

    /**
     * Recupera l'atribut sobserv de PendienteDB
     *
     * @return string sobserv
     */
    function getObserv()
    {
        if (!isset($this->sobserv) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sobserv;
    }

    /**
     * Recupera l'atribut sencargado de PendienteDB
     *
     * @return string|null sencargado
     */
    public function getEncargado(): ?string
    {
        if (!isset($this->sencargado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sencargado;
    }

    /**
     * Recupera l'atribut bcancilleria de PendienteDB
     *
     * @return boolean bcancilleria
     */
    function getCancilleria()
    {
        if (!isset($this->bcancilleria) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bcancilleria;
    }

    /**
     * Recupera l'atribut ivisibilidad de PendienteDB
     *
     * @return integer ivisibilidad
     */
    function getVisibilidad()
    {
        if (!isset($this->ivisibilidad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivisibilidad;
    }

    /**
     * Recupera l'atribut sdetalle de PendienteDB
     *
     * @return string sdetalle
     */
    function getDetalle()
    {
        if (!isset($this->sdetalle) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdetalle;
    }

    /**
     * Recupera l'atribut spendiente_con de PendienteDB
     *
     * @return string spendiente_con
     */
    function getPendiente_con()
    {
        if (!isset($this->spendiente_con) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->spendiente_con;
    }

    /**
     * Recupera l'atribut setiquetas de PendienteDB
     *
     * @return string setiquetas
     */
    function getEtiquetas()
    {
        if (!isset($this->setiquetas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->setiquetas;
    }

    /**
     * Recupera l'atribut soficinas de PendienteDB
     *
     * @return string soficinas
     */
    function getOficinas()
    {
        if (!isset($this->soficinas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->soficinas;
    }

    /**
     * Recupera l'atribut iid_oficina de PendienteDB
     *
     * @return integer iid_oficina
     */
    function getId_oficina()
    {
        if (!isset($this->iid_oficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_oficina;
    }

    /**
     * Recupera l'atribut srrule de PendienteDB
     *
     * @return string srrule
     */
    function getRrule()
    {
        if (!isset($this->srrule) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->srrule;
    }

    /**
     * Recupera l'atribut df_inicio de PendienteDB
     *
     * @return web\DateTimeLocal df_inicio
     */
    function getF_inicio()
    {
        if (!isset($this->df_inicio) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_inicio)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_inicio);
        return $oConverter->fromPg();
    }

    public function setEtiquetasArray($aEtiquetas)
    {
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        $etiquetas_csv = implode(",", $a_filter_etiquetas);

        $this->setiquetas = $etiquetas_csv;
    }

    public function setOficinasArray($aOficinas)
    {
        $a_filter_oficinas = array_filter($aOficinas); // Quita los elementos vacíos y nulos.
        $oficinas_csv = implode(",", $a_filter_oficinas);

        $this->oficinas = $oficinas_csv;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oPendienteDBSet = new core\Set();

        $oPendienteDBSet->add($this->getDatosAsunto());
        $oPendienteDBSet->add($this->getDatosStatus());
        $oPendienteDBSet->add($this->getDatosF_acabado());
        $oPendienteDBSet->add($this->getDatosF_plazo());
        $oPendienteDBSet->add($this->getDatosRef_mas());
        $oPendienteDBSet->add($this->getDatosObserv());
        $oPendienteDBSet->add($this->getDatosEncargado());
        $oPendienteDBSet->add($this->getDatosCancilleria());
        $oPendienteDBSet->add($this->getDatosVisibilidad());
        $oPendienteDBSet->add($this->getDatosDetalle());
        $oPendienteDBSet->add($this->getDatosPendiente_con());
        $oPendienteDBSet->add($this->getDatosEtiquetas());
        $oPendienteDBSet->add($this->getDatosOficinas());
        $oPendienteDBSet->add($this->getDatosId_oficina());
        $oPendienteDBSet->add($this->getDatosRrule());
        $oPendienteDBSet->add($this->getDatosF_inicio());
        return $oPendienteDBSet->getTot();
    }

    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut sasunto de PendienteDB
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
     * Recupera les propietats de l'atribut sstatus de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosStatus()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'status'));
        $oDatosCampo->setEtiqueta(_("status"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_acabado de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_acabado()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_acabado'));
        $oDatosCampo->setEtiqueta(_("f_acabado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_plazo de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_plazo()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_plazo'));
        $oDatosCampo->setEtiqueta(_("f_plazo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sref_mas de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosRef_mas()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'ref_mas'));
        $oDatosCampo->setEtiqueta(_("ref_mas"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sobserv de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosObserv()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'observ'));
        $oDatosCampo->setEtiqueta(_("observ"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sencargado de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEncargado()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'encargado'));
        $oDatosCampo->setEtiqueta(_("encargado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bcancilleria de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCancilleria()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'cancilleria'));
        $oDatosCampo->setEtiqueta(_("cancilleria"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivisibilidad de PendienteDB
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
     * Recupera les propietats de l'atribut sdetalle de PendienteDB
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
     * Recupera les propietats de l'atribut spendiente_con de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPendiente_con()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'pendiente_con'));
        $oDatosCampo->setEtiqueta(_("pendiente_con"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut setiquetas de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEtiquetas()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'etiquetas'));
        $oDatosCampo->setEtiqueta(_("etiquetas"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut soficinas de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOficinas()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'oficinas'));
        $oDatosCampo->setEtiqueta(_("oficinas"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_oficina de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_oficina()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_oficina'));
        $oDatosCampo->setEtiqueta(_("id_oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut srrule de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosRrule()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'rrule'));
        $oDatosCampo->setEtiqueta(_("rrule"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_inicio de PendienteDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_inicio()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_inicio'));
        $oDatosCampo->setEtiqueta(_("f_inicio"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de PendienteDB en un array
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
