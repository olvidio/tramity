<?php

namespace usuarios\domain\entity;
/**
 * Clase que implementa la entidad cargos_grupos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class CargoGrupo
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_grupo de CargoGrupo
     *
     * @var int
     */
    private int $iid_grupo;
    /**
     * Id_cargo_ref de CargoGrupo
     *
     * @var int
     */
    private int $iid_cargo_ref;
    /**
     * Descripcion de CargoGrupo
     *
     * @var string
     */
    private string $sdescripcion;
    /**
     * Miembros de CargoGrupo
     *
     * @var array|null
     */
    private ?array $a_miembros = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return CargoGrupo
     */
    public function setAllAttributes(array $aDatos): CargoGrupo
    {
        if (array_key_exists('id_grupo', $aDatos)) {
            $this->setId_grupo($aDatos['id_grupo']);
        }
        if (array_key_exists('id_cargo_ref', $aDatos)) {
            $this->setId_cargo_ref($aDatos['id_cargo_ref']);
        }
        if (array_key_exists('descripcion', $aDatos)) {
            $this->setDescripcion($aDatos['descripcion']);
        }
        if (array_key_exists('miembros', $aDatos)) {
            $this->setMiembros($aDatos['miembros']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_grupo
     */
    public function getId_grupo(): int
    {
        return $this->iid_grupo;
    }

    /**
     *
     * @param int $iid_grupo
     */
    public function setId_grupo(int $iid_grupo): void
    {
        $this->iid_grupo = $iid_grupo;
    }

    /**
     *
     * @return int $iid_cargo_ref
     */
    public function getId_cargo_ref(): int
    {
        return $this->iid_cargo_ref;
    }

    /**
     *
     * @param int $iid_cargo_ref
     */
    public function setId_cargo_ref(int $iid_cargo_ref): void
    {
        $this->iid_cargo_ref = $iid_cargo_ref;
    }

    /**
     *
     * @return string $sdescripcion
     */
    public function getDescripcion(): string
    {
        return $this->sdescripcion;
    }

    /**
     *
     * @param string $sdescripcion
     */
    public function setDescripcion(string $sdescripcion): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     *
     * @return array|null $a_miembros
     */
    public function getMiembros(): array|null
    {
        return $this->a_miembros;
    }

    /**
     *
     * @param array|null $a_miembros
     */
    public function setMiembros(array $a_miembros = null): void
    {
        $this->a_miembros = $a_miembros;
    }
}