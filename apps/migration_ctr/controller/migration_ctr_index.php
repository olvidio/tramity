<?php

use core\ViewTwig;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$str_id_ctr = '';
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
if (!empty($a_sel)) { //vengo de un checkbox
    foreach ($a_sel as $sel) {
        $str_id_ctr .= empty($str_id_ctr) ? '' : ',';
        $str_id_ctr .= (integer)strtok($sel, "#");
    }
}

$a_campos = [
    'lista_id_ctr' => $str_id_ctr,
];

$oView = new ViewTwig('migration_ctr/controller');
$oView->renderizar('migration_ctr_index.html.twig', $a_campos);
	