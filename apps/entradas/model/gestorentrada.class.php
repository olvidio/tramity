<?php
namespace entradas\model;

use core\Condicion;
use core\Set;
use entradas\model\entity\GestorEntradaDB;


class GestorEntrada Extends GestorEntradaDB {
    

    /**
     * retorna l'array d'objectes de tipus EntradaDB
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus EntradaDB
     */
    function getEntradas($aWhere=array(),$aOperators=array()) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') continue;
            if ($camp == '_limit') continue;
            if ($camp == 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";
                
                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) $aCondi[]=$a;
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') unset($aWhere[$camp]);
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') unset($aWhere[$camp]);
            if ($sOperador == 'TXT') unset($aWhere[$camp]);
        }
        $sCondi = implode(' AND ',$aCondi);
        if ($sCondi != '') $sCondi = " WHERE ".$sCondi;
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND ".$COND_OR;
            } else {
                $sCondi .= " WHERE ".$COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
        if (isset($aWhere['_ordre'])) unset($aWhere['_ordre']);
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') $sLimit = ' LIMIT '.$aWhere['_limit'];
        if (isset($aWhere['_limit'])) unset($aWhere['_limit']);
        $sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
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
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
    }
    
    
}