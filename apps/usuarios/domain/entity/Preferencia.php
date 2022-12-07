<?php

namespace usuarios\domain\entity;
/**
 * Clase que implementa la entidad usuario_preferencias
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class Preferencia
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_item de Preferencia
     *
     * @var int
     */
    private int $iid_item;
    /**
     * Id_usuario de Preferencia
     *
     * @var int
     */
    private int $iid_usuario;
    /**
     * Tipo de Preferencia
     *
     * @var string
     */
    private string $stipo;
    /**
     * Preferencia de Preferencia
     *
     * @var string|null
     */
    private ?string $spreferencia = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Preferencia
     */
    public function setAllAttributes(array $aDatos): Preferencia
    {
        if (array_key_exists('id_item', $aDatos)) {
            $this->setId_item($aDatos['id_item']);
        }
        if (array_key_exists('id_usuario', $aDatos)) {
            $this->setId_usuario($aDatos['id_usuario']);
        }
        if (array_key_exists('tipo', $aDatos)) {
            $this->setTipo($aDatos['tipo']);
        }
        if (array_key_exists('preferencia', $aDatos)) {
            $this->setPreferencia($aDatos['preferencia']);
        }
        return $this;
    }

    /**
     *
     * @param int $iid_item
     */
    public function setId_item(int $iid_item): void
    {
        $this->iid_item = $iid_item;
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
     * @param string $stipo
     */
    public function setTipo(string $stipo): void
    {
        $this->stipo = $stipo;
    }

    /**
     *
     * @param string|null $spreferencia
     */
    public function setPreferencia(?string $spreferencia = null): void
    {
        $this->spreferencia = $spreferencia;
    }

    /**
     *
     * @return int $iid_item
     */
    public function getId_item(): int
    {
        return $this->iid_item;
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
     * @return string $stipo
     */
    public function getTipo(): string
    {
        return $this->stipo;
    }

    /**
     *
     * @return string|null $spreferencia
     */
    public function getPreferencia(): ?string
    {
        return $this->spreferencia;
    }
}