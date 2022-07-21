<?php

use entradas\model\Convertir;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

if ($Qque == 'entradas') {
	$oConvertir = new Convertir('entradas');
	$oConvertir->ref();
}

if ($Qque == 'escritos') {
	$oConvertir = new Convertir('escritos');
	$oConvertir->destino();
}

if ($Qque == 'expedientes') {
	$oConvertir = new Convertir('expedientes');
	$oConvertir->expedientes();
}

