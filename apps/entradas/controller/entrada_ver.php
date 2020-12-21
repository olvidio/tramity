<?php
use core\ViewTwig;
use entradas\model\Entrada;
use etherpad\model\Etherpad;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
$Qmethod = (integer) \filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if ($Qmethod == 'POST') {
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
}
if ($Qmethod == 'GET') {
    $Qid_entrada = (integer) \filter_input(INPUT_GET, 'id_entrada');
}

$sigla = $_SESSION['oConfig']->getSigla();

$oEntrada = new Entrada($Qid_entrada);

if (!empty($Qid_entrada)) {
    
    $cabeceraIzqd = $oEntrada->cabeceraIzquierda();
    $cabeceraDcha = $oEntrada->cabeceraDerecha();
    
    $asunto_e = $oEntrada->getAsunto_entrada();
    $a_adjuntos = $oEntrada->getArrayIdAdjuntos();
    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();
    
    $oEtherpad = new Etherpad();
    $oEtherpad->setId (Etherpad::ID_ENTRADA,$Qid_entrada);
    
    $escrito_html = $oEtherpad->generarHtml();
} else {
    $cabeceraIzqd = '';
    $cabeceraDcha = '';
    $oArrayProtRef = [];
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
$url_download = $base_url.'/apps/entradas/controller/download.php?plugin=1';
$url_download_pdf = $base_url.'/apps/entradas/controller/entrada_download.php';

$a_campos = [
    'id_entrada' => $Qid_entrada,
    //'oHash' => $oHash,
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
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_ver.html.twig',$a_campos);