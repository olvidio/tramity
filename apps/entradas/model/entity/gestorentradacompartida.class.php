<?php
namespace entradas\model\entity;
use core\ClaseGestor;
use core\Condicion;
use core\Set;
use function core\any_2;
use usuarios\model\Categoria;

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

class GestorEntradaCompartida Extends ClaseGestor {
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

	public function posiblesYear() {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		
		$sql_anys="SELECT json_prot_origen -> 'any' as a
                    FROM $nom_tabla
                    WHERE categoria = ". Categoria::CAT_PERMANATE ."
                    GROUP BY a ORDER BY a";
		
		if (($oDblSt=$oDbl->Query($sql_anys)) === FALSE) {
			$sClauError = 'GestorEntradaDB.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		$a_anys = [];
		foreach ($oDblSt as $a_year) {
			$year = trim($a_year['a'],'"') ;
			$iyear = intval($year);
			if ($iyear > 70) {
				$iany = 1900 + $iyear;
			} else {
				$iany = 2000 + $iyear;
			}
			
			$a_anys[] = $iany;
		}
		sort($a_anys);
		
		return $a_anys;
	}
	
	/**
	 * Devuelve la colección de entradas, segun las condiciones del protcolo de entrada, más las normales
	 * 
	 * @param array $aProt_origen = ['lugar' => xx, 'num' => xx, 'any' => xx, 'mas' => xx]
	 * @param array $aWhere
	 * @param array $aOperators
	 * @return boolean|array
	 */
	function getEntradasByProtOrigenDestino($aProt_origen=[], $aWhere=[], $aOperators=[]) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaCompartidaSet = new Set();
        
        /* {"any": 20, "mas": null, "num": 15, "lugar": 58}
        $sQuery = "SELECT t.*
                        FROM $nom_tabla t, jsonb_to_recordset(t.json_prot_origen) as items(any smallint, mas text, num smallint, lugar integer)
                        WHERE items.id=$id_lugar";
        */
        
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
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND ".$COND_OR;
            } else {
                $sCondi .= " WHERE ".$COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] != '' ) {
            $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
        } else {
            $sOrdre = " ORDER BY CASE WHEN anulado IS NULL THEN 1 WHEN anulado = '' THEN 1 ELSE 2 END , t.f_entrada DESC";
        }
        if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
        if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
        
        // Where del prot_origen
        $Where_json = '';
        if (!empty($aProt_origen['lugar'])) {
            $lugar = $aProt_origen['lugar'];
            $Where_json .= empty($Where_json)? '' : ' AND ';    
            $Where_json .= "items.lugar='$lugar'";
        }
        if (!empty($aProt_origen['num'])) {
            $num = $aProt_origen['num'];
            $Where_json .= empty($Where_json)? '' : ' AND ';    
            $Where_json .= "items.num='$num'";
        }
        if (!empty($aProt_origen['any'])) {
            $any = $aProt_origen['any'];
            $any_2 = any_2($any);
            $Where_json .= empty($Where_json)? '' : ' AND ';    
            $Where_json .= "items.any='$any_2'";
        }
        if (!empty($aProt_origen['mas'])) {
            $mas = $aProt_origen['mas'];
            $Where_json .= empty($Where_json)? '' : ' AND ';    
            $Where_json .= "items.mas='$mas'";
        }
        
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json. " AND ". $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi)? '' : "WHERE ".$where_condi.' '.$sOrdre;
        
        // pongo tipo 'text' en todos los campos del json, porque si hay algun null devuelve error syntax
        $sQry = "SELECT t.*
                        FROM $nom_tabla t, jsonb_to_record(t.json_prot_origen) as items(\"any\" text, mas text, num text, lugar text)
                        $where_condi";
        
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaCompartida = new EntradaCompartida($a_pkey);
            $oEntradaCompartida->setAllAtributes($aDades);
            $oEntradaCompartidaSet->add($oEntradaCompartida);
        }
        return $oEntradaCompartidaSet->getTot();
	}
	
	/**
	 * retorna l'array d'objectes de tipus EntradaCompartida
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus EntradaCompartida
	 */
	function getEntradasCompartidasQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEntradaCompartidaSet = new Set();
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
		$oEntradaCompartidaSet = new Set();
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
