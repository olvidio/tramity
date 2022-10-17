<?php

namespace entradas\model;

use core\Condicion;
use core\Set;
use entradas\model\entity\GestorEntradaDB;
use usuarios\model\Categoria;


class GestorEntrada extends GestorEntradaDB
{

    /**
     * Anula las entradas individuales en cada nombre_entidad para una entrada compartida
     *
     * @param integer $id_entrada_compartida
     * @param string $anular_txt
     * @param array $aEntidades nombre del esquema de la DB
     * @return boolean
     */
    public function anularCompartidas($id_entrada_compartida, $anular_txt, $aEntidades)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // Quitar esquema al $nom_tabla
        preg_replace('/(\w+)\.(\w+)/i', '$2', $nom_tabla);
        $categoria = Categoria::CAT_NORMAL;

        foreach ($aEntidades as $schema) {
            $nom_tabla_entidad = '"' . $schema . '".' . $nom_tabla;
            $sQry = "UPDATE $nom_tabla_entidad SET anulado = '$anular_txt', categoria = $categoria
					WHERE id_entrada_compartida = $id_entrada_compartida";

            if (($oDbl->query($sQry)) === FALSE) {
                $sClauError = 'GestorEntradaDB.llistar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * retorna l'array d'objectes de tipus EntradaDB
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Entrada
     */
    function getEntradas($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp == 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = isset($aOperators[$camp]) ? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
            }
            // operadores que no requieren valores
            if ($sOperador === 'BETWEEN' || $sOperador === 'IS NULL' || $sOperador === 'IS NOT NULL' || $sOperador === 'OR') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'IN' || $sOperador === 'NOT IN') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'TXT') {
                unset($aWhere[$camp]);
            }
        }
        $sCondi = implode(' AND ', $aCondi);
        if ($sCondi != '') {
            $sCondi = " WHERE " . $sCondi;
        }
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND " . $COND_OR;
            } else {
                $sCondi .= " WHERE " . $COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        $sQry = "SELECT * FROM $nom_tabla " . $sCondi . $sOrdre . $sLimit;
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