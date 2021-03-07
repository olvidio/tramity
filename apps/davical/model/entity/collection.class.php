<?php
namespace davical\model\entity;
use core;
use web;
/**
 * Fitxer amb la Classe que accedeix a la taula collection
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */
/**
 * Classe que implementa l'entitat collection
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */
class Collection Extends core\ClasePropiedades {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de Collection
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de Collection
	 *
	 * @var array
	 */
	 private $aDades;

	/**
	 * bLoaded de Collection
	 *
	 * @var boolean
	 */
	 private $bLoaded = FALSE;

	/**
	 * Id_schema de Collection
	 *
	 * @var integer
	 */
	 private $iid_schema;

	/**
	 * User_no de Collection
	 *
	 * @var integer
	 */
	 private $iuser_no;
	/**
	 * Parent_container de Collection
	 *
	 * @var string
	 */
	 private $sparent_container;
	/**
	 * Dav_name de Collection
	 *
	 * @var string
	 */
	 private $sdav_name;
	/**
	 * Dav_etag de Collection
	 *
	 * @var string
	 */
	 private $sdav_etag;
	/**
	 * Dav_displayname de Collection
	 *
	 * @var string
	 */
	 private $sdav_displayname;
	/**
	 * Is_calendar de Collection
	 *
	 * @var boolean
	 */
	 private $bis_calendar;
	/**
	 * Created de Collection
	 *
	 * @var web\DateTimeLocal
	 */
	 private $dcreated;
	/**
	 * Modified de Collection
	 *
	 * @var web\DateTimeLocal
	 */
	 private $dmodified;
	/**
	 * Public_events_only de Collection
	 *
	 * @var boolean
	 */
	 private $bpublic_events_only;
	/**
	 * Publicly_readable de Collection
	 *
	 * @var boolean
	 */
	 private $bpublicly_readable;
	/**
	 * Collection_id de Collection
	 *
	 * @var integer
	 */
	 private $icollection_id;
	/**
	 * Default_privileges de Collection
	 *
	 * @var integer
	 */
	 private $idefault_privileges;
	/**
	 * Is_addressbook de Collection
	 *
	 * @var boolean
	 */
	 private $bis_addressbook;
	/**
	 * Resourcetypes de Collection
	 *
	 * @var string
	 */
	 private $sresourcetypes;
	/**
	 * Schedule_transp de Collection
	 *
	 * @var string
	 */
	 private $sschedule_transp;
	/**
	 * Timezone de Collection
	 *
	 * @var string
	 */
	 private $stimezone;
	/**
	 * Description de Collection
	 *
	 * @var string
	 */
	 private $sdescription;
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
	/**
	 * oDbl de Collection
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de Collection
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
	 * @param integer|array icollection_id
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBDavical'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'collection_id') && $val_id !== '') $this->icollection_id = (int)$val_id; // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->icollection_id = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('icollection_id' => $this->icollection_id);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('collection');
	}

	/* METODES PUBLICS ----------------------------------------------------------*/

	public function cambiarNombre($oficina_new, $oficina_old) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $parent_container_new = "/".$oficina_new."/";
	    $parent_container_old = "/".$oficina_old."/";
	    
	    // oficina
	    $dav_name_new = $parent_container_new."oficina/"; 
	    $dav_name_old = $parent_container_old."oficina/"; 
	    $sQry = "UPDATE $nom_tabla SET parent_container='$parent_container_new', dav_name='$dav_name_new'
                WHERE dav_name='$dav_name_old'";
	    
	    if (($oDbl->query($sQry)) === FALSE) {
	        $sClauError = 'DavicalUser.cambioNombre';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    
	    // registro
	    $dav_name_new = $parent_container_new."registro/"; 
	    $dav_name_old = $parent_container_old."registro/"; 
	    $sQry = "UPDATE $nom_tabla SET parent_container='$parent_container_new', dav_name='$dav_name_new'
                WHERE dav_name='$dav_name_old'";
	    
	    if (($oDbl->query($sQry)) === FALSE) {
	        $sClauError = 'DavicalUser.cambioNombre';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	}
	
	/**
	 * Desa els atributs de l'objecte a la base de dades.
	 * Si no hi ha el registre, fa el insert, si hi es fa el update.
	 *
	 */
	public function DBGuardar() {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if ($this->DBCarregar('guardar') === FALSE) { $bInsert=TRUE; } else { $bInsert=FALSE; }
		$aDades=array();
		$aDades['user_no'] = $this->iuser_no;
		$aDades['parent_container'] = $this->sparent_container;
		$aDades['dav_name'] = $this->sdav_name;
		$aDades['dav_etag'] = $this->sdav_etag;
		$aDades['dav_displayname'] = $this->sdav_displayname;
		$aDades['is_calendar'] = $this->bis_calendar;
		$aDades['created'] = $this->dcreated;
		$aDades['modified'] = $this->dmodified;
		$aDades['public_events_only'] = $this->bpublic_events_only;
		$aDades['publicly_readable'] = $this->bpublicly_readable;
		$aDades['default_privileges'] = $this->idefault_privileges;
		$aDades['is_addressbook'] = $this->bis_addressbook;
		$aDades['resourcetypes'] = $this->sresourcetypes;
		$aDades['schedule_transp'] = $this->sschedule_transp;
		$aDades['timezone'] = $this->stimezone;
		$aDades['description'] = $this->sdescription;
		array_walk($aDades, 'core\poner_null');
		//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
		if ( core\is_true($aDades['is_calendar']) ) { $aDades['is_calendar']='true'; } else { $aDades['is_calendar']='false'; }
		if ( core\is_true($aDades['public_events_only']) ) { $aDades['public_events_only']='true'; } else { $aDades['public_events_only']='false'; }
		if ( core\is_true($aDades['publicly_readable']) ) { $aDades['publicly_readable']='true'; } else { $aDades['publicly_readable']='false'; }
		if ( core\is_true($aDades['is_addressbook']) ) { $aDades['is_addressbook']='true'; } else { $aDades['is_addressbook']='false'; }

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					user_no                  = :user_no,
					parent_container         = :parent_container,
					dav_name                 = :dav_name,
					dav_etag                 = :dav_etag,
					dav_displayname          = :dav_displayname,
					is_calendar              = :is_calendar,
					created                  = :created,
					modified                 = :modified,
					public_events_only       = :public_events_only,
					publicly_readable        = :publicly_readable,
					default_privileges       = :default_privileges,
					is_addressbook           = :is_addressbook,
					resourcetypes            = :resourcetypes,
					schedule_transp          = :schedule_transp,
					timezone                 = :timezone,
					description              = :description";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE collection_id='$this->icollection_id'")) === FALSE) {
				$sClauError = 'Collection.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'Collection.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			$campos="(user_no,parent_container,dav_name,dav_etag,dav_displayname,is_calendar,created,modified,public_events_only,publicly_readable,default_privileges,is_addressbook,resourcetypes,schedule_transp,timezone,description)";
			$valores="(:user_no,:parent_container,:dav_name,:dav_etag,:dav_displayname,:is_calendar,:created,:modified,:public_events_only,:publicly_readable,:default_privileges,:is_addressbook,:resourcetypes,:schedule_transp,:timezone,:description)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'Collection.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'Collection.insertar.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
			$this->collection_id = $oDbl->lastInsertId('dav_id_seq');
		}
		$this->setAllAtributes($aDades);
		return TRUE;
	}

	/**
	 * Carrega els camps de la base de dades com atributs de l'objecte.
	 *
	 */
	public function DBCarregar($que=null) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (isset($this->icollection_id)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE collection_id='$this->icollection_id'")) === FALSE) {
				$sClauError = 'Collection.carregar';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			}
			$aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
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
	 * Elimina el registre de la base de dades corresponent a l'objecte.
	 *
	 */
	public function DBEliminar() {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE collection_id='$this->icollection_id'")) === FALSE) {
			$sClauError = 'Collection.eliminar';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		return TRUE;
	}
	
	/* METODES ALTRES  ----------------------------------------------------------*/
	/* METODES PRIVATS ----------------------------------------------------------*/

	/**
	 * Estableix el valor de tots els atributs
	 *
	 * @param array $aDades
	 */
	function setAllAtributes($aDades,$convert=FALSE) {
		if (!is_array($aDades)) return;
		if (array_key_exists('id_schema',$aDades)) $this->setId_schema($aDades['id_schema']);
		if (array_key_exists('user_no',$aDades)) $this->setUser_no($aDades['user_no']);
		if (array_key_exists('parent_container',$aDades)) $this->setParent_container($aDades['parent_container']);
		if (array_key_exists('dav_name',$aDades)) $this->setDav_name($aDades['dav_name']);
		if (array_key_exists('dav_etag',$aDades)) $this->setDav_etag($aDades['dav_etag']);
		if (array_key_exists('dav_displayname',$aDades)) $this->setDav_displayname($aDades['dav_displayname']);
		if (array_key_exists('is_calendar',$aDades)) $this->setIs_calendar($aDades['is_calendar']);
		if (array_key_exists('created',$aDades)) $this->setCreated($aDades['created'],$convert);
		if (array_key_exists('modified',$aDades)) $this->setModified($aDades['modified'],$convert);
		if (array_key_exists('public_events_only',$aDades)) $this->setPublic_events_only($aDades['public_events_only']);
		if (array_key_exists('publicly_readable',$aDades)) $this->setPublicly_readable($aDades['publicly_readable']);
		if (array_key_exists('collection_id',$aDades)) $this->setCollection_id($aDades['collection_id']);
		if (array_key_exists('default_privileges',$aDades)) $this->setDefault_privileges($aDades['default_privileges']);
		if (array_key_exists('is_addressbook',$aDades)) $this->setIs_addressbook($aDades['is_addressbook']);
		if (array_key_exists('resourcetypes',$aDades)) $this->setResourcetypes($aDades['resourcetypes']);
		if (array_key_exists('schedule_transp',$aDades)) $this->setSchedule_transp($aDades['schedule_transp']);
		if (array_key_exists('timezone',$aDades)) $this->setTimezone($aDades['timezone']);
		if (array_key_exists('description',$aDades)) $this->setDescription($aDades['description']);
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_schema('');
		$this->setUser_no('');
		$this->setParent_container('');
		$this->setDav_name('');
		$this->setDav_etag('');
		$this->setDav_displayname('');
		$this->setIs_calendar('');
		$this->setCreated('');
		$this->setModified('');
		$this->setPublic_events_only('');
		$this->setPublicly_readable('');
		$this->setCollection_id('');
		$this->setDefault_privileges('');
		$this->setIs_addressbook('');
		$this->setResourcetypes('');
		$this->setSchedule_transp('');
		$this->setTimezone('');
		$this->setDescription('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de Collection en un array
	 *
	 * @return array aDades
	 */
	function getTot() {
		if (!is_array($this->aDades)) {
			$this->DBCarregar('tot');
		}
		return $this->aDades;
	}

	/**
	 * Recupera las claus primàries de Collection en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('collection_id' => $this->icollection_id);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de Collection en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'collection_id') && $val_id !== '') $this->icollection_id = (int)$val_id; // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->icollection_id = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('icollection_id' => $this->icollection_id);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iuser_no de Collection
	 *
	 * @return integer iuser_no
	 */
	function getUser_no() {
		if (!isset($this->iuser_no) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iuser_no;
	}
	/**
	 * estableix el valor de l'atribut iuser_no de Collection
	 *
	 * @param integer iuser_no='' optional
	 */
	function setUser_no($iuser_no='') {
		$this->iuser_no = $iuser_no;
	}
	/**
	 * Recupera l'atribut sparent_container de Collection
	 *
	 * @return string sparent_container
	 */
	function getParent_container() {
		if (!isset($this->sparent_container) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sparent_container;
	}
	/**
	 * estableix el valor de l'atribut sparent_container de Collection
	 *
	 * @param string sparent_container='' optional
	 */
	function setParent_container($sparent_container='') {
		$this->sparent_container = $sparent_container;
	}
	/**
	 * Recupera l'atribut sdav_name de Collection
	 *
	 * @return string sdav_name
	 */
	function getDav_name() {
		if (!isset($this->sdav_name) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdav_name;
	}
	/**
	 * estableix el valor de l'atribut sdav_name de Collection
	 *
	 * @param string sdav_name='' optional
	 */
	function setDav_name($sdav_name='') {
		$this->sdav_name = $sdav_name;
	}
	/**
	 * Recupera l'atribut sdav_etag de Collection
	 *
	 * @return string sdav_etag
	 */
	function getDav_etag() {
		if (!isset($this->sdav_etag) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdav_etag;
	}
	/**
	 * estableix el valor de l'atribut sdav_etag de Collection
	 *
	 * @param string sdav_etag='' optional
	 */
	function setDav_etag($sdav_etag='') {
		$this->sdav_etag = $sdav_etag;
	}
	/**
	 * Recupera l'atribut sdav_displayname de Collection
	 *
	 * @return string sdav_displayname
	 */
	function getDav_displayname() {
		if (!isset($this->sdav_displayname) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdav_displayname;
	}
	/**
	 * estableix el valor de l'atribut sdav_displayname de Collection
	 *
	 * @param string sdav_displayname='' optional
	 */
	function setDav_displayname($sdav_displayname='') {
		$this->sdav_displayname = $sdav_displayname;
	}
	/**
	 * Recupera l'atribut bis_calendar de Collection
	 *
	 * @return boolean bis_calendar
	 */
	function getIs_calendar() {
		if (!isset($this->bis_calendar) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->bis_calendar;
	}
	/**
	 * estableix el valor de l'atribut bis_calendar de Collection
	 *
	 * @param boolean bis_calendar='f' optional
	 */
	function setIs_calendar($bis_calendar='f') {
		$this->bis_calendar = $bis_calendar;
	}
	/**
	 * Recupera l'atribut dcreated de Collection
	 *
	 * @return web\DateTimeLocal dcreated
	 */
	function getCreated() {
		if (!isset($this->dcreated) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->dcreated)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('timestamptz', $this->dcreated);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut dcreated de Collection
	 * Si dcreated es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, dcreated debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string dcreated='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setCreated($dcreated='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($dcreated)) {
            $oConverter = new core\Converter('timestamptz', $dcreated);
            $this->dcreated = $oConverter->toPg();
	    } else {
            $this->dcreated = $dcreated;
	    }
	}
	/**
	 * Recupera l'atribut dmodified de Collection
	 *
	 * @return web\DateTimeLocal dmodified
	 */
	function getModified() {
		if (!isset($this->dmodified) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->dmodified)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('timestamptz', $this->dmodified);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut dmodified de Collection
	 * Si dmodified es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, dmodified debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string dmodified='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setModified($dmodified='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($dmodified)) {
            $oConverter = new core\Converter('timestamptz', $dmodified);
            $this->dmodified = $oConverter->toPg();
	    } else {
            $this->dmodified = $dmodified;
	    }
	}
	/**
	 * Recupera l'atribut bpublic_events_only de Collection
	 *
	 * @return boolean bpublic_events_only
	 */
	function getPublic_events_only() {
		if (!isset($this->bpublic_events_only) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->bpublic_events_only;
	}
	/**
	 * estableix el valor de l'atribut bpublic_events_only de Collection
	 *
	 * @param boolean bpublic_events_only='f' optional
	 */
	function setPublic_events_only($bpublic_events_only='f') {
		$this->bpublic_events_only = $bpublic_events_only;
	}
	/**
	 * Recupera l'atribut bpublicly_readable de Collection
	 *
	 * @return boolean bpublicly_readable
	 */
	function getPublicly_readable() {
		if (!isset($this->bpublicly_readable) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->bpublicly_readable;
	}
	/**
	 * estableix el valor de l'atribut bpublicly_readable de Collection
	 *
	 * @param boolean bpublicly_readable='f' optional
	 */
	function setPublicly_readable($bpublicly_readable='f') {
		$this->bpublicly_readable = $bpublicly_readable;
	}
	/**
	 * Recupera l'atribut icollection_id de Collection
	 *
	 * @return integer icollection_id
	 */
	function getCollection_id() {
		if (!isset($this->icollection_id) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->icollection_id;
	}
	/**
	 * estableix el valor de l'atribut icollection_id de Collection
	 *
	 * @param integer icollection_id
	 */
	function setCollection_id($icollection_id) {
		$this->icollection_id = $icollection_id;
	}
	/**
	 * Recupera l'atribut idefault_privileges de Collection
	 *
	 * @return integer idefault_privileges
	 */
	function getDefault_privileges() {
		if (!isset($this->idefault_privileges) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->idefault_privileges;
	}
	/**
	 * estableix el valor de l'atribut idefault_privileges de Collection
	 *
	 * @param integer idefault_privileges='' optional
	 */
	function setDefault_privileges($idefault_privileges='') {
		$this->idefault_privileges = $idefault_privileges;
	}
	/**
	 * Recupera l'atribut bis_addressbook de Collection
	 *
	 * @return boolean bis_addressbook
	 */
	function getIs_addressbook() {
		if (!isset($this->bis_addressbook) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->bis_addressbook;
	}
	/**
	 * estableix el valor de l'atribut bis_addressbook de Collection
	 *
	 * @param boolean bis_addressbook='f' optional
	 */
	function setIs_addressbook($bis_addressbook='f') {
		$this->bis_addressbook = $bis_addressbook;
	}
	/**
	 * Recupera l'atribut sresourcetypes de Collection
	 *
	 * @return string sresourcetypes
	 */
	function getResourcetypes() {
		if (!isset($this->sresourcetypes) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sresourcetypes;
	}
	/**
	 * estableix el valor de l'atribut sresourcetypes de Collection
	 *
	 * @param string sresourcetypes='' optional
	 */
	function setResourcetypes($sresourcetypes='') {
		$this->sresourcetypes = $sresourcetypes;
	}
	/**
	 * Recupera l'atribut sschedule_transp de Collection
	 *
	 * @return string sschedule_transp
	 */
	function getSchedule_transp() {
		if (!isset($this->sschedule_transp) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sschedule_transp;
	}
	/**
	 * estableix el valor de l'atribut sschedule_transp de Collection
	 *
	 * @param string sschedule_transp='' optional
	 */
	function setSchedule_transp($sschedule_transp='') {
		$this->sschedule_transp = $sschedule_transp;
	}
	/**
	 * Recupera l'atribut stimezone de Collection
	 *
	 * @return string stimezone
	 */
	function getTimezone() {
		if (!isset($this->stimezone) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->stimezone;
	}
	/**
	 * estableix el valor de l'atribut stimezone de Collection
	 *
	 * @param string stimezone='' optional
	 */
	function setTimezone($stimezone='') {
		$this->stimezone = $stimezone;
	}
	/**
	 * Recupera l'atribut sdescription de Collection
	 *
	 * @return string sdescription
	 */
	function getDescription() {
		if (!isset($this->sdescription) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdescription;
	}
	/**
	 * estableix el valor de l'atribut sdescription de Collection
	 *
	 * @param string sdescription='' optional
	 */
	function setDescription($sdescription='') {
		$this->sdescription = $sdescription;
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oCollectionSet = new core\Set();

		$oCollectionSet->add($this->getDatosUser_no());
		$oCollectionSet->add($this->getDatosParent_container());
		$oCollectionSet->add($this->getDatosDav_name());
		$oCollectionSet->add($this->getDatosDav_etag());
		$oCollectionSet->add($this->getDatosDav_displayname());
		$oCollectionSet->add($this->getDatosIs_calendar());
		$oCollectionSet->add($this->getDatosCreated());
		$oCollectionSet->add($this->getDatosModified());
		$oCollectionSet->add($this->getDatosPublic_events_only());
		$oCollectionSet->add($this->getDatosPublicly_readable());
		$oCollectionSet->add($this->getDatosDefault_privileges());
		$oCollectionSet->add($this->getDatosIs_addressbook());
		$oCollectionSet->add($this->getDatosResourcetypes());
		$oCollectionSet->add($this->getDatosSchedule_transp());
		$oCollectionSet->add($this->getDatosTimezone());
		$oCollectionSet->add($this->getDatosDescription());
		return $oCollectionSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut iuser_no de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosUser_no() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'user_no'));
		$oDatosCampo->setEtiqueta(_("user_no"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sparent_container de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosParent_container() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'parent_container'));
		$oDatosCampo->setEtiqueta(_("parent_container"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sdav_name de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDav_name() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'dav_name'));
		$oDatosCampo->setEtiqueta(_("dav_name"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sdav_etag de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDav_etag() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'dav_etag'));
		$oDatosCampo->setEtiqueta(_("dav_etag"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sdav_displayname de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDav_displayname() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'dav_displayname'));
		$oDatosCampo->setEtiqueta(_("dav_displayname"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut bis_calendar de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosIs_calendar() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'is_calendar'));
		$oDatosCampo->setEtiqueta(_("is_calendar"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut dcreated de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosCreated() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'created'));
		$oDatosCampo->setEtiqueta(_("created"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut dmodified de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosModified() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'modified'));
		$oDatosCampo->setEtiqueta(_("modified"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut bpublic_events_only de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosPublic_events_only() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'public_events_only'));
		$oDatosCampo->setEtiqueta(_("public_events_only"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut bpublicly_readable de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosPublicly_readable() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'publicly_readable'));
		$oDatosCampo->setEtiqueta(_("publicly_readable"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut idefault_privileges de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDefault_privileges() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'default_privileges'));
		$oDatosCampo->setEtiqueta(_("default_privileges"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut bis_addressbook de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosIs_addressbook() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'is_addressbook'));
		$oDatosCampo->setEtiqueta(_("is_addressbook"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sresourcetypes de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosResourcetypes() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'resourcetypes'));
		$oDatosCampo->setEtiqueta(_("resourcetypes"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sschedule_transp de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosSchedule_transp() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'schedule_transp'));
		$oDatosCampo->setEtiqueta(_("schedule_transp"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut stimezone de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosTimezone() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'timezone'));
		$oDatosCampo->setEtiqueta(_("timezone"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sdescription de Collection
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDescription() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'description'));
		$oDatosCampo->setEtiqueta(_("description"));
		return $oDatosCampo;
	}
}
