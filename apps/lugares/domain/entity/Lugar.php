<?php

namespace lugares\domain\entity;

use function core\is_true;

/**
 * Clase que implementa la entidad lugares
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class Lugar
{

     /* CONSTANTES -------------------------------------------------------------- */
    // modo envío
    public const MODO_PDF = 1;
    public const MODO_XML = 2;
    public const MODO_AS4 = 3;

    public function getArrayModoEnvio(): array
    {
        return [
            self::MODO_AS4 => _("as4"),
            self::MODO_PDF => _("pdf"),
            self::MODO_XML => _("xml"),
        ];
    }

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_lugar de Lugar
     *
     * @var int
     */
    private int $iid_lugar;
    /**
     * Sigla de Lugar
     *
     * @var string
     */
    private string $ssigla;
    /**
     * Dl de Lugar
     *
     * @var string|null
     */
    private ?string $sdl = null;
    /**
     * Region de Lugar
     *
     * @var string|null
     */
    private ?string $sregion = null;
    /**
     * Nombre de Lugar
     *
     * @var string|null
     */
    private ?string $snombre = null;
    /**
     * Tipo_ctr de Lugar
     *
     * @var string|null
     */
    private ?string $stipo_ctr = null;
    /**
     * Modo_envio de Lugar
     *
     * @var int|null
     */
    private ?int $imodo_envio = null;
    /**
     * Pub_key de Lugar
     *
     * @var string|null
     */
    private ?string $spub_key = null;
    /**
     * E_mail de Lugar
     *
     * @var string|null
     */
    private ?string $se_mail = null;
    /**
     * Anulado de Lugar
     *
     * @var bool
     */
    private bool $banulado;
    /**
     * Plataforma de Lugar
     *
     * @var string|null
     */
    private ?string $splataforma = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Lugar
     */
    public function setAllAttributes(array $aDatos): Lugar
    {
        if (array_key_exists('id_lugar', $aDatos)) {
            $this->setId_lugar($aDatos['id_lugar']);
        }
        if (array_key_exists('sigla', $aDatos)) {
            $this->setSigla($aDatos['sigla']);
        }
        if (array_key_exists('dl', $aDatos)) {
            $this->setDl($aDatos['dl']);
        }
        if (array_key_exists('region', $aDatos)) {
            $this->setRegion($aDatos['region']);
        }
        if (array_key_exists('nombre', $aDatos)) {
            $this->setNombre($aDatos['nombre']);
        }
        if (array_key_exists('tipo_ctr', $aDatos)) {
            $this->setTipo_ctr($aDatos['tipo_ctr']);
        }
        if (array_key_exists('modo_envio', $aDatos)) {
            $this->setModo_envio($aDatos['modo_envio']);
        }
        if (array_key_exists('pub_key', $aDatos)) {
            $this->setPub_key($aDatos['pub_key']);
        }
        if (array_key_exists('e_mail', $aDatos)) {
            $this->setE_mail($aDatos['e_mail']);
        }
        if (array_key_exists('anulado', $aDatos)) {
            $this->setAnulado(is_true($aDatos['anulado']));
        }
        if (array_key_exists('plataforma', $aDatos)) {
            $this->setPlataforma($aDatos['plataforma']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_lugar
     */
    public function getId_lugar(): int
    {
        return $this->iid_lugar;
    }

    /**
     *
     * @param int $iid_lugar
     */
    public function setId_lugar(int $iid_lugar): void
    {
        $this->iid_lugar = $iid_lugar;
    }

    /**
     *
     * @return string $ssigla
     */
    public function getSigla(): string
    {
        return $this->ssigla;
    }

    /**
     *
     * @param string $ssigla
     */
    public function setSigla(string $ssigla): void
    {
        $this->ssigla = $ssigla;
    }

    /**
     *
     * @return string|null $sdl
     */
    public function getDl(): ?string
    {
        return $this->sdl;
    }

    /**
     *
     * @param string|null $sdl
     */
    public function setDl(?string $sdl = null): void
    {
        $this->sdl = $sdl;
    }

    /**
     *
     * @return string|null $sregion
     */
    public function getRegion(): ?string
    {
        return $this->sregion;
    }

    /**
     *
     * @param string|null $sregion
     */
    public function setRegion(?string $sregion = null): void
    {
        $this->sregion = $sregion;
    }

    /**
     *
     * @return string|null $snombre
     */
    public function getNombre(): ?string
    {
        return $this->snombre;
    }

    /**
     *
     * @param string|null $snombre
     */
    public function setNombre(?string $snombre = null): void
    {
        $this->snombre = $snombre;
    }

    /**
     *
     * @return string|null $stipo_ctr
     */
    public function getTipo_ctr(): ?string
    {
        return $this->stipo_ctr;
    }

    /**
     *
     * @param string|null $stipo_ctr
     */
    public function setTipo_ctr(?string $stipo_ctr = null): void
    {
        $this->stipo_ctr = $stipo_ctr;
    }

    /**
     *
     * @return int|null $imodo_envio
     */
    public function getModo_envio(): ?int
    {
        return $this->imodo_envio;
    }

    /**
     *
     * @param int|null $imodo_envio
     */
    public function setModo_envio(?int $imodo_envio = null): void
    {
        $this->imodo_envio = $imodo_envio;
    }

    /**
     *
     * @return string|null $spub_key
     */
    public function getPub_key(): ?string
    {
        return $this->spub_key;
    }

    /**
     *
     * @param string|null $spub_key
     */
    public function setPub_key(?string $spub_key = null): void
    {
        $this->spub_key = $spub_key;
    }

    /**
     *
     * @return string|null $se_mail
     */
    public function getE_mail(): ?string
    {
        return $this->se_mail;
    }

    /**
     *
     * @param string|null $se_mail
     */
    public function setE_mail(?string $se_mail = null): void
    {
        $this->se_mail = $se_mail;
    }

    /**
     *
     * @return bool $banulado
     */
    public function isAnulado(): bool
    {
        return $this->banulado;
    }

    /**
     *
     * @param bool $banulado
     */
    public function setAnulado(bool $banulado): void
    {
        $this->banulado = $banulado;
    }

    /**
     *
     * @return string|null $splataforma
     */
    public function getPlataforma(): ?string
    {
        return $this->splataforma;
    }

    /**
     *
     * @param string|null $splataforma
     */
    public function setPlataforma(?string $splataforma = null): void
    {
        $this->splataforma = $splataforma;
    }
}