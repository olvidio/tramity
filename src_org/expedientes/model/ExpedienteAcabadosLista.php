<?php

namespace expedientes\model;

use core\ConfigGlobal;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepository;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;


class ExpedienteAcabadosLista
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
        $pagina_mod = ConfigGlobal::getWeb() . '/src/expedientes/controller/expediente_distribuir.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/src/expedientes/controller/expediente_distribuir.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(5);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaModVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $oFormatoLista->setColumnaVerVisible(FALSE);
            /*
                $a_cosas = ['filtro' => $this->filtro];
                $pagina_nueva = Hash::link('src/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
                $oFormatoLista->setPaginaNueva($pagina_nueva);
            */
        }

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
        // Ahora (16/12/2020) todos los de la oficina si es director.
        // sino los encargados (salto a otro estado)
        // para los centros buscar en ESTADO_ACABADO
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $this->aWhere['estado'] = Expediente::ESTADO_ACABADO;
        } else {
            $this->aWhere['estado'] = Expediente::ESTADO_ACABADO_SECRETARIA;
        }
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

    public function getNumero()
    {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $ExpedienteRepository = new ExpedienteRepository();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $ExpedienteRepository->getExpedientes($this->aWhere, $this->aOperador);
            $num = count($cExpedientes);
        } else {
            $num = '';
        }
        return $num;
    }

}