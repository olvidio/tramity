<?php

use entradas\model\Convertir;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

if ($Q_que == 'entradas') {
    $oConvertir = new Convertir('entradas');
    $oConvertir->ref();
}

if ($Q_que == 'escritos') {
    $oConvertir = new Convertir('escritos');
    $oConvertir->destino();
}

if ($Q_que == 'expedientes') {
    $oConvertir = new Convertir('expedientes');
    $oConvertir->expedientes();
}

