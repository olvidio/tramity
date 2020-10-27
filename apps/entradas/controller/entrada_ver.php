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


// Para poder moverse de una entrada a otra:
$QSlide_mode =  (bool) \filter_input(INPUT_POST, 'slide_mode', FILTER_VALIDATE_BOOLEAN );

if ($QSlide_mode === TRUE) {
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
    $Qmov = (string) \filter_input(INPUT_POST, 'mov');

    if ($Qmov == 'prev') {
        $Qid_entrada = $Qid_entrada - 1;
    }
    if ($Qmov == 'next') {
        $Qid_entrada = $Qid_entrada + 1;
    }
} else {
    // porque también se puede abrir en una ventana nueva, y entonces se llama por GET
    $Qmethod = (integer) \filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    if ($Qmethod == 'POST') {
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
    }
    if ($Qmethod == 'GET') {
        $Qid_entrada = (integer) \filter_input(INPUT_GET, 'id_entrada');
    }
}

$sigla = $_SESSION['oConfig']->getSigla();

$oProtOrigen = new Protocolo();
$oProtOrigen->setEtiqueta('De');
$oProtOrigen->setNombre('origen');
$oProtOrigen->setBlanco(TRUE);
$oProtOrigen->setTabIndex(10);

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setBlanco(TRUE);

$oEntrada = new Entrada($Qid_entrada);


$pagina = core\ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
$a_cosas = [ 'id_entrada' => $Qid_entrada, 'slide_mode' => 'TRUE', 'mov' => 'prev'];
$pagina_prev = web\Hash::link($pagina.'?'.http_build_query($a_cosas));
$a_cosas = [ 'id_entrada' => $Qid_entrada, 'slide_mode' => 'TRUE', 'mov' => 'next'];
$pagina_next = web\Hash::link($pagina.'?'.http_build_query($a_cosas));


if (!empty($Qid_entrada)) {
    
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (count(get_object_vars($json_prot_origen)) == 0) {
        exit (_("No hay más"));
    }
    $oProtOrigen->setLugar($json_prot_origen->lugar);
    $oProtOrigen->setProt_num($json_prot_origen->num);
    $oProtOrigen->setProt_any($json_prot_origen->any);
    $oProtOrigen->setMas($json_prot_origen->mas);
        
    $json_prot_ref = $oEntrada->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,'','referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setRef(TRUE);
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');
    
    $asunto_e = $oEntrada->getAsunto_entrada();
    
    $a_adjuntos = $oEntrada->getArrayIdAdjuntos();
    
    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();
    
    $oEtherpad = new Etherpad();
    $oEtherpad->setId (Etherpad::ID_ENTRADA,$Qid_entrada);
    
    $escrito_html = $oEtherpad->generarHtml();
} else {
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

$a_campos = [
    'id_entrada' => $Qid_entrada,
    //'oHash' => $oHash,
    'oProtOrigen' => $oProtOrigen,
    'oArrayProtRef' => $oArrayProtRef,
    'asunto_e' => $asunto_e,
    'f_escrito' => $f_escrito,
    'a_adjuntos' => $a_adjuntos,
    'url_download' => $url_download,
    'pagina_prev' => $pagina_prev,
    'pagina_next' => $pagina_next,
    'chk_leido' => $chk_leido,
    'f_entrada' => $f_entrada,
    'base_url' => $base_url,
    'sigla' => $sigla,
    'escrito_html' => $escrito_html,
];

$oView = new ViewTwig('entradas/controller');
if ($QSlide_mode === TRUE) {
    echo $oView->renderizar('entrada_ver_slide.html.twig',$a_campos);
} else {
    echo $oView->renderizar('entrada_ver.html.twig',$a_campos);
}