<?php

namespace usuarios\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use PDO;
use PDOException;
use usuarios\domain\entity\Usuario;
use usuarios\domain\repositories\UsuarioRepositoryInterface;
use web\Desplegable;


/**
 * Clase que adapta la tabla aux_usuarios a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class PgUsuarioRepository extends ClaseRepository implements UsuarioRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_usuarios');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Usuario
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Usuario
     */
    public function getUsuarios(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $UsuarioSet = new Set();
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
            $sClaveError = 'PgUsuarioRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgUsuarioRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {// para los bytea: (resources)
            $handle = $aDatos['password'];
            if ($handle !== null) {
                $contents = stream_get_contents($handle);
                fclose($handle);
                $password = $contents;
                $aDatos['password'] = $password;
            }
            $Usuario = new Usuario();
            $Usuario->setAllAttributes($aDatos);
            $UsuarioSet->add($Usuario);
        }
        return $UsuarioSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Usuario $Usuario): bool
    {
        $id_usuario = $Usuario->getId_usuario();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_usuario = $id_usuario")) === FALSE) {
            $sClaveError = 'Usuario.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Usuario $Usuario): bool
    {
        $id_usuario = $Usuario->getId_usuario();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_usuario);

        $aDatos = [];
        $aDatos['usuario'] = $Usuario->getUsuario();
        $aDatos['id_cargo_preferido'] = $Usuario->getId_cargo_preferido();
        $aDatos['password'] = $Usuario->getPassword();
        $aDatos['email'] = $Usuario->getEmail();
        $aDatos['nom_usuario'] = $Usuario->getNom_usuario();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					usuario                  = :usuario,
					id_cargo_preferido       = :id_cargo_preferido,
					password                 = :password,
					email                    = :email,
					nom_usuario              = :nom_usuario";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_usuario = $id_usuario")) === FALSE) {
                $sClaveError = 'Usuario.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Usuario.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_usuario'] = $Usuario->getId_usuario();
            $campos = "(id_usuario,usuario,id_cargo_preferido,password,email,nom_usuario)";
            $valores = "(:id_usuario,:usuario,:id_cargo_preferido,:password,:email,:nom_usuario)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Usuario.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Usuario.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(?string $id_usuario): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario = $id_usuario")) === FALSE) {
            $sClaveError = 'Usuario.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Busca la clase con $id_usuario en la base de datos.
     */
    public function findById(int $id_usuario): ?Usuario
    {
        $aDatos = $this->datosById($id_usuario);
        if (empty($aDatos)) {
            return null;
        }
        return (new Usuario())->setAllAttributes($aDatos);
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_usuario): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario = $id_usuario")) === FALSE) {
            $sClaveError = 'Usuario.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        // para los bytea, sobre escribo los valores:
        $spassword = '';
        $oDblSt->bindColumn('password', $spassword, PDO::PARAM_STR);
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        $aDatos['password'] = $spassword;
        return $aDatos;
    }

    public function getNewId_usuario()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('aux_usuarios_id_usuario_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getArrayUsuarios(): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where = '';
        $sQuery = "SELECT id_usuario, usuario FROM $nom_tabla
                $Where ORDER BY usuario";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones = [];
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $aOpciones[$clave] = $val;
        }
        return $aOpciones;
    }

    public function getDesplUsuarios(): Desplegable|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $Where = '';
        $sQuery = "SELECT id_usuario, usuario FROM $nom_tabla
                $Where ORDER BY usuario";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones = [];
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $aOpciones[$clave] = $val;
        }
        return new Desplegable('', $aOpciones, '', true);
    }

}