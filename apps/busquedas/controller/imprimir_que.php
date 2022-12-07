<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use web\DateTimeLocal;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_f_min_enc = (string)filter_input(INPUT_POST, 'f_min');
$Q_f_min = urldecode($Q_f_min_enc);
$Q_f_max_enc = (string)filter_input(INPUT_POST, 'f_max');
$Q_f_max = urldecode($Q_f_max_enc);


// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha::getFormat();

$vista = ConfigGlobal::getVista();

$a_campos = [
    //'oHash' => $oHash,
    'filtro' => $Q_filtro,
    'f_min' => $Q_f_min,
    'f_max' => $Q_f_max,
    // datepicker
    'format' => $format,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('busquedas/controller');
$oView->renderizar('imprimir_que.html.twig', $a_campos);