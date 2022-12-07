<?php

namespace pendientes\domain\entity;

use web\DateTimeLocal;
use function core\is_true;

/**
 * Clase que implementa la entidad pendientes
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PendienteDB
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_pendiente de PendienteDB
     *
     * @var int
     */
    private int $iid_pendiente;
    /**
     * Asunto de PendienteDB
     *
     * @var string
     */
    private string $sasunto;
    /**
     * Status de PendienteDB
     *
     * @var string
     */
    private string $sstatus;
    /**
     * F_acabado de PendienteDB
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_acabado = null;
    /**
     * F_plazo de PendienteDB
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_plazo = null;
    /**
     * Ref_mas de PendienteDB
     *
     * @var string|null
     */
    private ?string $sref_mas = null;
    /**
     * Observ de PendienteDB
     *
     * @var string|null
     */
    private ?string $sobserv = null;
    /**
     * Encargado de PendienteDB
     *
     * @var string|null
     */
    private ?string $sencargado = null;
    /**
     * Cancilleria de PendienteDB
     *
     * @var bool
     */
    private bool $bcancilleria;
    /**
     * Visibilidad de PendienteDB
     *
     * @var int|null
     */
    private ?int $ivisibilidad = null;
    /**
     * Detalle de PendienteDB
     *
     * @var string|null
     */
    private ?string $sdetalle = null;
    /**
     * Pendiente_con de PendienteDB
     *
     * @var string|null
     */
    private ?string $spendiente_con = null;
    /**
     * Etiquetas de PendienteDB
     *
     * @var string|null
     */
    private ?string $setiquetas = null;
    /**
     * Oficinas de PendienteDB
     *
     * @var string|null
     */
    private ?string $soficinas = null;
    /**
     * Id_oficina de PendienteDB
     *
     * @var int
     */
    private int $iid_oficina;
    /**
     * Rrule de PendienteDB
     *
     * @var string|null
     */
    private ?string $srrule = null;
    /**
     * F_inicio de PendienteDB
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_inicio = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return PendienteDB
     */
    public function setAllAttributes(array $aDatos): PendienteDB
    {
        if (array_key_exists('id_pendiente', $aDatos)) {
            $this->setId_pendiente($aDatos['id_pendiente']);
        }
        if (array_key_exists('asunto', $aDatos)) {
            $this->setAsunto($aDatos['asunto']);
        }
        if (array_key_exists('status', $aDatos)) {
            $this->setStatus($aDatos['status']);
        }
        if (array_key_exists('f_acabado', $aDatos)) {
            $this->setF_acabado($aDatos['f_acabado']);
        }
        if (array_key_exists('f_plazo', $aDatos)) {
            $this->setF_plazo($aDatos['f_plazo']);
        }
        if (array_key_exists('ref_mas', $aDatos)) {
            $this->setRef_mas($aDatos['ref_mas']);
        }
        if (array_key_exists('observ', $aDatos)) {
            $this->setObserv($aDatos['observ']);
        }
        if (array_key_exists('encargado', $aDatos)) {
            $this->setEncargado($aDatos['encargado']);
        }
        if (array_key_exists('cancilleria', $aDatos)) {
            $this->setCancilleria(is_true($aDatos['cancilleria']));
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        if (array_key_exists('detalle', $aDatos)) {
            $this->setDetalle($aDatos['detalle']);
        }
        if (array_key_exists('pendiente_con', $aDatos)) {
            $this->setPendiente_con($aDatos['pendiente_con']);
        }
        if (array_key_exists('etiquetas', $aDatos)) {
            $this->setEtiquetas($aDatos['etiquetas']);
        }
        if (array_key_exists('oficinas', $aDatos)) {
            $this->setOficinas($aDatos['oficinas']);
        }
        if (array_key_exists('id_oficina', $aDatos)) {
            $this->setId_oficina($aDatos['id_oficina']);
        }
        if (array_key_exists('rrule', $aDatos)) {
            $this->setRrule($aDatos['rrule']);
        }
        if (array_key_exists('f_inicio', $aDatos)) {
            $this->setF_inicio($aDatos['f_inicio']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_pendiente
     */
    public function getId_pendiente(): int
    {
        return $this->iid_pendiente;
    }

    /**
     *
     * @param int $iid_pendiente
     */
    public function setId_pendiente(int $iid_pendiente): void
    {
        $this->iid_pendiente = $iid_pendiente;
    }

    /**
     *
     * @return string $sasunto
     */
    public function getAsunto(): string
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
     * @return string $sstatus
     */
    public function getStatus(): string
    {
        return $this->sstatus;
    }

    /**
     *
     * @param string $sstatus
     */
    public function setStatus(string $sstatus): void
    {
        $this->sstatus = $sstatus;
    }

    /**
     *
     * @return DateTimeLocal|null $df_acabado
     */
    public function getF_acabado(): DateTimeLocal|null
    {
        return $this->df_acabado;
    }

    /**
     *
     * @param DateTimeLocal|null $df_acabado
     */
    public function setF_acabado(DateTimeLocal|null $df_acabado = null): void
    {
        $this->df_acabado = $df_acabado;
    }

    /**
     *
     * @return DateTimeLocal|null $df_plazo
     */
    public function getF_plazo(): DateTimeLocal|null
    {
        return $this->df_plazo;
    }

    /**
     *
     * @param DateTimeLocal|null $df_plazo
     */
    public function setF_plazo(DateTimeLocal|null $df_plazo = null): void
    {
        $this->df_plazo = $df_plazo;
    }

    /**
     *
     * @return string|null $sref_mas
     */
    public function getRef_mas(): ?string
    {
        return $this->sref_mas;
    }

    /**
     *
     * @param string|null $sref_mas
     */
    public function setRef_mas(?string $sref_mas = null): void
    {
        $this->sref_mas = $sref_mas;
    }

    /**
     *
     * @return string|null $sobserv
     */
    public function getObserv(): ?string
    {
        return $this->sobserv;
    }

    /**
     *
     * @param string|null $sobserv
     */
    public function setObserv(?string $sobserv = null): void
    {
        $this->sobserv = $sobserv;
    }

    /**
     *
     * @return string|null $sencargado
     */
    public function getEncargado(): ?string
    {
        return $this->sencargado;
    }

    /**
     *
     * @param string|null $sencargado
     */
    public function setEncargado(?string $sencargado = null): void
    {
        $this->sencargado = $sencargado;
    }

    /**
     *
     * @return bool $bcancilleria
     */
    public function isCancilleria(): bool
    {
        return $this->bcancilleria;
    }

    /**
     *
     * @param bool $bcancilleria
     */
    public function setCancilleria(bool $bcancilleria): void
    {
        $this->bcancilleria = $bcancilleria;
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
     * @return string|null $sdetalle
     */
    public function getDetalle(): ?string
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
     * @return string|null $spendiente_con
     */
    public function getPendiente_con(): ?string
    {
        return $this->spendiente_con;
    }

    /**
     *
     * @param string|null $spendiente_con
     */
    public function setPendiente_con(?string $spendiente_con = null): void
    {
        $this->spendiente_con = $spendiente_con;
    }

    /**
     *
     * @return string|null $setiquetas
     */
    public function getEtiquetas(): ?string
    {
        return $this->setiquetas;
    }

    /**
     *
     * @param string|null $setiquetas
     */
    public function setEtiquetas(?string $setiquetas = null): void
    {
        $this->setiquetas = $setiquetas;
    }

    /**
     *
     * @return string|null $soficinas
     */
    public function getOficinas(): ?string
    {
        return $this->soficinas;
    }

    /**
     *
     * @param string|null $soficinas
     */
    public function setOficinas(?string $soficinas = null): void
    {
        $this->soficinas = $soficinas;
    }

    /**
     *
     * @return int $iid_oficina
     */
    public function getId_oficina(): int
    {
        return $this->iid_oficina;
    }

    /**
     *
     * @param int $iid_oficina
     */
    public function setId_oficina(int $iid_oficina): void
    {
        $this->iid_oficina = $iid_oficina;
    }

    /**
     *
     * @return string|null $srrule
     */
    public function getRrule(): ?string
    {
        return $this->srrule;
    }

    /**
     *
     * @param string|null $srrule
     */
    public function setRrule(?string $srrule = null): void
    {
        $this->srrule = $srrule;
    }

    /**
     *
     * @return DateTimeLocal|null $df_inicio
     */
    public function getF_inicio(): DateTimeLocal|null
    {
        return $this->df_inicio;
    }

    /**
     *
     * @param DateTimeLocal|null $df_inicio
     */
    public function setF_inicio(DateTimeLocal|null $df_inicio = null): void
    {
        $this->df_inicio = $df_inicio;
    }

    public function setEtiquetasArray(array $aEtiquetas): void
    {
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        $etiquetas_csv = implode(",", $a_filter_etiquetas);

        $this->setiquetas = $etiquetas_csv;
    }

    public function setOficinasArray(array $aOficinas): void
    {
        $a_filter_oficinas = array_filter($aOficinas); // Quita los elementos vacíos y nulos.
        $oficinas_csv = implode(",", $a_filter_oficinas);

        $this->soficinas = $oficinas_csv;
    }


}