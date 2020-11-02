<?php
namespace expedientes\model\entity;
use core;
/**
 * GestorExpedienteDB
 *
 * Classe per gestionar la llista d'objectes de la clase ExpedienteDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

class GestorExpedienteDB Extends core\ClaseGestor {
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
		$this->setNomTabla('expedientes');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/

	/**
	 * Devuelve un array con los id de los expedientes que están marcados
	 * para que los vea el id_cargo (o no los que ya ha visto si es TRUE).
	 * 
	 * @param integer $id_cargo
	 * @param string $visto ['visto'|'no_visto'|'']
	 * @return array de id_expedientes
	 */
	public function getIdExpedientesPreparar($id_cargo,$visto='no_visto') {
		$oDbl = $this->getoDbl();
		switch ($visto) {
		    case 'visto':
                $Where_visto = "AND items.visto=1";
		    break;
		    case 'no_visto':
                $Where_visto = "AND (items.visto=0 OR items.visto IS NULL)";
		    break;
		    case 'todos':
		    default:
                $Where_visto = "";
		}
	    $sQuery = "SELECT e.id_expediente, e.asunto, e.ponente, e.json_preparar, items.id, items.visto 
                    FROM expedientes e, jsonb_to_recordset(e.json_preparar) as items(id smallint,visto smallint) 
                    WHERE items.id=$id_cargo $Where_visto";
	    
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorExpedienteDB.queryPreparar';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		$a_expedientes = [];
		foreach ($oDbl->query($sQuery) as $aDades) {
			$id_expediente = $aDades['id_expediente'];
    		$a_expedientes[] = $id_expediente;
		}
		return $a_expedientes;
	}
	/**
	 * retorna l'array d'objectes de tipus ExpedienteDB
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus ExpedienteDB
	 */
	function getExpedientesDBQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oExpedienteDBSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorExpedienteDB.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_expediente' => $aDades['id_expediente']);
			$oExpedienteDB= new ExpedienteDB($a_pkey);
			$oExpedienteDB->setAllAtributes($aDades);
			$oExpedienteDBSet->add($oExpedienteDB);
		}
		return $oExpedienteDBSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus ExpedienteDB
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus ExpedienteDB
	 */
	function getExpedientesDB($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oExpedienteDBSet = new core\Set();
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
			$sClauError = 'GestorExpedienteDB.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorExpedienteDB.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_expediente' => $aDades['id_expediente']);
			$oExpedienteDB = new ExpedienteDB($a_pkey);
			$oExpedienteDB->setAllAtributes($aDades);
			$oExpedienteDBSet->add($oExpedienteDB);
		}
		return $oExpedienteDBSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
