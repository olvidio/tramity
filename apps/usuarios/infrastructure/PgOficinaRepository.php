<?php

namespace usuarios\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConfigGlobal;
use core\Set;
use PDO;
use PDOException;
use usuarios\domain\entity\Cargo;
use usuarios\domain\entity\Oficina;
use usuarios\domain\repositories\OficinaRepositoryInterface;
use web\Desplegable;


/**
 * Clase que adapta la tabla x_oficinas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class PgOficinaRepository extends ClaseRepository implements OficinaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_oficinas');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Oficina
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Oficina
     */
    public function getOficinas(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $OficinaSet = new Set();
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
            $sClaveError = 'PgOficinaRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgOficinaRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $Oficina = new Oficina();
            $Oficina->setAllAttributes($aDatos);
            $OficinaSet->add($Oficina);
        }
        return $OficinaSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Oficina $Oficina): bool
    {
        $id_oficina = $Oficina->getId_oficina();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_oficina = $id_oficina")) === FALSE) {
            $sClaveError = 'Oficina.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Oficina $Oficina): bool
    {
        $id_oficina = $Oficina->getId_oficina();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_oficina);

        $aDatos = [];
        $aDatos['sigla'] = $Oficina->getSigla();
        $aDatos['orden'] = $Oficina->getOrden();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					sigla                    = :sigla,
					orden                    = :orden";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_oficina = $id_oficina")) === FALSE) {
                $sClaveError = 'Oficina.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Oficina.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_oficina'] = $Oficina->getId_oficina();
            $campos = "(id_oficina,sigla,orden)";
            $valores = "(:id_oficina,:sigla,:orden)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Oficina.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Oficina.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(?int $id_oficina): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_oficina = $id_oficina")) === FALSE) {
            $sClaveError = 'Oficina.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_oficina): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_oficina = $id_oficina")) === FALSE) {
            $sClaveError = 'Oficina.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }


    /**
     * Busca la clase con id_oficina en la base de datos .
     */
    public function findById(int $id_oficina): ?Oficina
    {
        $aDatos = $this->datosById($id_oficina);
        if (empty($aDatos)) {
            return null;
        }
        return (new Oficina())->setAllAttributes($aDatos);
    }

    public function getNewId_oficina()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('x_oficinas_id_oficina_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    /**
     * @return array|false
     */
    public function getArrayOficinas(): array|false
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $clave = Cargo::OFICINA_ESQUEMA;
            $val = ConfigGlobal::nombreEntidad();
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
     * @return Desplegable|false
     */
    public function getListaOficinas(): Desplegable|false
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $clave = Cargo::OFICINA_ESQUEMA;
            $val = ConfigGlobal::nombreEntidad();
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

}