<?php

namespace tramites\domain\entity;
/**
 * Clase que implementa la entidad x_tramites
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class Tramite
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_tramite de Tramite
     *
     * @var int
     */
    private int $iid_tramite;
    /**
     * Tramite de Tramite
     *
     * @var string
     */
    private string $stramite;
    /**
     * Orden de Tramite
     *
     * @var int|null
     */
    private ?int $iorden = null;
    /**
     * Breve de Tramite
     *
     * @var string|null
     */
    private ?string $sbreve = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Tramite
     */
    public function setAllAttributes(array $aDatos): Tramite
    {
        if (array_key_exists('id_tramite', $aDatos)) {
            $this->setId_tramite($aDatos['id_tramite']);
        }
        if (array_key_exists('tramite', $aDatos)) {
            $this->setTramite($aDatos['tramite']);
        }
        if (array_key_exists('orden', $aDatos)) {
            $this->setOrden($aDatos['orden']);
        }
        if (array_key_exists('breve', $aDatos)) {
            $this->setBreve($aDatos['breve']);
        }
        return $this;
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
     * @param int $iid_tramite
     */
    public function setId_tramite(int $iid_tramite): void
    {
        $this->iid_tramite = $iid_tramite;
    }

    /**
     *
     * @return string $stramite
     */
    public function getTramite(): string
    {
        return $this->stramite;
    }

    /**
     *
     * @param string $stramite
     */
    public function setTramite(string $stramite): void
    {
        $this->stramite = $stramite;
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

    /**
     *
     * @return string|null $sbreve
     */
    public function getBreve(): ?string
    {
        return $this->sbreve;
    }

    /**
     *
     * @param string|null $sbreve
     */
    public function setBreve(?string $sbreve = null): void
    {
        $this->sbreve = $sbreve;
    }
}