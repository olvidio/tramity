<?php
namespace entradas\model\entity;
use core;
/**
 * Fitxer amb la Classe que accedeix a la taula entrada_compartida_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 5/5/2022
 */
/**
 * Classe que implementa l'entitat entrada_compartida_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 5/5/2022
 */
class EntradaCompartidaAdjunto Extends core\ClasePropiedades {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de EntradaCompartidaAdjunto
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de EntradaCompartidaAdjunto
	 *
	 * @var array
	 */
	 private $aDades;

	/**
	 * bLoaded de EntradaCompartidaAdjunto
	 *
	 * @var boolean
	 */
	 private $bLoaded = FALSE;

	/**
	 * Id_schema de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 private $iid_schema;

	/**
	 * Id_item de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 private $iid_item;
	/**
	 * Id_entrada_compartida de EntradaCompartidaAdjunto
	 *
	 * @var integer
	 */
	 private $iid_entrada_compartida;
	/**
	 * Nom de EntradaCompartidaAdjunto
	 *
	 * @var string
	 */
	 private $snom;
	/**
	 * Adjunto de EntradaCompartidaAdjunto
	 *
	 * @var string
	 */
	 private $sadjunto;
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
		$oDbl = $GLOBALS['oDBT'];
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
		$aDades['adjunto'] = $this->sadjunto;
		array_walk($aDades, 'core\poner_null');

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					id_entrada_compartida    = :id_entrada_compartida,
					nom                      = :nom,
					adjunto                  = :adjunto";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
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
			$valores="(:id_entrada_compartida,:nom,:adjunto)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
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
		if (isset($this->iid_item)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
				$sClauError = 'EntradaCompartidaAdjunto.carregar';
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
	function setAllAtributes($aDades) {
		if (!is_array($aDades)) { return; }
		if (array_key_exists('id_schema',$aDades)) { $this->setId_schema($aDades['id_schema']); }
		if (array_key_exists('id_item',$aDades)) { $this->setId_item($aDades['id_item']); }
		if (array_key_exists('id_entrada_compartida',$aDades)) { $this->setId_entrada_compartida($aDades['id_entrada_compartida']); }
		if (array_key_exists('nom',$aDades)) { $this->setNom($aDades['nom']); }
		if (array_key_exists('adjunto',$aDades)) { $this->setAdjunto($aDades['adjunto']); }
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
		$this->setAdjunto('');
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
	 * @param integer iid_entrada_compartida='' optional
	 */
	function setId_entrada_compartida($iid_entrada_compartida='') {
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
	 * Recupera l'atribut sadjunto de EntradaCompartidaAdjunto
	 *
	 * @return string sadjunto
	 */
	function getAdjunto() {
		if (!isset($this->sadjunto) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sadjunto;
	}
	/**
	 * estableix el valor de l'atribut sadjunto de EntradaCompartidaAdjunto
	 *
	 * @param string sadjunto='' optional
	 */
	function setAdjunto($sadjunto='') {
		$this->sadjunto = $sadjunto;
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oEntradaCompartidaAdjuntoSet = new core\Set();

		$oEntradaCompartidaAdjuntoSet->add($this->getDatosId_entrada_compartida());
		$oEntradaCompartidaAdjuntoSet->add($this->getDatosNom());
		$oEntradaCompartidaAdjuntoSet->add($this->getDatosAdjunto());
		return $oEntradaCompartidaAdjuntoSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut iid_entrada_compartida de EntradaCompartidaAdjunto
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosId_entrada_compartida() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_entrada_compartida'));
		$oDatosCampo->setEtiqueta(_("id_entrada_compartida"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut snom de EntradaCompartidaAdjunto
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosNom() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'nom'));
		$oDatosCampo->setEtiqueta(_("nom"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sadjunto de EntradaCompartidaAdjunto
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosAdjunto() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'adjunto'));
		$oDatosCampo->setEtiqueta(_("adjunto"));
		return $oDatosCampo;
	}
}
