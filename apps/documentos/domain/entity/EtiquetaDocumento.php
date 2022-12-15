<?php

namespace documentos\domain\entity;
/**
 * Clase que implementa la entidad etiquetas_documento
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EtiquetaDocumento
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_etiqueta de EtiquetaDocumento
     *
     * @var integer
     */
    private int $iid_etiqueta;
    /**
     * Id_doc de EtiquetaDocumento
     *
     * @var integer
     */
    private int $iid_doc;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EtiquetaDocumento
     */
    public function setAllAttributes(array $aDatos): EtiquetaDocumento
    {
        if (array_key_exists('id_etiqueta', $aDatos)) {
            $this->setId_etiqueta($aDatos['id_etiqueta']);
        }
        if (array_key_exists('id_doc', $aDatos)) {
            $this->setId_doc($aDatos['id_doc']);
        }
        return $this;
    }

    /**
     * @param integer $iid_etiqueta
     */
    public function setId_etiqueta(int $iid_etiqueta): void
    {
        $this->iid_etiqueta = $iid_etiqueta;
    }

    /**
     * @param integer $iid_doc
     */
    public function setId_doc(int $iid_doc): void
    {
        $this->iid_doc = $iid_doc;
    }

    /**
     * Recupera el atributo iid_etiqueta de EtiquetaDocumento
     *
     * @return integer $iid_etiqueta
     */
    public function getId_etiqueta(): int
    {
        return $this->iid_etiqueta;
    }

    /**
     * Recupera el atributo iid_doc de EtiquetaDocumento
     *
     * @return integer $iid_doc
     */
    public function getId_doc(): int
    {
        return $this->iid_doc;
    }

}