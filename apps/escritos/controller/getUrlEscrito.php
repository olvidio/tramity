<?php

// INICIO Cabecera global de URL de controlador *********************************

use escritos\model\Escrito;
use escritos\model\TextoDelEscrito;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id = (string)filter_input(INPUT_POST, 'id');
$Q_tipo_id = (string)filter_input(INPUT_POST, 'tipo_id');
$Q_tipo_doc = (int)filter_input(INPUT_POST, 'tipo_doc');
// creo que no usa
//$Q_modo = (string)filter_input(INPUT_POST, 'modo');

if (empty($Q_tipo_doc)) {
    $oEscrito = new Escrito($Q_id);
    $Q_tipo_doc = $oEscrito->getTipo_doc();
}

$oTextDelEscrito = new TextoDelEscrito($Q_tipo_doc, $Q_tipo_id, $Q_id);
$jsondata = $oTextDelEscrito->getJsonEditorUrl();

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();

