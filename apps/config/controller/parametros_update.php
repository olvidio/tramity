<?php

use config\model\entity\ConfigSchema;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Qparametro = (string)  \filter_input(INPUT_POST, 'parametro');
$Qvalor = (string)  \filter_input(INPUT_POST, 'valor');

$oConfigSchema = new ConfigSchema($Qparametro);
$oConfigSchema->setValor($Qvalor);
$oConfigSchema->DBGuardar();
