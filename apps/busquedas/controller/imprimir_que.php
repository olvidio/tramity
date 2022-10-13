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

$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');
$Qf_min_enc = (string)\filter_input(INPUT_POST, 'f_min');
$Qf_min = urldecode($Qf_min_enc);
$Qf_max_enc = (string)\filter_input(INPUT_POST, 'f_max');
$Qf_max = urldecode($Qf_max_enc);


// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();

$vista = ConfigGlobal::getVista();

$a_campos = [
    //'oHash' => $oHash,
    'filtro' => $Qfiltro,
    'f_min' => $Qf_min,
    'f_max' => $Qf_max,
    // datepicker
    'format' => $format,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('busquedas/controller');
echo $oView->renderizar('imprimir_que.html.twig', $a_campos);