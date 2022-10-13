<?php

// INICIO Cabecera global de URL de controlador *********************************

use escritos\model\EscritoForm;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer)\filter_input(INPUT_POST, 'id_expediente');
$Qid_escrito = (integer)\filter_input(INPUT_POST, 'id_escrito');
$Qaccion = (integer)\filter_input(INPUT_POST, 'accion');
$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');
$Qmodo = (string)\filter_input(INPUT_POST, 'modo');


$oEscritoForm = new EscritoForm($Qid_expediente, $Qid_escrito, $Qaccion, $Qfiltro, $Qmodo);


if (empty($Qid_escrito) && $Qfiltro == 'en_buscar') {
    $Qa_sel = (array)\filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $oEscritoForm->setQa_sel($Qa_sel);
}

if (empty($Qid_escrito)) {
    $Qid_entrada = (integer)\filter_input(INPUT_POST, 'id_entrada');
    $oEscritoForm->setQid_entrada($Qid_entrada);
}

if ($Qfiltro == 'en_buscar') {
    $str_condicion = (string)\filter_input(INPUT_POST, 'condicion');
    $oEscritoForm->setStr_condicion($str_condicion);
}

$oEscritoForm->render();
