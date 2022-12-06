<?php

namespace usuarios\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use PDO;
use PDOException;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepositoryInterface;
use usuarios\domain\repositories\UsuarioRepository;
use web\Desplegable;
use function core\is_true;


/**
 * Clase que adapta la tabla aux_cargos a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class PgCargoRepository extends ClaseRepository implements CargoRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_cargos');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Cargo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Cargo
     */
    public function getCargos(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $CargoSet = new Set();
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
            $sClaveError = 'PgCargoRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgCargoRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $Cargo = new Cargo();
            $Cargo->setAllAttributes($aDatos);
            $CargoSet->add($Cargo);
        }
        return $CargoSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Cargo $Cargo): bool
    {
        $id_cargo = $Cargo->getId_cargo();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_cargo = $id_cargo")) === FALSE) {
            $sClaveError = 'Cargo.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Cargo $Cargo): bool
    {
        $id_cargo = $Cargo->getId_cargo();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_cargo);

        $aDatos = [];
        $aDatos['id_ambito'] = $Cargo->getId_ambito();
        $aDatos['cargo'] = $Cargo->getCargo();
        $aDatos['descripcion'] = $Cargo->getDescripcion();
        $aDatos['id_oficina'] = $Cargo->getId_oficina();
        $aDatos['director'] = $Cargo->isDirector();
        $aDatos['id_usuario'] = $Cargo->getId_usuario();
        $aDatos['id_suplente'] = $Cargo->getId_suplente();
        $aDatos['sacd'] = $Cargo->isSacd();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['director'])) {
            $aDatos['director'] = 'true';
        } else {
            $aDatos['director'] = 'false';
        }
        if (is_true($aDatos['sacd'])) {
            $aDatos['sacd'] = 'true';
        } else {
            $aDatos['sacd'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_ambito                = :id_ambito,
					cargo                    = :cargo,
					descripcion              = :descripcion,
					id_oficina               = :id_oficina,
					director                 = :director,
					id_usuario               = :id_usuario,
					id_suplente              = :id_suplente,
					sacd                     = :sacd";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_cargo = $id_cargo")) === FALSE) {
                $sClaveError = 'Cargo.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Cargo.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_cargo'] = $Cargo->getId_cargo();
            $campos = "(id_cargo,id_ambito,cargo,descripcion,id_oficina,director,id_usuario,id_suplente,sacd)";
            $valores = "(:id_cargo,:id_ambito,:cargo,:descripcion,:id_oficina,:director,:id_usuario,:id_suplente,:sacd)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Cargo.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Cargo.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(bool $id_cargo): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_cargo = $id_cargo")) === FALSE) {
            $sClaveError = 'Cargo.isNew';
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
    public function datosById(int $id_cargo): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_cargo = $id_cargo")) === FALSE) {
            $sClaveError = 'Cargo.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }


    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function findById(int $id_cargo): ?Cargo
    {
        $aDatos = $this->datosById($id_cargo);
        if (empty($aDatos)) {
            return null;
        }
        return (new Cargo())->setAllAttributes($aDatos);
    }

    public function getNewId_cargo()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('aux_cargos_id_cargo_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    /**
     * @param int $id_oficina
     * @return false|integer $id_cargo del director de la oficina
     */
    public function getDirectorOficina(int $id_oficina): bool|int
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                WHERE id_oficina=$id_oficina AND director = 't'
                ";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
        }
        return $clave;
    }

    function getArrayUsuariosOficina($id_oficina = '', $sin_cargo = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $Where = "WHERE id_oficina = $id_oficina";
        } else {
            $Where = "WHERE id_oficina > 0";
            if (!empty($id_oficina)) {
                $Where .= " AND id_oficina = $id_oficina";
            }
        }
        $sQuery = "SELECT id_cargo, id_usuario, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }

        $UsuarioRepository = new UsuarioRepository();
        $a_usuarios_oficina = [];
        foreach ($oDbl->query($sQuery) as $aClave) {
            $id_cargo = $aClave[0];
            $id_usuario = $aClave[1];
            $cargo = $aClave[2];
            if (empty($id_usuario)) {
                continue;
            } // el titular puede estar en blanco.
            $oUsuario = $UsuarioRepository->findById($id_usuario);
            $nom_usuario = $oUsuario->getNom_usuario();
            if ($sin_cargo) {
                $a_usuarios_oficina[$id_cargo] = "$nom_usuario";
            } else {
                $a_usuarios_oficina[$id_cargo] = "$nom_usuario ($cargo)";
            }
        }
        return $a_usuarios_oficina;
    }

    function getArrayCargosOficina($id_oficina = '')
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $Where = "WHERE id_oficina = " . Cargo::OFICINA_ESQUEMA;
        } else {
            $Where = "WHERE id_oficina > 0";
            if (!empty($id_oficina)) {
                $Where .= " AND id_oficina = $id_oficina";
            }
        }
        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
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
        return $aOpciones;
    }

    function getArrayCargosConUsuario($conOficina = TRUE)
    {
        $aOpciones = $this->getArrayCargos($conOficina);
        $UsuarioRepository = new UsuarioRepository();
        $a_cargos_usuario = [];
        foreach ($aOpciones as $id_cargo => $sigla) {
            // buscar el usuario para cada cargo
            $oCargo = new Cargo($id_cargo);
            $id_suplente = $oCargo->getId_suplente();
            $id_usuario = $oCargo->getId_usuario();
            $id_nom = empty($id_suplente) ? $id_usuario : $id_suplente;
            $oUsuario = $UsuarioRepository->findById($id_nom);
            $nom_usuario = $oUsuario->getNom_usuario();
            $a_cargos_usuario[$id_cargo] = "$sigla($nom_usuario)";
        }
        return $a_cargos_usuario;
    }

    /**
     * @param bool $conOficina
     * @return array|false  [id_cargo => cargo]
     */
    public function getArrayCargos(bool $conOficina = TRUE): array|false
    {
        $id_ambito = $_SESSION['oConfig']->getAmbito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where = "WHERE id_ambito = $id_ambito";
        if ($conOficina) {
            if ($id_ambito == Cargo::AMBITO_CTR) {
                $Where .= " AND id_oficina = " . Cargo::OFICINA_ESQUEMA;
            } else {
                $Where .= " AND id_oficina > 0";
            }
        }
        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY director DESC, cargo";
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
        return $aOpciones;
    }

    function getArrayCargosRef()
    {
        $id_ambito = $_SESSION['oConfig']->getAmbito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where = "WHERE id_ambito = $id_ambito AND (id_oficina = 0 OR id_oficina IS NULL)";
        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY director DESC, cargo";
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
        return $aOpciones;
    }

    function getDesplCargosUsuario($id_usuario)
    {
        $id_ambito = $_SESSION['oConfig']->getAmbito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where = '';
        if (!empty($id_ambito)) {
            $Where = "WHERE id_ambito = $id_ambito
                         AND (id_usuario = $id_usuario OR id_suplente = $id_usuario)";
        }
        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
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
        return new Desplegable('', $aOpciones, '', true);
    }

    function getDesplCargos($id_oficina = '', $bdirector = FALSE)
    {
        $id_ambito = $_SESSION['oConfig']->getAmbito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where = '';
        if (!empty($id_ambito)) {
            $Where = "WHERE id_ambito = $id_ambito";
            if (!empty($id_oficina)) {
                if ($id_oficina == 'x') {
                    $Where .= " AND (id_oficina IS NOT NULL AND id_oficina != 0)";
                } else {
                    $Where .= " AND id_oficina = $id_oficina";
                }
            }
        } else {
            if (!empty($id_oficina)) {
                if ($id_oficina == 'x') {
                    $Where .= "WHERE (id_oficina IS NOT NULL AND id_oficina != 0)";
                } else {
                    $Where .= "WHERE id_oficina = $id_oficina";
                }
            }

        }
        if ($bdirector) {
            $Where .= " AND director = 't'";
        }
        $sQuery = "SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
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
        return new Desplegable('', $aOpciones, '', true);
    }

}