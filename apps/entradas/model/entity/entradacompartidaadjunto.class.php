<?php
namespace entradas\model\entity;
use core;

/**
 * Fitxer amb la Classe que accedeix a la taula entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @since 29/6/2020
 */

/**
 * Classe que implementa l'entitat entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @since 29/6/2020
 */
class EntradaCompartidaAdjunto Extends core\ClasePropiedades {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de EntradaCompartidaAdjunto
	 *
	 * @var array
	 */
	 protected $aPrimary_key;

	/**
	 * aDades de EntradaCompartidaAdjunto
	 *
	 * @var array
	 */
	 protected $aDades;

	/**
	 * bLoaded
	 *
	 * @var boolean
	 */
	 protected $bLoaded = FALSE;

	/**
	 * Id_schema de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 protected $iid_schema;

	/**
	 * Id_item de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 protected $iid_item;
	/**
	 * Id_entrada_compartida de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 protected $iid_entrada_compartida;
	/**
	 * Nom de EntradaCompartidaAdjunto
	 *
	 * @var string
	 */
	 protected $snom;
	/**
	 * Adjunto de EntradaCompartidaAdjunto
	 *
	 * @var string bytea
	 */
	 protected $adjunto;
	 
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
	/**
	 * oDbl de EntradaCompartidaAdjunto
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de EntradaCompartidaAdjunto
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
	 * @param integer|array iid_item
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBP'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_item') && $val_id !== '') { $this->iid_item = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_item' => $this->iid_item);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('entrada_compartida_adjuntos');
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
		$aDades['id_entrada_compartida'] = $this->iid_entrada_compartida;
		$aDades['nom'] = $this->snom;
        $aDades['adjunto'] = $this->adjunto;
		array_walk($aDades, 'core\poner_null');

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					id_entrada_compartida = :id_entrada_compartida,
					nom                   = :nom,
					adjunto               = :adjunto";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
			    $id_entrada_compartida = $aDades['id_entrada_compartida'];
			    $nom = $aDades['nom'];
			    $adjunto = $aDades['adjunto'];
			    
			    $oDblSt->bindParam(1, $id_entrada_compartida, \PDO::PARAM_INT);
			    $oDblSt->bindParam(2, $nom, \PDO::PARAM_STR);
			    $oDblSt->bindParam(3, $adjunto, \PDO::PARAM_STR);
				try {
					$oDblSt->execute();
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaCompartidaAdjunto.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			$campos="(id_entrada_compartida,nom,adjunto)";
			$valores="(:id_entrada_compartida,:nom,:ajunto)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
			    $id_entrada_compartida = $aDades['id_entrada_compartida'];
			    $nom = $aDades['nom'];
			    $adjunto = $aDades['adjunto'];
			    
			    $oDblSt->bindParam(1, $id_entrada_compartida, \PDO::PARAM_INT);
			    $oDblSt->bindParam(2, $nom, \PDO::PARAM_STR);
			    $oDblSt->bindParam(3, $adjunto, \PDO::PARAM_STR);
				try {
					$oDblSt->execute();
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaCompartidaAdjunto.insertar.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
			$this->id_item = $oDbl->lastInsertId('entrada_compartida_adjuntos_id_item_seq');
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
		$id_entrada_compartida = 0;
		$nom = '';
		$adjunto = '';
		if (isset($this->iid_item)) {
			if (($oDblSt = $oDbl->prepare("SELECT id_entrada_compartida, nom, adjunto FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.carregar';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			}
			$oDblSt->execute();
			$oDblSt->bindColumn(1, $id_entrada_compartida, \PDO::PARAM_INT);
			$oDblSt->bindColumn(2, $nom, \PDO::PARAM_STR, 256);
			$oDblSt->bindColumn(3, $adjunto, \PDO::PARAM_STR);
			$oDblSt->fetch(\PDO::FETCH_BOUND);
			
			$aDades = [ 'id_entrada_compartida' => $id_entrada_compartida,
			             'nom' => $nom,
			             'adjunto' => $adjunto,
			           ];
			
			switch ($que) {
				case 'tot':
				    $this->setAllAtributes($aDades);
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
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
			$sClauError = 'EntradaCompartidaAdjunto.eliminar';
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
		if (!is_array($aDades)) { return; }
		if (array_key_exists('id_schema',$aDades)) { $this->setId_schema($aDades['id_schema']); }
		if (array_key_exists('id_item',$aDades)) { $this->setId_item($aDades['id_item']); }
		if (array_key_exists('id_entrada_compartida',$aDades)) { $this->setId_entrada_compartida($aDades['id_entrada_compartida']); }
		if (array_key_exists('nom',$aDades)) { $this->setNom($aDades['nom']); }
		if (array_key_exists('adjunto',$aDades)) { $this->setAdjuntoEscaped($aDades['adjunto']); }
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_schema('');
		$this->setId_item('');
		$this->setId_entrada_compartida('');
		$this->setNom('');
		$this->setAdjuntoEscaped('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de EntradaCompartidaAdjunto en un array
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
	 * Recupera las claus primàries de EntradaCompartidaAdjunto en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_item' => $this->iid_item);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de EntradaCompartidaAdjunto en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_item') && $val_id !== '') { $this->iid_item = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_item' => $this->iid_item);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iid_item de EntradaCompartidaAdjunto
	 *
	 * @return integer iid_item
	 */
	function getId_item() {
		if (!isset($this->iid_item) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_item;
	}
	/**
	 * estableix el valor de l'atribut iid_item de EntradaCompartidaAdjunto
	 *
	 * @param integer iid_item
	 */
	function setId_item($iid_item) {
		$this->iid_item = $iid_item;
	}
	/**
	 * Recupera l'atribut iid_entrada_compartida de EntradaCompartidaAdjunto
	 *
	 * @return integer iid_entrada_compartida
	 */
	function getId_entrada_compartida() {
		if (!isset($this->iid_entrada_compartida) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_entrada_compartida;
	}
	/**
	 * estableix el valor de l'atribut iid_entrada_compartida de EntradaCompartidaAdjunto
	 *
	 * @param integer iid_entrada_compartida
	 */
	function setId_entrada_compartida($iid_entrada_compartida) {
		$this->iid_entrada_compartida = $iid_entrada_compartida;
	}
	/**
	 * Recupera l'atribut snom de EntradaCompartidaAdjunto
	 *
	 * @return string snom
	 */
	function getNom() {
		if (!isset($this->snom) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->snom;
	}
	/**
	 * estableix el valor de l'atribut snom de EntradaCompartidaAdjunto
	 *
	 * @param string snom='' optional
	 */
	function setNom($snom='') {
		$this->snom = $snom;
	}
	/**
	 * estableix el valor de l'atribut adjunto de EntradaCompartidaAdjunto
	 *
	 * @param string adjunto='' optional
	 */
	function setAdjunto($adjunto='') {
		// Escape the binary data
		$escaped = bin2hex( $adjunto );
		$this->adjunto = $escaped;
	}
	
	/**
	 * estableix el valor de l'atribut adjunto de EntradaCompartidaAdjunto
	 * per usar amb els valors directes de la DB.
	 *
	 * @param string adjunto='' optional (ja convertit a hexadecimal)
	 */
	private function setAdjuntoEscaped($adjunto='') {
		$this->adjunto = $adjunto;
	}
	
	public function getAdjunto() {
		if (!isset($this->adjunto) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return hex2bin($this->adjunto);
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

}
