<?php

namespace etiquetas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use etiquetas\domain\entity\EtiquetaEntrada;
use etiquetas\domain\repositories\EtiquetaEntradaRepositoryInterface;
use PDO;
use PDOException;


/**
 * Clase que adapta la tabla etiquetas_entrada a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class PgEtiquetaEntradaRepository extends ClaseRepository implements EtiquetaEntradaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas_entrada');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaEntrada
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaEntrada
     */
    public function getEtiquetasEntrada(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EtiquetaEntradaSet = new Set();
        $oCondicion = new Condicion();
        $aCondicion = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondicion[] = $a;
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
        $sCondicion = implode(' AND ', $aCondicion);
        if ($sCondicion !== '') {
            $sCondicion = " WHERE " . $sCondicion;
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
        $sQry = "SELECT * FROM $nom_tabla " . $sCondicion . $sOrdre . $sLimit;
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClaveError = 'PgEtiquetaEntradaRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEtiquetaEntradaRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $EtiquetaEntrada = new EtiquetaEntrada();
            $EtiquetaEntrada->setAllAttributes($aDatos);
            $EtiquetaEntradaSet->add($EtiquetaEntrada);
        }
        return $EtiquetaEntradaSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaEntrada $EtiquetaEntrada): bool
    {
        $id_etiqueta = $EtiquetaEntrada->getId_etiqueta();
        $id_entrada = $EtiquetaEntrada->getId_entrada();

        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta AND id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEtiquetaEntradaRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(EtiquetaEntrada $EtiquetaEntrada): bool
    {

        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $id_etiqueta = $EtiquetaEntrada->getId_etiqueta();
        $id_entrada = $EtiquetaEntrada->getId_entrada();

        $bInsert = $this->isNew($id_etiqueta, $id_entrada);

        $aDatos = [];
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_etiqueta = $id_etiqueta AND id_entrada = $id_entrada")) === FALSE) {
                $sClaveError = 'PgEtiquetaEntradaRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaEntradaRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_etiqueta'] = $EtiquetaEntrada->getId_etiqueta();
            $aDatos['id_entrada'] = $EtiquetaEntrada->getId_entrada();
            $campos = "(id_etiqueta,id_entrada)";
            $valores = "(:id_etiqueta,:id_entrada)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEtiquetaEntradaRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaEntradaRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew($id_etiqueta, $id_entrada): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE  id_etiqueta = $id_etiqueta AND id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEtiquetaEntradaRepository.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_etiqueta
     * @param int $id_entrada
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_entrada): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE  id_etiqueta = $id_etiqueta AND id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEtiquetaEntradaRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }


    /**
     * Busca la clase con id_entrada en la base de datos .
     */
    public function findById(int $id_etiqueta, int $id_entrada): ?EtiquetaEntrada
    {
        $aDatos = $this->datosById($id_etiqueta, $id_entrada);
        if (empty($aDatos)) {
            return null;
        }
        return (new EtiquetaEntrada())->setAllAttributes($aDatos);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getArrayEntradas(array $a_etiquetas, string $andOr = 'OR'): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // Filtering the array
        $a_etiquetas_filtered = array_filter($a_etiquetas);
        if (!empty($a_etiquetas_filtered)) {
            if ($andOr === 'AND') {
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

    public function deleteEtiquetasEntrada(int $id_entrada): bool
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

}