<?php

use config\model\entity\ConfigSchema;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Q_parametro = (string)filter_input(INPUT_POST, 'parametro');
$Q_valor = (string)filter_input(INPUT_POST, 'valor');

$oConfigSchema = new ConfigSchema($Q_parametro);
$oConfigSchema->setValor($Q_valor);

$error_txt = '';
if ($oConfigSchema->DBGuardar() === FALSE) {
    $error_txt = $oConfigSchema->getErrorTxt();
}

if (empty($error_txt)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $error_txt;
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();