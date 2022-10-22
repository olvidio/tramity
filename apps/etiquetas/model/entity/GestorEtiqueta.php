<?php

namespace etiquetas\model\entity;

use core;
use core\ConfigGlobal;
use usuarios\model\entity\Cargo;

/**
 * GestorEtiqueta
 *
 * Classe per gestionar la llista d'objectes de la clase Etiqueta
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 10/11/2020
 */
class GestorEtiqueta extends core\ClaseGestor
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
        $this->setNomTabla('etiquetas');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    public function getArrayMisEtiquetas($id_cargo = ''): array
    {
        $a_posibles_etiquetas = [];
        foreach ($this->getMisEtiquetas($id_cargo) as $oEtiqueta) {
            $id_etiqueta = $oEtiqueta->getId_etiqueta();
            $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
            $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
        }
        return $a_posibles_etiquetas;
    }

    public function getMisEtiquetas($id_cargo = '')
    {
        if (empty($id_cargo)) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $cEtiquetasPersonales = $this->getEtiquetasPersonales($id_cargo);
        $cEtiquetasOficina = $this->getEtiquetasMiOficina($id_cargo);

        return array_merge($cEtiquetasPersonales, $cEtiquetasOficina);
    }

    public function getEtiquetasPersonales($id_cargo = '')
    {
        if (empty($id_cargo)) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $aWhere = ['id_cargo' => $id_cargo,
            'oficina' => 'f',
            '_ordre' => 'nom_etiqueta',
        ];
        $aOperador = [];
        return $this->getEtiquetas($aWhere, $aOperador);
    }

    /**
     * retorna l'array d'objectes de tipus Etiqueta
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Etiqueta
     */
    function getEtiquetas($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEtiquetaSet = new core\Set();
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
            $sClauError = 'GestorEtiqueta.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEtiqueta.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_etiqueta' => $aDades['id_etiqueta']);
            $oEtiqueta = new Etiqueta($a_pkey);
            $oEtiquetaSet->add($oEtiqueta);
        }
        return $oEtiquetaSet->getTot();
    }

    public function getEtiquetasMiOficina($id_cargo = '')
    {
        if (empty($id_cargo)) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $oCargo = new Cargo($id_cargo);
            $id_oficina = $oCargo->getId_oficina();
        } else {
            $id_oficina = Cargo::OFICINA_ESQUEMA;
        }

        if (empty($id_oficina)) {
            return [];
        } else {
            $aWhere = ['id_cargo' => $id_oficina,
                'oficina' => 't',
                '_ordre' => 'nom_etiqueta',
            ];
            $aOperador = [];
            return $this->getEtiquetas($aWhere, $aOperador);
        }
    }

    /**
     * retorna l'array d'objectes de tipus Etiqueta
     *
     * @param string sQuery la query a executar.
     * @return array Una col·lecció d'objectes de tipus Etiqueta
     */
    function getEtiquetasQuery($sQuery = '')
    {
        $oDbl = $this->getoDbl();
        $oEtiquetaSet = new core\Set();
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEtiqueta.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQuery) as $aDades) {
            $a_pkey = array('id_etiqueta' => $aDades['id_etiqueta']);
            $oEtiqueta = new Etiqueta($a_pkey);
            $oEtiquetaSet->add($oEtiqueta);
        }
        return $oEtiquetaSet->getTot();
    }

    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
