<?php

namespace usuarios\model\entity;

use core\ClasePropiedades;
use PDO;
use PDOException;

/**
 * Clase que implementa la entidad aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */
class UsuarioRepository extends ClasePropiedades
{

    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_usuarios');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

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
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_usuario='$id_usuario'")) === FALSE) {
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

    private function isNew(int $id_usuario): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario='$id_usuario'")) === FALSE) {
            $sClaveError = 'Usuario.cargar';
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
    public function datosById(int $id_usuario): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario='$id_usuario'")) === FALSE) {
            $sClaveError = 'Usuario.cargar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        // para los bytea:
        $password = '';
        $oDblSt->bindColumn('password', $password, PDO::PARAM_STR);
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los bytea, sobre escribo los valores:
        $aDatos['password'] = $password;

        return $aDatos;
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function findById(int $id_usuario): Usuario|bool
    {
        $aDatos = $this->datosById($id_usuario);

        return $this->setAllAttributes(new Usuario(), $aDatos);
    }

    public function Eliminar(Usuario $Usuario): bool
    {
        $id_usuario = $Usuario->getId_usuario();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_usuario='$id_usuario'")) === FALSE) {
            $sClaveError = 'Usuario.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param Usuario $Usuario
     * @param array $aDatos
     * @return Usuario
     */
    public function setAllAttributes(Usuario $Usuario, array $aDatos): Usuario
    {
        if (array_key_exists('id_usuario', $aDatos)) {
            $Usuario->setId_usuario($aDatos['id_usuario']);
        }
        if (array_key_exists('usuario', $aDatos)) {
            $Usuario->setUsuario($aDatos['usuario']);
        }
        if (array_key_exists('id_cargo_preferido', $aDatos)) {
            $Usuario->setId_cargo_preferido($aDatos['id_cargo_preferido']);
        }
        if (array_key_exists('password', $aDatos)) {
            $Usuario->setPassword($aDatos['password']);
        }
        if (array_key_exists('email', $aDatos)) {
            $Usuario->setEmail($aDatos['email']);
        }
        if (array_key_exists('nom_usuario', $aDatos)) {
            $Usuario->setNom_usuario($aDatos['nom_usuario']);
        }
        return $Usuario;
    }

    public function getNewId_usuario()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('aux_usuarios_id_usuario_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

}