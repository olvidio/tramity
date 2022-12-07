<?php

namespace plantillas\domain\entity;
/**
 * Clase que implementa la entidad plantillas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class Plantilla
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_plantilla de Plantilla
     *
     * @var int
     */
    private int $iid_plantilla;
    /**
     * Nombre de Plantilla
     *
     * @var string
     */
    private string $snombre;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Plantilla
     */
    public function setAllAttributes(array $aDatos): Plantilla
    {
        if (array_key_exists('id_plantilla', $aDatos)) {
            $this->setId_plantilla($aDatos['id_plantilla']);
        }
        if (array_key_exists('nombre', $aDatos)) {
            $this->setNombre($aDatos['nombre']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_plantilla
     */
    public function getId_plantilla(): int
    {
        return $this->iid_plantilla;
    }

    /**
     *
     * @param int $iid_plantilla
     */
    public function setId_plantilla(int $iid_plantilla): void
    {
        $this->iid_plantilla = $iid_plantilla;
    }

    /**
     *
     * @return string $snombre
     */
    public function getNombre(): string
    {
        return $this->snombre;
    }

    /**
     *
     * @param string $snombre
     */
    public function setNombre(string $snombre): void
    {
        $this->snombre = $snombre;
    }
}