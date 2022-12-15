<?php

namespace escritos\domain\entity;
/**
 * Clase que implementa la entidad escrito_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EscritoAdjunto
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_item de EscritoAdjunto
     *
     * @var int
     */
    private int $iid_item;
    /**
     * Id_escrito de EscritoAdjunto
     *
     * @var int
     */
    private int $iid_escrito;
    /**
     * Nom de EscritoAdjunto
     *
     * @var string|null
     */
    private string|null $snom = null;
    /**
     * Adjunto de EscritoAdjunto
     *
     * @var string|null
     */
    private string|null $sadjunto = null;
    /**
     * Tipo_doc de EscritoAdjunto
     *
     * @var int|null
     */
    private int|null $itipo_doc = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EscritoAdjunto
     */
    public function setAllAttributes(array $aDatos): EscritoAdjunto
    {
        if (array_key_exists('id_item', $aDatos)) {
            $this->setId_item($aDatos['id_item']);
        }
        if (array_key_exists('id_escrito', $aDatos)) {
            $this->setId_escrito($aDatos['id_escrito']);
        }
        if (array_key_exists('nom', $aDatos)) {
            $this->setNom($aDatos['nom']);
        }
        if (array_key_exists('adjunto', $aDatos)) {
            $this->setAdjunto($aDatos['adjunto']);
        }
        if (array_key_exists('tipo_doc', $aDatos)) {
            $this->setTipo_doc($aDatos['tipo_doc']);
        }
        return $this;
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
     * @param int $iid_item
     */
    public function setId_item(int $iid_item): void
    {
        $this->iid_item = $iid_item;
    }

    /**
     *
     * @return int $iid_escrito
     */
    public function getId_escrito(): int
    {
        return $this->iid_escrito;
    }

    /**
     *
     * @param int $iid_escrito
     */
    public function setId_escrito(int $iid_escrito): void
    {
        $this->iid_escrito = $iid_escrito;
    }

    /**
     *
     * @return string|null $snom
     */
    public function getNom(): ?string
    {
        return $this->snom;
    }

    /**
     *
     * @param string|null $snom
     */
    public function setNom(?string $snom = null): void
    {
        $this->snom = $snom;
    }

    /**
     *
     * @return string|null $sadjunto
     */
    public function getAdjunto(): ?string
    {
        return $this->sadjunto;
    }

    /**
     *
     * @param string|null $sadjunto
     */
    public function setAdjunto(?string $sadjunto = null): void
    {
        $this->sadjunto = $sadjunto;
    }

    /**
     *
     * @return int|null $itipo_doc
     */
    public function getTipo_doc(): ?int
    {
        return $this->itipo_doc;
    }

    /**
     *
     * @param int|null $itipo_doc
     */
    public function setTipo_doc(?int $itipo_doc = null): void
    {
        $this->itipo_doc = $itipo_doc;
    }
}