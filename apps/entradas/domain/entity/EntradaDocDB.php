<?php

namespace entradas\domain\entity;

use web\DateTimeLocal;
use web\NullDateTimeLocal;

/**
 * Clase que implementa la entidad entrada_doc
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaDocDB
{

    /* CONSTANTES -------------------------------------------------------------- */

    // tipo documento
    public const TIPO_ETHERPAD = 1;
    public const TIPO_ETHERCALC = 2;
    public const TIPO_OTRO = 3;


    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_entrada de EntradaDocDB
     *
     * @var int
     */
    private int $iid_entrada;
    /**
     * Tipo_doc de EntradaDocDB
     *
     * @var int|null
     */
    private int|null $itipo_doc = null;
    /**
     * F_doc de EntradaDocDB
     *
     * @var DateTimeLocal
     */
    private DateTimeLocal $df_doc;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EntradaDocDB
     */
    public function setAllAttributes(array $aDatos): EntradaDocDB
    {
        if (array_key_exists('id_entrada', $aDatos)) {
            $this->setId_entrada($aDatos['id_entrada']);
        }
        if (array_key_exists('tipo_doc', $aDatos)) {
            $this->setTipo_doc($aDatos['tipo_doc']);
        }
        if (array_key_exists('f_doc', $aDatos)) {
            $this->setF_doc($aDatos['f_doc']);
        }
        return $this;
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
     * @return DateTimeLocal|NullDateTimeLocal|null $df_doc
     */
    public function getF_doc(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_doc ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_doc
     */
    public function setF_doc(DateTimeLocal|null $df_doc = null): void
    {
        $this->df_doc = $df_doc;
    }
}