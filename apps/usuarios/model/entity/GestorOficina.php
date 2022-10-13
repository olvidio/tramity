<?php

namespace usuarios\model\entity;

use core;
use core\ConfigGlobal;
use web\Desplegable;

/**
 * GestorOficina
 *
 * Classe per gestionar la llista d'objectes de la clase Oficina
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */
class GestorOficina extends core\ClaseGestor
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
        $this->setNomTabla('x_oficinas');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * retorna un array
     * Las posibles oficinas
     *
     * @return array
     */
    function getArrayOficinas()
    {
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $clave = Cargo::OFICINA_ESQUEMA;
            $val = ConfigGlobal::getEsquema();
            $aOpciones[$clave] = $val;
        } else {
            $oDbl = $this->getoDbl();
            $nom_tabla = $this->getNomTabla();

            $sQuery = "SELECT id_oficina, sigla FROM $nom_tabla
                 ORDER BY orden";
            if (($oDbl->query($sQuery)) === false) {
                $sClauError = 'GestorAsignaturaTipo.lista';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return false;
            }
            $aOpciones = array();
            foreach ($oDbl->query($sQuery) as $aClave) {
                $clave = $aClave[0];
                $val = $aClave[1];
                $aOpciones[$clave] = $val;
            }
        }
        return $aOpciones;
    }


    /**
     * retorna un objecte del tipus Desplegable
     * Las posibles oficinas
     *
     * @return Desplegable
     */
    function getListaOficinas()
    {
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $clave = Cargo::OFICINA_ESQUEMA;
            $val = ConfigGlobal::getEsquema();
            $aOpciones[$clave] = $val;
        } else {
            $oDbl = $this->getoDbl();
            $nom_tabla = $this->getNomTabla();

            $sQuery = "SELECT id_oficina, sigla FROM $nom_tabla
                     ORDER BY orden";
            if (($oDbl->query($sQuery)) === false) {
                $sClauError = 'GestorAsignaturaTipo.lista';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return false;
            }
            $aOpciones = array();
            foreach ($oDbl->query($sQuery) as $aClave) {
                $clave = $aClave[0];
                $val = $aClave[1];
                $aOpciones[$clave] = $val;
            }
        }
        return new Desplegable('', $aOpciones, '', true);
    }

    /**
     * retorna l'array d'objectes de tipus Oficina
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus Oficina
     */
    function getOficinasQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oOficinaSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorOficina.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_oficina' => $aDades['id_oficina']);
            $oOficina = new Oficina($a_pkey);
            $oOficinaSet->add($oOficina);
        }
        return $oOficinaSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus Oficina
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Oficina
     */
    function getOficinas($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oOficinaSet = new core\Set();
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
            $sClauError = 'GestorOficina.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorOficina.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_oficina' => $aDades['id_oficina']);
            $oOficina = new Oficina($a_pkey);
            $oOficinaSet->add($oOficina);
        }
        return $oOficinaSet->getTot();
    }

    /* METODES PROTECTED --------------------------------------------------------*/

    /* METODES GET i SET --------------------------------------------------------*/
}
