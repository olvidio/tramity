<?php

namespace etiquetas\domain\entity;
/**
 * Clase que implementa la entidad etiquetas_expediente
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EtiquetaExpediente
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_etiqueta de EtiquetaExpediente
     *
     * @var int
     */
    private int $iid_etiqueta;
    /**
     * Id_expediente de EtiquetaExpediente
     *
     * @var int
     */
    private int $iid_expediente;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EtiquetaExpediente
     */
    public function setAllAttributes(array $aDatos): EtiquetaExpediente
    {
        if (array_key_exists('id_etiqueta', $aDatos)) {
            $this->setId_etiqueta($aDatos['id_etiqueta']);
        }
        if (array_key_exists('id_expediente', $aDatos)) {
            $this->setId_expediente($aDatos['id_expediente']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_etiqueta
     */
    public function getId_etiqueta(): int
    {
        return $this->iid_etiqueta;
    }

    /**
     *
     * @param int $iid_etiqueta
     */
    public function setId_etiqueta(int $iid_etiqueta): void
    {
        $this->iid_etiqueta = $iid_etiqueta;
    }

    /**
     *
     * @return int $iid_expediente
     */
    public function getId_expediente(): int
    {
        return $this->iid_expediente;
    }

    /**
     *
     * @param int $iid_expediente
     */
    public function setId_expediente(int $iid_expediente): void
    {
        $this->iid_expediente = $iid_expediente;
    }
}