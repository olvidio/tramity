<?php
namespace etiquetas\model\entity;
use core\ConfigGlobal;
use core;
use usuarios\model\entity\Cargo;
/**
 * GestorEtiqueta
 *
 * Classe per gestionar la llista d'objectes de la clase Etiqueta
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 10/11/2020
 */

class GestorEtiqueta Extends core\ClaseGestor {
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
		$this->setNomTabla('etiquetas');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/

	public function getMisEtiquetas($id_cargo='') {
	    if (empty($id_cargo)) {
	        $id_cargo = ConfigGlobal::mi_id_cargo();
	    }
	    $cEtiquetasPersonales = $this->getEtiquetasPersonales($id_cargo);
	    $cEtiquetasOficina = $this->getEtiquetasMiOficina($id_cargo);
	    
	    return array_merge($cEtiquetasPersonales, $cEtiquetasOficina);
	}
	
	public function getEtiquetasPersonales($id_cargo='') {
	    if (empty($id_cargo)) {
	        $id_cargo = ConfigGlobal::mi_id_cargo();
	    }
	    $aWhere = [ 'id_cargo' => $id_cargo,
	        '_ordre' => 'nom_etiqueta',
	    ];
	    $aOperador = [];
	    $cEtiquetasPersonales = $this->getEtiquetas($aWhere,$aOperador);
	    
	    return $cEtiquetasPersonales;
	}
	    
	public function getEtiquetasMiOficina($id_cargo='') {
	    if (empty($id_cargo)) {
	        $id_cargo = ConfigGlobal::mi_id_cargo();
	    }
	    $oCargo = new Cargo($id_cargo);
	    $id_oficina = $oCargo->getId_oficina();
	    
	    $aWhere = [ 'id_cargo' => $id_oficina,
	        '_ordre' => 'nom_etiqueta',
	    ];
	    $aOperador = [];
	    $cEtiquetasOficina = $this->getEtiquetas($aWhere,$aOperador);
	    
	    return $cEtiquetasOficina;
	}
	/**
	 * retorna l'array d'objectes de tipus Etiqueta
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus Etiqueta
	 */
	function getEtiquetasQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEtiquetaSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorEtiqueta.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_etiqueta' => $aDades['id_etiqueta']);
			$oEtiqueta= new Etiqueta($a_pkey);
			$oEtiqueta->setAllAtributes($aDades);
			$oEtiquetaSet->add($oEtiqueta);
		}
		return $oEtiquetaSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus Etiqueta
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus Etiqueta
	 */
	function getEtiquetas($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEtiquetaSet = new core\Set();
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
			$sClauError = 'GestorEtiqueta.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEtiqueta.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_etiqueta' => $aDades['id_etiqueta']);
			$oEtiqueta = new Etiqueta($a_pkey);
			$oEtiqueta->setAllAtributes($aDades);
			$oEtiquetaSet->add($oEtiqueta);
		}
		return $oEtiquetaSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
