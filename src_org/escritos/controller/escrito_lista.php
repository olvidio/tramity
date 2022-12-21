<?php

use escritos\model\EscritoLista;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$oTabla = new EscritoLista();
$oTabla->setFiltro($Q_filtro);
$oTabla->setModo($Q_modo);

if ($Q_filtro === 'enviar') {
    $oTabla->mostrarTablaEnviar();
} else {
    $oTabla->mostrarTabla();
}