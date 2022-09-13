<?php
namespace entradas\model\entity;
use core\Condicion;
use core\Set;
use core;
/**
 * GestorEntradaBypass
 *
 * Classe per gestionar la llista d'objectes de la clase EntradaBypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

class GestorEntradaBypass Extends core\ClaseGestor {
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
		$this->setNomTabla('entradas_bypass');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	
	function getEntradasBypassByDestino($id_lugar, $aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEntradaBypassSet = new Set();
		$oCondicion = new Condicion();
		$aCondi = array();
		$COND_OR = '';
		foreach ($aWhere as $camp => $val) {
			if ($camp == '_ordre') { continue; }
			if ($camp == '_limit') { continue; }
			if ($camp == 'asunto_detalle') {
				$valor = $aWhere[$camp];
				$COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
				$COND_OR .= " OR ";
				$COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";
				
				unset($aWhere[$camp]);
				continue;
			}
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
			// operadores que no requieren valores
			if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
			if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
			if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
		}
		$sCondi = implode(' AND ',$aCondi);
		// Buscar en prot_destino
		if (empty($sCondi)) {
			$sCondi1 = " WHERE json_prot_destino @> '{\"id_lugar\":$id_lugar}'";
		} else {
			$sCondi1 = " WHERE json_prot_destino @> '{\"id_lugar\":$id_lugar}' AND ".$sCondi;
		}
		if ($COND_OR != '') {
			if ($sCondi1 != '') {
				$sCondi1 .= " AND ".$COND_OR;
			} else {
				$sCondi1 .= " WHERE ".$COND_OR;
			}
		}
		$sQry1 = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) ".$sCondi1;
		// buscar en a_destinos
		if (empty($sCondi)) {
			$sCondi2 = " WHERE $id_lugar = ANY(destinos)";
		} else {
			$sCondi2 = " WHERE $id_lugar = ANY(destinos) AND ".$sCondi;
		}
		if ($COND_OR != '') {
			if ($sCondi2 != '') {
				$sCondi2 .= " AND ".$COND_OR;
			} else {
				$sCondi2 .= " WHERE ".$COND_OR;
			}
		}
		$sQry2 = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) ".$sCondi2;
		
		$sOrdre = '';
		$sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		
		$sQry = "$sQry1 UNION $sQry2 ".$sOrdre.$sLimit;
		
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorEntradaBypass.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEntradaBypass.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_entrada' => $aDades['id_entrada']);
			$oEntradaBypass = new EntradaBypass($a_pkey);
			$oEntradaBypassSet->add($oEntradaBypass);
		}
		return $oEntradaBypassSet->getTot();
	}
	
	
	/**
	 * retorna l'array d'objectes de tipus EntradaBypass
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus EntradaBypass
	 */
	function getEntradasBypassQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEntradaBypassSet = new Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorEntradaBypass.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_entrada' => $aDades['id_entrada']);
			$oEntradaBypass= new EntradaBypass($a_pkey);
			$oEntradaBypassSet->add($oEntradaBypass);
		}
		return $oEntradaBypassSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus EntradaBypass
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus EntradaBypass
	 */
	function getEntradasBypass($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEntradaBypassSet = new Set();
		$oCondicion = new Condicion();
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
		
		//$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		$sQry = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) ".$sCondi.$sOrdre.$sLimit;
		
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorEntradaBypass.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEntradaBypass.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_entrada' => $aDades['id_entrada']);
			$oEntradaBypass = new EntradaBypass($a_pkey);
			$oEntradaBypassSet->add($oEntradaBypass);
		}
		return $oEntradaBypassSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
