<?php

namespace expedientes\model;

use core\ConfigGlobal;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepository;
use usuarios\domain\repositories\CargoRepository;
use web\Hash;


class ExpedientepermanentesClLista
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
        $a_cosas = ['filtro' => $this->filtro];
        $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(1);
        $oFormatoLista->setColumnaModVisible(FALSE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaNueva($pagina_nueva);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);

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

        $this->aWhere['vida'] = Expediente::VIDA_PERMANENTE;
        // solo los de la oficina:
        // posibles oficiales de la oficina:
        $a_cargos_oficina = [];
        $CargoRepository = new CargoRepository();
        $oCargo = $CargoRepository->findById(ConfigGlobal::role_id_cargo());
        if ($oCargo !== null) {
            $id_oficina = $oCargo->getId_oficina();
            $a_cargos_oficina = $CargoRepository->getArrayCargosOficina($id_oficina);
        }
        $a_cargos = [];
        foreach (array_keys($a_cargos_oficina) as $id_cargo) {
            $a_cargos[] = $id_cargo;
        }
        if (!empty($a_cargos)) {
            $this->aWhere['ponente'] = implode(',', $a_cargos);
            $this->aOperador['ponente'] = 'IN';
        } else {
            // para que no salga nada pongo
            $this->aWhere = [];
        }
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