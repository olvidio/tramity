<?php

namespace escritos\domain\entity;

use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\is_true;

/**
 * Clase que implementa la entidad escritos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EscritoDB
{

    /* CONSTANTES ----------------------------------------------------------------- */

    // tipo documento (igual que entradadocdb)
    public const TIPO_ETHERPAD = 1;
    public const TIPO_ETHERCALC = 2;
    public const TIPO_OTRO = 3;

    // ok
    public const OK_NO = 1;
    public const OK_OFICINA = 2;
    public const OK_SECRETARIA = 3;

    // visibilidad
    // USAR LAS DE ENTRADADB


    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Para que sea compatible com métodos de Entradas.
     *
     * @return int|null $icreador
     */
    public function getPonente(): ?int
    {
        return $this->icreador;
    }

    /**
     * Id_escrito de EscritoDB
     *
     * @var int
     */
    protected int $iid_escrito;
    /**
     * Json_prot_local de EscritoDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_prot_local = null;
    /**
     * Json_prot_destino de EscritoDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_prot_destino = null;
    /**
     * Json_prot_ref de EscritoDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_prot_ref = null;
    /**
     * Id_grupos de EscritoDB
     *
     * @var array|null
     */
    protected array|null $a_id_grupos = null;
    /**
     * Destinos de EscritoDB
     *
     * @var array|null
     */
    protected array|null $a_destinos = null;
    /**
     * Asunto de EscritoDB
     *
     * @var string
     */
    protected string $sasunto;
    /**
     * Detalle de EscritoDB
     *
     * @var string|null
     */
    protected string|null $sdetalle = null;
    /**
     * Creador de EscritoDB
     *
     * @var int|null
     */
    protected int|null $icreador = null;
    /**
     * Resto_oficinas de EscritoDB
     *
     * @var array|null
     */
    protected array|null $a_resto_oficinas = null;
    /**
     * Comentarios de EscritoDB
     *
     * @var string|null
     */
    protected string|null $scomentarios = null;
    /**
     * F_aprobacion de EscritoDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_aprobacion = null;
    /**
     * F_escrito de EscritoDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_escrito = null;
    /**
     * F_contestar de EscritoDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_contestar = null;
    /**
     * Categoria de EscritoDB
     *
     * @var int|null
     */
    protected int|null $icategoria = null;
    /**
     * Visibilidad de EscritoDB
     *
     * @var int|null
     */
    protected int|null $ivisibilidad = null;
    /**
     * Accion de EscritoDB
     *
     * @var int
     */
    protected int $iaccion;
    /**
     * Modo_envio de EscritoDB
     *
     * @var int
     */
    protected int $imodo_envio;
    /**
     * F_salida de EscritoDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_salida = null;
    /**
     * Ok de EscritoDB
     *
     * @var int|null
     */
    protected int|null $iok = null;
    /**
     * Tipo_doc de EscritoDB
     *
     * @var int|null
     */
    protected int|null $itipo_doc = null;
    /**
     * Anulado de EscritoDB
     *
     * @var bool|null
     */
    protected bool|null $banulado = null;
    /**
     * Descripcion de EscritoDB
     *
     * @var string|null
     */
    protected string|null $sdescripcion = null;
    /**
     * Visibilidad_dst de EscritoDB
     *
     * @var int|null
     */
    protected int|null $ivisibilidad_dst = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EscritoDB
     */
    public function setAllAttributes(array $aDatos): EscritoDB
    {
        if (array_key_exists('id_escrito', $aDatos)) {
            $this->setId_escrito($aDatos['id_escrito']);
        }
        if (array_key_exists('json_prot_local', $aDatos)) {
            $this->setJson_prot_local($aDatos['json_prot_local']);
        }
        if (array_key_exists('json_prot_destino', $aDatos)) {
            $this->setJson_prot_destino($aDatos['json_prot_destino']);
        }
        if (array_key_exists('json_prot_ref', $aDatos)) {
            $this->setJson_prot_ref($aDatos['json_prot_ref']);
        }
        if (array_key_exists('id_grupos', $aDatos)) {
            $this->setId_grupos($aDatos['id_grupos']);
        }
        if (array_key_exists('destinos', $aDatos)) {
            $this->setDestinos($aDatos['destinos']);
        }
        if (array_key_exists('asunto', $aDatos)) {
            $this->setAsunto($aDatos['asunto']);
        }
        if (array_key_exists('detalle', $aDatos)) {
            $this->setDetalle($aDatos['detalle']);
        }
        if (array_key_exists('creador', $aDatos)) {
            $this->setCreador($aDatos['creador']);
        }
        if (array_key_exists('resto_oficinas', $aDatos)) {
            $this->setResto_oficinas($aDatos['resto_oficinas']);
        }
        if (array_key_exists('comentarios', $aDatos)) {
            $this->setComentarios($aDatos['comentarios']);
        }
        if (array_key_exists('f_aprobacion', $aDatos)) {
            $this->setF_aprobacion($aDatos['f_aprobacion']);
        }
        if (array_key_exists('f_escrito', $aDatos)) {
            $this->setF_escrito($aDatos['f_escrito']);
        }
        if (array_key_exists('f_contestar', $aDatos)) {
            $this->setF_contestar($aDatos['f_contestar']);
        }
        if (array_key_exists('categoria', $aDatos)) {
            $this->setCategoria($aDatos['categoria']);
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        if (array_key_exists('accion', $aDatos)) {
            $this->setAccion($aDatos['accion']);
        }
        if (array_key_exists('modo_envio', $aDatos)) {
            $this->setModo_envio($aDatos['modo_envio']);
        }
        if (array_key_exists('f_salida', $aDatos)) {
            $this->setF_salida($aDatos['f_salida']);
        }
        if (array_key_exists('ok', $aDatos)) {
            $this->setOk($aDatos['ok']);
        }
        if (array_key_exists('tipo_doc', $aDatos)) {
            $this->setTipo_doc($aDatos['tipo_doc']);
        }
        if (array_key_exists('anulado', $aDatos)) {
            $this->setAnulado(is_true($aDatos['anulado']));
        }
        if (array_key_exists('descripcion', $aDatos)) {
            $this->setDescripcion($aDatos['descripcion']);
        }
        if (array_key_exists('visibilidad_dst', $aDatos)) {
            $this->setVisibilidad_dst($aDatos['visibilidad_dst']);
        }
        return $this;
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
     * @return array|stdClass|null $json_prot_local
     */
    public function getJson_prot_local(): array|stdClass|null
    {
        return $this->json_prot_local;
    }

    /**
     *
     * @param stdClass|array|null $json_prot_local
     */
    public function setJson_prot_local(stdClass|array|null $json_prot_local = null): void
    {
        $this->json_prot_local = $json_prot_local;
    }

    /**
     *
     * @return array|stdClass|null $json_prot_destino
     */
    public function getJson_prot_destino(): array|stdClass|null
    {
        return $this->json_prot_destino;
    }

    /**
     *
     * @param stdClass|array|null $json_prot_destino
     */
    public function setJson_prot_destino(stdClass|array|null $json_prot_destino = null): void
    {
        $this->json_prot_destino = $json_prot_destino;
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
     * @return array|null $a_id_grupos
     */
    public function getId_grupos(): array|null
    {
        return $this->a_id_grupos;
    }

    /**
     *
     * @param array|null $a_id_grupos
     */
    public function setId_grupos(array $a_id_grupos = null): void
    {
        $this->a_id_grupos = $a_id_grupos;
    }

    /**
     *
     * @return array|null $a_destinos
     */
    public function getDestinos(): array|null
    {
        return $this->a_destinos;
    }

    /**
     *
     * @param array|null $a_destinos
     */
    public function setDestinos(array $a_destinos = null): void
    {
        $this->a_destinos = $a_destinos;
    }

    /**
     *
     * @return string $sasunto
     */
    public function getAsuntoDB(): string
    {
        return $this->sasunto;
    }

    /**
     *
     * @param string $sasunto
     */
    public function setAsunto(string $sasunto): void
    {
        $this->sasunto = $sasunto;
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
     * @return string|null $scomentarios
     */
    public function getComentarios(): ?string
    {
        return $this->scomentarios;
    }

    /**
     *
     * @param string|null $scomentarios
     */
    public function setComentarios(?string $scomentarios = null): void
    {
        $this->scomentarios = $scomentarios;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_aprobacion
     */
    public function getF_aprobacion(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_aprobacion ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_aprobacion
     */
    public function setF_aprobacion(DateTimeLocal|null $df_aprobacion = null): void
    {
        $this->df_aprobacion = $df_aprobacion;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_escrito
     */
    public function getF_escrito(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_escrito ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_escrito
     */
    public function setF_escrito(DateTimeLocal|null $df_escrito = null): void
    {
        $this->df_escrito = $df_escrito;
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
     * @return int $iaccion
     */
    public function getAccion(): int
    {
        return $this->iaccion;
    }

    /**
     *
     * @param int $iaccion
     */
    public function setAccion(int $iaccion): void
    {
        $this->iaccion = $iaccion;
    }

    /**
     *
     * @return int $imodo_envio
     */
    public function getModo_envio(): int
    {
        return $this->imodo_envio;
    }

    /**
     *
     * @param int $imodo_envio
     */
    public function setModo_envio(int $imodo_envio): void
    {
        $this->imodo_envio = $imodo_envio;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_salida
     */
    public function getF_salida(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_salida ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_salida
     */
    public function setF_salida(DateTimeLocal|null $df_salida = null): void
    {
        $this->df_salida = $df_salida;
    }

    /**
     *
     * @return int|null $iok
     */
    public function getOk(): ?int
    {
        return $this->iok;
    }

    /**
     *
     * @param int|null $iok
     */
    public function setOk(?int $iok = null): void
    {
        $this->iok = $iok;
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

    /**
     *
     * @return string|null $sdescripcion
     */
    public function getDescripcion(): ?string
    {
        return $this->sdescripcion;
    }

    /**
     *
     * @param string|null $sdescripcion
     */
    public function setDescripcion(?string $sdescripcion = null): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     *
     * @return int|null $ivisibilidad_dst
     */
    public function getVisibilidad_dst(): ?int
    {
        return $this->ivisibilidad_dst;
    }

    /**
     *
     * @param int|null $ivisibilidad_dst
     */
    public function setVisibilidad_dst(?int $ivisibilidad_dst = null): void
    {
        $this->ivisibilidad_dst = $ivisibilidad_dst;
    }
}