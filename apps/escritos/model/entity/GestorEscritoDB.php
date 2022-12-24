<?php

namespace escritos\model\entity;

use core;
use core\ConfigGlobal;
use escritos\model\Escrito;
use Exception;
use PDO;
use function core\any_2;

/**
 * GestorEscritoDB
 *
 * Classe per gestionar la llista d'objectes de la clase EscritoDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class GestorEscritoDB extends core\ClaseGestor
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /* CONSTRUCTOR -------------------------------------------------------------- */


    /**
     * Constructor de la classe.
     *
     */
    function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('escritos');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * Devuelve la colección de escritos, segun las condiciones del protcolo de referencias, más las normales
     *
     * @param array $aProt_ref = ['id_lugar' => xx, 'num' => xx, 'any' => xx, 'mas' => xx]
     * @param array $aWhere
     * @param array $aOperators
     * @return boolean|array
     */
    function getEscritosByRefDB($aProt_ref = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();

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

        // Where del prot_ref
        $json = '';
        if (!empty($aProt_ref['id_lugar'])) {
            $id_lugar = $aProt_ref['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";

        }
        if (!empty($aProt_ref['num'])) {
            $num = $aProt_ref['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_ref['any'])) {
            $any = $aProt_ref['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_ref['mas'])) {
            $mas = $aProt_ref['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }

        if (!empty($json)) {
            $Where_json = "json_prot_ref @> '[{" . $json . "}]'";
        }

        if (empty($sCondi)) {
            if (empty($json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

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

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre $sLimit ";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new Escrito($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    /**
     * Devuelve la colección de escritos, segun las condiciones del protcolo local, más las normales
     *
     * @param array $aProt_local = ['id_lugar', 'num', 'any', 'mas']
     * @param array $aWhere
     * @param array $aOperators
     * @return boolean|array
     */
    function getEscritosByProtLocalDB($aProt_local = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
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

        // Where del prot_destino
        // pongo tipo 'text' en todos los campos, porque si hay algun null devuelve error syntax
        $Where_json = '';
        $json = '';
        if (!empty($aProt_local['id_lugar'])) {
            $id_lugar = $aProt_local['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_local['num'])) {
            $num = $aProt_local['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_local['any'])) {
            $any = $aProt_local['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_local['mas'])) {
            $mas = $aProt_local['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }
        if (!empty($json)) {
            $Where_json = "json_prot_local @> '{" . $json . "}'";
        }
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

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

        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre $sLimit";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new Escrito($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    /**
     * Devuelve la colección de escritos, segun las condiciones del protcolo de destino, más las normales
     *
     * @param array $aProt_destino = ['id_lugar', 'num', 'any', 'mas']
     * @param array $aWhere
     * @param array $aOperators
     * @return boolean|array
     */
    function getEscritosByProtDestinoDB($aProt_destino = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
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

        // Where del prot_destino
        $Where_json = '';
        $json = '';
        if (!empty($aProt_destino['id_lugar'])) {
            $id_lugar = $aProt_destino['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_destino['num'])) {
            $num = $aProt_destino['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_destino['any'])) {
            $any = $aProt_destino['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_destino['mas'])) {
            $mas = $aProt_destino['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }

        if (!empty($json)) {
            $Where_json = "json_prot_ref @> '[{" . $json . "}]'";
        }

        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

        $sOrdre = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new Escrito($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    function getEscritosByLugarDeGrupo($id_lugar, $aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();

        $oCondicion = new core\Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
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
        if (empty($sCondi)) {
            $sCondi = " WHERE '$id_lugar' = ANY(destinos) ";
        } else {
            $sCondi = " WHERE '$id_lugar' = ANY(destinos) AND " . $sCondi;
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

        // pongo tipo 'text' en todos los campos del json, porque si hay algun null devuelve error syntax
        $sQry = "SELECT * FROM $nom_tabla $sCondi $sOrdre $sLimit ";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new Escrito($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();

    }


    function getEscritosByLugarDB($id_lugar, $aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
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

        if (empty($sCondi)) {
            $sCondi = " WHERE json_prot_destino @> '[{\"id_lugar\":$id_lugar}]'";
        } else {
            $sCondi = " WHERE json_prot_destino @> '[{\"id_lugar\":$id_lugar}]' AND " . $sCondi;
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
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new Escrito($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    function getEscritosNumerados($aWhere = array(), $aOperators = array())
    {
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
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

        if (empty($sCondi)) {
            $sCondi = " WHERE NOT (json_prot_local @> '{\"num\":0}')";
        } else {
            $sCondi = " WHERE NOT (json_prot_local @> '{\"num\":0}') AND " . $sCondi;
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

        // Se usa la utilidad CURSOR del Postgresql para evitar colapsar la memoria del servidor
        // cuando se busca un número muy grande de registros (más de 20.000)
        foreach ($this->fetchCursor($sQry, $aWhere) as $row) {
            $oEscritoDB = new Escrito($row['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);

        }

        return $oEscritoDBSet->getTot();
    }

    private function fetchCursor($sql, $aWhere, $idCol = false)
    {
        $pdo = $this->getoDbl();
        /*
         nextCursorId() is an undefined function, but
         the objective of it is to create a unique Id for each cursor.
         */
        try {
            $cursorID = 'cursor_' . ConfigGlobal::mi_id_usuario();
            $pdo->beginTransaction();
            //$stm0 = $pdo->exec("DECLARE $cursorID CURSOR FOR $sql ");
            $stm0 = $pdo->prepare("DECLARE $cursorID CURSOR FOR $sql ");
            $stm0->execute($aWhere);

            $stm = $pdo->prepare("FETCH NEXT FROM $cursorID");
            $stm->execute();
            if ($stm) {
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    if (is_string($idCol) && array_key_exists($idCol, $row)) {
                        yield $row[$idCol] => $row;
                    } else {
                        yield $row;
                    }
                    $stm->execute();
                }
            }
        } catch (Exception $e) {
            // Anything you want [*Parece que no hace nada!!]
            echo _("Demasiados registros");
            echo sprintf(_("Excepción capturada: %s"), $e->getMessage());
            echo "\n";
        } finally {
            /*
             Do some clean up after the loop is done.
             This is in a "finally" block because if you break the parent loop, it still gets called.
             */
            $pdo->exec("CLOSE $cursorID");
            $pdo->commit();
            return;
        }
    }

    function getEscritosByLocal($id_lugar, $aWhere = array(), $aOperators = array())
    {
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
        $oCondicion = new core\Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
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

        if (empty($sCondi)) {
            $sCondi = " WHERE json_prot_local @> '{\"id_lugar\":$id_lugar}'";
        } else {
            $sCondi = " WHERE json_prot_local @> '{\"id_lugar\":$id_lugar}' AND " . $sCondi;
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

        // Se usa la utilidad CURSOR del Postgresql para evitar colapsar la memoria del servidor
        // cuando se busca un número muy grande de registros (más de 20.000)
        foreach ($this->fetchCursor($sQry, $aWhere) as $row) {
            $oEscritoDB = new Escrito($row['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);

        }

        return $oEscritoDBSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus EscritoDB
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus EscritoDB
     */
    function getEscritosDBQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oEscritoDBSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEscritoDB.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $oEscritoDB = new EscritoDB($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    /**
     * retorna l'array d'objectes de tipus EscritoDB
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus EscritoDB
     */
    function getEscritosDB($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new core\Set();
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
            $sClauError = 'GestorEscritoDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEscritoDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEscritoDB = new EscritoDB($aDades['id_escrito']);
            $oEscritoDBSet->add($oEscritoDB);
        }
        return $oEscritoDBSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
