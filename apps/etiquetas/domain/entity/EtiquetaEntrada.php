<?php

namespace etiquetas\domain\entity;
/**
 * Clase que implementa la entidad etiquetas_entrada
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EtiquetaEntrada
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_etiqueta de EtiquetaEntrada
     *
     * @var int
     */
    private int $iid_etiqueta;
    /**
     * Id_entrada de EtiquetaEntrada
     *
     * @var int
     */
    private int $iid_entrada;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EtiquetaEntrada
     */
    public function setAllAttributes(array $aDatos): EtiquetaEntrada
    {
        if (array_key_exists('id_etiqueta', $aDatos)) {
            $this->setId_etiqueta($aDatos['id_etiqueta']);
        }
        if (array_key_exists('id_entrada', $aDatos)) {
            $this->setId_entrada($aDatos['id_entrada']);
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
     * @return int $iid_entrada
     */
    public function getId_entrada(): int
    {
        return $this->iid_entrada;
    }

    /**
     *
     * @param int $iid_entrada
     */
    public function setId_entrada(int $iid_entrada): void
    {
        $this->iid_entrada = $iid_entrada;
    }
}