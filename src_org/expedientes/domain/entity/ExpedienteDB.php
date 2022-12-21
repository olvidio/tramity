<?php

namespace expedientes\domain\entity;

use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;

/**
 * Clase que implementa la entidad expedientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class ExpedienteDB
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_expediente de ExpedienteDB
     *
     * @var int
     */
    protected int $iid_expediente;
    /**
     * Id_tramite de ExpedienteDB
     *
     * @var int
     */
    protected int $iid_tramite;
    /**
     * Ponente de ExpedienteDB
     *
     * @var int|null
     */
    protected int|null $iponente = null;
    /**
     * Resto_oficinas de ExpedienteDB
     *
     * @var array|null
     */
    protected array|null $a_resto_oficinas = null;
    /**
     * Asunto de ExpedienteDB
     *
     * @var string|null
     */
    protected string|null $sasunto = null;
    /**
     * Entradilla de ExpedienteDB
     *
     * @var string|null
     */
    protected string|null $sentradilla = null;
    /**
     * Comentarios de ExpedienteDB
     *
     * @var string|null
     */
    protected string|null $scomentarios = null;
    /**
     * Prioridad de ExpedienteDB
     *
     * @var int
     */
    protected int $iprioridad;
    /**
     * Json_antecedentes de ExpedienteDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_antecedentes = null;
    /**
     * Json_acciones de ExpedienteDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_acciones = null;
    /**
     * Etiquetas de ExpedienteDB
     *
     * @var array|null
     */
    protected array|null $a_etiquetas = null;
    /**
     * F_contestar de ExpedienteDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_contestar = null;
    /**
     * Estado de ExpedienteDB
     *
     * @var int
     */
    protected int $iestado;
    /**
     * F_ini_circulacion de ExpedienteDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_ini_circulacion = null;
    /**
     * F_reunion de ExpedienteDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_reunion = null;
    /**
     * F_aprobacion de ExpedienteDB
     *
     * @var DateTimeLocal|null
     */
    protected DateTimeLocal|null $df_aprobacion = null;
    /**
     * Vida de ExpedienteDB
     *
     * @var int|null
     */
    protected int|null $ivida = null;
    /**
     * Json_preparar de ExpedienteDB
     *
     * @var array|stdClass|null
     */
    protected array|stdClass|null $json_preparar = null;
    /**
     * Firmas_oficina de ExpedienteDB
     *
     * @var array|null
     */
    protected array|null $a_firmas_oficina = null;
    /**
     * Visibilidad de ExpedienteDB
     *
     * @var int|null
     */
    protected int|null $ivisibilidad = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return ExpedienteDB
     */
    public function setAllAttributes(array $aDatos): ExpedienteDB
    {
        if (array_key_exists('id_expediente', $aDatos)) {
            $this->setId_expediente($aDatos['id_expediente']);
        }
        if (array_key_exists('id_tramite', $aDatos)) {
            $this->setId_tramite($aDatos['id_tramite']);
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
        if (array_key_exists('entradilla', $aDatos)) {
            $this->setEntradilla($aDatos['entradilla']);
        }
        if (array_key_exists('comentarios', $aDatos)) {
            $this->setComentarios($aDatos['comentarios']);
        }
        if (array_key_exists('prioridad', $aDatos)) {
            $this->setPrioridad($aDatos['prioridad']);
        }
        if (array_key_exists('json_antecedentes', $aDatos)) {
            $this->setJson_antecedentes($aDatos['json_antecedentes']);
        }
        if (array_key_exists('json_acciones', $aDatos)) {
            $this->setJson_acciones($aDatos['json_acciones']);
        }
        if (array_key_exists('etiquetas', $aDatos)) {
            $this->setEtiquetas($aDatos['etiquetas']);
        }
        if (array_key_exists('f_contestar', $aDatos)) {
            $this->setF_contestar($aDatos['f_contestar']);
        }
        if (array_key_exists('estado', $aDatos)) {
            $this->setEstado($aDatos['estado']);
        }
        if (array_key_exists('f_ini_circulacion', $aDatos)) {
            $this->setF_ini_circulacion($aDatos['f_ini_circulacion']);
        }
        if (array_key_exists('f_reunion', $aDatos)) {
            $this->setF_reunion($aDatos['f_reunion']);
        }
        if (array_key_exists('f_aprobacion', $aDatos)) {
            $this->setF_aprobacion($aDatos['f_aprobacion']);
        }
        if (array_key_exists('vida', $aDatos)) {
            $this->setVida($aDatos['vida']);
        }
        if (array_key_exists('json_preparar', $aDatos)) {
            $this->setJson_preparar($aDatos['json_preparar']);
        }
        if (array_key_exists('firmas_oficina', $aDatos)) {
            $this->setFirmas_oficina($aDatos['firmas_oficina']);
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        return $this;
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

    /**
     *
     * @return int $iid_tramite
     */
    public function getId_tramite(): int
    {
        return $this->iid_tramite;
    }

    /**
     *
     * @param int $iid_tramite
     */
    public function setId_tramite(int $iid_tramite): void
    {
        $this->iid_tramite = $iid_tramite;
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
    public function getAsunto(): ?string
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
     * @return string|null $sentradilla
     */
    public function getEntradilla(): ?string
    {
        return $this->sentradilla;
    }

    /**
     *
     * @param string|null $sentradilla
     */
    public function setEntradilla(?string $sentradilla = null): void
    {
        $this->sentradilla = $sentradilla;
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
     * @return int $iprioridad
     */
    public function getPrioridad(): int
    {
        return $this->iprioridad;
    }

    /**
     *
     * @param int $iprioridad
     */
    public function setPrioridad(int $iprioridad): void
    {
        $this->iprioridad = $iprioridad;
    }

    /**
     *
     * @return array|stdClass|null $json_antecedentes
     */
    public function getJson_antecedentes(): array|stdClass|null
    {
        return $this->json_antecedentes;
    }

    /**
     *
     * @param stdClass|array|null $json_antecedentes
     */
    public function setJson_antecedentes(stdClass|array|null $json_antecedentes = null): void
    {
        $this->json_antecedentes = $json_antecedentes;
    }

    /**
     *
     * @return array|stdClass|null $json_acciones
     */
    public function getJson_acciones(): array|stdClass|null
    {
        return $this->json_acciones;
    }

    /**
     *
     * @param stdClass|array|null $json_acciones
     */
    public function setJson_acciones(stdClass|array|null $json_acciones = null): void
    {
        $this->json_acciones = $json_acciones;
    }

    /**
     *
     * @return array|null $a_etiquetas
     */
    public function getEtiquetas(): array|null
    {
        return $this->a_etiquetas;
    }

    /**
     *
     * @param array|null $a_etiquetas
     */
    public function setEtiquetas(array $a_etiquetas = null): void
    {
        $this->a_etiquetas = $a_etiquetas;
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
     * @return int $iestado
     */
    public function getEstado(): int
    {
        return $this->iestado;
    }

    /**
     *
     * @param int $iestado
     */
    public function setEstado(int $iestado): void
    {
        $this->iestado = $iestado;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_ini_circulacion
     */
    public function getF_ini_circulacion(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_ini_circulacion ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_ini_circulacion
     */
    public function setF_ini_circulacion(DateTimeLocal|null $df_ini_circulacion = null): void
    {
        $this->df_ini_circulacion = $df_ini_circulacion;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_reunion
     */
    public function getF_reunion(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_reunion ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_reunion
     */
    public function setF_reunion(DateTimeLocal|null $df_reunion = null): void
    {
        $this->df_reunion = $df_reunion;
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
     * @return int|null $ivida
     */
    public function getVida(): ?int
    {
        return $this->ivida;
    }

    /**
     *
     * @param int|null $ivida
     */
    public function setVida(?int $ivida = null): void
    {
        $this->ivida = $ivida;
    }

    /**
     *
     * @return array|stdClass|null $json_preparar
     */
    public function getJson_preparar(): array|stdClass|null
    {
        return $this->json_preparar;
    }

    /**
     *
     * @param stdClass|array|null $json_preparar
     */
    public function setJson_preparar(stdClass|array|null $json_preparar = null): void
    {
        $this->json_preparar = $json_preparar;
    }

    /**
     *
     * @return array|null $a_firmas_oficina
     */
    public function getFirmas_oficina(): array|null
    {
        return $this->a_firmas_oficina;
    }

    /**
     *
     * @param array|null $a_firmas_oficina
     */
    public function setFirmas_oficina(array $a_firmas_oficina = null): void
    {
        $this->a_firmas_oficina = $a_firmas_oficina;
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
}