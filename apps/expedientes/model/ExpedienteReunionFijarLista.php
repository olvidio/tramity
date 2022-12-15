<?php

namespace expedientes\model;

use core\ConfigGlobal;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepository;


class ExpedienteReunionFijarLista
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
        $this->setCondicion();
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/fecha_reunion.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(1);
        $oFormatoLista->setColumnaModVisible(TRUE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);
        $oFormatoLista->setTxtColumnaVer(_("revisar"));
        $oFormatoLista->setTxtColumnaMod(_("fecha"));
        /*
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $a_cosas = ['filtro' => $this->filtro];
            $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $oFormatoLista->setPaginaNueva($pagina_nueva);
        }
        */

        if (!empty($this->aWhere)) {
            $ExpedienteRepository = new ExpedienteRepository();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $ExpedienteRepository->getExpedientes($this->aWhere, $this->aOperador);
        } else {
            $cExpedientes = [];
        }
        $oExpedienteLista = new ExpedienteLista($cExpedientes, $oFormatoLista, new ExpedientesDeColor());
        $oExpedienteLista->setFiltro($this->filtro);

        $oExpedienteLista->mostrarTabla();
    }

    public function setCondicion(): void
    {
        $this->aWhere = [];
        $this->aOperador = [];
        $this->aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
        $this->aWhere['f_reunion'] = 'x';
        $this->aOperador['f_reunion'] = 'IS NULL';
    }

    public function getNumero(): ?int
    {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $ExpedienteRepository = new ExpedienteRepository();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $ExpedienteRepository->getExpedientes($this->aWhere, $this->aOperador);
            $num = count($cExpedientes);
        } else {
            $num = null;
        }
        return $num;
    }

}