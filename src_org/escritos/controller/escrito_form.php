<?php

// INICIO Cabecera global de URL de controlador *********************************

use escritos\model\EscritoForm;

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
$Q_accion = (integer)filter_input(INPUT_POST, 'accion');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');
$Q_volver_a = (string)filter_input(INPUT_POST, 'volver_a');

if (empty($Q_id_escrito) && $Q_filtro === 'en_buscar') {
    $Q_a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Q_id_escrito = $Q_a_sel[0];
}

$oEscritoForm = new EscritoForm($Q_id_expediente, $Q_id_escrito, $Q_accion, $Q_filtro, $Q_modo, $Q_volver_a);

if (empty($Q_id_escrito)) {
    $Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
    $oEscritoForm->setId_entrada($Q_id_entrada);
}

if ($Q_filtro === 'en_buscar') {
    $str_condicion = (string)filter_input(INPUT_POST, 'condicion');
    $oEscritoForm->setStr_condicion($str_condicion);
}

$oEscritoForm->render();