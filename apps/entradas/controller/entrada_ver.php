<?php

use core\ViewTwig;
use entradas\model\entity\EntradaCompartida;
use entradas\model\entity\GestorEntradaCompartidaAdjunto;
use entradas\model\Entrada;
use etherpad\model\Etherpad;
use usuarios\model\entity\Cargo;
use usuarios\model\Visibilidad;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // nuevo formato: id_entrada#comparida (compartida = boolean)
    $QQid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
    $a_entrada = explode('#', $QQid_entrada);
    $Qid_entrada = (int)$a_entrada[0];
    $Qcompartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $QQid_entrada = rawurldecode( (string)filter_input(INPUT_GET, 'id_entrada'));
    $a_entrada = explode('#', $QQid_entrada);
    $Qid_entrada = (int)$a_entrada[0];
    $Qcompartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);
}

$sigla = $_SESSION['oConfig']->getSigla();

if (is_true($Qcompartida)) {
    $oEntrada = new EntradaCompartida($Qid_entrada);
    $id_entrada_compartida = $Qid_entrada;
} else {
    $oEntrada = new Entrada($Qid_entrada);
    $id_entrada_compartida = $oEntrada->getId_entrada_compartida();
}

if (!empty($Qid_entrada)) {

    $asunto_e = $oEntrada->getAsunto_entrada();
    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();


    // excepción para bypass
    $visibilidad_txt = '';
    if (!is_true($oEntrada->getBypass())) {
        $oVisibilidad = new Visibilidad();
        $visibilidad = $oEntrada->getVisibilidad();
        // si soy dl o ctr
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            if ($visibilidad !== NULL && $visibilidad !== Visibilidad::V_CTR_TODOS) {
                $a_visibilidad_dl = $oVisibilidad->getArrayVisibilidadDl();
                $visibilidad_txt = "(".$a_visibilidad_dl[$visibilidad].")";
            }
        } else {
            if ($visibilidad !== NULL && $visibilidad !== Visibilidad::V_CTR_TODOS) {
                $a_visibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();
                $visibilidad_txt = "(".$a_visibilidad_dst[$visibilidad].")";
            }
        }
    }

    if (!empty($id_entrada_compartida)) {
        $bCompartida = TRUE;
        $cabeceraIzqd = $oEntrada->cabeceraIzquierda();
        $cabeceraDcha = $oEntrada->cabeceraDerecha();

        $gesEntradaAdjuntos = new GestorEntradaCompartidaAdjunto();
        $a_adjuntos = $gesEntradaAdjuntos->getArrayIdAdjuntos($id_entrada_compartida);

        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada_compartida);
    } else {
        $bCompartida = FALSE;
        // En el caso de distribución cr, si ya está aceptado, el ver es ya para enviar
        // y por tanto las cabeceras van al revés, y el destino se coge del bypass.
        $estado = $oEntrada->getEstado();
        $bypass = $oEntrada->getBypass();
        if (is_true($bypass) && $estado == Entrada::ESTADO_ACEPTADO) {
            $cabeceraIzqd = $oEntrada->cabeceraDistribucion_cr();
            $cabeceraDcha = $oEntrada->cabeceraDerecha();
        } else {
            $cabeceraIzqd = $oEntrada->cabeceraIzquierda();
            $cabeceraDcha = $oEntrada->cabeceraDerecha();
        }

        $a_adjuntos = $oEntrada->getArrayIdAdjuntos();

        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ENTRADA, $Qid_entrada);
    }
    $escrito_html = $oEtherpad->generarHtml();
} else {
    $bCompartida = FALSE;
    $cabeceraIzqd = '';
    $cabeceraDcha = '';
    $a_adjuntos = [];
    $asunto_e = '';
    $f_escrito = '';
    $f_entrada = '';
    $escrito_html = '';
}

if (!empty($f_entrada)) {
    $chk_leido = 'checked';
} else {
    $chk_leido = '';
}

$base_url = core\ConfigGlobal::getWeb();
$url_download = $base_url . '/apps/entradas/controller/download.php';
$url_download_pdf_adjunto = $base_url . '/apps/entradas/controller/download_as_pdf.php';
$url_download_pdf = $base_url . '/apps/entradas/controller/entrada_download.php';

$a_campos = [
    'id_entrada' => $Qid_entrada,
    //'oHash' => $oHash,
    'visibilidad_txt' => $visibilidad_txt,
    'cabeceraIzqd' => $cabeceraIzqd,
    'cabeceraDcha' => $cabeceraDcha,
    'asunto_e' => $asunto_e,
    'f_escrito' => $f_escrito,
    'a_adjuntos' => $a_adjuntos,
    'url_download' => $url_download,
    'chk_leido' => $chk_leido,
    'f_entrada' => $f_entrada,
    'base_url' => $base_url,
    'sigla' => $sigla,
    'escrito_html' => $escrito_html,
    'url_download_pdf' => $url_download_pdf,
    'bCompartida' => $bCompartida,
    'url_download_pdf_adjunto' => $url_download_pdf_adjunto,
];

$oView = new ViewTwig('entradas/controller');
$oView->renderizar('entrada_ver.html.twig', $a_campos);