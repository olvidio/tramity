<?php
namespace lugares\model\entity;
use core;
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
		$this->setNomTabla('lugares');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	/**
	 * retorna un array
	 * Els posibles llocs
	 *
	 * @return array
	 */
	function getArrayLugares($tipo_ctr='',$dl='',$region='') {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where = "WHERE anulado = 'f'";
	    if (!empty($tipo_ctr)) {
    	    $Where .= empty($Where)? '' : ' AND ';
	        $Where .= "tipo_ctr = '$tipo_ctr'";
	    }
	    if (!empty($dl)) {
    	    $Where .= empty($Where)? '' : ' AND ';
	        $Where .= "dl = '$dl'";
	    }
	    if (!empty($region)) {
    	    $Where .= empty($Where)? '' : ' AND ';
	        $Where .= "region = '$region'";
	    }
	    //$Where2 = empty($Where)? '' : 'WHERE '.$Where;
	    $sQuery="SELECT id_lugar, sigla FROM $nom_tabla
                   $Where
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
			$oLugar->setAllAtributes($aDades);
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
        $sLimit='';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
		if (isset($aWhere['_ordre'])) unset($aWhere['_ordre']);
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') $sLimit = ' LIMIT '.$aWhere['_limit'];
		if (isset($aWhere['_limit'])) unset($aWhere['_limit']);
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
			$oLugar->setAllAtributes($aDades);
			$oLugarSet->add($oLugar);
		}
		return $oLugarSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
