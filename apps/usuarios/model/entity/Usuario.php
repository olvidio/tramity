<?php

namespace usuarios\model\entity;

/**
 * Clase que implementa la entidad aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */
class Usuario
{
    /* ATRIBUTOS ----------------------------------------------------------------- */


    /**
     * aPrimary_key de Usuario
     *
     * @var array
     */
    private array $aPrimary_key;

    /**
     * bLoaded de Usuario
     *
     * @var bool
     */
    private bool $bLoaded = FALSE;


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
    /* ATRIBUTOS QUE NO SON CAMPOS------------------------------------------------- */

    private UsuarioRepository $repository;

    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * @param integer|null $iid_usuario
     */
    public function __construct(int $iid_usuario = null)
    {
        $this->repository = new UsuarioRepository();
        if ($iid_usuario !== null) {
            $this->iid_usuario = $iid_usuario;
            $this->aPrimary_key = array('iid_usuario' => $this->iid_usuario);
        }
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function DBGuardar(): bool
    {
        return $this->repository->Guardar($this);
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function DBCargar(): void
    {
        $this->bLoaded = TRUE;
        $aDatos = $this->repository->datosById($this->iid_usuario);
        $this->repository->setAllAttributes($this, $aDatos);
    }

    public function DBEliminar(): bool
    {
        return $this->repository->Eliminar($this);
    }

    public function hidrate($aDatos): void
    {
        $this->repository->setAllAttributes($this, $aDatos);
    }

    /**
     * Recupera las claves primarias de Usuario en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key(): array
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_usuario' => $this->iid_usuario);
        }
        return $this->aPrimary_key;
    }

    /**
     * Establece las claves primarias de Usuario en un array
     *
     */
    public function setPrimary_key(array $aPrimaryKey): void
    {
        $this->aPrimary_key = $aPrimaryKey;
    }


    /**
     *
     * @return int $iid_usuario
     */
    public function getId_usuario(): int
    {
        if (!isset($this->iid_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
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
        if (!isset($this->susuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
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
        if (!isset($this->iid_cargo_preferido) && !$this->bLoaded) {
            $this->DBCargar();
        }
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
        if (!isset($this->spassword) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->spassword;
    }

    /**
     *
     * @param string|null $spassword
     */
    public function setPassword(string $spassword = null): void
    {
        $this->spassword = $spassword;
    }

    /**
     *
     * @return string|null $semail
     */
    public function getEmail(): ?string
    {
        if (!isset($this->semail) && !$this->bLoaded) {
            $this->DBCargar();
        }
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
        if (!isset($this->snom_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
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

    public function getNewId_usuario()
    {
        return $this->repository->getNewId_usuario();
    }

}