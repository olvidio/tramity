<?php

namespace davical\model\entity;

use core;
use PDO;
use PDOException;
use web;

/**
 * Fitxer amb la Classe que accedeix a la taula calendar_item
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 25/2/2021
 */

/**
 * Classe que implementa l'entitat calendar_item
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 25/2/2021
 */
class CalendarItem extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de CalendarItem
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de CalendarItem
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de CalendarItem
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de CalendarItem
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de CalendarItem
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de CalendarItem
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * User_no de CalendarItem
     *
     * @var integer
     */
    private $iuser_no;
    /**
     * Dav_name de CalendarItem
     *
     * @var string
     */
    private $sdav_name;
    /**
     * Dav_etag de CalendarItem
     *
     * @var string
     */
    private $sdav_etag;
    /**
     * Uid de CalendarItem
     *
     * @var string
     */
    private $suid;
    /**
     * Created de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $dcreated;
    /**
     * Last_modified de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $dlast_modified;
    /**
     * Dtstamp de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $ddtstamp;
    /**
     * Dtstart de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $ddtstart;
    /**
     * Dtend de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $ddtend;
    /**
     * Due de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $ddue;
    /**
     * Summary de CalendarItem
     *
     * @var string
     */
    private $ssummary;
    /**
     * Location de CalendarItem
     *
     * @var string
     */
    private $slocation;
    /**
     * Description de CalendarItem
     *
     * @var string
     */
    private $sdescription;
    /**
     * Priority de CalendarItem
     *
     * @var integer
     */
    private $ipriority;
    /**
     * Class de CalendarItem
     *
     * @var string
     */
    private $sclass;
    /**
     * Transp de CalendarItem
     *
     * @var string
     */
    private $stransp;
    /**
     * Rrule de CalendarItem
     *
     * @var string
     */
    private $srrule;
    /**
     * Url de CalendarItem
     *
     * @var string
     */
    private $surl;
    /**
     * Percent_complete de CalendarItem
     *
     * @var float
     */
    private $ipercent_complete;
    /**
     * Tz_id de CalendarItem
     *
     * @var string
     */
    private $stz_id;
    /**
     * Status de CalendarItem
     *
     * @var string
     */
    private $sstatus;
    /**
     * Completed de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $dcompleted;
    /**
     * Dav_id de CalendarItem
     *
     * @var integer
     */
    private $idav_id;
    /**
     * Collection_id de CalendarItem
     *
     * @var integer
     */
    private $icollection_id;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * First_instance_start de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $dfirst_instance_start;
    /**
     * Last_instance_end de CalendarItem
     *
     * @var web\DateTimeLocal
     */
    private $dlast_instance_end;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iuser_no,sdav_name
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'dav_id') && $val_id !== '') {
                    $this->idav_id = (integer)$val_id;
                }
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('calendar_item');
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
        $aDades['user_no'] = $this->iuser_no;
        $aDades['dav_name'] = $this->sdav_name;
        $aDades['dav_etag'] = $this->sdav_etag;
        $aDades['uid'] = $this->suid;
        $aDades['created'] = $this->dcreated;
        $aDades['last_modified'] = $this->dlast_modified;
        $aDades['dtstamp'] = $this->ddtstamp;
        $aDades['dtstart'] = $this->ddtstart;
        $aDades['dtend'] = $this->ddtend;
        $aDades['due'] = $this->ddue;
        $aDades['summary'] = $this->ssummary;
        $aDades['location'] = $this->slocation;
        $aDades['description'] = $this->sdescription;
        $aDades['priority'] = $this->ipriority;
        $aDades['class'] = $this->sclass;
        $aDades['transp'] = $this->stransp;
        $aDades['rrule'] = $this->srrule;
        $aDades['url'] = $this->surl;
        $aDades['percent_complete'] = $this->ipercent_complete;
        $aDades['tz_id'] = $this->stz_id;
        $aDades['status'] = $this->sstatus;
        $aDades['completed'] = $this->dcompleted;
        $aDades['collection_id'] = $this->icollection_id;
        $aDades['first_instance_start'] = $this->dfirst_instance_start;
        $aDades['last_instance_end'] = $this->dlast_instance_end;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					user_no                  = :user_no,
					dav_name                 = :dav_name,
					dav_etag                 = :dav_etag,
					uid                      = :uid,
					created                  = :created,
					last_modified            = :last_modified,
					dtstamp                  = :dtstamp,
					dtstart                  = :dtstart,
					dtend                    = :dtend,
					due                      = :due,
					summary                  = :summary,
					location                 = :location,
					description              = :description,
					priority                 = :priority,
					class                    = :class,
					transp                   = :transp,
					rrule                    = :rrule,
					url                      = :url,
					percent_complete         = :percent_complete,
					tz_id                    = :tz_id,
					status                   = :status,
					completed                = :completed,
					collection_id            = :collection_id,
					first_instance_start     = :first_instance_start,
					last_instance_end        = :last_instance_end";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE dav_id='$this->idav_id'")) === FALSE) {
                $sClauError = 'CalendarItem.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'CalendarItem.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->idav_id);
            $campos = "(dav_id,user_no,dav_name,dav_etag,uid,created,last_modified,dtstamp,dtstart,dtend,due,summary,location,description,priority,class,transp,rrule,url,percent_complete,tz_id,status,completed,collection_id,first_instance_start,last_instance_end)";
            $valores = "(:dav_id,:user_no,:dav_name,:dav_etag,:uid,:created,:last_modified,:dtstamp,:dtstart,:dtend,:due,:summary,:location,:description,:priority,:class,:transp,:rrule,:url,:percent_complete,:tz_id,:status,:completed,:collection_id,:first_instance_start,:last_instance_end)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'CalendarItem.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'CalendarItem.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
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
        if (isset($this->idav_id)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE dav_id='$this->idav_id'")) === FALSE) {
                $sClauError = 'CalendarItem.carregar';
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
        if (array_key_exists('user_no', $aDades)) {
            $this->setUser_no($aDades['user_no']);
        }
        if (array_key_exists('dav_name', $aDades)) {
            $this->setDav_name($aDades['dav_name']);
        }
        if (array_key_exists('dav_etag', $aDades)) {
            $this->setDav_etag($aDades['dav_etag']);
        }
        if (array_key_exists('uid', $aDades)) {
            $this->setUid($aDades['uid']);
        }
        if (array_key_exists('created', $aDades)) {
            $this->setCreated($aDades['created'], $convert);
        }
        if (array_key_exists('last_modified', $aDades)) {
            $this->setLast_modified($aDades['last_modified'], $convert);
        }
        if (array_key_exists('dtstamp', $aDades)) {
            $this->setDtstamp($aDades['dtstamp'], $convert);
        }
        if (array_key_exists('dtstart', $aDades)) {
            $this->setDtstart($aDades['dtstart'], $convert);
        }
        if (array_key_exists('dtend', $aDades)) {
            $this->setDtend($aDades['dtend'], $convert);
        }
        if (array_key_exists('due', $aDades)) {
            $this->setDue($aDades['due'], $convert);
        }
        if (array_key_exists('summary', $aDades)) {
            $this->setSummary($aDades['summary']);
        }
        if (array_key_exists('location', $aDades)) {
            $this->setLocation($aDades['location']);
        }
        if (array_key_exists('description', $aDades)) {
            $this->setDescription($aDades['description']);
        }
        if (array_key_exists('priority', $aDades)) {
            $this->setPriority($aDades['priority']);
        }
        if (array_key_exists('class', $aDades)) {
            $this->setClass($aDades['class']);
        }
        if (array_key_exists('transp', $aDades)) {
            $this->setTransp($aDades['transp']);
        }
        if (array_key_exists('rrule', $aDades)) {
            $this->setRrule($aDades['rrule']);
        }
        if (array_key_exists('url', $aDades)) {
            $this->setUrl($aDades['url']);
        }
        if (array_key_exists('percent_complete', $aDades)) {
            $this->setPercent_complete($aDades['percent_complete']);
        }
        if (array_key_exists('tz_id', $aDades)) {
            $this->setTz_id($aDades['tz_id']);
        }
        if (array_key_exists('status', $aDades)) {
            $this->setStatus($aDades['status']);
        }
        if (array_key_exists('completed', $aDades)) {
            $this->setCompleted($aDades['completed'], $convert);
        }
        if (array_key_exists('dav_id', $aDades)) {
            $this->setDav_id($aDades['dav_id']);
        }
        if (array_key_exists('collection_id', $aDades)) {
            $this->setCollection_id($aDades['collection_id']);
        }
        if (array_key_exists('first_instance_start', $aDades)) {
            $this->setFirst_instance_start($aDades['first_instance_start'], $convert);
        }
        if (array_key_exists('last_instance_end', $aDades)) {
            $this->setLast_instance_end($aDades['last_instance_end'], $convert);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param integer iuser_no
     */
    function setUser_no($iuser_no)
    {
        $this->iuser_no = $iuser_no;
    }

    /**
     * @param string sdav_name
     */
    function setDav_name($sdav_name)
    {
        $this->sdav_name = $sdav_name;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string sdav_etag='' optional
     */
    function setDav_etag($sdav_etag = '')
    {
        $this->sdav_etag = $sdav_etag;
    }

    /**
     * @param string suid='' optional
     */
    function setUid($suid = '')
    {
        $this->suid = $suid;
    }

    /**
     * Si dcreated es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dcreated debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dcreated='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setCreated($dcreated = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dcreated)) {
            $oConverter = new core\ConverterDate('timestamp', $dcreated);
            $this->dcreated = $oConverter->toPg();
        } else {
            $this->dcreated = $dcreated;
        }
    }

    /**
     * Si dlast_modified es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dlast_modified debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dlast_modified='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setLast_modified($dlast_modified = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dlast_modified)) {
            $oConverter = new core\ConverterDate('timestamp', $dlast_modified);
            $this->dlast_modified = $oConverter->toPg();
        } else {
            $this->dlast_modified = $dlast_modified;
        }
    }

    /**
     * Si ddtstamp es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, ddtstamp debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string ddtstamp='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setDtstamp($ddtstamp = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($ddtstamp)) {
            $oConverter = new core\ConverterDate('timestamp', $ddtstamp);
            $this->ddtstamp = $oConverter->toPg();
        } else {
            $this->ddtstamp = $ddtstamp;
        }
    }

    /**
     * Si ddtstart es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, ddtstart debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string ddtstart='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setDtstart($ddtstart = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($ddtstart)) {
            $oConverter = new core\ConverterDate('timestamptz', $ddtstart);
            $this->ddtstart = $oConverter->toPg();
        } else {
            $this->ddtstart = $ddtstart;
        }
    }

    /**
     * Si ddtend es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, ddtend debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string ddtend='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setDtend($ddtend = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($ddtend)) {
            $oConverter = new core\ConverterDate('timestamptz', $ddtend);
            $this->ddtend = $oConverter->toPg();
        } else {
            $this->ddtend = $ddtend;
        }
    }

    /**
     * Si ddue es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, ddue debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string ddue='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setDue($ddue = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($ddue)) {
            $oConverter = new core\ConverterDate('timestamptz', $ddue);
            $this->ddue = $oConverter->toPg();
        } else {
            $this->ddue = $ddue;
        }
    }

    /**
     * @param string ssummary='' optional
     */
    function setSummary($ssummary = '')
    {
        $this->ssummary = $ssummary;
    }

    /**
     * @param string slocation='' optional
     */
    function setLocation($slocation = '')
    {
        $this->slocation = $slocation;
    }

    /**
     * @param string sdescription='' optional
     */
    function setDescription($sdescription = '')
    {
        $this->sdescription = $sdescription;
    }

    /**
     * @param integer ipriority='' optional
     */
    function setPriority($ipriority = '')
    {
        $this->ipriority = $ipriority;
    }

    /**
     * @param string sclass='' optional
     */
    function setClass($sclass = '')
    {
        $this->sclass = $sclass;
    }

    /**
     * @param string stransp='' optional
     */
    function setTransp($stransp = '')
    {
        $this->stransp = $stransp;
    }

    /**
     * @param string srrule='' optional
     */
    function setRrule($srrule = '')
    {
        $this->srrule = $srrule;
    }

    /**
     * @param string surl='' optional
     */
    function setUrl($surl = '')
    {
        $this->surl = $surl;
    }

    /**
     * @param float ipercent_complete='' optional
     */
    function setPercent_complete($ipercent_complete = '')
    {
        $this->ipercent_complete = $ipercent_complete;
    }

    /**
     * @param string stz_id='' optional
     */
    function setTz_id($stz_id = '')
    {
        $this->stz_id = $stz_id;
    }

    /**
     * @param string sstatus='' optional
     */
    function setStatus($sstatus = '')
    {
        $this->sstatus = $sstatus;
    }

    /**
     * Si dcompleted es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dcompleted debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dcompleted='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setCompleted($dcompleted = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dcompleted)) {
            $oConverter = new core\ConverterDate('timestamptz', $dcompleted);
            $this->dcompleted = $oConverter->toPg();
        } else {
            $this->dcompleted = $dcompleted;
        }
    }

    /**
     * @param integer idav_id='' optional
     */
    function setDav_id($idav_id = '')
    {
        $this->idav_id = $idav_id;
    }

    /**
     * @param integer icollection_id='' optional
     */
    function setCollection_id($icollection_id = '')
    {
        $this->icollection_id = $icollection_id;
    }

    /**
     * Si dfirst_instance_start es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dfirst_instance_start debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dfirst_instance_start='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setFirst_instance_start($dfirst_instance_start = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dfirst_instance_start)) {
            $oConverter = new core\ConverterDate('timestamptz', $dfirst_instance_start);
            $this->dfirst_instance_start = $oConverter->toPg();
        } else {
            $this->dfirst_instance_start = $dfirst_instance_start;
        }
    }

    /**
     * Si dlast_instance_end es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dlast_instance_end debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dlast_instance_end='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setLast_instance_end($dlast_instance_end = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dlast_instance_end)) {
            $oConverter = new core\ConverterDate('timestamptz', $dlast_instance_end);
            $this->dlast_instance_end = $oConverter->toPg();
        } else {
            $this->dlast_instance_end = $dlast_instance_end;
        }
    }

    
    /**
     * Recupera las claus primàries de CalendarItem en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('dav_id' => $this->idav_id);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de CalendarItem en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'dav_id') && $val_id !== '') {
                    $this->idav_id = (integer)$val_id;
                }
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE dav_id='$this->idav_id'")) === FALSE) {
            $sClauError = 'CalendarItem.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iuser_no de CalendarItem
     *
     * @return integer iuser_no
     */
    function getUser_no()
    {
        if (!isset($this->iuser_no) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iuser_no;
    }

    /**
     * Recupera l'atribut sdav_name de CalendarItem
     *
     * @return string sdav_name
     */
    function getDav_name()
    {
        if (!isset($this->sdav_name) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdav_name;
    }

    /**
     * Recupera l'atribut sdav_etag de CalendarItem
     *
     * @return string sdav_etag
     */
    function getDav_etag()
    {
        if (!isset($this->sdav_etag) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdav_etag;
    }

    /**
     * Recupera l'atribut suid de CalendarItem
     *
     * @return string suid
     */
    function getUid()
    {
        if (!isset($this->suid) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->suid;
    }

    /**
     * Recupera l'atribut dcreated de CalendarItem
     *
     * @return web\DateTimeLocal dcreated
     */
    function getCreated()
    {
        if (!isset($this->dcreated) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dcreated)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamp', $this->dcreated);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut dlast_modified de CalendarItem
     *
     * @return web\DateTimeLocal dlast_modified
     */
    function getLast_modified()
    {
        if (!isset($this->dlast_modified) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dlast_modified)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamp', $this->dlast_modified);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ddtstamp de CalendarItem
     *
     * @return web\DateTimeLocal ddtstamp
     */
    function getDtstamp()
    {
        if (!isset($this->ddtstamp) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->ddtstamp)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamp', $this->ddtstamp);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ddtstart de CalendarItem
     *
     * @return web\DateTimeLocal ddtstart
     */
    function getDtstart()
    {
        if (!isset($this->ddtstart) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->ddtstart)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->ddtstart);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ddtend de CalendarItem
     *
     * @return web\DateTimeLocal ddtend
     */
    function getDtend()
    {
        if (!isset($this->ddtend) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->ddtend)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->ddtend);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ddue de CalendarItem
     *
     * @return web\DateTimeLocal ddue
     */
    function getDue()
    {
        if (!isset($this->ddue) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->ddue)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->ddue);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut ssummary de CalendarItem
     *
     * @return string ssummary
     */
    function getSummary()
    {
        if (!isset($this->ssummary) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ssummary;
    }

    /**
     * Recupera l'atribut slocation de CalendarItem
     *
     * @return string slocation
     */
    function getLocation()
    {
        if (!isset($this->slocation) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->slocation;
    }

    /**
     * Recupera l'atribut sdescription de CalendarItem
     *
     * @return string sdescription
     */
    function getDescription()
    {
        if (!isset($this->sdescription) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdescription;
    }

    /**
     * Recupera l'atribut ipriority de CalendarItem
     *
     * @return integer ipriority
     */
    function getPriority()
    {
        if (!isset($this->ipriority) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ipriority;
    }

    /**
     * Recupera l'atribut sclass de CalendarItem
     *
     * @return string sclass
     */
    function getClass()
    {
        if (!isset($this->sclass) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sclass;
    }

    /**
     * Recupera l'atribut stransp de CalendarItem
     *
     * @return string stransp
     */
    function getTransp()
    {
        if (!isset($this->stransp) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->stransp;
    }

    /**
     * Recupera l'atribut srrule de CalendarItem
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
     * Recupera l'atribut surl de CalendarItem
     *
     * @return string surl
     */
    function getUrl()
    {
        if (!isset($this->surl) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->surl;
    }

    /**
     * Recupera l'atribut ipercent_complete de CalendarItem
     *
     * @return float ipercent_complete
     */
    function getPercent_complete()
    {
        if (!isset($this->ipercent_complete) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ipercent_complete;
    }

    /**
     * Recupera l'atribut stz_id de CalendarItem
     *
     * @return string stz_id
     */
    function getTz_id()
    {
        if (!isset($this->stz_id) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->stz_id;
    }

    /**
     * Recupera l'atribut sstatus de CalendarItem
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
     * Recupera l'atribut dcompleted de CalendarItem
     *
     * @return web\DateTimeLocal dcompleted
     */
    function getCompleted()
    {
        if (!isset($this->dcompleted) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dcompleted)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->dcompleted);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut idav_id de CalendarItem
     *
     * @return integer idav_id
     */
    function getDav_id()
    {
        if (!isset($this->idav_id) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->idav_id;
    }

    /**
     * Recupera l'atribut icollection_id de CalendarItem
     *
     * @return integer icollection_id
     */
    function getCollection_id()
    {
        if (!isset($this->icollection_id) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icollection_id;
    }

    /**
     * Recupera l'atribut dfirst_instance_start de CalendarItem
     *
     * @return web\DateTimeLocal dfirst_instance_start
     */
    function getFirst_instance_start()
    {
        if (!isset($this->dfirst_instance_start) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dfirst_instance_start)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->dfirst_instance_start);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut dlast_instance_end de CalendarItem
     *
     * @return web\DateTimeLocal dlast_instance_end
     */
    function getLast_instance_end()
    {
        if (!isset($this->dlast_instance_end) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dlast_instance_end)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\ConverterDate('timestamptz', $this->dlast_instance_end);
        return $oConverter->fromPg();
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oCalendarItemSet = new core\Set();

        $oCalendarItemSet->add($this->getDatosUser_no());
        $oCalendarItemSet->add($this->getDatosDav_name());
        $oCalendarItemSet->add($this->getDatosDav_etag());
        $oCalendarItemSet->add($this->getDatosUid());
        $oCalendarItemSet->add($this->getDatosCreated());
        $oCalendarItemSet->add($this->getDatosLast_modified());
        $oCalendarItemSet->add($this->getDatosDtstamp());
        $oCalendarItemSet->add($this->getDatosDtstart());
        $oCalendarItemSet->add($this->getDatosDtend());
        $oCalendarItemSet->add($this->getDatosDue());
        $oCalendarItemSet->add($this->getDatosSummary());
        $oCalendarItemSet->add($this->getDatosLocation());
        $oCalendarItemSet->add($this->getDatosDescription());
        $oCalendarItemSet->add($this->getDatosPriority());
        $oCalendarItemSet->add($this->getDatosClass());
        $oCalendarItemSet->add($this->getDatosTransp());
        $oCalendarItemSet->add($this->getDatosRrule());
        $oCalendarItemSet->add($this->getDatosUrl());
        $oCalendarItemSet->add($this->getDatosPercent_complete());
        $oCalendarItemSet->add($this->getDatosTz_id());
        $oCalendarItemSet->add($this->getDatosStatus());
        $oCalendarItemSet->add($this->getDatosCompleted());
        $oCalendarItemSet->add($this->getDatosDav_id());
        $oCalendarItemSet->add($this->getDatosCollection_id());
        $oCalendarItemSet->add($this->getDatosFirst_instance_start());
        $oCalendarItemSet->add($this->getDatosLast_instance_end());
        return $oCalendarItemSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iuser_no de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUser_no()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'user_no'));
        $oDatosCampo->setEtiqueta(_("user_no"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdav_name de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDav_name()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dav_name'));
        $oDatosCampo->setEtiqueta(_("dav_name"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdav_etag de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDav_etag()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dav_etag'));
        $oDatosCampo->setEtiqueta(_("dav_etag"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut suid de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUid()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'uid'));
        $oDatosCampo->setEtiqueta(_("uid"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dcreated de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCreated()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'created'));
        $oDatosCampo->setEtiqueta(_("created"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dlast_modified de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosLast_modified()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'last_modified'));
        $oDatosCampo->setEtiqueta(_("last_modified"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ddtstamp de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDtstamp()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dtstamp'));
        $oDatosCampo->setEtiqueta(_("dtstamp"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ddtstart de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDtstart()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dtstart'));
        $oDatosCampo->setEtiqueta(_("dtstart"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ddtend de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDtend()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dtend'));
        $oDatosCampo->setEtiqueta(_("dtend"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ddue de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDue()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'due'));
        $oDatosCampo->setEtiqueta(_("due"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ssummary de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosSummary()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'summary'));
        $oDatosCampo->setEtiqueta(_("summary"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut slocation de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosLocation()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'location'));
        $oDatosCampo->setEtiqueta(_("location"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescription de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDescription()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'description'));
        $oDatosCampo->setEtiqueta(_("description"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ipriority de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPriority()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'priority'));
        $oDatosCampo->setEtiqueta(_("priority"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sclass de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosClass()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'class'));
        $oDatosCampo->setEtiqueta(_("class"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut stransp de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTransp()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'transp'));
        $oDatosCampo->setEtiqueta(_("transp"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut srrule de CalendarItem
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
     * Recupera les propietats de l'atribut surl de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUrl()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'url'));
        $oDatosCampo->setEtiqueta(_("url"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ipercent_complete de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPercent_complete()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'percent_complete'));
        $oDatosCampo->setEtiqueta(_("percent_complete"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut stz_id de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTz_id()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tz_id'));
        $oDatosCampo->setEtiqueta(_("tz_id"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sstatus de CalendarItem
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
     * Recupera les propietats de l'atribut dcompleted de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCompleted()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'completed'));
        $oDatosCampo->setEtiqueta(_("completed"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut idav_id de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDav_id()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dav_id'));
        $oDatosCampo->setEtiqueta(_("dav_id"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut icollection_id de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCollection_id()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'collection_id'));
        $oDatosCampo->setEtiqueta(_("collection_id"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dfirst_instance_start de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosFirst_instance_start()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'first_instance_start'));
        $oDatosCampo->setEtiqueta(_("first_instance_start"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dlast_instance_end de CalendarItem
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosLast_instance_end()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'last_instance_end'));
        $oDatosCampo->setEtiqueta(_("last_instance_end"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de CalendarItem en un array
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
