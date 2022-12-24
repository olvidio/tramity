<?php

namespace expedientes\model\entity;

use core;
use expedientes\model\Expediente;

/**
 * GestorExpedienteDB
 *
 * Classe per gestionar la llista d'objectes de la clase ExpedienteDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class GestorExpedienteDB extends core\ClaseGestor
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
        $this->setNomTabla('expedientes');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * Devuelve un array con el id_expediente que tengan un antecedente determinado.
     *
     * @param integer $id
     * @param string $tipo (entrada|expediente|escrito|documento)
     * @return array de id_expedientes
     */
    public function getIdExpedientesConAntecedente($id, $tipo)
    {
        $oDbl = $this->getoDbl();

        $json = "\"id\": $id";
        $json .= ", \"tipo\": \"$tipo\"";
        $Where_json = "json_antecedentes @> '[{" . $json . "}]'";

        $sQuery = "SELECT e.id_expediente
                    FROM expedientes e 
                    WHERE $Where_json ";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorExpedienteDB.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_expedientes = [];
        foreach ($oDbl->query($sQuery) as $aDades) {
            $id_expediente = $aDades['id_expediente'];
            $a_expedientes[] = $id_expediente;
        }
        return $a_expedientes;

    }

    /**
     * Devuelve un array con los id de los expedientes que están marcados
     * para que los vea el id_cargo (o no los que ya ha visto si es TRUE).
     *
     * @param integer $id_cargo
     * @param string $visto ['visto'|'no_visto'|'']
     * @return array de id_expedientes
     */
    public function getIdExpedientesPreparar($id_cargo, $visto = 'no_visto')
    {
        $oDbl = $this->getoDbl();

        $json = "\"id\": $id_cargo";
        switch ($visto) {
            case 'visto':
                $json .= empty($json) ? '' : ',';
                $json .= "\"visto\": true";
                break;
            case 'no_visto':
                $json .= empty($json) ? '' : ',';
                $json .= "\"visto\": false";
                break;
            case 'todos':
            default:
                // No añado nada.
                //$json .= "";
        }
        $Where_json = "json_preparar @> '[{" . $json . "}]'";

        $sQuery = "SELECT e.id_expediente
                    FROM expedientes e WHERE $Where_json ";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorExpedienteDB.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_expedientes = [];
        foreach ($oDbl->query($sQuery) as $aDades) {
            $id_expediente = $aDades['id_expediente'];
            $a_expedientes[] = $id_expediente;
        }
        return $a_expedientes;
    }

    /**
     * retorna l'array d'objectes de tipus ExpedienteDB
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus ExpedienteDB
     */
    function getExpedientesDBQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oExpedienteDBSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorExpedienteDB.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $oExpedienteDB = new ExpedienteDB($aDades['id_expediente']);
            $oExpedienteDBSet->add($oExpedienteDB);
        }
        return $oExpedienteDBSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus ExpedienteDB
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus ExpedienteDB
     */
    function getExpedientesDB($aWhere = array(), $aOperators = array(), $parent = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oExpedienteDBSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
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
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorExpedienteDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorExpedienteDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            if ($parent) {
                $oExpedienteDB = new Expediente($aDades['id_expediente']);
            } else {
                $oExpedienteDB = new ExpedienteDB($aDades['id_expediente']);
            }
            $oExpedienteDBSet->add($oExpedienteDB);
        }
        return $oExpedienteDBSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
