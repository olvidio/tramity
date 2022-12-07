<?php

namespace usuarios\domain\entity;

use function core\is_true;

/**
 * Clase que implementa la entidad x_locales
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class Locale
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_locale de Locale
     *
     * @var string
     */
    private string $sid_locale;
    /**
     * Nom_locale de Locale
     *
     * @var string|null
     */
    private ?string $snom_locale = null;
    /**
     * Idioma de Locale
     *
     * @var string|null
     */
    private ?string $sidioma = null;
    /**
     * Nom_idioma de Locale
     *
     * @var string|null
     */
    private ?string $snom_idioma = null;
    /**
     * Activo de Locale
     *
     * @var bool
     */
    private bool $bactivo;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Locale
     */
    public function setAllAttributes(array $aDatos): Locale
    {
        if (array_key_exists('id_locale', $aDatos)) {
            $this->setId_locale($aDatos['id_locale']);
        }
        if (array_key_exists('nom_locale', $aDatos)) {
            $this->setNom_locale($aDatos['nom_locale']);
        }
        if (array_key_exists('idioma', $aDatos)) {
            $this->setIdioma($aDatos['idioma']);
        }
        if (array_key_exists('nom_idioma', $aDatos)) {
            $this->setNom_idioma($aDatos['nom_idioma']);
        }
        if (array_key_exists('activo', $aDatos)) {
            $this->setActivo(is_true($aDatos['activo']));
        }
        return $this;
    }

    /**
     *
     * @return string $sid_locale
     */
    public function getId_locale(): string
    {
        return $this->sid_locale;
    }

    /**
     *
     * @param string $sid_locale
     */
    public function setId_locale(string $sid_locale): void
    {
        $this->sid_locale = $sid_locale;
    }

    /**
     *
     * @return string|null $snom_locale
     */
    public function getNom_locale(): ?string
    {
        return $this->snom_locale;
    }

    /**
     *
     * @param string|null $snom_locale
     */
    public function setNom_locale(?string $snom_locale = null): void
    {
        $this->snom_locale = $snom_locale;
    }

    /**
     *
     * @return string|null $sidioma
     */
    public function getIdioma(): ?string
    {
        return $this->sidioma;
    }

    /**
     *
     * @param string|null $sidioma
     */
    public function setIdioma(?string $sidioma = null): void
    {
        $this->sidioma = $sidioma;
    }

    /**
     *
     * @return string|null $snom_idioma
     */
    public function getNom_idioma(): ?string
    {
        return $this->snom_idioma;
    }

    /**
     *
     * @param string|null $snom_idioma
     */
    public function setNom_idioma(?string $snom_idioma = null): void
    {
        $this->snom_idioma = $snom_idioma;
    }

    /**
     *
     * @return bool $bactivo
     */
    public function isActivo(): bool
    {
        return $this->bactivo;
    }

    /**
     *
     * @param bool $bactivo
     */
    public function setActivo(bool $bactivo): void
    {
        $this->bactivo = $bactivo;
    }
}