<?php
namespace entradas\model\entity;
use core;
use web;
use stdClass;
/**
 * Fitxer amb la Classe que accedeix a la taula entradas_compartidas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 5/5/2022
 */
/**
 * Classe que implementa l'entitat entradas_compartidas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 5/5/2022
 */
class EntradaCompartida Extends core\ClasePropiedades {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * aPrimary_key de EntradaCompartida
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de EntradaCompartida
	 *
	 * @var array
	 */
	 private $aDades;

	/**
	 * bLoaded de EntradaCompartida
	 *
	 * @var boolean
	 */
	 private $bLoaded = FALSE;

	/**
	 * Id_schema de EntradaCompartida
	 *
	 * @var integer
	 */
	 private $iid_schema;

	/**
	 * Id_entrada_compartida de EntradaCompartida
	 *
	 * @var integer
	 */
	 private $iid_entrada_compartida;
	/**
	 * Descripcion de EntradaCompartida
	 *
	 * @var string
	 */
	 private $sdescripcion;
	/**
	 * Json_prot_destino de EntradaCompartida
	 *
	 * @var object JSON
	 */
	 private $json_prot_destino;
	/**
	 * Destinos de EntradaCompartida
	 *
	 * @var array
	 */
	 private $a_destinos;
	/**
	 * F_documento de EntradaCompartida
	 *
	 * @var web\DateTimeLocal
	 */
	 private $df_documento;
	/**
	 * Json_prot_origen de EntradaCompartida
	 *
	 * @var object JSON
	 */
	 private $json_prot_origen;
	/**
	 * Json_prot_ref de EntradaCompartida
	 *
	 * @var object JSON
	 */
	 private $json_prot_ref;
	/**
	 * Categoria de EntradaCompartida
	 *
	 * @var integer
	 */
	 private $icategoria;
	/**
	 * Asunto_entrada de EntradaCompartida
	 *
	 * @var string
	 */
	 private $sasunto_entrada;
	/**
	 * F_entrada de EntradaCompartida
	 *
	 * @var web\DateTimeLocal
	 */
	 private $df_entrada;
	/**
	 * Anulado de EntradaCompartida
	 *
	 * @var string
	 */
	 private $sanulado;
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
	/**
	 * oDbl de EntradaCompartida
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de EntradaCompartida
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
	 * @param integer|array iid_entrada_compartida
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBT'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_entrada_compartida') && $val_id !== '') { $this->iid_entrada_compartida = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_entrada_compartida = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_entrada_compartida' => $this->iid_entrada_compartida);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('entradas_compartidas');
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
		$aDades['descripcion'] = $this->sdescripcion;
		$aDades['json_prot_destino'] = $this->json_prot_destino;
		$aDades['destinos'] = $this->a_destinos;
		$aDades['f_documento'] = $this->df_documento;
		$aDades['json_prot_origen'] = $this->json_prot_origen;
		$aDades['json_prot_ref'] = $this->json_prot_ref;
		$aDades['categoria'] = $this->icategoria;
		$aDades['asunto_entrada'] = $this->sasunto_entrada;
		$aDades['f_entrada'] = $this->df_entrada;
		$aDades['anulado'] = $this->sanulado;
		array_walk($aDades, 'core\poner_null');

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					descripcion              = :descripcion,
					json_prot_destino        = :json_prot_destino,
					destinos                 = :destinos,
					f_documento              = :f_documento,
					json_prot_origen         = :json_prot_origen,
					json_prot_ref            = :json_prot_ref,
					categoria                = :categoria,
					asunto_entrada           = :asunto_entrada,
					f_entrada                = :f_entrada,
					anulado                  = :anulado";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada_compartida='$this->iid_entrada_compartida'")) === FALSE) {
				$sClauError = 'EntradaCompartida.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaCompartida.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			$campos="(descripcion,json_prot_destino,destinos,f_documento,json_prot_origen,json_prot_ref,categoria,asunto_entrada,f_entrada,anulado)";
			$valores="(:descripcion,:json_prot_destino,:destinos,:f_documento,:json_prot_origen,:json_prot_ref,:categoria,:asunto_entrada,:f_entrada,:anulado)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'EntradaCompartida.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EntradaCompartida.insertar.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
			$this->id_entrada_compartida = $oDbl->lastInsertId('entradas_compartidas_id_entrada_compartida_seq');
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
		if (isset($this->iid_entrada_compartida)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada_compartida='$this->iid_entrada_compartida'")) === FALSE) {
				$sClauError = 'EntradaCompartida.carregar';
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
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada_compartida='$this->iid_entrada_compartida'")) === FALSE) {
			$sClauError = 'EntradaCompartida.eliminar';
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
		if (array_key_exists('id_entrada_compartida',$aDades)) { $this->setId_entrada_compartida($aDades['id_entrada_compartida']); }
		if (array_key_exists('descripcion',$aDades)) { $this->setDescripcion($aDades['descripcion']); }
		if (array_key_exists('json_prot_destino',$aDades)) { $this->setJson_prot_destino($aDades['json_prot_destino'],TRUE); }
		if (array_key_exists('destinos',$aDades)) { $this->setDestinos($aDades['destinos'],TRUE); }
		if (array_key_exists('f_documento',$aDades)) { $this->setF_documento($aDades['f_documento'],$convert); }
		if (array_key_exists('json_prot_origen',$aDades)) { $this->setJson_prot_origen($aDades['json_prot_origen'],TRUE); }
		if (array_key_exists('json_prot_ref',$aDades)) { $this->setJson_prot_ref($aDades['json_prot_ref'],TRUE); }
		if (array_key_exists('categoria',$aDades)) { $this->setCategoria($aDades['categoria']); }
		if (array_key_exists('asunto_entrada',$aDades)) { $this->setAsunto_entrada($aDades['asunto_entrada']); }
		if (array_key_exists('f_entrada',$aDades)) { $this->setF_entrada($aDades['f_entrada'],$convert); }
		if (array_key_exists('anulado',$aDades)) { $this->setAnulado($aDades['anulado']); }
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_schema('');
		$this->setId_entrada_compartida('');
		$this->setDescripcion('');
		$this->setJson_prot_destino('');
		$this->setDestinos('');
		$this->setF_documento('');
		$this->setJson_prot_origen('');
		$this->setJson_prot_ref('');
		$this->setCategoria('');
		$this->setAsunto_entrada('');
		$this->setF_entrada('');
		$this->setAnulado('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de EntradaCompartida en un array
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
	 * Recupera las claus primàries de EntradaCompartida en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_entrada_compartida' => $this->iid_entrada_compartida);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de EntradaCompartida en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_entrada_compartida') && $val_id !== '') { $this->iid_entrada_compartida = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_entrada_compartida = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_entrada_compartida' => $this->iid_entrada_compartida);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iid_entrada_compartida de EntradaCompartida
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
	 * estableix el valor de l'atribut iid_entrada_compartida de EntradaCompartida
	 *
	 * @param integer iid_entrada_compartida
	 */
	function setId_entrada_compartida($iid_entrada_compartida) {
		$this->iid_entrada_compartida = $iid_entrada_compartida;
	}
	/**
	 * Recupera l'atribut sdescripcion de EntradaCompartida
	 *
	 * @return string sdescripcion
	 */
	function getDescripcion() {
		if (!isset($this->sdescripcion) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdescripcion;
	}
	/**
	 * estableix el valor de l'atribut sdescripcion de EntradaCompartida
	 *
	 * @param string sdescripcion='' optional
	 */
	function setDescripcion($sdescripcion='') {
		$this->sdescripcion = $sdescripcion;
	}
	/**
	 * Recupera l'atribut json_prot_destino de EntradaCompartida
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_destino
	 */
	function getJson_prot_destino($bArray=FALSE) {
		if (!isset($this->json_prot_destino) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_destino,$bArray);
	    if (empty($oJSON) || $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_destino de EntradaCompartida
	 * 
	 * @param object JSON json_prot_destino
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 */
	function setJson_prot_destino($oJSON,$db=FALSE) {
        if ($db === FALSE) {
	        $json = json_encode($oJSON);
	    } else {
	        $json = $oJSON;
	    }
        $this->json_prot_destino = $json;
	}
	/**
	 * Recupera l'atribut a_destinos de EntradaCompartida
	 *
	 * @return array a_destinos
	 */
	function getDestinos() {
		if (!isset($this->a_destinos) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        return core\array_pg2php($this->a_destinos);
	}
	/**
	 * estableix el valor de l'atribut a_destinos de EntradaCompartida
	 * 
	 * @param array a_destinos
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
	 *  o es una variable de php hay que convertirlo.
	 */
	function setDestinos($a_destinos='',$db=FALSE) {
        if ($db === FALSE) {
	        $postgresArray = core\array_php2pg($a_destinos);
	    } else {
	        $postgresArray = $a_destinos;
	    }
        $this->a_destinos = $postgresArray;
	}
	/**
	 * Recupera l'atribut df_documento de EntradaCompartida
	 *
	 * @return web\DateTimeLocal df_documento
	 */
	function getF_documento() {
		if (!isset($this->df_documento) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_documento)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_documento);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_documento de EntradaCompartida
	 * Si df_documento es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es FALSE, df_documento debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_documento='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_documento($df_documento='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_documento)) {
            $oConverter = new core\Converter('date', $df_documento);
            $this->df_documento = $oConverter->toPg();
	    } else {
            $this->df_documento = $df_documento;
	    }
	}
	/**
	 * Recupera l'atribut json_prot_origen de EntradaCompartida
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_origen
	 */
	function getJson_prot_origen($bArray=FALSE) {
		if (!isset($this->json_prot_origen) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_origen,$bArray);
	    if (empty($oJSON) || $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_origen de EntradaCompartida
	 * 
	 * @param object JSON json_prot_origen
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 */
	function setJson_prot_origen($oJSON,$db=FALSE) {
        if ($db === FALSE) {
	        $json = json_encode($oJSON);
	    } else {
	        $json = $oJSON;
	    }
        $this->json_prot_origen = $json;
	}
	/**
	 * Recupera l'atribut json_prot_ref de EntradaCompartida
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_ref
	 */
	function getJson_prot_ref($bArray=FALSE) {
		if (!isset($this->json_prot_ref) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_ref,$bArray);
	    if (empty($oJSON) || $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_ref de EntradaCompartida
	 * 
	 * @param object JSON json_prot_ref
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 */
	function setJson_prot_ref($oJSON,$db=FALSE) {
        if ($db === FALSE) {
	        $json = json_encode($oJSON);
	    } else {
	        $json = $oJSON;
	    }
        $this->json_prot_ref = $json;
	}
	/**
	 * Recupera l'atribut icategoria de EntradaCompartida
	 *
	 * @return integer icategoria
	 */
	function getCategoria() {
		if (!isset($this->icategoria) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->icategoria;
	}
	/**
	 * estableix el valor de l'atribut icategoria de EntradaCompartida
	 *
	 * @param integer icategoria='' optional
	 */
	function setCategoria($icategoria='') {
		$this->icategoria = $icategoria;
	}
	/**
	 * Recupera l'atribut sasunto_entrada de EntradaCompartida
	 *
	 * @return string sasunto_entrada
	 */
	function getAsunto_entrada() {
		if (!isset($this->sasunto_entrada) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sasunto_entrada;
	}
	/**
	 * estableix el valor de l'atribut sasunto_entrada de EntradaCompartida
	 *
	 * @param string sasunto_entrada='' optional
	 */
	function setAsunto_entrada($sasunto_entrada='') {
		$this->sasunto_entrada = $sasunto_entrada;
	}
	/**
	 * Recupera l'atribut df_entrada de EntradaCompartida
	 *
	 * @return web\DateTimeLocal df_entrada
	 */
	function getF_entrada() {
		if (!isset($this->df_entrada) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_entrada)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_entrada);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_entrada de EntradaCompartida
	 * Si df_entrada es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es FALSE, df_entrada debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_entrada='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_entrada($df_entrada='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_entrada)) {
            $oConverter = new core\Converter('date', $df_entrada);
            $this->df_entrada = $oConverter->toPg();
	    } else {
            $this->df_entrada = $df_entrada;
	    }
	}
	/**
	 * Recupera l'atribut sanulado de EntradaCompartida
	 *
	 * @return string sanulado
	 */
	function getAnulado() {
		if (!isset($this->sanulado) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sanulado;
	}
	/**
	 * estableix el valor de l'atribut sanulado de EntradaCompartida
	 *
	 * @param string sanulado='' optional
	 */
	function setAnulado($sanulado='') {
		$this->sanulado = $sanulado;
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oEntradaCompartidaSet = new core\Set();

		$oEntradaCompartidaSet->add($this->getDatosDescripcion());
		$oEntradaCompartidaSet->add($this->getDatosJson_prot_destino());
		$oEntradaCompartidaSet->add($this->getDatosDestinos());
		$oEntradaCompartidaSet->add($this->getDatosF_documento());
		$oEntradaCompartidaSet->add($this->getDatosJson_prot_origen());
		$oEntradaCompartidaSet->add($this->getDatosJson_prot_ref());
		$oEntradaCompartidaSet->add($this->getDatosCategoria());
		$oEntradaCompartidaSet->add($this->getDatosAsunto_entrada());
		$oEntradaCompartidaSet->add($this->getDatosF_entrada());
		$oEntradaCompartidaSet->add($this->getDatosAnulado());
		return $oEntradaCompartidaSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut sdescripcion de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDescripcion() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'descripcion'));
		$oDatosCampo->setEtiqueta(_("descripcion"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut json_prot_destino de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosJson_prot_destino() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'json_prot_destino'));
		$oDatosCampo->setEtiqueta(_("json_prot_destino"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut a_destinos de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDestinos() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'destinos'));
		$oDatosCampo->setEtiqueta(_("destinos"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_documento de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_documento() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_documento'));
		$oDatosCampo->setEtiqueta(_("f_documento"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut json_prot_origen de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosJson_prot_origen() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'json_prot_origen'));
		$oDatosCampo->setEtiqueta(_("json_prot_origen"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut json_prot_ref de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosJson_prot_ref() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'json_prot_ref'));
		$oDatosCampo->setEtiqueta(_("json_prot_ref"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut icategoria de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosCategoria() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'categoria'));
		$oDatosCampo->setEtiqueta(_("categoria"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sasunto_entrada de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosAsunto_entrada() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'asunto_entrada'));
		$oDatosCampo->setEtiqueta(_("asunto_entrada"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_entrada de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_entrada() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_entrada'));
		$oDatosCampo->setEtiqueta(_("f_entrada"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sanulado de EntradaCompartida
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosAnulado() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'anulado'));
		$oDatosCampo->setEtiqueta(_("anulado"));
		return $oDatosCampo;
	}
}
