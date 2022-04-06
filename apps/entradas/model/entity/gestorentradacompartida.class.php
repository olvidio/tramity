<?php
namespace entradas\model\entity;
use core;
/**
 * GestorEntradaCompartida
 *
 * Classe per gestionar la llista d'objectes de la clase EntradaCompartida
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/4/2022
 */

class GestorEntradaCompartida Extends core\ClaseGestor {
	/* ATRIBUTS ----------------------------------------------------------------- */

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
		$this->setNomTabla('entradas_compartidas');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/

	/**
	 * retorna l'array d'objectes de tipus EntradaCompartida
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus EntradaCompartida
	 */
	function getEntradasCompartidasQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEntradaCompartidaSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorEntradaCompartida.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_entrada_compartida' => $aDades['id_entrada_compartida']);
			$oEntradaCompartida= new EntradaCompartida($a_pkey);
			$oEntradaCompartidaSet->add($oEntradaCompartida);
		}
		return $oEntradaCompartidaSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus EntradaCompartida
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus EntradaCompartida
	 */
	function getEntradasCompartidas($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEntradaCompartidaSet = new core\Set();
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
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorEntradaCompartida.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEntradaCompartida.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_entrada_compartida' => $aDades['id_entrada_compartida']);
			$oEntradaCompartida = new EntradaCompartida($a_pkey);
			$oEntradaCompartidaSet->add($oEntradaCompartida);
		}
		return $oEntradaCompartidaSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
