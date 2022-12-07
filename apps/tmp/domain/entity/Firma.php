<?php

namespace tmp\domain\entity;

use web\DateTimeLocal;

/**
 * Clase que implementa la entidad expediente_firmas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class Firma
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_item de Firma
     *
     * @var int
     */
    private int $iid_item;
    /**
     * Id_expediente de Firma
     *
     * @var int
     */
    private int $iid_expediente;
    /**
     * Id_tramite de Firma
     *
     * @var int
     */
    private int $iid_tramite;
    /**
     * Id_cargo_creador de Firma
     *
     * @var int
     */
    private int $iid_cargo_creador;
    /**
     * Cargo_tipo de Firma
     *
     * @var int
     */
    private int $icargo_tipo;
    /**
     * Id_cargo de Firma
     *
     * @var int
     */
    private int $iid_cargo;
    /**
     * Id_usuario de Firma
     *
     * @var int|null
     */
    private ?int $iid_usuario = null;
    /**
     * Orden_tramite de Firma
     *
     * @var int
     */
    private int $iorden_tramite;
    /**
     * Orden_oficina de Firma
     *
     * @var int|null
     */
    private ?int $iorden_oficina = null;
    /**
     * Tipo de Firma
     *
     * @var int
     */
    private int $itipo;
    /**
     * Valor de Firma
     *
     * @var int|null
     */
    private ?int $ivalor = null;
    /**
     * Observ_creador de Firma
     *
     * @var string|null
     */
    private ?string $sobserv_creador = null;
    /**
     * Observ de Firma
     *
     * @var string|null
     */
    private ?string $sobserv = null;
    /**
     * F_valor de Firma
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_valor = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * return Firma
     */
    public function setAllAttributes(array $aDatos): Firma
    {
        if (array_key_exists('id_item', $aDatos)) {
            $this->setId_item($aDatos['id_item']);
        }
        if (array_key_exists('id_expediente', $aDatos)) {
            $this->setId_expediente($aDatos['id_expediente']);
        }
        if (array_key_exists('id_tramite', $aDatos)) {
            $this->setId_tramite($aDatos['id_tramite']);
        }
        if (array_key_exists('id_cargo_creador', $aDatos)) {
            $this->setId_cargo_creador($aDatos['id_cargo_creador']);
        }
        if (array_key_exists('cargo_tipo', $aDatos)) {
            $this->setCargo_tipo($aDatos['cargo_tipo']);
        }
        if (array_key_exists('id_cargo', $aDatos)) {
            $this->setId_cargo($aDatos['id_cargo']);
        }
        if (array_key_exists('id_usuario', $aDatos)) {
            $this->setId_usuario($aDatos['id_usuario']);
        }
        if (array_key_exists('orden_tramite', $aDatos)) {
            $this->setOrden_tramite($aDatos['orden_tramite']);
        }
        if (array_key_exists('orden_oficina', $aDatos)) {
            $this->setOrden_oficina($aDatos['orden_oficina']);
        }
        if (array_key_exists('tipo', $aDatos)) {
            $this->setTipo($aDatos['tipo']);
        }
        if (array_key_exists('valor', $aDatos)) {
            $this->setValor($aDatos['valor']);
        }
        if (array_key_exists('observ_creador', $aDatos)) {
            $this->setObserv_creador($aDatos['observ_creador']);
        }
        if (array_key_exists('observ', $aDatos)) {
            $this->setObserv($aDatos['observ']);
        }
        if (array_key_exists('f_valor', $aDatos)) {
            $this->setF_valor($aDatos['f_valor'], FALSE);
        }
        return $this;
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
     * @param int $iid_expediente
     */
    public function setId_expediente(int $iid_expediente): void
    {
        $this->iid_expediente = $iid_expediente;
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
     * @param int $iid_cargo_creador
     */
    public function setId_cargo_creador(int $iid_cargo_creador): void
    {
        $this->iid_cargo_creador = $iid_cargo_creador;
    }

    /**
     *
     * @param int $icargo_tipo
     */
    public function setCargo_tipo(int $icargo_tipo): void
    {
        $this->icargo_tipo = $icargo_tipo;
    }

    /**
     *
     * @param int $iid_cargo
     */
    public function setId_cargo(int $iid_cargo): void
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     *
     * @param int|null $iid_usuario
     */
    public function setId_usuario(?int $iid_usuario = null): void
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     *
     * @param int $iorden_tramite
     */
    public function setOrden_tramite(int $iorden_tramite): void
    {
        $this->iorden_tramite = $iorden_tramite;
    }

    /**
     *
     * @param int|null $iorden_oficina
     */
    public function setOrden_oficina(?int $iorden_oficina = null): void
    {
        $this->iorden_oficina = $iorden_oficina;
    }

    /**
     *
     * @param int $itipo
     */
    public function setTipo(int $itipo): void
    {
        $this->itipo = $itipo;
    }

    /**
     *
     * @param int|null $ivalor
     */
    public function setValor(?int $ivalor = null): void
    {
        $this->ivalor = $ivalor;
    }

    /**
     *
     * @param string|null $sobserv_creador
     */
    public function setObserv_creador(?string $sobserv_creador = null): void
    {
        $this->sobserv_creador = $sobserv_creador;
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
     * @param DateTimeLocal|null $df_valor
     */
    public function setF_valor(DateTimeLocal|null $df_valor = 'null'): void
    {
        $this->df_valor = $df_valor;
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
     * @return int $iid_expediente
     */
    public function getId_expediente(): int
    {
        return $this->iid_expediente;
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
     * @return int $iid_cargo_creador
     */
    public function getId_cargo_creador(): int
    {
        return $this->iid_cargo_creador;
    }

    /**
     *
     * @return int $icargo_tipo
     */
    public function getCargo_tipo(): int
    {
        return $this->icargo_tipo;
    }

    /**
     *
     * @return int $iid_cargo
     */
    public function getId_cargo(): int
    {
        return $this->iid_cargo;
    }

    /**
     *
     * @return int|null $iid_usuario
     */
    public function getId_usuario(): ?int
    {
        return $this->iid_usuario;
    }

    /**
     *
     * @return int $iorden_tramite
     */
    public function getOrden_tramite(): int
    {
        return $this->iorden_tramite;
    }

    /**
     *
     * @return int|null $iorden_oficina
     */
    public function getOrden_oficina(): ?int
    {
        return $this->iorden_oficina;
    }

    /**
     *
     * @return int $itipo
     */
    public function getTipo(): int
    {
        return $this->itipo;
    }

    /**
     *
     * @return int|null $ivalor
     */
    public function getValor(): ?int
    {
        return $this->ivalor;
    }

    /**
     *
     * @return string|null $sobserv_creador
     */
    public function getObserv_creador(): ?string
    {
        return $this->sobserv_creador;
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
     * @return DateTimeLocal|null $df_valor
     */
    public function getF_valor(): DateTimeLocal|null
    {
        return $this->df_valor;
    }
}