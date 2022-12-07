<?php

namespace expedientes\model;

use core\ConfigGlobal;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;
use function core\is_true;


class ExpedienteCirculandoLista
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
        $oExpedienteLista = new ExpedienteLista($cExpedientes, $oFormatoLista, new ExpedientesDeColor());
        $oExpedienteLista->setFiltro($this->filtro);

        $oExpedienteLista->mostrarTabla();
    }

    public function setCondicion(): void
    {
        $this->aWhere = [];
        $this->aOperador = [];
        // Quito los permanentes_cl (de momento para los ctr)
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $this->aWhere['vida'] = Expediente::VIDA_PERMANENTE;
            $this->aOperador['vida'] = '!=';
        }
        if (ConfigGlobal::role_actual() === 'vcd') {
            $a_tipos_acabado = [Expediente::ESTADO_CIRCULANDO,
                Expediente::ESTADO_ESPERA,
            ];
            $this->aWhere['estado'] = implode(',', $a_tipos_acabado);
            $this->aOperador['estado'] = 'IN';
        } else {
            $this->aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
            unset($this->aOperador['estado']);
        }
        // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
        // para los centros, todos ven igual que el director
        if (is_true(ConfigGlobal::soy_dtor()) || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            // posibles oficiales de la oficina:
            $CargoRepository = new CargoRepository();
            $oCargo = $CargoRepository->findById(ConfigGlobal::role_id_cargo());
            $id_oficina = $oCargo->getId_oficina();
            $a_cargos_oficina = $CargoRepository->getArrayCargosOficina($id_oficina);
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
        } else {
            // solo los propios:
            $this->aWhere['ponente'] = ConfigGlobal::role_id_cargo();
        }

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

}