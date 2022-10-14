<?php

use escritos\model\EscritoLista;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$oTabla = new EscritoLista();
$oTabla->setFiltro($Q_filtro);
$oTabla->setModo($Q_modo);

switch ($Q_filtro) {
    case 'enviar':
        echo $oTabla->mostrarTablaEnviar();
        break;
    default:
        echo $oTabla->mostrarTabla();
        break;
}