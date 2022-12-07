<?php

namespace usuarios\domain\entity;
/**
 * Clase que implementa la entidad aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class Usuario
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_usuario de Usuario
     *
     * @var int
     */
    private int $iid_usuario;
    /**
     * Usuario de Usuario
     *
     * @var string
     */
    private string $susuario;
    /**
     * Id_cargo_preferido de Usuario
     *
     * @var int
     */
    private int $iid_cargo_preferido;
    /**
     * Password de Usuario
     *
     * @var string|null
     */
    private ?string $spassword = null;
    /**
     * Email de Usuario
     *
     * @var string|null
     */
    private ?string $semail = null;
    /**
     * Nom_usuario de Usuario
     *
     * @var string|null
     */
    private ?string $snom_usuario = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Usuario
     */
    public function setAllAttributes(array $aDatos): Usuario
    {
        if (array_key_exists('id_usuario', $aDatos)) {
            $this->setId_usuario($aDatos['id_usuario']);
        }
        if (array_key_exists('usuario', $aDatos)) {
            $this->setUsuario($aDatos['usuario']);
        }
        if (array_key_exists('id_cargo_preferido', $aDatos)) {
            $this->setId_cargo_preferido($aDatos['id_cargo_preferido']);
        }
        if (array_key_exists('password', $aDatos)) {
            $this->setPassword($aDatos['password']);
        }
        if (array_key_exists('email', $aDatos)) {
            $this->setEmail($aDatos['email']);
        }
        if (array_key_exists('nom_usuario', $aDatos)) {
            $this->setNom_usuario($aDatos['nom_usuario']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_usuario
     */
    public function getId_usuario(): int
    {
        return $this->iid_usuario;
    }

    /**
     *
     * @param int $iid_usuario
     */
    public function setId_usuario(int $iid_usuario): void
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     *
     * @return string $susuario
     */
    public function getUsuario(): string
    {
        return $this->susuario;
    }

    /**
     *
     * @param string $susuario
     */
    public function setUsuario(string $susuario): void
    {
        $this->susuario = $susuario;
    }

    /**
     *
     * @return int $iid_cargo_preferido
     */
    public function getId_cargo_preferido(): int
    {
        return $this->iid_cargo_preferido;
    }

    /**
     *
     * @param int $iid_cargo_preferido
     */
    public function setId_cargo_preferido(int $iid_cargo_preferido): void
    {
        $this->iid_cargo_preferido = $iid_cargo_preferido;
    }

    /**
     *
     * @return string|null $spassword
     */
    public function getPassword(): ?string
    {
        return $this->spassword;
    }

    /**
     *
     * @param string|null $spassword
     */
    public function setPassword(?string $spassword = null): void
    {
        $this->spassword = $spassword;
    }

    /**
     *
     * @return string|null $semail
     */
    public function getEmail(): ?string
    {
        return $this->semail;
    }

    /**
     *
     * @param string|null $semail
     */
    public function setEmail(?string $semail = null): void
    {
        $this->semail = $semail;
    }

    /**
     *
     * @return string|null $snom_usuario
     */
    public function getNom_usuario(): ?string
    {
        return $this->snom_usuario;
    }

    /**
     *
     * @param string|null $snom_usuario
     */
    public function setNom_usuario(?string $snom_usuario = null): void
    {
        $this->snom_usuario = $snom_usuario;
    }
}