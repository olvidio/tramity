<?php

namespace entradas\domain\entity;

use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\is_true;

/**
 * Clase que implementa la entidad entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaDB
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_entrada de EntradaDB
     *
     * @var int
     */
    protected int $iid_entrada;
    /**
     * Modo_entrada de EntradaDB
     *
     * @var int
     */
    protected int $imodo_entrada;
    /**
     * Json_prot_origen de EntradaDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_prot_origen = null;
    /**
     * Asunto_entrada de EntradaDB
     *
     * @var string
     */
    protected string $sasunto_entrada;
    /**
     * Json_prot_ref de EntradaDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_prot_ref = null;
    /**
     * Ponente de EntradaDB
     *
     * @var int|null
     */
    protected int|null $iponente = null;
    /**
     * Resto_oficinas de EntradaDB
     *
     * @var array|null
     */
    protected array|null $a_resto_oficinas = null;
    /**
     * Asunto de EntradaDB
     *
     * @var string|null
     */
    protected string|null $sasunto = null;
    /**
     * F_entrada de EntradaDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_entrada = null;
    /**
     * Detalle de EntradaDB
     *
     * @var string|null
     */
    protected string|null $sdetalle = null;
    /**
     * Categoria de EntradaDB
     *
     * @var int|null
     */
    protected int|null $icategoria = null;
    /**
     * Visibilidad de EntradaDB
     *
     * @var int|null
     */
    protected int|null $ivisibilidad = null;
    /**
     * F_contestar de EntradaDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_contestar = null;
    /**
     * Bypass de EntradaDB
     *
     * @var bool|null
     */
    protected bool|null $bbypass = null;
    /**
     * Estado de EntradaDB
     *
     * @var int|null
     */
    protected int|null $iestado = null;
    /**
     * Anulado de EntradaDB
     *
     * @var string|null
     */
    protected string|null $sanulado = null;
    /**
     * Encargado de EntradaDB
     *
     * @var int|null
     */
    protected int|null $iencargado = null;
    /**
     * Json_visto de EntradaDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_visto = null;
    /**
     * Id_entrada_compartida de EntradaDB
     *
     * @var int|null
     */
    protected int|null $iid_entrada_compartida = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EntradaDB
     */
    public function setAllAttributes(array $aDatos): EntradaDB
    {
        if (array_key_exists('id_entrada', $aDatos)) {
            $this->setId_entrada($aDatos['id_entrada']);
        }
        if (array_key_exists('modo_entrada', $aDatos)) {
            $this->setModo_entrada($aDatos['modo_entrada']);
        }
        if (array_key_exists('json_prot_origen', $aDatos)) {
            $this->setJson_prot_origen($aDatos['json_prot_origen']);
        }
        if (array_key_exists('asunto_entrada', $aDatos)) {
            $this->setAsunto_entrada($aDatos['asunto_entrada']);
        }
        if (array_key_exists('json_prot_ref', $aDatos)) {
            $this->setJson_prot_ref($aDatos['json_prot_ref']);
        }
        if (array_key_exists('ponente', $aDatos)) {
            $this->setPonente($aDatos['ponente']);
        }
        if (array_key_exists('resto_oficinas', $aDatos)) {
            $this->setResto_oficinas($aDatos['resto_oficinas']);
        }
        if (array_key_exists('asunto', $aDatos)) {
            $this->setAsunto($aDatos['asunto']);
        }
        if (array_key_exists('f_entrada', $aDatos)) {
            $this->setF_entrada($aDatos['f_entrada']);
        }
        if (array_key_exists('detalle', $aDatos)) {
            $this->setDetalle($aDatos['detalle']);
        }
        if (array_key_exists('categoria', $aDatos)) {
            $this->setCategoria($aDatos['categoria']);
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        if (array_key_exists('f_contestar', $aDatos)) {
            $this->setF_contestar($aDatos['f_contestar']);
        }
        if (array_key_exists('bypass', $aDatos)) {
            $this->setBypass(is_true($aDatos['bypass']));
        }
        if (array_key_exists('estado', $aDatos)) {
            $this->setEstado($aDatos['estado']);
        }
        if (array_key_exists('anulado', $aDatos)) {
            $this->setAnulado($aDatos['anulado']);
        }
        if (array_key_exists('encargado', $aDatos)) {
            $this->setEncargado($aDatos['encargado']);
        }
        if (array_key_exists('json_visto', $aDatos)) {
            $this->setJson_visto($aDatos['json_visto']);
        }
        if (array_key_exists('id_entrada_compartida', $aDatos)) {
            $this->setId_entrada_compartida($aDatos['id_entrada_compartida']);
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
     * @return int $imodo_entrada
     */
    public function getModo_entrada(): int
    {
        return $this->imodo_entrada;
    }

    /**
     *
     * @param int $imodo_entrada
     */
    public function setModo_entrada(int $imodo_entrada): void
    {
        $this->imodo_entrada = $imodo_entrada;
    }

    /**
     *
     * @return array|stdClass|null $json_prot_origen
     */
    public function getJson_prot_origen(): array|stdClass|null
    {
        return $this->json_prot_origen;
    }

    /**
     *
     * @param stdClass|array|null $json_prot_origen
     */
    public function setJson_prot_origen(stdClass|array|null $json_prot_origen = null): void
    {
        $this->json_prot_origen = $json_prot_origen;
    }

    /**
     *
     * @return string $sasunto_entrada
     */
    public function getAsunto_entrada(): string
    {
        return $this->sasunto_entrada;
    }

    /**
     *
     * @param string $sasunto_entrada
     */
    public function setAsunto_entrada(string $sasunto_entrada): void
    {
        $this->sasunto_entrada = $sasunto_entrada;
    }

    /**
     *
     * @return array|stdClass|null $json_prot_ref
     */
    public function getJson_prot_ref(): array|stdClass|null
    {
        return $this->json_prot_ref;
    }

    /**
     *
     * @param stdClass|array|null $json_prot_ref
     */
    public function setJson_prot_ref(stdClass|array|null $json_prot_ref = null): void
    {
        $this->json_prot_ref = $json_prot_ref;
    }

    /**
     *
     * @return int|null $iponente
     */
    public function getPonente(): ?int
    {
        return $this->iponente;
    }

    /**
     *
     * @param int|null $iponente
     */
    public function setPonente(?int $iponente = null): void
    {
        $this->iponente = $iponente;
    }

    /**
     *
     * @return array|null $a_resto_oficinas
     */
    public function getResto_oficinas(): array|null
    {
        return $this->a_resto_oficinas;
    }

    /**
     *
     * @param array|null $a_resto_oficinas
     */
    public function setResto_oficinas(array $a_resto_oficinas = null): void
    {
        $this->a_resto_oficinas = $a_resto_oficinas;
    }

    /**
     *
     * @return string|null $sasunto
     */
    public function getAsuntoDB(): ?string
    {
        return $this->sasunto;
    }

    /**
     *
     * @param string|null $sasunto
     */
    public function setAsunto(?string $sasunto = null): void
    {
        $this->sasunto = $sasunto;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_entrada
     */
    public function getF_entrada(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_entrada ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_entrada
     */
    public function setF_entrada(DateTimeLocal|null $df_entrada = null): void
    {
        $this->df_entrada = $df_entrada;
    }

    /**
     *
     * @return string|null $sdetalle
     */
    public function getDetalleDB(): ?string
    {
        return $this->sdetalle;
    }

    /**
     *
     * @param string|null $sdetalle
     */
    public function setDetalle(?string $sdetalle = null): void
    {
        $this->sdetalle = $sdetalle;
    }

    /**
     *
     * @return int|null $icategoria
     */
    public function getCategoria(): ?int
    {
        return $this->icategoria;
    }

    /**
     *
     * @param int|null $icategoria
     */
    public function setCategoria(?int $icategoria = null): void
    {
        $this->icategoria = $icategoria;
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
     * @return DateTimeLocal|NullDateTimeLocal|null $df_contestar
     */
    public function getF_contestar(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_contestar ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_contestar
     */
    public function setF_contestar(DateTimeLocal|null $df_contestar = null): void
    {
        $this->df_contestar = $df_contestar;
    }

    /**
     *
     * @return bool|null $bbypass
     */
    public function isBypass(): ?bool
    {
        return $this->bbypass;
    }

    /**
     *
     * @param bool|null $bbypass
     */
    public function setBypass(?bool $bbypass = null): void
    {
        $this->bbypass = $bbypass;
    }

    /**
     *
     * @return int|null $iestado
     */
    public function getEstado(): ?int
    {
        return $this->iestado;
    }

    /**
     *
     * @param int|null $iestado
     */
    public function setEstado(?int $iestado = null): void
    {
        $this->iestado = $iestado;
    }

    /**
     *
     * @return string|null $sanulado
     */
    public function getAnulado(): ?string
    {
        return $this->sanulado;
    }

    /**
     *
     * @param string|null $sanulado
     */
    public function setAnulado(?string $sanulado = null): void
    {
        $this->sanulado = $sanulado;
    }

    /**
     *
     * @return int|null $iencargado
     */
    public function getEncargado(): ?int
    {
        return $this->iencargado;
    }

    /**
     *
     * @param int|null $iencargado
     */
    public function setEncargado(?int $iencargado = null): void
    {
        $this->iencargado = $iencargado;
    }

    /**
     *
     * @return array|stdClass|null $json_visto
     */
    public function getJson_visto(): array|stdClass|null
    {
        return $this->json_visto;
    }

    /**
     *
     * @param stdClass|array|null $json_visto
     */
    public function setJson_visto(stdClass|array|null $json_visto = null): void
    {
        $this->json_visto = $json_visto;
    }

    /**
     *
     * @return int|null $iid_entrada_compartida
     */
    public function getId_entrada_compartida(): ?int
    {
        return $this->iid_entrada_compartida;
    }

    /**
     *
     * @param int|null $iid_entrada_compartida
     */
    public function setId_entrada_compartida(?int $iid_entrada_compartida = null): void
    {
        $this->iid_entrada_compartida = $iid_entrada_compartida;
    }
}