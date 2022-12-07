<?php

namespace usuarios\domain\entity;
/**
 * Clase que implementa la entidad x_oficinas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class Oficina
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_oficina de Oficina
     *
     * @var int
     */
    private int $iid_oficina;
    /**
     * Sigla de Oficina
     *
     * @var string
     */
    private string $ssigla;
    /**
     * Orden de Oficina
     *
     * @var int|null
     */
    private ?int $iorden = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Oficina
     */
    public function setAllAttributes(array $aDatos): Oficina
    {
        if (array_key_exists('id_oficina', $aDatos)) {
            $this->setId_oficina($aDatos['id_oficina']);
        }
        if (array_key_exists('sigla', $aDatos)) {
            $this->setSigla($aDatos['sigla']);
        }
        if (array_key_exists('orden', $aDatos)) {
            $this->setOrden($aDatos['orden']);
        }
        return $this;
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
     * @return int|null $iorden
     */
    public function getOrden(): ?int
    {
        return $this->iorden;
    }

    /**
     *
     * @param int|null $iorden
     */
    public function setOrden(?int $iorden = null): void
    {
        $this->iorden = $iorden;
    }
}