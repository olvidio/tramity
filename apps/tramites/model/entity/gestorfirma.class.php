<?php
namespace tramites\model\entity;
use core\ConfigGlobal;
use expedientes\model\Expediente;
use core;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\Usuario;
/**
 * GestorFirma
 *
 * Classe per gestionar la llista d'objectes de la clase Firma
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 14/11/2020
 */

class GestorFirma Extends core\ClaseGestor {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */


	/**
	 * Constructor de la classe.
	 *
	 * @return $gestor
	 *
	 */
	function __construct() {
		$oDbl = $GLOBALS['oDBT'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('expediente_firmas');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	/**
	 * devuelve un array con los id cargos que faltan por firmar el expediente para la reunión.
	 * 
	 * @return array 
	 */
	public function faltaFirmarReunionExpediente($id_expediente) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $estado = Expediente::ESTADO_FIJAR_REUNION;
	    //orden_tramite para las firmas de reunion (corresponde a 'todos_d' del tramite);
	    $oExpediente = new Expediente($id_expediente);
	    $id_tramite = $oExpediente->getId_tramite();
	    $gesTramiteCargo = new GestorTramiteCargo();
	    $cTramiteCargo = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $id_tramite, 'id_cargo' => Cargo::CARGO_TODOS_DIR]);
	    if (!empty($cTramiteCargo)) {
    	    $orden_tramite = $cTramiteCargo[0]->getOrden_tramite();
	    } else {
	        return FALSE;
	    }
	    
	    $tipo_voto = Firma::TIPO_VOTO;
	    $valor_ok = Firma::V_OK;
	    $valor_no = Firma::V_NO;
	    
	    $sQuery = "SELECT f.*
                    FROM $nom_tabla f JOIN expedientes e USING (id_expediente)
                    WHERE e.id_expediente = $id_expediente AND e.estado = $estado AND e.f_reunion IS NOT NULL
                        AND f.orden_tramite = $orden_tramite
                        AND f.tipo = $tipo_voto
                        AND (f.valor IS NULL OR (f.valor != $valor_ok AND f.valor != $valor_no))
                    ";
	    if ($oDbl->query($sQuery) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
        $a_exp_faltan_firmas = [];
	    foreach ($oDbl->query($sQuery) as $aFirma) {
            if (empty($aFirma)) {
                return FALSE;
            }
            $a_exp_faltan_firmas[] = $aFirma['id_cargo'];
	    }
	    return $a_exp_faltan_firmas;
	}
	/**
	 * devuelve un array con el id_expediente de los que faltan firmas para la reunión.
	 * 
	 * @return array $a_exp_faltan_firmas
	 */
	public function faltaFirmarReunion() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $estado = Expediente::ESTADO_FIJAR_REUNION;
	    $cargo_tipo = Cargo::CARGO_TODOS_DIR;
	    $tipo_voto = Firma::TIPO_VOTO;
	    $valor_ok = Firma::V_OK;
	    $valor_no = Firma::V_NO;
	    
	    $sQuery = "SELECT DISTINCT f.id_expediente
                    FROM $nom_tabla f JOIN expedientes e USING (id_expediente)
                    WHERE e.estado = $estado AND e.f_reunion IS NOT NULL
                        AND f.cargo_tipo = $cargo_tipo
                        AND f.tipo = $tipo_voto
                        AND (f.valor IS NULL OR (f.valor != $valor_ok AND f.valor != $valor_no))
                    ";
	    if ($oDbl->query($sQuery) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
        $a_exp_faltan_firmas = [];
	    foreach ($oDbl->query($sQuery) as $aFirma) {
            if (empty($aFirma)) {
                return FALSE;
            }
            $a_exp_faltan_firmas[] = $aFirma['id_expediente'];
	    }
	    return $a_exp_faltan_firmas;
	}
	
	/**
	 * devuelve el orden_tramite para el cargo tipo ultimo firmado con ok,no
	 * 
	 * @param integer $orden_tramite
	 */
	public function getOrdenCargo($id_expediente,$cargo_tipo) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $tipo_voto = Firma::TIPO_VOTO;
	    $valor_ok = Firma::V_OK;
	    $valor_no = Firma::V_NO;
	    $valor_vb = Firma::V_VISTO_BUENO;
	    
	    $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND cargo_tipo = $cargo_tipo AND tipo = $tipo_voto
                        AND (valor = $valor_ok OR valor = $valor_no OR valor = $valor_vb)
                    ORDER BY orden_tramite DESC, orden_oficina DESC LIMIT 1";
	    if (($stmt = $oDbl->query($sQuery)) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    $aFirma = $stmt->fetch(\PDO::FETCH_ASSOC);
	    if (!empty($aFirma)) {
	        return FALSE;
	    }
	    $orden_tramite = $aFirma['orden_tramite'];
	    
	    return $orden_tramite;
	}
	
	public function getRecorrido($id_expediente) {
	    // cabio el nombre para los tipo_cargo:
	    $a_cargos_especicales[] = Cargo::CARGO_DISTRIBUIR;
	    $a_cargos_especicales[] = Cargo::CARGO_VB_VCD;
	    $a_cargos_especicales[] = Cargo::CARGO_REUNION;
	    
	    $gesCargos = new GestorCargo();
	    $aCargos =$gesCargos->getArrayCargos(FALSE);
	    $aWhere = ['id_expediente' => $id_expediente,
	        '_ordre' => 'orden_tramite, orden_oficina ASC'
	    ];
	    $cFirmas = $this->getFirmas($aWhere);
	    $comentarios = '';
	    $a_recorrido = [];
	    $oFirma = new Firma();
	    $a_valores = $oFirma->getArrayValor('all');
	    foreach ($cFirmas as $oFirma) {
	        $a_rec = [];
	        $tipo = $oFirma->getTipo();
	        $valor = $oFirma->getValor();
	        $oFvalor = $oFirma->getF_valor();
	        $f_valor = empty($oFvalor)? '' : $oFvalor->getFromLocalHora();
	        $id_usuario = $oFirma->getId_usuario();
	        $oUsuario = new Usuario($id_usuario);
	        $nom_usuario = $oUsuario->getNom_usuario();
	        $id_cargo = $oFirma->getId_cargo();
	        $cargo_tipo = $oFirma->getCargo_tipo();
	        if (in_array($cargo_tipo, $a_cargos_especicales)) {
    	        $cargo = $aCargos[$cargo_tipo];
	        } else {
                $cargo = $aCargos[$id_cargo];
	        }
	        if (!empty($valor) && ($valor != Firma::V_VISTO)) {
	            $voto = $a_valores[$valor];
	            $observ = $oFirma->getObserv();
	            $observ_ponente = $oFirma->getObserv_creador();
	            if ($tipo == Firma::TIPO_VOTO) {
	                if (!empty($observ)) {
	                    $comentarios .= empty($comentarios)? '' : "<br>";
	                    $comentarios .= "$cargo($voto): $observ";
	                    $comentarios .= " $cargo($nom_usuario) [$voto]: $observ";
	                }
	                switch ($valor) {
	                    case Firma::V_NO:
	                    case Firma::V_RECHAZADO:
	                        $a_rec['class'] = "list-group-item-danger";
	                        break;
	                    case Firma::V_OK:
	                        $a_rec['class'] = "list-group-item-success";
	                        break;
	                    default:
	                        $a_rec['class'] = "list-group-item-info";
	                }
	                $a_rec['valor'] = "$f_valor $cargo($nom_usuario) [$voto]";
	                $a_recorrido[] = $a_rec;
	            }
	            if ($tipo == Firma::TIPO_ACLARACION) {
	                $voto = _("aclaración");
	                $comentarios .= empty($comentarios)? '' : "<br>";
	                $comentarios .= "$cargo($voto): $observ";
	                if (!empty($observ_ponente)) {
	                    $comentarios .= " rta: $observ_ponente";
	                }
	            }
	        } else {
	            if ($tipo == Firma::TIPO_VOTO) {
	                $a_rec['class'] = "";
	                $a_rec['valor'] = $cargo;
	                // lo marco como visto (sólo el mio). Si hay más de uno sólo debería ser el primero vacío
	                if ($id_cargo == ConfigGlobal::mi_id_cargo()) {
	                    $orden_tramite_ref = $oFirma->getOrden_tramite();
	                    // sólo el siguiente en orden tramite si estan todos completos.
                    	if ($this->getAnteriorOK($id_expediente,$orden_tramite_ref)) {
	                       $oFirma->setValor(Firma::V_VISTO);
	                       $oFirma->DBGuardar();
	                    
                           $voto = $a_valores[Firma::V_VISTO];
	                       $a_rec['class'] = "list-group-item-info";
                    	   $a_rec['valor'] = "$f_valor $cargo($nom_usuario) [$voto]";
                    	}
	                }
	                $a_recorrido[] = $a_rec;
	            }
	        }
	    }
	    
	    return ['recorrido' => $a_recorrido,
	        'comentarios' => $comentarios,
	    ];
	}
	
	/**
	 * Devuelve boolean
	 * comprobar la ultima firma es el vº bº del vcd
     * pasarlo a scdl para fijar reunión
	 *
	 * @param integer $id_expediente
	 * @return boolean
	 */
	public function paraReunion($id_expediente) {
	    $oFirmaUltimaOk = $this->getUltimaOk($id_expediente);
	    
	    $valor = $oFirmaUltimaOk->getValor();
	    $cargo_tipo = $oFirmaUltimaOk->getCargo_tipo();
	    
	    if ($cargo_tipo == Cargo::CARGO_VB_VCD && $valor == Firma::V_VISTO_BUENO) {
	        return TRUE;
	    } else {
	        return FALSE;
	    }
	}
	
	/**
	 * Devuelve boolean
	 * comprobar que ya ha firmado todo el mundo, para 
     * pasarlo a scdl para distribuir (ok_scdl)
	 * 
	 *
	 * @param integer $id_expediente
	 * @return boolean
	 */
	public function paraDistribuir($id_expediente) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    // El siguienet paso es distribuir, y ya han firmado todos:
	    
	    // Buscar el orden tramite de distribuir, y comprobar que todos los anteriores son ok.
	    $cargo_tipo_distribuir = Cargo::CARGO_DISTRIBUIR;
	    $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND cargo_tipo = $cargo_tipo_distribuir AND valor IS NULL
                    ORDER BY orden_tramite, orden_oficina LIMIT 1";
	    if (($stmt = $oDbl->query($sQuery)) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    $aFirma = $stmt->fetch(\PDO::FETCH_ASSOC);
	    if (!empty($aFirma['orden_tramite'])) {
	        $orden_tramite_secretaria = $aFirma['orden_tramite'];
	        // mirar los anteriores:
	        $aOrdenTramite = $this->arrayOrdenTramite($id_expediente);
	        krsort($aOrdenTramite);
	        foreach (array_keys($aOrdenTramite) as $orden_tramite) {
	            if ($orden_tramite > $orden_tramite_secretaria) continue;
	            if (!$this->getAnteriorOK($id_expediente, $orden_tramite)) {
	                // no está completo. devuelve FALSE
	                return FALSE;
	            }
	        }
	    }
	    return TRUE;
	}
	
	private function arrayOrdenTramite($id_expediente) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    // posibles orden_tramite:
	    $sQuery = "SELECT DISTINCT orden_tramite, cargo_tipo
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente
                    ORDER BY orden_tramite DESC";
	    if (($oDbl->query($sQuery)) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    // el primero es el actual, el segundo (si existe) es el anterior.
	    $aOrdenTramite = [];
	    foreach ($oDbl->query($sQuery) as $aDades) {
	        $orden_tramite = $aDades['orden_tramite'];
	        $cargo_tipo = $aDades['cargo_tipo'];
	        $aOrdenTramite[$orden_tramite] = $cargo_tipo;
	    }
	    return $aOrdenTramite;
	}

	/**
	 * devuelve el objeto Firma. El primero que tiene que firmar el expediente.
	 * Al ponerlo a circular, si soy el primero, lo firmo directamente.
	 *
	 * @param integer $id_expediente
	 * @return object $oFirma
	 */
	public function getPrimeraFirma($id_expediente) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    // posibles orden_tramite:
	    $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente
                    ORDER BY orden_tramite, orden_oficina LIMIT 1";
	    if (($oDbl->query($sQuery)) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    // el primero es el actual, el segundo (si existe) es el anterior.
	    foreach ($oDbl->query($sQuery) as $aDades) {
	        $a_pkey = array('id_item' => $aDades['id_item']);
	        $oFirma = new Firma($a_pkey);
	        $oFirma->setAllAtributes($aDades);
	    }
	    return $oFirma;
	}

	/**
	 * devuelve el objeto Firma. La ultima firmada
	 *
	 * @param integer $id_expediente
	 * @return object $oFirma
	 */
	public function getUltimaOk($id_expediente) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $tipo_voto = Firma::TIPO_VOTO;
        $valor_ok = Firma::V_OK;
        $valor_no = Firma::V_NO;
        $valor_vb = Firma::V_VISTO_BUENO;
        
	    // posibles orden_tramite:
	    $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND tipo = $tipo_voto
                        AND (valor = $valor_ok OR valor = $valor_no OR valor = $valor_vb)
                    ORDER BY orden_tramite DESC, orden_oficina DESC LIMIT 1";
	    if (($oDbl->query($sQuery)) === FALSE) {
	        $sClauError = 'GestorFirma.query';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return FALSE;
	    }
	    // el primero es el actual, el segundo (si existe) es el anterior.
	    foreach ($oDbl->query($sQuery) as $aDades) {
	        $a_pkey = array('id_item' => $aDades['id_item']);
	        $oFirma = new Firma($a_pkey);
	        $oFirma->setAllAtributes($aDades);
	    }
	    return $oFirma;
	}
	
	/**
	 * Comprobar si el bloque de orden_tramite anterior està todo firmado.
	 *
	 * @param integer $id_expediente
	 * @param integer $orden_tramite
	 * @return boolean
	 */
	public function getAnteriorOK($id_expediente,$orden_tramite_ref) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    // posibles orden_tramite:
	    $aOrdenTramite = $this->arrayOrdenTramite($id_expediente);
	    krsort($aOrdenTramite);
	    $flag = 0;
	    $orden_anterior = 0;
	    foreach (array_keys($aOrdenTramite) as $orden_tramite) {
	        if ($flag == 1) {
	            $orden_anterior = $orden_tramite;
	            break;
	        }
	        if ($orden_tramite > $orden_tramite_ref) continue;
	        if ($orden_tramite == $orden_tramite_ref) {
	            $flag = 1;
	        }
	    }
	    if ($flag == 1 && empty($orden_anterior)) {
            // No existe, el primero es el actual: ok
	        return TRUE;
	    }
	    
        $tipo_voto = Firma::TIPO_VOTO;
        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND tipo = $tipo_voto AND orden_tramite = $orden_anterior
                    ";
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorFirma.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // Contar que todos sean ok:
        foreach ($oDbl->query($sQuery) as $aDades) {
            $valor = $aDades['valor'];
            /*
             const TIPO_VOTO          = 1;
             const TIPO_ACLARACION    = 2;
             // valor
             const V_VISTO        = 1;  // leído, pensando
             const V_ESPERA       = 2;  // distinto a no leído
             const V_NO           = 3;  // voto negativo
             const V_OK           = 4;  // voto positivo
             const V_DILATA       = 22;  // sólo vcd
             const V_RECHAZADO    = 23;  // sólo vcd
             const V_VISTO_BUENO  = 24;  // sólo vcd VºBº
             */
            if ($valor == Firma::V_NO OR $valor == Firma::V_OK) {
            } else {
                return FALSE;
            }
        }
        return TRUE;
	}
	
	/**
	 * retorna l'array d'objectes de tipus Firma
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus Firma
	 */
	function getFirmasQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oFirmaSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorFirma.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oFirma= new Firma($a_pkey);
			$oFirma->setAllAtributes($aDades);
			$oFirmaSet->add($oFirma);
		}
		return $oFirmaSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus Firma
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus Firma
	 */
	function getFirmas($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oFirmaSet = new core\Set();
		$oCondicion = new core\Condicion();
		$aCondi = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp == '_ordre') continue;
			if ($camp == '_limit') continue;
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) $aCondi[]=$a;
			// operadores que no requieren valores
			if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') unset($aWhere[$camp]);
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') unset($aWhere[$camp]);
            if ($sOperador == 'TXT') unset($aWhere[$camp]);
		}
		$sCondi = implode(' AND ',$aCondi);
		if ($sCondi!='') $sCondi = " WHERE ".$sCondi;
		$sOrdre = '';
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
		if (isset($aWhere['_ordre'])) unset($aWhere['_ordre']);
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') $sLimit = ' LIMIT '.$aWhere['_limit'];
		if (isset($aWhere['_limit'])) unset($aWhere['_limit']);
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorFirma.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorFirma.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oFirma = new Firma($a_pkey);
			$oFirma->setAllAtributes($aDades);
			$oFirmaSet->add($oFirma);
		}
		return $oFirmaSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
