<?php
namespace usuarios\model\entity;

use core;
use web;

/**
 * GestorLocale
 *
 * Classe per gestionar la llista d'objectes de la clase Locale
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2014
 */
class GestorLocale extends core\ClaseGestor
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
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_locales');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * retorna un objecte del tipus Desplegable
     * Els posibles idiomas
     *
     * @param string sWhere condicion con el WHERE.
     * @return array Una Llista
     */
    function getListaIdiomas($sWhere = '')
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $sQuery = "SELECT DISTINCT idioma, nom_idioma
				FROM $nom_tabla $sWhere
				ORDER BY nom_idioma";
        if (($oDblSt = $oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLocale.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        return new web\Desplegable('', $oDblSt, '', true);
    }

    /**
     * retorna un objecte del tipus Desplegable
     * Els posibles locals
     *
     * @param string sWhere condicion con el WHERE.
     * @return array Una Llista
     */
    function getListaLocales($sWhere = '')
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (empty($sWhere)) $sWhere = "WHERE activo = 't'";
        $sQuery = "SELECT id_locale, nom_locale
				FROM $nom_tabla $sWhere
				ORDER BY nom_locale";
        if (($oDblSt = $oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLocale.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        return new web\Desplegable('', $oDblSt, '', true);
    }

    /**
     * retorna l'array d'objectes de tipus Locale
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus Locale
     */
    function getLocalesQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oLocaleSet = new core\Set();
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLocale.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_locale' => $aDades['id_locale']);
            $oLocale = new Locale($a_pkey);
            $oLocaleSet->add($oLocale);
        }
        return $oLocaleSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus Locale
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Locale
     */
    function getLocales($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oLocaleSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
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
        if (($oDblSt = $oDbl->prepare($sQry)) === false) {
            $sClauError = 'GestorLocale.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        if (($oDblSt->execute($aWhere)) === false) {
            $sClauError = 'GestorLocale.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return false;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_locale' => $aDades['id_locale']);
            $oLocale = new Locale($a_pkey);
            $oLocaleSet->add($oLocale);
        }
        return $oLocaleSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}

?>
