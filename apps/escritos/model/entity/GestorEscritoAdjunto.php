<?php

namespace escritos\model\entity;

use core;

/**
 * GestorEscritoAdjunto
 *
 * Classe per gestionar la llista d'objectes de la clase EscritoAdjunto
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 23/7/2020
 */
class GestorEscritoAdjunto extends core\ClaseGestor
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
        $this->setNomTabla('escrito_adjuntos');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    public function getArrayIdAdjuntos($id_escrito, $tipo_doc = '')
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $aAdjuntos = [];

        $whereTipo = empty($tipo_doc) ? '' : " AND tipo_doc = $tipo_doc ";
        $sQry = "SELECT * FROM $nom_tabla WHERE id_escrito = $id_escrito $whereTipo";
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEscritoAdjunto.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute()) === FALSE) {
            $sClauError = 'GestorEscritoAdjunto.llistar.execute';
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
     * retorna l'array d'objectes de tipus EscritoAdjunto
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus EscritoAdjunto
     */
    function getEscritoAdjuntosQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oEscritoAdjuntoSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEscritoAdjunto.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_item' => $aDades['id_item']);
            $oEscritoAdjunto = new EscritoAdjunto($a_pkey);
            $oEscritoAdjuntoSet->add($oEscritoAdjunto);
        }
        return $oEscritoAdjuntoSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus EscritoAdjunto
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus EscritoAdjunto
     */
    function getEscritoAdjuntos($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoAdjuntoSet = new core\Set();
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
            $sClauError = 'GestorEscritoAdjunto.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoAdjunto.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_item' => $aDades['id_item']);
            $oEscritoAdjunto = new EscritoAdjunto($a_pkey);
            $oEscritoAdjuntoSet->add($oEscritoAdjunto);
        }
        return $oEscritoAdjuntoSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
