<?php

namespace expedientes\model;

use core\ConfigGlobal;
use usuarios\domain\repositories\CargoRepository;


class ExpedienteArchivadosLista
{
    private string $filtro;
    private array $aWhere;
    private array $aOperador;
    /**
     * Condición añadida por las condiciones de búsqueda (período, etiquetas...)
     *
     * @var array
     */
    private array $aWhereADD = [];
    /**
     * Condición añadida por las condiciones de búsqueda (período, etiquetas...)
     *
     * @var array
     */
    private array $aOperadorADD = [];

    /**
     * Para mantener las condiciones del diálogo
     *
     * @var array
     */
    private array $condiciones_busqueda = [];


    public function __construct(string $filtro)
    {
        $this->filtro = $filtro;
    }

    public function mostrarTabla(): void
    {
        $this->setCondicion();
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(2);
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
        $oExpedienteLista->setCondicionesBusqueda($this->condiciones_busqueda);

        $oExpedienteLista->mostrarTabla();
    }

    public function setCondicion(): void
    {
        $this->aWhere = [];
        $this->aOperador = [];
        if (!empty($this->aWhereADD)) {
            $this->aWhere = $this->aWhereADD;
            if (!empty($this->aOperadorADD)) {
                $this->aOperador = $this->aOperadorADD;
            }
        }
        $this->aWhere['estado'] = Expediente::ESTADO_ARCHIVADO;
        // solo los de la oficina:
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
    }

    /**
     * @param array $condiciones_busqueda
     */
    public function setCondicionesBusqueda(array $condiciones_busqueda): void
    {
        $this->condiciones_busqueda = $condiciones_busqueda;
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

    /**
     * @return array
     */
    public function getAWhereADD(): array
    {
        return $this->aWhereADD;
    }

    /**
     * @param array $aWhereADD
     */
    public function setAWhereADD(array $aWhereADD): void
    {
        $this->aWhereADD = $aWhereADD;
    }

    /**
     * @return array
     */
    public function getAOperadorADD(): array
    {
        return $this->aOperadorADD;
    }

    /**
     * @param array $aOperadorADD
     */
    public function setAOperadorADD(array $aOperadorADD): void
    {
        $this->aOperadorADD = $aOperadorADD;
    }

}