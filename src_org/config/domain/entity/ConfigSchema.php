<?php

namespace config\domain\entity;
/**
 * Clase que implementa la entidad x_config
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class ConfigSchema
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Parametro de ConfigSchema
     *
     * @var string
     */
    private string $sparametro;
    /**
     * Valor de ConfigSchema
     *
     * @var string|null
     */
    private string|null $svalor = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return ConfigSchema
     */
    public function setAllAttributes(array $aDatos): ConfigSchema
    {
        if (array_key_exists('parametro', $aDatos)) {
            $this->setParametro($aDatos['parametro']);
        }
        if (array_key_exists('valor', $aDatos)) {
            $this->setValor($aDatos['valor']);
        }
        return $this;
    }

    /**
     *
     * @return string $sparametro
     */
    public function getParametro(): string
    {
        return $this->sparametro;
    }

    /**
     *
     * @param string $sparametro
     */
    public function setParametro(string $sparametro): void
    {
        $this->sparametro = $sparametro;
    }

    /**
     *
     * @return string|null $svalor
     */
    public function getValor(): ?string
    {
        return $this->svalor;
    }

    /**
     *
     * @param string|null $svalor
     */
    public function setValor(?string $svalor = null): void
    {
        $this->svalor = $svalor;
    }
}