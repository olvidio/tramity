<?php
namespace expedientes\model\entity;
use core;
use web;
use stdClass;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
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
class EscritoDB Extends core\ClasePropiedades {
    
    // tipo documento (igual que entradadocdb)
    const TIPO_ETHERPAD     = 1;
    const TIPO_ETHERCALC    = 2;
    const TIPO_OTRO         = 3;
    
    // ok
    const OK_NO         = 1;
    const OK_OFICINA    = 2;
    const OK_SECRETARIA = 3;
    
    // visibilidad
    // USAR LAS DE ENTRADADB
    
    
	/* ATRIBUTS ----------------------------------------------------------------- */

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
	 * @var object JSON
	 */
	 protected $json_prot_local;
	/**
	 * Json_prot_destino de EscritoDB
	 *
	 * @var object JSON
	 */
	 protected $json_prot_destino;
	/**
	 * Json_prot_ref de EscritoDB
	 *
	 * @var object JSON
	 */
	 protected $json_prot_ref;
	/**
	 * Id_grupos de EscritoDB
	 *
	 * @var array
	 */
	 protected $a_id_grupos;
	/**
	 * Destinos de EscritoDB
	 *
	 * @var array
	 */
	 protected $a_destinos;
	/**
	 * Entradilla de EscritoDB
	 *
	 * @var string
	 */
	 protected $sentradilla;
	/**
	 * Asunto de EscritoDB
	 *
	 * @var string
	 */
	 protected $sasunto;
	/**
	 * Detalle de EscritoDB
	 *
	 * @var string
	 */
	 protected $sdetalle;
	/**
	 * Creador de EscritoDB
	 *
	 * @var integer
	 */
	 protected $icreador;
	/**
	 * Resto_oficinas de EscritoDB
	 *
	 * @var array
	 */
	 protected $a_resto_oficinas;
	/**
	 * Comentarios de EscritoDB
	 *
	 * @var string
	 */
	 protected $scomentarios;
	/**
	 * F_aprobacion de EscritoDB
	 *
	 * @var web\DateTimeLocal
	 */
	 protected $df_aprobacion;
	/**
	 * F_escrito de EscritoDB
	 *
	 * @var web\DateTimeLocal
	 */
	 protected $df_escrito;
	/**
	 * F_contestar de EscritoDB
	 *
	 * @var web\DateTimeLocal
	 */
	 protected $df_contestar;
	/**
	 * Categoria de EscritoDB
	 *
	 * @var integer
	 */
	 protected $icategoria;
	/**
	 * Visibilidad de EscritoDB
	 *
	 * @var integer
	 */
	 protected $ivisibilidad;
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
	 protected $df_salida;
	/**
	 * Ok de EscritoDB
	 *
	 * @var integer
	 */
	 protected $iok;
	/**
	 * Tipo_doc de EscritoDB
	 *
	 * @var integer
	 */
	 protected $itipo_doc;
	/**
	 * anulado de EscritoDB
	 *
	 * @var boolean
	 */
	 protected $banulado;
	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
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
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBT'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_escrito') && $val_id !== '') $this->iid_escrito = (int)$val_id; // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_escrito = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('escritos');
	}
	
	public function __clone() {
	    $this->clone = TRUE;
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
		$aDades['json_prot_local'] = $this->json_prot_local;
		$aDades['json_prot_destino'] = $this->json_prot_destino;
		$aDades['json_prot_ref'] = $this->json_prot_ref;
		$aDades['id_grupos'] = $this->a_id_grupos;
		$aDades['destinos'] = $this->a_destinos;
		$aDades['entradilla'] = $this->sentradilla;
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
		$aDades['accion'] = $this->iaccion;
		$aDades['modo_envio'] = $this->imodo_envio;
		$aDades['f_salida'] = $this->df_salida;
		$aDades['ok'] = $this->iok;
		$aDades['tipo_doc'] = $this->itipo_doc;
		$aDades['anulado'] = $this->banulado;
		array_walk($aDades, 'core\poner_null');
		//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
		if ( core\is_true($aDades['anulado']) ) { $aDades['anulado']='true'; } else { $aDades['anulado']='false'; }

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					json_prot_local          = :json_prot_local,
					json_prot_destino        = :json_prot_destino,
					json_prot_ref            = :json_prot_ref,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					entradilla               = :entradilla,
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
					accion                   = :accion,
					modo_envio               = :modo_envio,
					f_salida                 = :f_salida,
					ok                       = :ok,
					tipo_doc                 = :tipo_doc,
					anulado                  = :anulado";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
				$sClauError = 'EscritoDB.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = 'EscritoDB.update.execute';
					$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT
			$campos="(json_prot_local,json_prot_destino,json_prot_ref,id_grupos,destinos,entradilla,asunto,detalle,creador,resto_oficinas,comentarios,f_aprobacion,f_escrito,f_contestar,categoria,visibilidad,accion,modo_envio,f_salida,ok,tipo_doc,anulado)";
			$valores="(:json_prot_local,:json_prot_destino,:json_prot_ref,:id_grupos,:destinos,:entradilla,:asunto,:detalle,:creador,:resto_oficinas,:comentarios,:f_aprobacion,:f_escrito,:f_contestar,:categoria,:visibilidad,:accion,:modo_envio,:f_salida,:ok,:tipo_doc,:anulado)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = 'EscritoDB.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
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
	 * Carrega els camps de la base de dades com atributs de l'objecte.
	 *
	 */
	public function DBCarregar($que=null) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (isset($this->iid_escrito) && $this->clone === FALSE) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
				$sClauError = 'EscritoDB.carregar';
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
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_escrito='$this->iid_escrito'")) === FALSE) {
			$sClauError = 'EscritoDB.eliminar';
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
		if (array_key_exists('id_escrito',$aDades)) $this->setId_escrito($aDades['id_escrito']);
		if (array_key_exists('json_prot_local',$aDades)) $this->setJson_prot_local($aDades['json_prot_local'],TRUE);
		if (array_key_exists('json_prot_destino',$aDades)) $this->setJson_prot_destino($aDades['json_prot_destino'],TRUE);
		if (array_key_exists('json_prot_ref',$aDades)) $this->setJson_prot_ref($aDades['json_prot_ref'],TRUE);
		if (array_key_exists('id_grupos',$aDades)) $this->setId_grupos($aDades['id_grupos'],TRUE);
		if (array_key_exists('destinos',$aDades)) $this->setDestinos($aDades['destinos'],TRUE);
		if (array_key_exists('entradilla',$aDades)) $this->setEntradilla($aDades['entradilla']);
		if (array_key_exists('asunto',$aDades)) $this->setAsunto($aDades['asunto']);
		if (array_key_exists('detalle',$aDades)) $this->setDetalle($aDades['detalle']);
		if (array_key_exists('creador',$aDades)) $this->setCreador($aDades['creador']);
		if (array_key_exists('resto_oficinas',$aDades)) $this->setResto_oficinas($aDades['resto_oficinas'],TRUE);
		if (array_key_exists('comentarios',$aDades)) $this->setComentarios($aDades['comentarios']);
		if (array_key_exists('f_aprobacion',$aDades)) $this->setF_aprobacion($aDades['f_aprobacion'],$convert);
		if (array_key_exists('f_escrito',$aDades)) $this->setF_escrito($aDades['f_escrito'],$convert);
		if (array_key_exists('f_contestar',$aDades)) $this->setF_contestar($aDades['f_contestar'],$convert);
		if (array_key_exists('categoria',$aDades)) $this->setCategoria($aDades['categoria']);
		if (array_key_exists('visibilidad',$aDades)) $this->setVisibilidad($aDades['visibilidad']);
		if (array_key_exists('accion',$aDades)) $this->setAccion($aDades['accion']);
		if (array_key_exists('modo_envio',$aDades)) $this->setModo_envio($aDades['modo_envio']);
		if (array_key_exists('f_salida',$aDades)) $this->setF_salida($aDades['f_salida'],$convert);
		if (array_key_exists('ok',$aDades)) $this->setOk($aDades['ok']);
		if (array_key_exists('tipo_doc',$aDades)) $this->setTipo_doc($aDades['tipo_doc']);
		if (array_key_exists('anulado',$aDades)) $this->setAnulado($aDades['anulado']);
	}	
	/**
	 * Estableix a empty el valor de tots els atributs
	 *
	 */
	function setNullAllAtributes() {
		$aPK = $this->getPrimary_key();
		$this->setId_schema('');
		$this->setId_escrito('');
		$this->setJson_prot_local('');
		$this->setJson_prot_destino('');
		$this->setJson_prot_ref('');
		$this->setId_grupos();
		$this->setDestinos('');
		$this->setEntradilla('');
		$this->setAsunto('');
		$this->setDetalle('');
		$this->setCreador('');
		$this->setResto_oficinas('');
		$this->setComentarios('');
		$this->setF_aprobacion('');
		$this->setF_escrito('');
		$this->setF_contestar('');
		$this->setCategoria('');
		$this->setVisibilidad('');
		$this->setAccion('');
		$this->setModo_envio('');
		$this->setF_salida('');
		$this->setOk('');
		$this->setTipo_doc('');
		$this->setAnulado('');
		$this->setPrimary_key($aPK);
	}

	/* METODES GET i SET --------------------------------------------------------*/

	/**
	 * Recupera tots els atributs de EscritoDB en un array
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
	 * Recupera las claus primàries de EscritoDB en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_escrito' => $this->iid_escrito);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de EscritoDB en un array
	 *
	 */
	public function setPrimary_key($a_id='') {
	    if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_escrito') && $val_id !== '') $this->iid_escrito = (int)$val_id; // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_escrito = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
			}
		}
	}
	

	/**
	 * Recupera l'atribut iid_escrito de EscritoDB
	 *
	 * @return integer iid_escrito
	 */
	function getId_escrito() {
		if (!isset($this->iid_escrito) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iid_escrito;
	}
	/**
	 * estableix el valor de l'atribut iid_escrito de EscritoDB
	 *
	 * @param integer iid_escrito
	 */
	function setId_escrito($iid_escrito) {
		$this->iid_escrito = $iid_escrito;
	}
	
	/**
	 * Recupera l'atribut json_prot_local de EscritoDB
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_local
	 */
	function getJson_prot_local($bArray=FALSE) {
		if (!isset($this->json_prot_local) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_local,$bArray);
	    if (empty($oJSON) OR $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    //$this->json_prot_local = $oJSON;
	    //return $this->json_prot_local;
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_local de EscritoDB
	 * 
	 * @param object JSON json_prot_local
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 */
	function setJson_prot_local($oJSON,$db=FALSE) {
        if ($db === FALSE) {
	        $json = json_encode($oJSON);
	    } else {
	        $json = $oJSON;
	    }
        $this->json_prot_local = $json;
	}
	/**
	 * Recupera l'atribut json_prot_destino de EscritoDB
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_destino
	 */
	function getJson_prot_destino($bArray=FALSE) {
		if (!isset($this->json_prot_destino) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_destino,$bArray);
	    if (empty($oJSON) OR $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    //$this->json_prot_destino = $oJSON;
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_destino de EscritoDB
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
	 * Recupera l'atribut json_prot_ref de EscritoDB
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return object JSON json_prot_ref
	 */
	function getJson_prot_ref($bArray=FALSE) {
		if (!isset($this->json_prot_ref) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        $oJSON = json_decode($this->json_prot_ref,$bArray);
	    if (empty($oJSON) OR $oJSON == '[]') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    //$this->json_prot_ref = $oJSON;
	    return $oJSON;
	}
	/**
	 * estableix el valor de l'atribut json_prot_ref de EscritoDB
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
	 * Recupera l'atribut a_id_grupos de EscritoDB
	 *
	 * @return array a_id_grupos
	 */
	function getId_grupos() {
		if (!isset($this->a_id_grupos) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        return core\array_pg2php($this->a_id_grupos);
	}
	/**
	 * estableix el valor de l'atribut a_id_grupos de EscritoDB
	 * 
	 * @param array a_id_grupos
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
	 *  o es una variable de php hay que convertirlo.
	 */
	function setId_grupos($a_id_grupos=[],$db=FALSE) {
        if ($db === FALSE) {
	        $postgresArray = core\array_php2pg($a_id_grupos);
	    } else {
	        $postgresArray = $a_id_grupos;
	    }
        $this->a_id_grupos = $postgresArray;
	}
	/**
	 * Recupera l'atribut a_destinos de EscritoDB
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
	 * estableix el valor de l'atribut a_destinos de EscritoDB
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
	 * Recupera l'atribut sentradilla de EscritoDB
	 *
	 * @return string sentradilla
	 */
	function getEntradilla() {
		if (!isset($this->sentradilla) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sentradilla;
	}
	/**
	 * estableix el valor de l'atribut sentradilla de EscritoDB
	 *
	 * @param string sentradilla='' optional
	 */
	function setEntradilla($sentradilla='') {
		$this->sentradilla = $sentradilla;
	}
	/**
	 * Recupera l'atribut sasunto de EscritoDB
	 *
	 * @return string sasunto
	 */
	function getAsunto() {
		if (!isset($this->sasunto) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sasunto;
	}
	/**
	 * estableix el valor de l'atribut sasunto de EscritoDB
	 *
	 * @param string sasunto='' optional
	 */
	function setAsunto($sasunto='') {
		$this->sasunto = $sasunto;
	}
	/**
	 * Recupera l'atribut sdetalle de EscritoDB
	 *
	 * @return string sdetalle
	 */
	function getDetalle() {
		if (!isset($this->sdetalle) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->sdetalle;
	}
	/**
	 * estableix el valor de l'atribut sdetalle de EscritoDB
	 *
	 * @param string sdetalle='' optional
	 */
	function setDetalle($sdetalle='') {
		$this->sdetalle = $sdetalle;
	}
	/**
	 * Recupera l'atribut icreador de EscritoDB
	 *
	 * @return integer icreador
	 */
	function getCreador() {
		if (!isset($this->icreador) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->icreador;
	}
	/**
	 * estableix el valor de l'atribut icreador de EscritoDB
	 *
	 * @param integer icreador='' optional
	 */
	function setCreador($icreador='') {
		$this->icreador = $icreador;
	}
	/**
	 * Recupera l'atribut a_resto_oficinas de EscritoDB
	 *
	 * @return array a_resto_oficinas
	 */
	function getResto_oficinas() {
		if (!isset($this->a_resto_oficinas) && !$this->bLoaded) {
			$this->DBCarregar();
		}
        return core\array_pg2php($this->a_resto_oficinas);
	}
	/**
	 * estableix el valor de l'atribut a_resto_oficinas de EscritoDB
	 * 
	 * @param array a_resto_oficinas
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
	 *  o es una variable de php hay que convertirlo.
	 */
	function setResto_oficinas($a_resto_oficinas='',$db=FALSE) {
        if ($db === FALSE) {
	        $postgresArray = core\array_php2pg($a_resto_oficinas);
	    } else {
	        $postgresArray = $a_resto_oficinas;
	    }
        $this->a_resto_oficinas = $postgresArray;
	}
	/**
	 * Recupera l'atribut scomentarios de EscritoDB
	 *
	 * @return string scomentarios
	 */
	function getComentarios() {
		if (!isset($this->scomentarios) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->scomentarios;
	}
	/**
	 * estableix el valor de l'atribut scomentarios de EscritoDB
	 *
	 * @param string scomentarios='' optional
	 */
	function setComentarios($scomentarios='') {
		$this->scomentarios = $scomentarios;
	}
	/**
	 * Recupera l'atribut df_aprobacion de EscritoDB
	 *
	 * @return web\DateTimeLocal df_aprobacion
	 */
	function getF_aprobacion() {
		if (!isset($this->df_aprobacion) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_aprobacion)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_aprobacion);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_aprobacion de EscritoDB
	 * Si df_aprobacion es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, df_aprobacion debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_aprobacion='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_aprobacion($df_aprobacion='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_aprobacion)) {
            $oConverter = new core\Converter('date', $df_aprobacion);
            $this->df_aprobacion = $oConverter->toPg();
	    } else {
            $this->df_aprobacion = $df_aprobacion;
	    }
	}
	/**
	 * Recupera l'atribut df_escrito de EscritoDB
	 *
	 * @return web\DateTimeLocal df_escrito
	 */
	function getF_escrito() {
		if (!isset($this->df_escrito) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_escrito)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_escrito);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_escrito de EscritoDB
	 * Si df_escrito es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, df_escrito debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_escrito='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_escrito($df_escrito='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_escrito)) {
            $oConverter = new core\Converter('date', $df_escrito);
            $this->df_escrito = $oConverter->toPg();
	    } else {
            $this->df_escrito = $df_escrito;
	    }
	}
	/**
	 * Recupera l'atribut df_contestar de EscritoDB
	 *
	 * @return web\DateTimeLocal df_contestar
	 */
	function getF_contestar() {
		if (!isset($this->df_contestar) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_contestar)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_contestar);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_contestar de EscritoDB
	 * Si df_contestar es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, df_contestar debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_contestar='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_contestar($df_contestar='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_contestar)) {
            $oConverter = new core\Converter('date', $df_contestar);
            $this->df_contestar = $oConverter->toPg();
	    } else {
            $this->df_contestar = $df_contestar;
	    }
	}
	/**
	 * Recupera l'atribut icategoria de EscritoDB
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
	 * estableix el valor de l'atribut icategoria de EscritoDB
	 *
	 * @param integer icategoria='' optional
	 */
	function setCategoria($icategoria='') {
		$this->icategoria = $icategoria;
	}
	/**
	 * Recupera l'atribut ivisibilidad de EscritoDB
	 *
	 * @return integer ivisibilidad
	 */
	function getVisibilidad() {
		if (!isset($this->ivisibilidad) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->ivisibilidad;
	}
	/**
	 * estableix el valor de l'atribut ivisibilidad de EscritoDB
	 *
	 * @param integer ivisibilidad='' optional
	 */
	function setVisibilidad($ivisibilidad='') {
		$this->ivisibilidad = $ivisibilidad;
	}
	/**
	 * Recupera l'atribut iaccion de EscritoDB
	 *
	 * @return integer iaccion
	 */
	function getAccion() {
		if (!isset($this->iaccion) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iaccion;
	}
	/**
	 * estableix el valor de l'atribut iaccion de EscritoDB
	 *
	 * @param integer iaccion='' optional
	 */
	function setAccion($iaccion='') {
		$this->iaccion = $iaccion;
	}
	/**
	 * Recupera l'atribut imodo_envio de EscritoDB
	 *
	 * @return integer imodo_envio
	 */
	function getModo_envio() {
		if (!isset($this->imodo_envio) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->imodo_envio;
	}
	/**
	 * estableix el valor de l'atribut imodo_envio de EscritoDB
	 *
	 * @param integer imodo_envio='' optional
	 */
	function setModo_envio($imodo_envio='') {
		$this->imodo_envio = $imodo_envio;
	}
	/**
	 * Recupera l'atribut df_salida de EscritoDB
	 *
	 * @return web\DateTimeLocal df_salida
	 */
	function getF_salida() {
		if (!isset($this->df_salida) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		if (empty($this->df_salida)) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter('date', $this->df_salida);
		return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_salida de EscritoDB
	 * Si df_salida es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
	 * Si convert es FALSE, df_salida debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param web\DateTimeLocal|string df_salida='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_salida($df_salida='',$convert=TRUE) {
        if ($convert === TRUE  && !empty($df_salida)) {
            $oConverter = new core\Converter('date', $df_salida);
            $this->df_salida = $oConverter->toPg();
	    } else {
            $this->df_salida = $df_salida;
	    }
	}
	/**
	 * Recupera l'atribut iok de EscritoDB
	 *
	 * @return integer iok
	 */
	function getOk() {
		if (!isset($this->iok) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->iok;
	}
	/**
	 * estableix el valor de l'atribut iok de EscritoDB
	 *
	 * @param integer iok
	 */
	function setOk($iok) {
		$this->iok = $iok;
	}
	/**
	 * Recupera l'atribut itipo_doc de EscritoDB
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
	 * estableix el valor de l'atribut itipo_doc de EscritoDB
	 *
	 * @param integer itipo_doc='' optional
	 */
	function setTipo_doc($itipo_doc='') {
		$this->itipo_doc = $itipo_doc;
	}
	/**
	 * Recupera l'atribut banulado de EscritoDB
	 *
	 * @return boolean banulado
	 */
	function getAnulado() {
		if (!isset($this->banulado) && !$this->bLoaded) {
			$this->DBCarregar();
		}
		return $this->banulado;
	}
	/**
	 * estableix el valor de l'atribut banulado de EscritoDB
	 *
	 * @param boolean banulado='f' optional
	 */
	function setAnulado($banulado='f') {
		$this->banulado = $banulado;
	}
	/* METODES GET i SET D'ATRIBUTS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$oEscritoDBSet = new core\Set();

		$oEscritoDBSet->add($this->getDatosJson_prot_local());
		$oEscritoDBSet->add($this->getDatosJson_prot_destino());
		$oEscritoDBSet->add($this->getDatosJson_prot_ref());
		$oEscritoDBSet->add($this->getDatosId_grupos());
		$oEscritoDBSet->add($this->getDatosDestinos());
		$oEscritoDBSet->add($this->getDatosEntradilla());
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
		return $oEscritoDBSet->getTot();
	}



	/**
	 * Recupera les propietats de l'atribut json_prot_local de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosJson_prot_local() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'json_prot_local'));
		$oDatosCampo->setEtiqueta(_("json_prot_local"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut json_prot_destino de EscritoDB
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
	 * Recupera les propietats de l'atribut json_prot_ref de EscritoDB
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
	 * Recupera les propietats de l'atribut a_id_grupos de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosId_grupos() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_grupos'));
		$oDatosCampo->setEtiqueta(_("id_grupos"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut a_destinos de EscritoDB
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
	 * Recupera les propietats de l'atribut sentradilla de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosEntradilla() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'entradilla'));
		$oDatosCampo->setEtiqueta(_("entradilla"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sasunto de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosAsunto() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'asunto'));
		$oDatosCampo->setEtiqueta(_("asunto"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut sdetalle de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosDetalle() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'detalle'));
		$oDatosCampo->setEtiqueta(_("detalle"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut icreador de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosCreador() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'creador'));
		$oDatosCampo->setEtiqueta(_("creador"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut a_resto_oficinas de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosResto_oficinas() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'resto_oficinas'));
		$oDatosCampo->setEtiqueta(_("resto_oficinas"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut scomentarios de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosComentarios() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'comentarios'));
		$oDatosCampo->setEtiqueta(_("comentarios"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_aprobacion de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_aprobacion() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_aprobacion'));
		$oDatosCampo->setEtiqueta(_("f_aprobacion"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_escrito de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_escrito() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_escrito'));
		$oDatosCampo->setEtiqueta(_("f_escrito"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_contestar de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_contestar() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_contestar'));
		$oDatosCampo->setEtiqueta(_("f_contestar"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut icategoria de EscritoDB
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
	 * Recupera les propietats de l'atribut ivisibilidad de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosVisibilidad() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'visibilidad'));
		$oDatosCampo->setEtiqueta(_("visibilidad"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut iaccion de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosAccion() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'accion'));
		$oDatosCampo->setEtiqueta(_("accion"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut imodo_envio de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosModo_envio() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'modo_envio'));
		$oDatosCampo->setEtiqueta(_("modo_envio"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut df_salida de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosF_salida() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'f_salida'));
		$oDatosCampo->setEtiqueta(_("f_salida"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut iok de EscritoDB
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatosOk() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'ok'));
		$oDatosCampo->setEtiqueta(_("ok"));
		return $oDatosCampo;
	}
	/**
	 * Recupera les propietats de l'atribut itipo_doc de EscritoDB
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
	 * Recupera les propietats de l'atribut banulado de EscritoDB
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
