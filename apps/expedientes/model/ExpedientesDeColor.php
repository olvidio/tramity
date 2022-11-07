<?php

namespace expedientes\model;

class ExpedientesDeColor
{
   /**
     *
     * @var array
     */
    private array $expedientes_espera = [];
    /**
     *
     * @var array
     */
    private array $expedientes_nuevos = [];
    /**
     *
     * @var array
     */
    private array $exp_aclaracion = [];
    /**
     *
     * @var array
     */
    private array $exp_peticion = [];
    /**
     *
     * @var array
     */
    private array $exp_respuesta = [];

    /**
     *
     * @var array
     */
    private array $exp_reunion_falta_firma = [];

    /**
     *
     * @var array
     */
    private array $exp_reunion_falta_mi_firma = [];

    /**
     * @return array
     */
    public function getExpedientesEspera(): array
    {
        return $this->expedientes_espera;
    }

    /**
     * @param array $expedientes_espera
     */
    public function setExpedientesEspera(array $expedientes_espera): void
    {
        $this->expedientes_espera = $expedientes_espera;
    }

    /**
     * @return array
     */
    public function getExpedientesNuevos(): array
    {
        return $this->expedientes_nuevos;
    }

    /**
     * @param array $expedientes_nuevos
     */
    public function setExpedientesNuevos(array $expedientes_nuevos): void
    {
        $this->expedientes_nuevos = $expedientes_nuevos;
    }

    /**
     * @return array
     */
    public function getExpAclaracion(): array
    {
        return $this->exp_aclaracion;
    }

    /**
     * @param array $exp_aclaracion
     */
    public function setExpAclaracion(array $exp_aclaracion): void
    {
        $this->exp_aclaracion = $exp_aclaracion;
    }

    /**
     * @return array
     */
    public function getExpPeticion(): array
    {
        return $this->exp_peticion;
    }

    /**
     * @param array $exp_peticion
     */
    public function setExpPeticion(array $exp_peticion): void
    {
        $this->exp_peticion = $exp_peticion;
    }

    /**
     * @return array
     */
    public function getExpRespuesta(): array
    {
        return $this->exp_respuesta;
    }

    /**
     * @param array $exp_respuesta
     */
    public function setExpRespuesta(array $exp_respuesta): void
    {
        $this->exp_respuesta = $exp_respuesta;
    }

    /**
     * @return array
     */
    public function getExpReunionFaltaFirma(): array
    {
        return $this->exp_reunion_falta_firma;
    }

    /**
     * @param array $exp_reunion_falta_firma
     */
    public function setExpReunionFaltaFirma(array $exp_reunion_falta_firma): void
    {
        $this->exp_reunion_falta_firma = $exp_reunion_falta_firma;
    }

    /**
     * @return array
     */
    public function getExpReunionFaltaMiFirma(): array
    {
        return $this->exp_reunion_falta_mi_firma;
    }

    /**
     * @param array $exp_reunion_falta_mi_firma
     */
    public function setExpReunionFaltaMiFirma(array $exp_reunion_falta_mi_firma): void
    {
        $this->exp_reunion_falta_mi_firma = $exp_reunion_falta_mi_firma;
    }


}