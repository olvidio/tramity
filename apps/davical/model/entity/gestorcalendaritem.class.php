<?php
namespace davical\model\entity;
use core;
/**
 * GestorCalendarItem
 *
 * Classe per gestionar la llista d'objectes de la clase CalendarItem
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 25/2/2021
 */

class GestorCalendarItem Extends core\ClaseGestor {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */


	/**
	 * Constructor de la classe.
	 *
	 * @return $gestor
	 *
	 */
	function __construct() {
		$oDbl = $GLOBALS['oDBDavical'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('calendar_item');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	/**
	 * Devuelve los Calendar items para un determiado id_reg ('REN' + id_entrada)
	 * 
	 * @param string $id_reg
	 * @return array
	 */
	public function getCalendarItemsById_reg($id_reg) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    $oCalendarItemSet = new core\Set();
	    
	    $sCondi = "WHERE uid ~ '^".$id_reg."-' ORDER BY due";
	    
	    $sQuery = "SELECT * FROM $nom_tabla ".$sCondi;

		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorCalendarItem.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('dav_id' => $aDades['dav_id']);
			$oCalendarItem= new CalendarItem($a_pkey);
			$oCalendarItem->setAllAtributes($aDades);
			$oCalendarItemSet->add($oCalendarItem);
		}
	    return $oCalendarItemSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus CalendarItem
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus CalendarItem
	 */
	function getCalendarItemsQuery($sQuery='') {
	$oDbl = $this->getoDbl();
		$oCalendarItemSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorCalendarItem.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('dav_id' => $aDades['dav_id']);
			$oCalendarItem= new CalendarItem($a_pkey);
			$oCalendarItem->setAllAtributes($aDades);
			$oCalendarItemSet->add($oCalendarItem);
		}
		return $oCalendarItemSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus CalendarItem
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus CalendarItem
	 */
	function getCalendarItems($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oCalendarItemSet = new core\Set();
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
			$sClauError = 'GestorCalendarItem.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorCalendarItem.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array( 'dav_id' => $aDades['dav_id']);
			$oCalendarItem = new CalendarItem($a_pkey);
			$oCalendarItem->setAllAtributes($aDades);
			$oCalendarItemSet->add($oCalendarItem);
		}
		return $oCalendarItemSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
