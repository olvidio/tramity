<?php

namespace expedientes\model;

use core\ConfigGlobal;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use usuarios\model\entity\Cargo;
use web\Hash;


class ExpedienteReunionLista
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
        $oFormatoLista->setColumnaModVisible(TRUE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);
        $oFormatoLista->setTxtColumnaVer(_("revisar"));
        $oFormatoLista->setTxtColumnaMod(_("fecha"));
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $a_cosas = ['filtro' => $this->filtro];
            $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $oFormatoLista->setPaginaNueva($pagina_nueva);
        }

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

        //pendientes de mi firma, con fecha de reunión
        $this->aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
        $this->aWhere['f_reunion'] = 'x';
        $this->aOperador['f_reunion'] = 'IS NOT NULL';

        //pendientes de mi firma
        $aWhereFirma = [
            'id_cargo' => ConfigGlobal::role_id_cargo(),
            'tipo' => Firma::TIPO_VOTO,
            'valor' => 'x',
        ];
        $aOperadorFirma = [
            'valor' => 'IS NULL',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmasNull = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);

        // Sumar los firmados, pero no OK
        $aWhereFirma = [
            'id_cargo' => ConfigGlobal::role_id_cargo(),
            'tipo' => Firma::TIPO_VOTO,
            'valor' => Firma::V_VISTO . ',' . Firma::V_ESPERA . ',' . Firma::V_D_ESPERA,
        ];
        $aOperadorFirma = [
            'valor' => 'IN',
        ];
        $cFirmasVisto = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
        $cFirmas = array_merge($cFirmasNull, $cFirmasVisto);
        $a_expedientes = [];
        $a_expedientes_espera = [];
        $a_exp_reunion_falta_mi_firma = [];
        foreach ($cFirmas as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $orden_tramite = $oFirma->getOrden_tramite();
            // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
            if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                continue;
            }

            if ($oFirma->getValor() === Firma::V_D_ESPERA) {
                $a_expedientes_espera[] = $id_expediente;
            } else {
                $a_exp_reunion_falta_mi_firma[] = $id_expediente;
            }
            $a_expedientes[] = $id_expediente;
        }
        if (!empty($a_expedientes)) {
            $this->aWhere['id_expediente'] = implode(',', $a_expedientes);
            $this->aOperador['id_expediente'] = 'IN';
        } else {
            // para que no salga nada pongo
            $this->aWhere = [];
        }

        //////// mirar los que falta alguna firma para marcarlos en color /////////
        $gesFirmas = new GestorFirma();
        $a_exp_reunion_falta_firma = $gesFirmas->faltaFirmarReunion();

        //que tengan de mi firma, independiente de firmado o no
        $cFirmas = $gesFirmas->getFirmasReunion(ConfigGlobal::role_id_cargo());
        $a_expedientes = [];
        foreach ($cFirmas as $oFirma) {
            $id_expediente = $oFirma->getId_expediente();
            $orden_tramite = $oFirma->getOrden_tramite();
            // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
            if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                continue;
            }

            $a_expedientes[] = $id_expediente;
        }
        if (!empty($a_expedientes)) {
            $this->aWhere['id_expediente'] = implode(',', $a_expedientes);
            $this->aOperador['id_expediente'] = 'IN';
        } else {
            // para que no salga nada pongo
            $this->aWhere = [];
        }

        $oExpedientesDeColor->setExpedientesEspera($a_expedientes_espera);
        $oExpedientesDeColor->setExpReunionFaltaFirma($a_exp_reunion_falta_firma);
        $oExpedientesDeColor->setExpReunionFaltaMiFirma($a_exp_reunion_falta_mi_firma);

        return $oExpedientesDeColor;
    }

}