<?php

namespace expedientes\model;

use core\ConfigGlobal;
use web\Hash;


class ExpedienteDistribuirLista
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
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(5);
        $oFormatoLista->setColumnaModVisible(TRUE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(TRUE);
        $oFormatoLista->setPaginaNueva($pagina_nueva);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);

        $gesExpedientes = new GestorExpediente();
        $this->aWhere['_ordre'] = 'id_expediente';
        $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);
        $oExpedienteLista = new ExpedienteLista($cExpedientes, $oFormatoLista, new ExpedientesDeColor());
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

    public function setCondicion(): void
    {
        $this->aWhere = [];
        $this->aOperador = [];
        $a_tipos_acabado = [Expediente::ESTADO_ACABADO,
            Expediente::ESTADO_NO,
            Expediente::ESTADO_DILATA,
            Expediente::ESTADO_RECHAZADO,
        ];
        $this->aWhere['estado'] = implode(',', $a_tipos_acabado);
        $this->aOperador['estado'] = 'IN';
        // todavía sin marcar por scdl con ok.
    }

}