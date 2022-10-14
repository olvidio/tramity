<?php

namespace etiquetas\model\entity;

use core;

/**
 * GestorEtiquetaEntrada
 *
 * Classe per gestionar la llista d'objectes de la clase EtiquetaEntrada
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 27/4/2022
 */
class GestorEtiquetaEntrada extends core\ClaseGestor
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /* CONSTRUCTOR -------------------------------------------------------------- */


    /**
     * Constructor de la classe.
     *
     * @return $gestor
     *
     */
    function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas_entrada');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    public function getArrayEntradas($a_etiquetas, $andOr = 'OR')
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // Filtering the array
        $a_etiquetas_filtered = array_filter($a_etiquetas);
        if (!empty($a_etiquetas_filtered)) {
            if ($andOr == 'AND') {
                $sQuery = '';
                foreach ($a_etiquetas_filtered as $etiqueta) {
                    $sql = "SELECT DISTINCT id_entrada
                        FROM $nom_tabla
                        WHERE id_etiqueta = $etiqueta";
                    $sQuery .= empty($sQuery) ? $sql : " INTERSECT $sql";
                }

            } else {
                $valor = implode(',', $a_etiquetas_filtered);
                $where = " id_etiqueta IN ($valor)";
                $sQuery = "SELECT DISTINCT id_entrada
                        FROM $nom_tabla
                        WHERE $where ";
            }

            if (($oDbl->query($sQuery)) === FALSE) {
                $sClauError = 'GestorEtiquetaEntrada.queryPreparar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $a_entradas = [];
            foreach ($oDbl->query($sQuery) as $aDades) {
                $a_entradas[] = $aDades['id_entrada'];
            }
        } else {
            $a_entradas = [];
        }
        return $a_entradas;

    }

    public function deleteEtiquetasEntrada($id_entrada)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $sQuery = "DELETE
                    FROM $nom_tabla
                    WHERE id_entrada=$id_entrada";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEtiquetaEntrada.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        return TRUE;
    }

    /**
     * retorna l'array d'objectes de tipus EtiquetaEntrada
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus EtiquetaEntrada
     */
    function getEtiquetasEntradaQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oEtiquetaEntradaSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEtiquetaEntrada.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_etiqueta' => $aDades['id_etiqueta'],
                'id_entrada' => $aDades['id_entrada']);
            $oEtiquetaEntrada = new EtiquetaEntrada($a_pkey);
            $oEtiquetaEntradaSet->add($oEtiquetaEntrada);
        }
        return $oEtiquetaEntradaSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus EtiquetaEntrada
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus EtiquetaEntrada
     */
    function getEtiquetasEntrada($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEtiquetaEntradaSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') {
                continue;
            }
            if ($camp == '_limit') {
                continue;
            }
            $sOperador = isset($aOperators[$camp]) ? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
            }
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') {
                unset($aWhere[$camp]);
            }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') {
                unset($aWhere[$camp]);
            }
            if ($sOperador == 'TXT') {
                unset($aWhere[$camp]);
            }
        }
        $sCondi = implode(' AND ', $aCondi);
        if ($sCondi != '') {
            $sCondi = " WHERE " . $sCondi;
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] != '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] != '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        $sQry = "SELECT * FROM $nom_tabla " . $sCondi . $sOrdre . $sLimit;
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEtiquetaEntrada.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEtiquetaEntrada.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_etiqueta' => $aDades['id_etiqueta'],
                'id_entrada' => $aDades['id_entrada']);
            $oEtiquetaEntrada = new EtiquetaEntrada($a_pkey);
            $oEtiquetaEntradaSet->add($oEtiquetaEntrada);
        }
        return $oEtiquetaEntradaSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
