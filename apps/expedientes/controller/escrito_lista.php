<?php
use expedientes\model\EscritoLista;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

$oTabla = new EscritoLista();
$oTabla->setFiltro($Qfiltro);
$oTabla->setModo($Qmodo);

switch ($Qfiltro) {
    case 'enviar':
        echo $oTabla->mostrarTablaEnviar();
        break;
    default:
        echo $oTabla->mostrarTabla();
    break;
}