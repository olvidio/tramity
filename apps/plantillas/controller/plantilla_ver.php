<?php
use core\ViewTwig;
use etherpad\model\Etherpad;

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
    $Qid_plantilla = (integer) \filter_input(INPUT_POST, 'id_plantilla');
}
if ($Qmethod == 'GET') {
    $Qid_plantilla = (integer) \filter_input(INPUT_GET, 'id_plantilla');
}

if (!empty($Qid_plantilla)) {
    $oEtherpad = new Etherpad();
    $oEtherpad->setId (Etherpad::ID_PLANTILLA,$Qid_plantilla);
    
    $escrito_html = $oEtherpad->generarHtml();
} else {
    $escrito_html = '';
}

$base_url = core\ConfigGlobal::getWeb();

$a_campos = [
    'id_plantilla' => $Qid_plantilla,
    //'oHash' => $oHash,
    'base_url' => $base_url,
    'escrito_html' => $escrito_html,
];

$oView = new ViewTwig('plantillas/controller');
echo $oView->renderizar('plantilla_ver.html.twig',$a_campos);