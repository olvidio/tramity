<?php
namespace entradas\model\entity;
use core;
use web;
/**
 * Fitxer amb la Classe que accedeix a la taula entrada_doc
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 8/7/2020
 */
/**
 * Classe que implementa l'entitat entrada_doc
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 8/7/2020
 */
class EntradaDocDB Extends core\ClasePropiedades {
    
/* CONST -------------------------------------------------------------- */
    
    // tipo documento
    const TIPO_ETHERPAD     = 1;
    const TIPO_ETHERCALC    = 2;
    const TIPO_OTRO         = 3;
    
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de EntradaDocDB
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de EntradaDocDB
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
	 * Id_schema de EntradaDocDB
	 *
	 * @var integer
	 */
	 private $iid_schema;

	/**
	 * Id_entrada de EntradaDocDB
	 *
	 * @var integer
	 */
	 private $iid_entrada;
	/**
	 * Tipo_doc de EntradaDocDB
	 *
	 * @var integer
	 */
	 private $itipo_doc;
	/**
	 * F_doc de EntradaDocDB
	 *
	 * @var web\DateTimeLocal
	 */
	 private $df_doc;
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
	/**
	 * oDbl de EntradaDocDB
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de EntradaDocDB
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
	 * @param integer|array iid_entrada
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBT'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_entrada') && $val_id !== '') { $this->iid_entrada = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('entrada_doc');
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
		$aDades['tipo_doc'] = $this->itipo_doc;
		$aDades['f_doc'] = $this->df_doc;
		array_walk($aDades, 'core\poner_null');

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					tipo_doc                 = :tipo_doc,
					f_doc                    = :f_doc";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
				$sClauError = 'EntradaDocDB.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaDocDB.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			array_unshift($aDades, $this->iid_entrada);
			$campos="(id_entrada,tipo_doc,f_doc)";
			$valores="(:id_entrada,:tipo_doc,:f_doc)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'EntradaDocDB.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaDocDB.insertar.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
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
		if (isset($this->iid_entrada)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
				$sClauError = 'EntradaDocDB.carregar';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			}
			$aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
			// Para evitar posteriores cargas
			$this->bLoaded = TRUE;
			switch ($que) {
				case 'tot':
					$this->aDades=$aDades;
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
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
			$sClauError = 'EntradaDocDB.eliminar';
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
		if (array_key_exists('id_entrada',$aDades)) { $this->setId_entrada($aDades['id_entrada']); }
		if (array_key_exists('tipo_doc',$aDades)) { $this->setTipo_doc($aDades['tipo_doc']); }
		if (array_key_exists('f_doc',$aDades)) { $this->setF_doc($aDades['f_doc'],$convert); }
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_schema('');
		$this->setId_entrada('');
		$this->setTipo_doc('');
		$this->setF_doc('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de EntradaDocDB en un array
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
	 * Recupera las claus primàries de EntradaDocDB en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_entrada' => $this->iid_entrada);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de EntradaDocDB en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_entrada') && $val_id !== '') { $this->iid_entrada = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iid_entrada de EntradaDocDB
	 *
	 * @return integer iid_entrada
	 */
	function getId_entrada() {
		if (!isset($this->iid_entrada) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_entrada;
	}
	/**
	 * estableix el valor de l'atribut iid_entrada de EntradaDocDB
	 *
	 * @param integer iid_entrada
	 */
	function setId_entrada($iid_entrada) {
		$this->iid_entrada = $iid_entrada;
	}
	/**
	 * Recupera l'atribut itipo_doc de EntradaDocDB
	 *
	 * @return integer itipo_doc
	 */
	function getTipo_doc() {
		if (!isset($this->itipo_doc) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->itipo_doc;
	}
	/**
	 * estableix el valor de l'atribut itipo_doc de EntradaDocDB
	 *
	 * @param integer itipo_doc='' optional
	 */
	function setTipo_doc($itipo_doc='') {
		$this->itipo_doc = $itipo_doc;
	}
	/**
	 * Recupera l'atribut df_doc de EntradaDocDB
	 *
	 * @return web\DateTimeLocal df_doc
	 */
	function getF_doc() {
		if (!isset($this->df_doc) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_doc)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_doc);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_doc de EntradaDocDB
	 * Si df_doc es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, df_doc debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_doc='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_doc($df_doc='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_doc)) {
            $oConverter = new core\Converter('date', $df_doc);
            $this->df_doc = $oConverter->toPg();
	    } else {
            $this->df_doc = $df_doc;
	    }
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oEntradaDocDBSet = new core\Set();

		$oEntradaDocDBSet->add($this->getDatosTipo_doc());
		$oEntradaDocDBSet->add($this->getDatosF_doc());
		return $oEntradaDocDBSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut itipo_doc de EntradaDocDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosTipo_doc() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'tipo_doc'));
		$oDatosCampo->setEtiqueta(_("tipo_doc"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_doc de EntradaDocDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_doc() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_doc'));
		$oDatosCampo->setEtiqueta(_("f_doc"));
		return $oDatosCampo;
	}
}
