<?php

namespace expedientes\model;

use core\ConfigGlobal;
use tramites\domain\entity\Firma;
use tramites\domain\repositories\FirmaRepository;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;


class ExpedienteParaFirmarLista
{
    private string $filtro;
    private array $aWhere;
    private array $aOperador;

    public function __construct(string $filtro)
    {
        $this->filtro = $filtro;
    }

    public function mostrarTabla(): void
    {
        $oExpedientesDeColor = $this->setCondicion();
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(1);
        $oFormatoLista->setTxtColumnaVer(_("revisar"));
        $oFormatoLista->setColumnaModVisible(FALSE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);
        /*
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $a_cosas = ['filtro' => $this->filtro];
            $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $oFormatoLista->setPaginaNueva($pagina_nueva);
        }
        */

        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);
        } else {
            $cExpedientes = [];
        }
        $oExpedienteLista = new ExpedienteLista($cExpedientes, $oFormatoLista, $oExpedientesDeColor);
        $oExpedienteLista->setFiltro($this->filtro);

        $oExpedienteLista->mostrarTabla();
    }

    public function getNumero()
    {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);
            $num = count($cExpedientes);
        } else {
            $num = '';
        }
        return $num;
    }

    public function setCondicion(): ExpedientesDeColor
    {
        $this->aWhere = [];
        $this->aOperador = [];
        $oExpedientesDeColor = new ExpedientesDeColor();

        // Quito los permanentes_cl (de momento para los ctr)
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR ) {
            $this->aWhere['vida'] = Expediente::VIDA_PERMANENTE;
            $this->aOperador['vida'] = '!=';
        }
        // añadir las que requieren aclaración.
        if (ConfigGlobal::role_actual() === 'secretaria') {
            $a_tipos_acabado = [Expediente::ESTADO_NO,
                Expediente::ESTADO_DILATA,
                Expediente::ESTADO_RECHAZADO,
                Expediente::ESTADO_CIRCULANDO,
            ];
        } else {
            $a_tipos_acabado = [Expediente::ESTADO_CIRCULANDO,
                Expediente::ESTADO_ESPERA,
            ];
        }
        $this->aWhere['estado'] = implode(',', $a_tipos_acabado);
        $this->aOperador['estado'] = 'IN';
        //pendientes de mi firma, pero ya circulando
        $aWhereFirma['id_cargo'] = ConfigGlobal::role_id_cargo();
        $aWhereFirma['tipo'] = Firma::TIPO_VOTO;
        $aWhereFirma['valor'] = 'x';
        $aOperadorFirma['valor'] = 'IS NULL';
        $FirmaRepository = new FirmaRepository();
        $cFirmasNull = $FirmaRepository->getFirmas($aWhereFirma, $aOperadorFirma);
        // Sumar los firmados, pero no OK
        $aWhereFirma['valor'] = Firma::V_VISTO . ',' . Firma::V_ESPERA . ',' . Firma::V_D_ESPERA;
        $aOperadorFirma['valor'] = 'IN';
        $cFirmasVisto = $FirmaRepository->getFirmas($aWhereFirma, $aOperadorFirma);
        $cFirmas = array_merge($cFirmasNull, $cFirmasVisto);
        $a_expedientes = [];
        $a_expedientes_nuevos = [];
        foreach ($cFirmas as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $orden_tramite = $oFirma->getOrden_tramite();
            // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
            if ($_SESSION['oConfig']->getAmbito() !== Cargo::AMBITO_CTR ) {
                // Para los ctr NO. Puede ser que el d no haya firmado (nivel ponente) y
                // no se debe impedir firmar a otros (nivel oficiales).
                if (!$FirmaRepository->isAnteriorOK($id_expediente, $orden_tramite)) {
                    continue;
                }
            }

            $a_expedientes[] = $id_expediente;
            $tipo = $oFirma->getTipo();
            $valor = $oFirma->getValor();
            if ($tipo === Firma::TIPO_VOTO && empty($valor)) {
                $a_expedientes_nuevos[] = $id_expediente;
            }
        }
        //////// mirar los que se ha pedido aclaración para marcarlos en naranja /////////
        $aWhereFirma2 = ['tipo' => Firma::TIPO_ACLARACION,
            'valor' => Firma::V_A_NUEVA,
            'observ_creador' => 'x',
            'id_cargo' => ConfigGlobal::role_id_cargo(),
        ];
        $aOperadorFirma2 = ['observ_creador' => 'IS NULL'];
        $cFirmas2 = $FirmaRepository->getFirmas($aWhereFirma2, $aOperadorFirma2);
        $a_exp_peticion = [];
        foreach ($cFirmas2 as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $a_exp_peticion[] = $id_expediente;
        }
        //////// mirar los que ya se ha contestado para marcarlos en verde /////////
        $aWhereFirma2 = ['tipo' => Firma::TIPO_ACLARACION,
            'valor' => Firma::V_A_NUEVA,
            'observ_creador' => 'x',
            'id_cargo' => ConfigGlobal::role_id_cargo(),
        ];
        $aOperadorFirma2 = ['observ_creador' => 'IS NOT NULL'];
        $cFirmas2 = $FirmaRepository->getFirmas($aWhereFirma2, $aOperadorFirma2);
        $a_exp_respuesta = [];
        foreach ($cFirmas2 as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $a_exp_respuesta[] = $id_expediente;
        }

        //////// añadir las que requieren aclaración. //////////////////////////////
        $aWhereFirma = ['tipo' => Firma::TIPO_ACLARACION,
            'valor' => Firma::V_A_NUEVA,
            'observ_creador' => 'x',
            'id_cargo_creador' => ConfigGlobal::role_id_cargo(),
        ];
        $aOperadorFirma = ['observ_creador' => 'IS NULL'];
        // 31.5.2021 Que también el director de la oficina pueda responder.
        $CargoRepository = new CargoRepository();
        $a_cargos_oficina = $CargoRepository->getArrayCargosOficina(ConfigGlobal::role_id_oficina());
        if (ConfigGlobal::soy_dtor()) {
            $ids_cargos = array_keys($a_cargos_oficina);
            if (!empty($ids_cargos)) {
                $aWhereFirma['id_cargo_creador'] = implode(',', $ids_cargos);
                $aOperadorFirma['id_cargo_creador'] = 'IN';
            }
        }

        $cFirmas = $FirmaRepository->getFirmas($aWhereFirma, $aOperadorFirma);
        $a_exp_aclaracion = [];
        foreach ($cFirmas as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $a_exp_aclaracion[] = $id_expediente;
        }
        // sumar los dos: nuevos + aclaraciones.
        $a_exp_suma = array_merge($a_expedientes, $a_exp_aclaracion);
        if (!empty($a_exp_suma)) {
            $this->aWhere['id_expediente'] = implode(',', $a_exp_suma);
            $this->aOperador['id_expediente'] = 'IN';
        } else {
            // para que no salga nada pongo
            $this->aWhere = [];
        }

        $oExpedientesDeColor->setExpedientesNuevos($a_expedientes_nuevos);
        $oExpedientesDeColor->setExpPeticion($a_exp_peticion);
        $oExpedientesDeColor->setExpRespuesta($a_exp_respuesta);
        $oExpedientesDeColor->setExpAclaracion($a_exp_aclaracion);

        return $oExpedientesDeColor;
    }

}