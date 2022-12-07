<?php

namespace usuarios\domain\entity;

use function core\is_true;

/**
 * Clase que implementa la entidad aux_cargos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class Cargo
{

    /* CONSTANTES ----------------------------------------------------------------- */

    public const AMBITO_CG = 1;
    public const AMBITO_CR = 2;
    public const AMBITO_DL = 3;
    public const AMBITO_CTR = 4;

    public const CARGO_PONENTE = 1;
    public const CARGO_OFICIALES = 2;
    public const CARGO_VARIAS = 3;
    public const CARGO_TODOS_DIR = 4;
    public const CARGO_VB_VCD = 5;
    public const CARGO_DISTRIBUIR = 6;
    public const CARGO_REUNION = 7;

    public const OFICINA_ESQUEMA = -10;

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_cargo de Cargo
     *
     * @var int
     */
    private int $iid_cargo;
    /**
     * Id_ambito de Cargo
     *
     * @var int
     */
    private int $iid_ambito;
    /**
     * Cargo de Cargo
     *
     * @var string
     */
    private string $scargo;
    /**
     * Descripcion de Cargo
     *
     * @var string|null
     */
    private ?string $sdescripcion = null;
    /**
     * Id_oficina de Cargo
     *
     * @var int
     */
    private int $iid_oficina;
    /**
     * Director de Cargo
     *
     * @var bool
     */
    private bool $bdirector;
    /**
     * Id_usuario de Cargo
     *
     * @var int|null
     */
    private ?int $iid_usuario = null;
    /**
     * Id_suplente de Cargo
     *
     * @var int|null
     */
    private ?int $iid_suplente = null;
    /**
     * Sacd de Cargo
     *
     * @var bool
     */
    private bool $bsacd;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Cargo
     */
    public function setAllAttributes(array $aDatos): Cargo
    {
        if (array_key_exists('id_cargo', $aDatos)) {
            $this->setId_cargo($aDatos['id_cargo']);
        }
        if (array_key_exists('id_ambito', $aDatos)) {
            $this->setId_ambito($aDatos['id_ambito']);
        }
        if (array_key_exists('cargo', $aDatos)) {
            $this->setCargo($aDatos['cargo']);
        }
        if (array_key_exists('descripcion', $aDatos)) {
            $this->setDescripcion($aDatos['descripcion']);
        }
        if (array_key_exists('id_oficina', $aDatos)) {
            $this->setId_oficina($aDatos['id_oficina']);
        }
        if (array_key_exists('director', $aDatos)) {
            $this->setDirector(is_true($aDatos['director']));
        }
        if (array_key_exists('id_usuario', $aDatos)) {
            $this->setId_usuario($aDatos['id_usuario']);
        }
        if (array_key_exists('id_suplente', $aDatos)) {
            $this->setId_suplente($aDatos['id_suplente']);
        }
        if (array_key_exists('sacd', $aDatos)) {
            $this->setSacd(is_true($aDatos['sacd']));
        }
        return $this;
    }

    /**
     *
     * @return int $iid_cargo
     */
    public function getId_cargo(): int
    {
        return $this->iid_cargo;
    }

    /**
     *
     * @param int $iid_cargo
     */
    public function setId_cargo(int $iid_cargo): void
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     *
     * @return int $iid_ambito
     */
    public function getId_ambito(): int
    {
        return $this->iid_ambito;
    }

    /**
     *
     * @param int $iid_ambito
     */
    public function setId_ambito(int $iid_ambito): void
    {
        $this->iid_ambito = $iid_ambito;
    }

    /**
     *
     * @return string $scargo
     */
    public function getCargo(): string
    {
        return $this->scargo;
    }

    /**
     *
     * @param string $scargo
     */
    public function setCargo(string $scargo): void
    {
        $this->scargo = $scargo;
    }

    /**
     *
     * @return string|null $sdescripcion
     */
    public function getDescripcion(): ?string
    {
        return $this->sdescripcion;
    }

    /**
     *
     * @param string|null $sdescripcion
     */
    public function setDescripcion(?string $sdescripcion = null): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     *
     * @return int $iid_oficina
     */
    public function getId_oficina(): int
    {
        return $this->iid_oficina;
    }

    /**
     *
     * @param int $iid_oficina
     */
    public function setId_oficina(int $iid_oficina): void
    {
        $this->iid_oficina = $iid_oficina;
    }

    /**
     *
     * @return bool $bdirector
     */
    public function isDirector(): bool
    {
        return $this->bdirector;
    }

    /**
     *
     * @param bool $bdirector
     */
    public function setDirector(bool $bdirector): void
    {
        $this->bdirector = $bdirector;
    }

    /**
     *
     * @return int|null $iid_usuario
     */
    public function getId_usuario(): ?int
    {
        return $this->iid_usuario;
    }

    /**
     *
     * @param int|null $iid_usuario
     */
    public function setId_usuario(?int $iid_usuario = null): void
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     *
     * @return int|null $iid_suplente
     */
    public function getId_suplente(): ?int
    {
        return $this->iid_suplente;
    }

    /**
     *
     * @param int|null $iid_suplente
     */
    public function setId_suplente(?int $iid_suplente = null): void
    {
        $this->iid_suplente = $iid_suplente;
    }

    /**
     *
     * @return bool $bsacd
     */
    public function isSacd(): bool
    {
        return $this->bsacd;
    }

    /**
     *
     * @param bool $bsacd
     */
    public function setSacd(bool $bsacd): void
    {
        $this->bsacd = $bsacd;
    }
}