<?php


// INICIO Cabecera global de URL de controlador *********************************

use config\domain\entity\ConfigSchema;
use config\domain\repositories\ConfigSchemaPublicRepository;
use config\domain\repositories\ConfigSchemaRepository;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Q_parametro = (string)filter_input(INPUT_POST, 'parametro');
$Q_valor = (string)filter_input(INPUT_POST, 'valor');

if ($esquema === 'admin') {
    $ConfigSchemaRepository = new ConfigSchemaPublicRepository();
} else {
    $ConfigSchemaRepository = new ConfigSchemaRepository();
}
$oConfigSchema = $ConfigSchemaRepository->findById($Q_parametro);
if ($oConfigSchema === null) {
    $oConfigSchema = new ConfigSchema();
    $oConfigSchema->setParametro($Q_parametro);
}
$oConfigSchema->setValor($Q_valor);

$error_txt = '';
if ($ConfigSchemaRepository->Guardar($oConfigSchema) === FALSE) {
    $error_txt = $ConfigSchemaRepository->getErrorTxt();
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