<?php

namespace documentos\model\entity;

use core;
use documentos\model\Documento;

/**
 * GestorDocumentoDB
 *
 * Classe per gestionar la llista d'objectes de la clase Documento
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/6/2021
 */
class GestorDocumentoDB extends core\ClaseGestor
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
        $this->setNomTabla('documentos');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * retorna l'array d'objectes de tipus Documento
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus Documento
     */
    function getDocumentosDBQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oDocumentoSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorDocumentoDB.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_doc' => $aDades['id_doc']);
            $oDocumentoDB = new DocumentoDB($a_pkey);
            $oDocumentoSet->add($oDocumentoDB);
        }
        return $oDocumentoSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus Documento
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Documento
     */
    function getDocumentosDB($aWhere = array(), $aOperators = array(), $parent = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oDocumentoSet = new core\Set();
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
            $sClauError = 'GestorDocumentoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorDocumentoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_doc' => $aDades['id_doc']);
            if ($parent) {
                $oDocumentoDB = new Documento($a_pkey);
            } else {
                $oDocumentoDB = new DocumentoDB($a_pkey);
            }
            $oDocumentoSet->add($oDocumentoDB);
        }
        return $oDocumentoSet->getTot();
    }

    /* METODES PROTECTED --------------------------------------------------------*/

    /* METODES GET i SET --------------------------------------------------------*/
}
