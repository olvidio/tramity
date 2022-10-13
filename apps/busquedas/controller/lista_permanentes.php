<?php

// INICIO Cabecera global de URL de controlador *********************************
use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\entity\GestorEntradaCompartida;
use entradas\model\GestorEntrada;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;
use web\Desplegable;
use function core\any_2;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Qtipo_lista = (string)\filter_input(INPUT_POST, 'tipo_lista');
$Qid_lugar = (integer)\filter_input(INPUT_POST, 'id_lugar');
$Qprot_num = '';
$Qprot_any = '';
$Qasunto = '';
$filtro = 'permanentes_cr';

$titulo = '';
$lista = '';
$oVerTabla = '';
switch ($Qtipo_lista) {
    case 54:
        // por asunto
        break;
    case 'any':
        // por año
        $oBuscar = new Buscar();
        // En los centros, no busco en entradas, sino en entradas_compartidas y
        // veo si el centro está en los destinos.
        if ($_SESSION['oConfig']->getAmbito() != Cargo::AMBITO_CTR) {
            // Busco el id_lugar de cr.
            $gesLugares = new GestorLugar();
            $id_cr = $gesLugares->getId_cr();
            $oBuscar->setId_lugar($id_cr);
        }
        $Qany = (integer)\filter_input(INPUT_POST, 'any');
        $any2 = any_2($Qany);

        $oBuscar->setProt_any($any2);
        $aCollection = $oBuscar->getCollection($Qtipo_lista);
        foreach ($aCollection as $key => $cCollection) {
            $oVerTabla = new VerTabla();
            $oVerTabla->setKey($key);
            $oVerTabla->setCollection($cCollection);
            $oVerTabla->setFiltro($filtro);
            $oVerTabla->setBotonesDefault();
        }
        break;
    case 'oficina':
        // por oficina
        // Busco el id_lugar de cr.
        $gesLugares = new GestorLugar();
        $id_cr = $gesLugares->getId_cr();
        $Qid_oficina = (integer)\filter_input(INPUT_POST, 'id_oficina');

        $oBuscar = new Buscar();
        $oBuscar->setId_lugar($id_cr);
        $oBuscar->setPonente($Qid_oficina);

        $aCollection = $oBuscar->getCollection($Qtipo_lista);
        foreach ($aCollection as $key => $cCollection) {
            $oVerTabla = new VerTabla();
            $oVerTabla->setKey($key);
            $oVerTabla->setCollection($cCollection);
            $oVerTabla->setFiltro($filtro);
            $oVerTabla->setBotonesDefault();
        }
        break;
    case 'proto': // un protocolo concreto:
        $oBuscar = new Buscar();
        // por año
        $flag = 0;
        if (!empty($Qid_lugar)) {

            $Qprot_num = (integer)\filter_input(INPUT_POST, 'prot_num');
            $Qprot_any = (string)\filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
            $Qany = (integer)\filter_input(INPUT_POST, 'any');
            $Qprot_any2 = core\any_2($Qprot_any);

            if (!empty($Qprot_num)) {
                $oBuscar->setProt_num($Qprot_num);
                $flag = 1;
            } else {
                $Qprot_num = '';
            }
            if ($Qprot_any2 != '') { // para aceptar el 00
                $oBuscar->setProt_any($Qprot_any2);
                $flag = 1;
            }

            $oBuscar->setId_lugar($Qid_lugar);
            $oBuscar->setProt_any($Qprot_any2);
        }

        $Qasunto = (string)\filter_input(INPUT_POST, 'asunto');
        if (!empty($Qasunto)) {
            $oBuscar->setAsunto($Qasunto);
            $flag = 1;
        }
        if ($flag == 0) {
            $lista = _("Debe indicar el protocolo o el asunto");
        } else {
            $aCollection = $oBuscar->getCollection($Qtipo_lista);
            foreach ($aCollection as $key => $cCollection) {
                $oVerTabla = new VerTabla();
                $oVerTabla->setKey($key);
                $oVerTabla->setCollection($cCollection);
                $oVerTabla->setFiltro($filtro);
                $oVerTabla->setBotonesDefault();
            }
        }
        break;
    case 'lst_oficinas':
        //oficinas posibles:
        $gesOficinas = new GestorOficina();
        $cOficinas = $gesOficinas->getArrayOficinas();

        $titulo = _("AVISOS DE CR DE NÚMERO BAJO: por oficinas");
        $lista = '';
        foreach ($cOficinas as $id_oficina => $sigla) {
            $ira = "apps/busquedas/controller/lista_permanentes.php?tipo_lista=oficina&id_oficina=$id_oficina";

            $lista .= "<button onclick=\"fnjs_update_div('#main','$ira')\" type=\"button\" class=\"col-2 btn btn-outline-primary m-2\">";
            $lista .= sprintf(_("oficina %s"), $sigla);
            $lista .= "</button>";
        }
        break;
    case 'lst_todos':
        $titulo = _("AVISOS DE CR DE NÚMERO BAJO");
        $oBuscar = new Buscar();
        // En los centros, no busco en entradas, sino en entradas_compartidas y
        // veo si el centro está en los destinos.
        if ($_SESSION['oConfig']->getAmbito() != Cargo::AMBITO_CTR) {
            // Busco el id_lugar de cr.
            $gesLugares = new GestorLugar();
            $id_cr = $gesLugares->getId_cr();
            $oBuscar->setId_lugar($id_cr);
        }

        $aCollection = $oBuscar->getCollection($Qtipo_lista);
        foreach ($aCollection as $key => $cCollection) {
            $oVerTabla = new VerTabla();
            $oVerTabla->setKey($key);
            $oVerTabla->setCollection($cCollection);
            $oVerTabla->setFiltro($filtro);
            $oVerTabla->setBotonesDefault();
        }
        break;
    case 'lst_years':
    default:
        //anys posibles:
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $gesEntradas = new GestorEntradaCompartida();
        } else {
            $gesEntradas = new GestorEntrada();
        }
        $a_anys = $gesEntradas->posiblesYear();

        $a_any_min = current($a_anys);
        $any_min = $a_any_min;
        end($a_anys);
        $a_any_max = current($a_anys);
        $any_max = $a_any_max;
        reset($a_anys);

        $decena_min = floor($any_min / 10) * 10;
        $decena_max = floor($any_max / 10) * 10;

        $titulo = _("AVISOS DE CR DE NÚMERO BAJO: por años");
        foreach ($a_anys as $any) {
            $ira = "apps/busquedas/controller/lista_permanentes.php?tipo_lista=any&any=$any";
            $btn = "<button onclick=\"fnjs_update_div('#main','$ira')\" type=\"button\" class=\"col-1 btn btn-outline-primary \" >";
            $btn .= sprintf(_("año %s"), $any);
            $btn .= "</button>";
            $lista_any[$any] = $btn;
        }
        $lista = '';
        for ($fila = 0; $fila < 10; $fila++) {
            $lista .= '<div class="row">';
            for ($decena = $decena_min; $decena <= $decena_max; $decena = $decena + 10) {
                $any_lista = (int)$decena + $fila;
                if (!empty($lista_any[$any_lista])) {
                    $txt = $lista_any[$any_lista];
                } else {
                    $btn = "<button type=\"button\" class=\"col-1 btn btn-outline-secondary\" disabled >";
                    $btn .= sprintf(_("año %s"), $any_lista);
                    $btn .= "</button>";
                    $txt = $btn;
                }
                $lista .= $txt;
            }
            $lista .= '</div>';
        }
}

$gesLugares = new GestorLugar();
// Busco el id_lugar de cr.
$id_cr = $gesLugares->getId_cr();
// sigla local:
// Busco el id_lugar de la dl.
$sigla_local = $_SESSION['oConfig']->getSigla();
$sigla_dl = $gesLugares->getSigla_superior($sigla_local);
$cLugares = $gesLugares->getLugares(['sigla' => $sigla_dl]);
if (!empty($cLugares)) {
    $id_sigla_dl = $cLugares[0]->getId_lugar();
}

$a_lugares = [$id_cr => 'cr', $id_sigla_dl => $sigla_dl];
// por defecto cr, en el caso de dl. vacio en caso de ctr.
$ambito_dl = FALSE;
if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
    $Qid_lugar = empty($Qid_lugar) ? $id_cr : $Qid_lugar;
    $ambito_dl = TRUE;
}
$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setOpcion_sel($Qid_lugar);

$vista = ConfigGlobal::getVista();

$a_campos = [
    //'oHash' => $oHash,
    'prot_num' => $Qprot_num,
    'prot_any' => $Qprot_any,
    'asunto' => $Qasunto,
    'tipo_lista' => $Qtipo_lista,
    'titulo' => $titulo,
    'lista' => $lista,
    'filtro' => $filtro,
    'oVerTabla' => $oVerTabla,
    'oDesplLugar' => $oDesplLugar,
    'ambito_dl' => $ambito_dl,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('busquedas/controller');
echo $oView->renderizar('lista_permanentes.html.twig', $a_campos);