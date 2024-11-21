<?php

use core\ViewTwig;
use escritos\model\TextoDelEscrito;
use plantillas\model\entity\Plantilla;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Q_id_plantilla = (integer)filter_input(INPUT_POST, 'id_plantilla');
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $Q_id_plantilla = (integer)filter_input(INPUT_GET, 'id_plantilla');
}

if (!empty($Q_id_plantilla)) {
    $oPlantilla = new Plantilla($Q_id_plantilla);
    $tipo_doc = $oPlantilla->getTipo_doc();
    $oTextoDelEscrito = new TextoDelEscrito($tipo_doc, TextoDelEscrito::ID_PLANTILLA, $Q_id_plantilla);

    $escrito_html = $oTextoDelEscrito->generarHtml();
} else {
    $escrito_html = '';
}

$base_url = core\ConfigGlobal::getWeb();

$a_campos = [
    'id_plantilla' => $Q_id_plantilla,
    //'oHash' => $oHash,
    'base_url' => $base_url,
    'escrito_html' => $escrito_html,
];

$oView = new ViewTwig('plantillas/controller');
$oView->renderizar('plantilla_ver.html.twig', $a_campos);