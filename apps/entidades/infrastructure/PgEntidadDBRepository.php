<?php

namespace entidades\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use entidades\domain\entity\EntidadDB;
use entidades\domain\repositories\EntidadDBRepositoryInterface;
use PDO;
use PDOException;
use function core\is_true;


/**
 * Clase que adapta la tabla entidades a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class PgEntidadDBRepository extends ClaseRepository implements EntidadDBRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('entidades');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntidadDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntidadDB
     */
    public function getEntidadesDB(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntidadDBSet = new Set();
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
            $sClaveError = 'PgEntidadDBRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEntidadDBRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $EntidadDB = new EntidadDB();
            $EntidadDB->setAllAttributes($aDatos);
            $EntidadDBSet->add($EntidadDB);
        }
        return $EntidadDBSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntidadDB $EntidadDB): bool
    {
        $id_entidad = $EntidadDB->getId_entidad();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entidad = $id_entidad")) === FALSE) {
            $sClaveError = 'PgEntidadDBRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(EntidadDB $EntidadDB): bool
    {
        $id_entidad = $EntidadDB->getId_entidad();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_entidad);

        $aDatos = [];
        $aDatos['nombre'] = $EntidadDB->getNombre();
        $aDatos['schema'] = $EntidadDB->getSchema();
        $aDatos['tipo'] = $EntidadDB->getTipo();
        $aDatos['anulado'] = $EntidadDB->isAnulado();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['anulado'])) {
            $aDatos['anulado'] = 'true';
        } else {
            $aDatos['anulado'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nombre                   = :nombre,
					schema                   = :schema,
					tipo                     = :tipo,
					anulado                  = :anulado";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entidad = $id_entidad")) === FALSE) {
                $sClaveError = 'PgEntidadDBRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntidadDBRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_entidad'] = $EntidadDB->getId_entidad();
            $campos = "(id_entidad,nombre,schema,tipo,anulado)";
            $valores = "(:id_entidad,:nombre,:schema,:tipo,:anulado)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEntidadDBRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntidadDBRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_entidad): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entidad = $id_entidad")) === FALSE) {
            $sClaveError = 'PgEntidadDBRepository.isNew';
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
     * @param int $id_entidad
     * @return array|bool
     */
    public function datosById(int $id_entidad): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entidad = $id_entidad")) === FALSE) {
            $sClaveError = 'PgEntidadDBRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }


    /**
     * Busca la clase con id_entidad en la base de datos .
     */
    public function findById(int $id_entidad): ?EntidadDB
    {
        $aDatos = $this->datosById($id_entidad);
        if (empty($aDatos)) {
            return null;
        }
        return (new EntidadDB())->setAllAttributes($aDatos);
    }

    public function getNewId_entidad()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('entidades_id_entidad_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }
}