<?php

namespace expedientes\model;

use core\ConfigGlobal;
use web\Hash;
use function core\is_true;


class ExpedienteBorradorLista
{
    private string $filtro;
    private int $prioridad_sel = 0;
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

    public function __construct(string $filtro)
    {
        $this->filtro = $filtro;
    }

    public function mostrarTabla(): void
    {

        $this->setCondicion();
        $a_cosas = ['filtro' => $this->filtro, 'prioridad_sel' => $this->prioridad_sel];
        $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
        $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_form.php';
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';

        $oFormatoLista = new FormatoLista();
        $oFormatoLista->setPresentacion(6); // añado las etiquetas
        $oFormatoLista->setColumnaModVisible(TRUE);
        $oFormatoLista->setColumnaVerVisible(TRUE);
        $oFormatoLista->setColumnaFIniVisible(FALSE);
        $oFormatoLista->setPaginaNueva($pagina_nueva);
        $oFormatoLista->setPaginaMod($pagina_mod);
        $oFormatoLista->setPaginaVer($pagina_ver);

        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);
        } else {
            $cExpedientes = [];
        }
        $oExpedienteLista = new ExpedienteLista($cExpedientes, $oFormatoLista, new ExpedientesDeColor());
        $oExpedienteLista->setFiltro($this->filtro);
        $oExpedienteLista->setPrioridad_sel($this->prioridad_sel);

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
        if (!empty($this->aWhereADD)) {
            $this->aWhere = $this->aWhereADD;
            if (!empty($this->aOperadorADD)) {
                $this->aOperador = $this->aOperadorADD;
            }
        }
        $this->aWhere['estado'] = Expediente::ESTADO_BORRADOR;
        // solo los propios:
        $mi_cargo = ConfigGlobal::role_id_cargo();
        if ($this->filtro === 'borrador_propio') {
            $this->aWhere['ponente'] = $mi_cargo;
        } else { //oficina
            // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
            if (is_true(ConfigGlobal::soy_dtor())) {
                $visto = 'todos';
            } else {
                $visto = 'no_visto';
            }
            $gesExpedientes = new GestorExpediente();
            $a_expedientes = $gesExpedientes->getIdExpedientesPreparar($mi_cargo, $visto);
            if (!empty($a_expedientes)) {
                // OJO Puedo tener ya una selección de id_expediente por el filtro de etiquetas:
                if (!empty($this->aWhere['id_expediente']) && ($this->aOperador['id_expediente'] === 'IN')) {
                    $a_expedientes_por_filtro = explode(',',$this->aWhere['id_expediente']);
                    $a_interseccion = array_intersect($a_expedientes_por_filtro, $a_expedientes);
                    if (empty($a_interseccion)) {
                        // para que no salga nada pongo
                        $this->aWhere = [];
                    }
                } else {
                    $this->aWhere['id_expediente'] = implode(',', $a_expedientes);
                    $this->aOperador['id_expediente'] = 'IN';
                }
            } else {
                // para que no salga nada pongo
                $this->aWhere = [];
            }
        }
    }

    public function setPrioridad_sel(string $prioridad_sel): void
    {
        $this->prioridad_sel = $prioridad_sel;
    }

    public function getAWhereADD(): array
    {
        return $this->aWhereADD;
    }

    public function setAWhereADD(array $aWhereADD): void
    {
        $this->aWhereADD = $aWhereADD;
    }

    public function getAOperadorADD(): array
    {
        return $this->aOperadorADD;
    }

    public function setAOperadorADD(array $aOperadorADD): void
    {
        $this->aOperadorADD = $aOperadorADD;
    }

}