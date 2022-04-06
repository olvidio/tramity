<?php
namespace entradas\model\entity;
use core;
/**
 * GestorEntradaCompartidaAdjunto
 *
 * Classe per gestionar la llista d'objectes de la clase EntradaCompartidaAdjunto
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 30/3/2022
 */

class GestorEntradaCompartidaAdjunto Extends core\ClaseGestor {
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
		$this->setNomTabla('entrada_compartida_adjuntos');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	public function getArrayIdAdjuntos($id_entrada_compartida) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$aAdjuntos = [];
		
		$sQry = "SELECT * FROM $nom_tabla WHERE id_entrada_compartida = $id_entrada_compartida ";
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorEntradaCompartidaAdjunto.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute()) === FALSE) {
			$sClauError = 'GestorEntradaCompartidaAdjunto.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$id_item = $aDades['id_item'];
			$nom = $aDades['nom'];
			$aAdjuntos[$id_item] = $nom;
		}
		return $aAdjuntos;
		
	}
	
	/**
	 * retorna l'array d'objectes de tipus EntradaCompartidaAdjunto
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus EntradaCompartidaAdjunto
	 */
	function getEntradaCompartidaAdjuntosQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEntradaCompartidaAdjuntoSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorEntradaCompartidaAdjunto.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oEntradaCompartidaAdjunto= new EntradaCompartidaAdjunto($a_pkey);
			$oEntradaCompartidaAdjuntoSet->add($oEntradaCompartidaAdjunto);
		}
		return $oEntradaCompartidaAdjuntoSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus EntradaCompartidaAdjunto
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus EntradaCompartidaAdjunto
	 */
	function getEntradaCompartidaAdjuntos($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEntradaCompartidaAdjuntoSet = new core\Set();
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
			$sClauError = 'GestorEntradaCompartidaAdjunto.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEntradaCompartidaAdjunto.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oEntradaCompartidaAdjunto = new EntradaCompartidaAdjunto($a_pkey);
			$oEntradaCompartidaAdjuntoSet->add($oEntradaCompartidaAdjunto);
		}
		return $oEntradaCompartidaAdjuntoSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
