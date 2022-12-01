<?php
namespace usuarios\model\entity;

use core\ClaseGestor;
use core\Condicion;
use core\Set;
use PDO;

/**
 * GestorUsuario
 *
 * Clase para gestionar la lista de objetos de la clase Usuario
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 30/11/2022
 */

class GestorBaseUsuario Extends ClaseGestor {
	/* ATRIBUTOS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */
	

	public function __construct() {
		$oDbl = $GLOBALS['oDBT'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('aux_usuarios');
	}


	/* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

	/**
	 * devuelve una colección (array) de objetos de tipo Usuario
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo Usuario
	 */
	public function getUsuarios(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oUsuarioSet = new Set();
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
			$sClaveError = 'GestorUsuario.listar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClaveError = 'GestorUsuario.listar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}

		foreach ($oDblSt as $aDades) {
            // para los bytea, sobre escribo los valores:
            $password = '';
            $oDblSt->bindColumn('password', $password, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_ASSOC);
            $aDades['password'] = $password;

			$oUsuario = new Usuario($aDades['id_usuario']);
            $oUsuario->hidrate($aDades);
			$oUsuarioSet->add($oUsuario);
		}
		return $oUsuarioSet->getTot();
	}

	/* MÉTODOS PROTECTED --------------------------------------------------------*/

	/* MÉTODOS GET y SET --------------------------------------------------------*/
}
