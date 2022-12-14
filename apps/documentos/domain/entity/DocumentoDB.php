<?php

namespace documentos\domain\entity;

use web\DateTimeLocal;
use web\NullDateTimeLocal;

/**
 * Clase que implementa la entidad documentos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class DocumentoDB
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_doc de DocumentoDB
     *
     * @var int
     */
    protected int $iid_doc;
    /**
     * Nom de DocumentoDB
     *
     * @var string|null
     */
    protected string|null $snom = null;
    /**
     * Nombre_fichero de DocumentoDB
     *
     * @var string|null
     */
    protected string|null $snombre_fichero = null;
    /**
     * Creador de DocumentoDB
     *
     * @var int|null
     */
    protected int|null $icreador = null;
    /**
     * Visibilidad de DocumentoDB
     *
     * @var int|null
     */
    protected int|null $ivisibilidad = null;
    /**
     * F_upload de DocumentoDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_upload = null;
    /**
     * Tipo_doc de DocumentoDB
     *
     * @var int|null
     */
    protected int|null $itipo_doc = null;
    /**
     * Documento de DocumentoDB
     *
     * @var string|null
     */
    protected string|null $sdocumento = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return DocumentoDB
     */
    public function setAllAttributes(array $aDatos): DocumentoDB
    {
        if (array_key_exists('id_doc', $aDatos)) {
            $this->setId_doc($aDatos['id_doc']);
        }
        if (array_key_exists('nom', $aDatos)) {
            $this->setNom($aDatos['nom']);
        }
        if (array_key_exists('nombre_fichero', $aDatos)) {
            $this->setNombre_fichero($aDatos['nombre_fichero']);
        }
        if (array_key_exists('creador', $aDatos)) {
            $this->setCreador($aDatos['creador']);
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        if (array_key_exists('f_upload', $aDatos)) {
            $this->setF_upload($aDatos['f_upload']);
        }
        if (array_key_exists('tipo_doc', $aDatos)) {
            $this->setTipo_doc($aDatos['tipo_doc']);
        }
        if (array_key_exists('documento', $aDatos)) {
            $this->setDocumento($aDatos['documento']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_doc
     */
    public function getId_doc(): int
    {
        return $this->iid_doc;
    }

    /**
     *
     * @param int $iid_doc
     */
    public function setId_doc(int $iid_doc): void
    {
        $this->iid_doc = $iid_doc;
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
     * @return string|null $snombre_fichero
     */
    public function getNombre_fichero(): ?string
    {
        return $this->snombre_fichero;
    }

    /**
     *
     * @param string|null $snombre_fichero
     */
    public function setNombre_fichero(?string $snombre_fichero = null): void
    {
        $this->snombre_fichero = $snombre_fichero;
    }

    /**
     *
     * @return int|null $icreador
     */
    public function getCreador(): ?int
    {
        return $this->icreador;
    }

    /**
     *
     * @param int|null $icreador
     */
    public function setCreador(?int $icreador = null): void
    {
        $this->icreador = $icreador;
    }

    /**
     *
     * @return int|null $ivisibilidad
     */
    public function getVisibilidad(): ?int
    {
        return $this->ivisibilidad;
    }

    /**
     *
     * @param int|null $ivisibilidad
     */
    public function setVisibilidad(?int $ivisibilidad = null): void
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_upload
     */
    public function getF_upload(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_upload ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_upload
     */
    public function setF_upload(DateTimeLocal|null $df_upload = null): void
    {
        $this->df_upload = $df_upload;
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

    /**
     *
     * @return string|null $sdocumento
     */
    public function getDocumento(): ?string
    {
        return $this->sdocumento;
    }

    /**
     *
     * @param string|null $sdocumento
     */
    public function setDocumento(?string $sdocumento = null): void
    {
        $this->sdocumento = $sdocumento;
    }
}