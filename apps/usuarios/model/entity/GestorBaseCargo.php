<?php
namespace usuarios\model\entity;

use core\ClaseGestor;
use core\Condicion;
use core\Set;

/**
 * GestorBaseCargo
 *
 * Clase para gestionar la lista de objetos de la clase Cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 30/11/2022
 */

class GestorBaseCargo Extends ClaseGestor {
	/* ATRIBUTOS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */
	

	public function __construct() {
		$oDbl = $GLOBALS['oDBT'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('aux_cargos');
	}


	/* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

	/**
	 * devuelve una colección (array) de objetos de tipo Cargo
	 *
	 * @param string $sQuery la query a ejecutar.
	 * @return array|FALSE Una colección de objetos de tipo Cargo
	 */
	public function getCargosQuery(string $sQuery=''): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$oCargoSet = new Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClaveError = 'GestorBaseCargo.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$oCargo = new Cargo($aDades['id_cargo']);
			$oCargoSet->add($oCargo);
		}
		return $oCargoSet->getTot();
	}

	/**
	 * devuelve una colección (array) de objetos de tipo Cargo
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo Cargo
	 */
	public function getCargos(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oCargoSet = new Set();
		$oCondicion = new Condicion();
		$aCondicion = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp === '_ordre') { continue; }
			if ($camp === '_limit') { continue; }
			$sOperador = $aOperators[$camp] ?? '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondicion[]=$a; }
			// operadores que no requieren valores
			if ($sOperador === 'BETWEEN' || $sOperador === 'IS NULL' || $sOperador === 'IS NOT NULL' || $sOperador === 'OR') { unset($aWhere[$camp]); }
            if ($sOperador === 'IN' || $sOperador === 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador === 'TXT') { unset($aWhere[$camp]); }
		}
		$sCondicion = implode(' AND ',$aCondicion);
		if ($sCondicion !=='') { $sCondicion = " WHERE ".$sCondicion; }
		$sOrdre = '';
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondicion.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClaveError = 'GestorBaseCargo.listar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClaveError = 'GestorBaseCargo.listar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$oCargo = new Cargo($aDades['id_cargo']);
			$oCargoSet->add($oCargo);
		}
		return $oCargoSet->getTot();
	}

	/* MÉTODOS PROTECTED --------------------------------------------------------*/

	/* MÉTODOS GET y SET --------------------------------------------------------*/
}
