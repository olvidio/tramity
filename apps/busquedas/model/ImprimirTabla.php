<?php

namespace busquedas\model;

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\Lista;
use web\Protocolo;
use web\ProtocoloArray;

class ImprimirTabla
{

    /**
     * Key (entradas | escritos)
     *
     * @var string
     */
    private $sKey;

    /**
     *
     * @var string
     */
    private $sTitulo;


    private $dt_op_dom;


    /**
     * @param string $sKey
     */
    public function setKey($key)
    {
        $this->sKey = $key;
    }

    public function mostrarTabla($aCollection)
    {
        $this->sTitulo = _("registro de escritos recibidos o enviados en la Delegación");

        $a_cabeceras = array(array('name' => ucfirst(_("protocolo")), 'formatter' => 'clickFormatter'),
            ucfirst(_("destinos")),
            ucfirst(_("ref.")),
            array('name' => ucfirst(_("asunto")), 'formatter' => 'clickFormatter2'),
            ucfirst(_("oficinas")),
            array('name' => ucfirst(_("fecha doc.")), 'class' => 'fecha'),
            array('name' => ucfirst(_("fecha aprobación")), 'class' => 'fecha'),
            array('name' => ucfirst(_("fecha entrada/salida")), 'class' => 'fecha')
        );

        $a_botones = [];

        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();

        $i = 0;
        $a_valores = [];
        $a_sort_fecha = [];
        $a_sort_e_s = [];
        $a_sort_id_reg = [];
        // Protocol d'origen (E i S) – Destinacions (E) – Ref. (E i S) – Assumpte (E i S) [sense «detall»] – 
        // Oficines (E i S) –  Data del document. (E i S) – Data d'aprovació (S) –  Data d'entrada (E) / Enviat (S)
        $oProtOrigen = new Protocolo();
        foreach ($aCollection as $key => $cCollection) {
            if ($key === 'entradas') {
                $destinos = '';
                $f_aprovacion = '';
                foreach ($cCollection as $oEntrada) {
                    $id_entrada = $oEntrada->getId_entrada();
                    $f_entrada = $oEntrada->getF_entrada();

                    $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
                    $protocolo = $oProtOrigen->ver_txt();

                    // referencias
                    $json_ref = $oEntrada->getJson_prot_ref();
                    $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
                    $oArrayProtRef->setRef(TRUE);
                    $referencias = $oArrayProtRef->ListaTxtBr();

                    // oficinas
                    $id_of_ponente = $oEntrada->getPonente();
                    $a_resto_oficinas = $oEntrada->getResto_oficinas();
                    $oficinas_txt = '';
                    if (!empty($id_of_ponente)) {
                        $of_ponente_txt = empty($a_posibles_oficinas[$id_of_ponente]) ? '??' : $a_posibles_oficinas[$id_of_ponente];
                        $oficinas_txt .= '<span class="text-danger">' . $of_ponente_txt . '</span>';
                    }
                    foreach ($a_resto_oficinas as $id_oficina) {
                        $oficinas_txt .= empty($oficinas_txt) ? '' : ', ';
                        $oficinas_txt .= empty($a_posibles_oficinas[$id_oficina]) ? '?' : $a_posibles_oficinas[$id_oficina];
                    }
                    $oficinas = $oficinas_txt;

                    $asunto = $oEntrada->getAsunto();
                    $f_doc = $oEntrada->getF_documento();


                    //$a_valores[$i]['sel'] = "$id_entrada";
                    $a_valores[$i][1] = $protocolo;
                    $a_valores[$i][2] = $destinos;
                    $a_valores[$i][3] = $referencias;
                    $a_valores[$i][4] = $asunto;
                    $a_valores[$i][5] = $oficinas;
                    $a_valores[$i][6] = $f_doc->getFromLocal();
                    $a_valores[$i][7] = $f_aprovacion;
                    $a_valores[$i][8] = $f_entrada->getFromLocal();

                    $a_sort_fecha[$i] = $f_entrada->format('Ymd');
                    $a_sort_e_s[$i] = 'e';
                    $a_sort_id_reg[$i] = $id_entrada;
                    $i++;
                }
            }

            if ($key === 'escritos') {
                $gesCargos = new GestorCargo();
                $a_posibles_cargos = $gesCargos->getArrayCargos();
                foreach ($cCollection as $oEscrito) {
                    $asunto = $oEscrito->getAsunto();
                    $anulado = $oEscrito->getAnulado();

                    // protocolo local
                    $protocolo_local = $oEscrito->getProt_local_txt();
                    // destinos
                    $destino_txt = $oEscrito->getDestinosEscrito();

                    $id_escrito = $oEscrito->getId_escrito();
                    $f_aprobacion = $oEscrito->getF_aprobacion();
                    $f_escrito = $oEscrito->getF_escrito();
                    $f_salida = $oEscrito->getF_salida();

                    // referencias
                    $json_ref = $oEscrito->getJson_prot_ref();
                    $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
                    $oArrayProtRef->setRef(TRUE);
                    $referencias = $oArrayProtRef->ListaTxtBr();

                    // oficinas
                    $id_ponente = $oEscrito->getCreador();
                    $a_resto_oficinas = $oEscrito->getResto_oficinas();
                    $oficina_txt = empty($a_posibles_cargos[$id_ponente]) ? '?' : $a_posibles_cargos[$id_ponente];
                    $oficinas_txt = '';
                    $oficinas_txt .= '<span class="text-danger">' . $oficina_txt . '</span>';
                    foreach ($a_resto_oficinas as $id_oficina) {
                        $oficinas_txt .= empty($oficinas_txt) ? '' : ', ';
                        $oficinas_txt .= empty($a_posibles_cargos[$id_oficina]) ? '' : $a_posibles_cargos[$id_oficina];
                    }
                    $oficinas = $oficinas_txt;

                    if (!empty($anulado)) {
                        $asunto = _("ANULADO") . " ($anulado) $asunto";
                    }

                    //$a_valores[$i]['sel'] = "$id_escrito";
                    $a_valores[$i][1] = $protocolo_local;
                    $a_valores[$i][2] = $destino_txt;
                    $a_valores[$i][3] = $referencias;
                    $a_valores[$i][4] = $asunto;
                    $a_valores[$i][5] = $oficinas;
                    $a_valores[$i][6] = $f_escrito->getFromLocal();
                    $a_valores[$i][7] = $f_aprobacion->getFromLocal();
                    $a_valores[$i][8] = $f_salida->getFromLocal();

                    $a_sort_fecha[$i] = $f_salida->format('Ymd');
                    $a_sort_e_s[$i] = 's';
                    $a_sort_id_reg[$i] = $id_escrito;
                    $i++;
                }

            }
        }
        // Ordenar: NO HACE FALTA porque lo hace el javascript.
        // Sort the data with fechas descending
        // Add $a_valores as the last parameter, to sort by the common key
        //array_multisort($a_sort_fecha,SORT_NUMERIC, SORT_ASC,
        //		$a_valores);

        $oTabla = new Lista();
        $oTabla->setId_tabla('ver_tabla_' . $this->sKey);
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($a_botones);
        $oTabla->setDatos($a_valores);
        $oTabla->setDataTable_options_dom($this->dt_op_dom);
        $oTabla->setDataTable_options_order('[7,"asc"],[0,"asc"]');

        $server = ConfigGlobal::getWeb(); //http://tramity.local
        $vista = ConfigGlobal::getVista();

        $filtro = 'imprimir';
        $condicion = '';
        $a_campos = [
            'titulo' => $this->sTitulo,
            'oEntradaLista' => $oTabla,
            'key' => $this->sKey,
            'condicion' => $condicion,
            //'oHash' => $oHash,
            'server' => $server,
            'filtro' => $filtro,
            // tabs_show
            'vista' => $vista,
        ];

        $oView = new ViewTwig('busquedas/controller');
        $oView->renderizar('ver_tabla.html.twig', $a_campos);
    }

    // ---------------------------------- botones ----------------------------

    /**
     * @param mixed $dt_op_dom
     */
    public function setDataTable_options_dom($dt_op_dom)
    {
        $this->dt_op_dom = $dt_op_dom;
    }

    /**
     * @return mixed
     */
    public function getDataTable_options_dom()
    {
        return $this->dt_op_dom;
    }

    /**
     * @return mixed
     */
    public function getDataTable_options_buttons()
    {
        return $this->dt_op_buttons;
    }

    /**
     * @param mixed $dt_op_buttons
     */
    public function setDataTable_options_buttons($dt_op_buttons)
    {
        $this->dt_op_buttons = $dt_op_buttons;
    }


}