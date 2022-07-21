<?php
namespace usuarios\model\entity;
use core;
/**
 * Fitxer amb la Classe que accedeix a la taula aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
/**
 * Classe que implementa l'entitat aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
class Usuario Extends core\ClasePropiedades {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de Usuario
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de Usuario
	 *
	 * @var array
	 */
	 private $aDades;

	/**
	 * bLoaded
	 *
	 * @var boolean
	 */
	 private $bLoaded = FALSE;

	/**
	 * Id_usuario de Usuario
	 *
	 * @var integer
	 */
	 private $iid_usuario;
	/**
	 * Usuario de Usuario
	 *
	 * @var string
	 */
	 private $susuario;
	/**
	 * Id_cargo_preferido de Usuario
	 *
	 * @var integer
	 */
	 private $iid_cargo_preferido;
	/**
	 * Password de Usuario
	 *
	 * @var string
	 */
	 private $spassword;
	/**
	 * Email de Usuario
	 *
	 * @var string
	 */
	 private $semail;
	/**
	 * Nom_usuario de Usuario
	 *
	 * @var string
	 */
	 private $snom_usuario;
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
	/**
	 * oDbl de Usuario
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de Usuario
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
	 * @param integer|array iid_usuario
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBT'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_usuario') && $val_id !== '') { $this->iid_usuario = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_usuario = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_usuario' => $this->iid_usuario);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('aux_usuarios');
	}

	/* METODES PUBLICS ----------------------------------------------------------*/

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
		$aDades['usuario'] = $this->susuario;
		$aDades['id_cargo_preferido'] = $this->iid_cargo_preferido;
		$aDades['password'] = $this->spassword;
		$aDades['email'] = $this->semail;
		$aDades['nom_usuario'] = $this->snom_usuario;
		array_walk($aDades, 'core\poner_null');

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					usuario                  = :usuario,
					id_cargo_preferido       = :id_cargo_preferido,
					password                 = :password,
					email                    = :email,
					nom_usuario              = :nom_usuario";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
				$sClauError = 'Usuario.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'Usuario.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			$campos="(usuario,id_cargo_preferido,password,email,nom_usuario)";
			$valores="(:usuario,:id_cargo_preferido,:password,:email,:nom_usuario)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'Usuario.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'Usuario.insertar.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
			$this->id_usuario = $oDbl->lastInsertId('aux_usuarios_id_usuario_seq');
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
		if (isset($this->iid_usuario)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
				$sClauError = 'Usuario.carregar';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			}
			// para los bytea:
			$sPasswd = '';
			$oDblSt->bindColumn('password', $sPasswd, \PDO::PARAM_STR);
			$aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
			// Para evitar posteriores cargas
			$this->bLoaded = TRUE;
			$aDades['password'] = $sPasswd;
			switch ($que) {
				case 'tot':
					$this->aDades=$aDades;
					break;
				case 'guardar':
				    if (!$oDblSt->rowCount()) { return FALSE; }
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
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
			$sClauError = 'Usuario.eliminar';
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
	function setAllAtributes($aDades) {
		if (!is_array($aDades)) { return; }
		if (array_key_exists('id_usuario',$aDades)) { $this->setId_usuario($aDades['id_usuario']); }
		if (array_key_exists('usuario',$aDades)) { $this->setUsuario($aDades['usuario']); }
		if (array_key_exists('id_cargo_preferido',$aDades)) { $this->setId_cargo_preferido($aDades['id_cargo_preferido']); }
		if (array_key_exists('password',$aDades)) { $this->setPassword($aDades['password']); }
		if (array_key_exists('email',$aDades)) { $this->setEmail($aDades['email']); }
		if (array_key_exists('nom_usuario',$aDades)) { $this->setNom_usuario($aDades['nom_usuario']); }
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_usuario('');
		$this->setUsuario('');
		$this->setId_cargo_preferido('');
		$this->setPassword('');
		$this->setEmail('');
		$this->setNom_usuario('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de Usuario en un array
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
	 * Recupera las claus primàries de Usuario en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_usuario' => $this->iid_usuario);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de Usuario en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_usuario') && $val_id !== '') { $this->iid_usuario = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_usuario = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_usuario' => $this->iid_usuario);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iid_usuario de Usuario
	 *
	 * @return integer iid_usuario
	 */
	function getId_usuario() {
		if (!isset($this->iid_usuario) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_usuario;
	}
	/**
	 * estableix el valor de l'atribut iid_usuario de Usuario
	 *
	 * @param integer iid_usuario
	 */
	function setId_usuario($iid_usuario) {
		$this->iid_usuario = $iid_usuario;
	}
	/**
	 * Recupera l'atribut susuario de Usuario
	 *
	 * @return string susuario
	 */
	function getUsuario() {
		if (!isset($this->susuario) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->susuario;
	}
	/**
	 * estableix el valor de l'atribut susuario de Usuario
	 *
	 * @param string susuario='' optional
	 */
	function setUsuario($susuario='') {
		$this->susuario = $susuario;
	}
	/**
	 * Recupera l'atribut iid_cargo_preferido de Usuario
	 *
	 * @return integer iid_cargo_preferido
	 */
	function getId_cargo_preferido() {
		if (!isset($this->iid_cargo_preferido) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_cargo_preferido;
	}
	/**
	 * estableix el valor de l'atribut iid_cargo_preferido de Usuario
	 *
	 * @param integer iid_cargo_preferido='' optional
	 */
	function setId_cargo_preferido($iid_cargo_preferido='') {
		$this->iid_cargo_preferido = $iid_cargo_preferido;
	}
	/**
	 * Recupera l'atribut spassword de Usuario
	 *
	 * @return integer spassword
	 */
	function getPassword() {
		if (!isset($this->spassword) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->spassword;
	}
	/**
	 * estableix el valor de l'atribut spassword de Usuario
	 *
	 * @param integer spassword='' optional
	 */
	function setPassword($spassword='') {
		$this->spassword = $spassword;
	}
	/**
	 * Recupera l'atribut semail de Usuario
	 *
	 * @return string semail
	 */
	function getEmail() {
		if (!isset($this->semail) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->semail;
	}
	/**
	 * estableix el valor de l'atribut semail de Usuario
	 *
	 * @param string semail='' optional
	 */
	function setEmail($semail='') {
		$this->semail = $semail;
	}
	/**
	 * Recupera l'atribut snom_usuario de Usuario
	 *
	 * @return string snom_usuario
	 */
	function getNom_usuario() {
		if (!isset($this->snom_usuario) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->snom_usuario;
	}
	/**
	 * estableix el valor de l'atribut snom_usuario de Usuario
	 *
	 * @param string snom_usuario='' optional
	 */
	function setNom_usuario($snom_usuario='') {
		$this->snom_usuario = $snom_usuario;
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oUsuarioSet = new core\Set();

		$oUsuarioSet->add($this->getDatosUsuario());
		$oUsuarioSet->add($this->getDatosId_cargo_preferido());
		$oUsuarioSet->add($this->getDatosPassword());
		$oUsuarioSet->add($this->getDatosEmail());
		$oUsuarioSet->add($this->getDatosNom_usuario());
		return $oUsuarioSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut susuario de Usuario
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosUsuario() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'usuario'));
		$oDatosCampo->setEtiqueta(_("usuario"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut iid_cargo_preferido de Usuario
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosId_cargo_preferido() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_cargo_preferido'));
		$oDatosCampo->setEtiqueta(_("id_cargo_preferido"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut spassword de Usuario
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosPassword() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'password'));
		$oDatosCampo->setEtiqueta(_("password"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut semail de Usuario
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosEmail() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'email'));
		$oDatosCampo->setEtiqueta(_("email"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut snom_usuario de Usuario
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosNom_usuario() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'nom_usuario'));
		$oDatosCampo->setEtiqueta(_("nom_usuario"));
		return $oDatosCampo;
	}
}
