<?php

namespace etiquetas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use etiquetas\domain\entity\EtiquetaExpediente;
use etiquetas\domain\repositories\EtiquetaExpedienteRepositoryInterface;
use PDO;
use PDOException;


/**
 * Clase que adapta la tabla etiquetas_expediente a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class PgEtiquetaExpedienteRepository extends ClaseRepository implements EtiquetaExpedienteRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas_expediente');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaExpediente
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaExpediente
     */
    public function getEtiquetasExpediente(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EtiquetaExpedienteSet = new Set();
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
            $sClaveError = 'PgEtiquetaExpedienteRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEtiquetaExpedienteRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $EtiquetaExpediente = new EtiquetaExpediente();
            $EtiquetaExpediente->setAllAttributes($aDatos);
            $EtiquetaExpedienteSet->add($EtiquetaExpediente);
        }
        return $EtiquetaExpedienteSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaExpediente $EtiquetaExpediente): bool
    {
        $id_etiqueta = $EtiquetaExpediente->getId_etiqueta();
        $id_expediente = $EtiquetaExpediente->getId_expediente();

        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta AND id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'PgEtiquetaExpedienteRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(EtiquetaExpediente $EtiquetaExpediente): bool
    {

        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $id_etiqueta = $EtiquetaExpediente->getId_etiqueta();
        $id_expediente = $EtiquetaExpediente->getId_expediente();

        $bInsert = $this->isNew($id_etiqueta, $id_expediente);

        $aDatos = [];
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_etiqueta = $id_etiqueta AND id_expediente = $id_expediente")) === FALSE) {
                $sClaveError = 'PgEtiquetaExpedienteRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaExpedienteRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_etiqueta'] = $EtiquetaExpediente->getId_etiqueta();
            $aDatos['id_expediente'] = $EtiquetaExpediente->getId_expediente();
            $campos = "(id_etiqueta,id_expediente)";
            $valores = "(:id_etiqueta,:id_expediente)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEtiquetaExpedienteRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaExpedienteRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_etiqueta, int $id_expediente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta AND id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'PgEtiquetaExpedienteRepository.isNew';
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
     * @param int $id_expediente
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_expediente): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta AND id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'PgEtiquetaExpedienteRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }


    /**
     * Busca la clase con id_expediente en la base de datos .
     */
    public function findById(int $id_etiqueta, int $id_expediente): ?EtiquetaExpediente
    {
        $aDatos = $this->datosById($id_etiqueta, $id_expediente);
        if (empty($aDatos)) {
            return null;
        }
        return (new EtiquetaExpediente())->setAllAttributes($aDatos);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getArrayExpedientes(array $a_etiquetas, string $andOr = 'OR'): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // Filtering the array
        $a_etiquetas_filtered = array_filter($a_etiquetas);
        if (!empty($a_etiquetas_filtered)) {
            if ($andOr === 'AND') {
                $sQuery = '';
                foreach ($a_etiquetas_filtered as $etiqueta) {
                    $sql = "SELECT DISTINCT id_expediente
                        FROM $nom_tabla
                        WHERE id_etiqueta = $etiqueta";
                    $sQuery .= empty($sQuery) ? $sql : " INTERSECT $sql";
                }

            } else {
                $valor = implode(',', $a_etiquetas_filtered);
                $where = " id_etiqueta IN ($valor)";
                $sQuery = "SELECT DISTINCT id_expediente
                        FROM $nom_tabla
                        WHERE $where ";
            }

            if (($oDbl->query($sQuery)) === FALSE) {
                $sClauError = 'GestorEtiquetaExpediente.queryPreparar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $a_expedientes = [];
            foreach ($oDbl->query($sQuery) as $aDades) {
                $a_expedientes[] = $aDades['id_expediente'];
            }
        } else {
            $a_expedientes = [];
        }
        return $a_expedientes;

    }

    public function deleteEtiquetasExpediente(int $id_expediente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $sQuery = "DELETE
                    FROM $nom_tabla
                    WHERE id_expediente=$id_expediente";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorEtiquetaExpediente.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        return TRUE;
    }

}