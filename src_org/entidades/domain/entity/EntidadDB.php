<?php

namespace entidades\domain\entity;

use function core\is_true;

/**
 * Clase que implementa la entidad entidades
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntidadDB
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_entidad de EntidadDB
     *
     * @var int
     */
    protected int $iid_entidad;
    /**
     * Nombre de EntidadDB
     *
     * @var string|null
     */
    protected string|null $snombre = null;
    /**
     * Schema de EntidadDB
     *
     * @var string|null
     */
    protected string|null $sschema = null;
    /**
     * Tipo de EntidadDB
     *
     * @var int|null
     */
    protected int|null $itipo = null;
    /**
     * Anulado de EntidadDB
     *
     * @var bool|null
     */
    protected bool|null $banulado = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EntidadDB
     */
    public function setAllAttributes(array $aDatos): EntidadDB
    {
        if (array_key_exists('id_entidad', $aDatos)) {
            $this->setId_entidad($aDatos['id_entidad']);
        }
        if (array_key_exists('nombre', $aDatos)) {
            $this->setNombre($aDatos['nombre']);
        }
        if (array_key_exists('schema', $aDatos)) {
            $this->setSchema($aDatos['schema']);
        }
        if (array_key_exists('tipo', $aDatos)) {
            $this->setTipo($aDatos['tipo']);
        }
        if (array_key_exists('anulado', $aDatos)) {
            $this->setAnulado(is_true($aDatos['anulado']));
        }
        return $this;
    }

    /**
     *
     * @return int $iid_entidad
     */
    public function getId_entidad(): int
    {
        return $this->iid_entidad;
    }

    /**
     *
     * @param int $iid_entidad
     */
    public function setId_entidad(int $iid_entidad): void
    {
        $this->iid_entidad = $iid_entidad;
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
     * @return string|null $sschema
     */
    public function getSchema(): ?string
    {
        return $this->sschema;
    }

    /**
     *
     * @param string|null $sschema
     */
    public function setSchema(?string $sschema = null): void
    {
        $this->sschema = $sschema;
    }

    /**
     *
     * @return int|null $itipo
     */
    public function getTipo(): ?int
    {
        return $this->itipo;
    }

    /**
     *
     * @param int|null $itipo
     */
    public function setTipo(?int $itipo = null): void
    {
        $this->itipo = $itipo;
    }

    /**
     *
     * @return bool|null $banulado
     */
    public function isAnulado(): ?bool
    {
        return $this->banulado;
    }

    /**
     *
     * @param bool|null $banulado
     */
    public function setAnulado(?bool $banulado = null): void
    {
        $this->banulado = $banulado;
    }
}