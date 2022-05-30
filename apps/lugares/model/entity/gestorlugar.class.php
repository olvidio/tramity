<?php
namespace lugares\model\entity;
use function core\is_true;
use core;
use usuarios\model\entity\Cargo;
/**
 * GestorLugar
 *
 * Classe per gestionar la llista d'objectes de la clase Lugar
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */

class GestorLugar Extends core\ClaseGestor {
	/* ATRIBUTS ----------------------------------------------------------------- */
	
	const SEPARADOR = '-------------'; 

	/* CONSTRUCTOR -------------------------------------------------------------- */


	/**
	 * Constructor de la classe.
	 *
	 * @return $gestor
	 *
	 */
	function __construct() {
		$oDbl = $GLOBALS['oDBP'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('lugares');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	/**
	 * Devuelve el nombre de las posibles plataformas as4
	 * 
	 * @return array []
	 */
	public function getPlataformas() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    $a_plataformas = [];
	    
	    $query_plataforma = "SELECT DISTINCT plataforma FROM $nom_tabla
                            WHERE anulado = FALSE AND plataforma IS NOT NULL
                            ORDER BY plataforma";
	    foreach ($oDbl->query($query_plataforma) as $aClave) {
	        $clave=$aClave[0];
	        $a_plataformas[$clave] = $clave;
	    }
	    
	    if (is_array($a_plataformas)) {
	        return $a_plataformas;
	    } else {
	        exit (_("Error al buscar las plataformas posibles"));
	    }
	    
		return $a_plataformas;
	}
	
	/**
	 * devuelve el id del IESE
	 */
	public function getId_iese() {
	    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
	        exit (_("Error al buscar el id del IESE"));
	    }
	    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
	    	$sigla = 'IESE';
	    	$cLugares = $this->getLugares(['sigla' => $sigla]);
	    	$oLugar = $cLugares[0];
	    	return $oLugar->getId_lugar();
	    }
	}
	
	/**
	 * devuelve el id de la cr (cr)
	 */
	public function getId_cr() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $mi_ctr = $_SESSION['oConfig']->getSigla();
            $mi_dl = $this->getSigla_superior($mi_ctr);
            $mi_cr = $this->getSigla_superior($mi_dl);
	    }
	    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
            $mi_dl = $_SESSION['oConfig']->getSigla();
            $mi_cr = $this->getSigla_superior($mi_dl);
	    }

	    // 0º dlb y cr y el propio ctr
	    $lugares = [];
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE sigla='$mi_cr' AND anulado = FALSE
                            ORDER BY sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $lugares[] = $clave;
	    }
	    
	    if (is_array($lugares) && count($lugares) == 1) {
	        return $lugares[0];
	    } else {
	        exit (_("Error al buscar el id de cr"));
	    }
	}
	
	/**
	 * devuelve el id de la sigla (dlb)
	 */
	public function getId_sigla_local() {
	    $sigla = $_SESSION['oConfig']->getSigla();
	    $cLugares = $this->getLugares(['sigla' => $sigla]);
	    if (!empty($cLugares)) {
	        $id_sigla = $cLugares[0]->getId_lugar();
	    }
	    return $id_sigla;
	}
	
	/**
	 * devuelve la sigla (o el id) de la entidad superior (dl para los centros, cr para las dl)
	 *  
	 * @param boolean $id Si quero el id o la sigla.
	 * @return string|integer
	 */
	public function getSigla_superior($sigla_base,$id=FALSE) {
	    $rta = '';
	    $cLugares = $this->getLugares(['sigla' => $sigla_base]);
	    if (!empty($cLugares)) {
	        $region = $cLugares[0]->getRegion();
	        $dl = $cLugares[0]->getDl();
	        $tipo_ctr = $cLugares[0]->getTipo_ctr();
	        switch ($tipo_ctr) {
	            case 'dl':
	                $tipo_sup = 'cr';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                               'region' => $region,
                               'sigla' => $region, // quitar cancilleria...
                    ];
	                break;
	            case 'cr':
	                $tipo_sup = 'cg';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                               'region' => $region,
                               'dl' => $dl,
                               'sigla' => $dl,
                    ];
	                break;
	            case 'cg':
	                $tipo_sup = 'vat';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                               'region' => $region,
                               'dl' => $dl
                    ];
	                break;
	            default:   // 'ctr', am, nj, igl...
	                $tipo_sup = 'dl';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                               'region' => $region,
                               'dl' => $dl,
                               'sigla' => $dl, // quitar dlbf, cancilleria...
                    ];
	                break;
	                
	        }
	            
	        $cLugarSup = $this->getLugares($aWhere);
            if (!empty($cLugarSup)) {
                if ($id) {
                    $rta = $cLugarSup[0]->getId_lugar();
                } else {
                    $rta = ($tipo_sup == 'cr')? $tipo_sup : $cLugarSup[0]->getSigla();
                }
            }
	        
	    }
	    
	    if (empty($rta)) {
	        return '?';
	    } else {
            return $rta;
	    }
	}
	
	/**
	 * retorna un array 
	 * Els posibles llocs per buscar: també els anulados
	 *
	 * @param boolean $ctr_anulados
	 * @return array   id_lugar => sigla
	 */
	function getArrayBusquedas($ctr_anulados=FALSE) {
	    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            return $this->getArrayBusquedasCtr();
	    } else {
            return $this->getArrayBusquedasDl($ctr_anulados);
	    }
	}
	/**
	 * retorna un array 
	 * Els posibles llocs per buscar en el cas del ctr
	 *
	 * @return array   id_lugar => sigla
	 */
	function getArrayBusquedasCtr() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    $mi_ctr = $_SESSION['oConfig']->getSigla();
	    $mi_dl = $this->getSigla_superior($mi_ctr);
	    $mi_cr = $this->getSigla_superior($mi_dl);
	    
        $lugares = [];
	    // 0º dlb y cr y el propio ctr
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE (sigla='$mi_cr' OR sigla='$mi_dl' OR sigla='$mi_ctr') AND anulado = FALSE
                            ORDER BY sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    
	    return $lugares;
	}
	/**
	 * retorna un array 
	 * Els posibles llocs per buscar: també els anulados
	 *
	 * @param boolean $ctr_anulados
	 * @return array   id_lugar => sigla
	 */
	function getArrayBusquedasDl($ctr_anulados=FALSE) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    $mi_dl = $_SESSION['oConfig']->getSigla();
	    
	    $Where_anulados = is_true($ctr_anulados)? '' :  ' AND anulado=FALSE';
	    
        $lugares = [];
	    // 0º dlb y cr
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE (sigla='cr' OR sigla='$mi_dl') $Where_anulados
                            ORDER BY sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // separación
	    $lugares['separador'] = self::SEPARADOR;
	    // 1º ctr de dl
	    $query_ctr="SELECT id_lugar, sigla, nombre, substring(tipo_ctr from 1 for 1) as tipo FROM $nom_tabla
                            WHERE dl='$mi_dl' AND tipo_ctr ~ '^(a|n|s)' $Where_anulados
                            ORDER BY tipo,sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // 2º oc de dlb
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE dl='$mi_dl' AND tipo_ctr ~ 'oc' $Where_anulados
                            ORDER BY tipo_ctr,sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // 3º separación
	    $lugares['separador3'] = self::SEPARADOR;
	    // 4º dl de H
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='dl' AND region='H'  $Where_anulados
                            ORDER BY tipo_ctr,sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // 5º separación
	    $lugares['separador5'] = self::SEPARADOR;
	    // 6º cr
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='cr' $Where_anulados
                            ORDER BY tipo_ctr,sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // 7º separación
	    $lugares['separador7'] = self::SEPARADOR;
	    // 8º dl ex
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='dl' AND region != 'H' AND sigla != 'ro'  $Where_anulados
                            ORDER BY tipo_ctr,sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    // 9º separación
	    $lugares['separador9'] = self::SEPARADOR;
	    // 10º cg
	    $query_ctr="SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE sigla='cg' $Where_anulados
                            ORDER BY sigla";
	    foreach ($oDbl->query($query_ctr) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $lugares[$clave]=$val;
	    }
	    
	    return $lugares;
	}

	/**
	 * retorna un array
	 * Els posibles ctr de la dl
	 *
	 * @param boolean $ctr_anulados
	 * @return array   id_lugar => sigla
	 */
	function getArrayLugaresCtr($ctr_anulados=FALSE) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    $mi_dl = $_SESSION['oConfig']->getSigla();
	    
	    $Where_anulados = is_true($ctr_anulados)? '' :  ' AND anulado=FALSE';
	    
	    $sQuery="SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE dl = '$mi_dl' $Where_anulados
                 ORDER BY sigla";
	    if (($oDbl->query($sQuery)) === false) {
	        $sClauError = 'GestorLugares.Array';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return false;
	    }
	    $aOpciones=array();
	    foreach ($oDbl->query($sQuery) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $aOpciones[$clave]=$val;
	    }
	    return $aOpciones;
	}

	/**
	 * retorna un array
	 * Els posibles dl
	 *
	 * @param string $tipo_ctr ('dl', 'cr')
	 * @param boolean $ctr_anulados
	 * @return array   id_lugar => sigla
	 */
	function getArrayLugaresTipo($tipo_ctr,$ctr_anulados=FALSE) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where_anulados = is_true($ctr_anulados)? '' :  ' AND anulado=FALSE';
	    
	    $sQuery="SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE tipo_ctr = '$tipo_ctr' $Where_anulados
                 ORDER BY sigla";
	    if (($oDbl->query($sQuery)) === false) {
	        $sClauError = 'GestorLugares.Array';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return false;
	    }
	    $aOpciones=array();
	    foreach ($oDbl->query($sQuery) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $aOpciones[$clave]=$val;
	    }
	    return $aOpciones;
	}

	/**
	 * Devuelve un array con los lugares
	 * 
	 * @param boolean $ctr_anulados
	 * @return array   id_lugar => sigla
	 */
	function getArrayLugares($ctr_anulados=FALSE) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where_anulados = is_true($ctr_anulados)? '' :  ' WHERE anulado=FALSE';
	    
	    $sQuery="SELECT id_lugar, sigla FROM $nom_tabla
                 $Where_anulados
                 ORDER BY sigla";
	    if (($oDbl->query($sQuery)) === false) {
	        $sClauError = 'GestorLugares.Array';
	        $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
	        return false;
	    }
	    $aOpciones=array();
	    foreach ($oDbl->query($sQuery) as $aClave) {
	        $clave=$aClave[0];
	        $val=$aClave[1];
	        $aOpciones[$clave]=$val;
	    }
	    return $aOpciones;
	}


	/**
	 * retorna l'array d'objectes de tipus Lugar
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus Lugar
	 */
	function getLugaresQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oLugarSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorLugar.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_lugar' => $aDades['id_lugar']);
			$oLugar= new Lugar($a_pkey);
			$oLugarSet->add($oLugar);
		}
		return $oLugarSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus Lugar
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus Lugar
	 */
	function getLugares($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oLugarSet = new core\Set();
		$oCondicion = new core\Condicion();
		$aCondi = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp == '_ordre') { continue; }
			if ($camp == '_limit') { continue; }
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
			// operadores que no requieren valores
			if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
		}
		$sCondi = implode(' AND ',$aCondi);
		if ($sCondi!='') { $sCondi = " WHERE ".$sCondi; }
		$sOrdre = '';
        $sLimit='';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorLugar.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorLugar.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_lugar' => $aDades['id_lugar']);
			$oLugar= new Lugar($a_pkey);
			$oLugarSet->add($oLugar);
		}
		return $oLugarSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
