<?php

namespace tramites\domain\entity;
/**
 * Clase que implementa la entidad tramite_cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class TramiteCargo
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_item de TramiteCargo
     *
     * @var int
     */
    private int $iid_item;
    /**
     * Id_tramite de TramiteCargo
     *
     * @var int
     */
    private int $iid_tramite;
    /**
     * Orden_tramite de TramiteCargo
     *
     * @var int
     */
    private int $iorden_tramite;
    /**
     * Id_cargo de TramiteCargo
     *
     * @var int
     */
    private int $iid_cargo;
    /**
     * Multiple de TramiteCargo
     *
     * @var int|null
     */
    private ?int $imultiple = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return TramiteCargo
     */
    public function setAllAttributes(array $aDatos): TramiteCargo
    {
        if (array_key_exists('id_item', $aDatos)) {
            $this->setId_item($aDatos['id_item']);
        }
        if (array_key_exists('id_tramite', $aDatos)) {
            $this->setId_tramite($aDatos['id_tramite']);
        }
        if (array_key_exists('orden_tramite', $aDatos)) {
            $this->setOrden_tramite($aDatos['orden_tramite']);
        }
        if (array_key_exists('id_cargo', $aDatos)) {
            $this->setId_cargo($aDatos['id_cargo']);
        }
        if (array_key_exists('multiple', $aDatos)) {
            $this->setMultiple($aDatos['multiple']);
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
     * @param int $iid_tramite
     */
    public function setId_tramite(int $iid_tramite): void
    {
        $this->iid_tramite = $iid_tramite;
    }

    /**
     *
     * @param int $iorden_tramite
     */
    public function setOrden_tramite(int $iorden_tramite): void
    {
        $this->iorden_tramite = $iorden_tramite;
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
     * @param int|null $imultiple
     */
    public function setMultiple(?int $imultiple = null): void
    {
        $this->imultiple = $imultiple;
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
     * @return int $iid_tramite
     */
    public function getId_tramite(): int
    {
        return $this->iid_tramite;
    }

    /**
     *
     * @return int $iorden_tramite
     */
    public function getOrden_tramite(): int
    {
        return $this->iorden_tramite;
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
     * @return int|null $imultiple
     */
    public function getMultiple(): ?int
    {
        return $this->imultiple;
    }
}