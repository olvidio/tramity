<?php

// INICIO Cabecera global de URL de controlador *********************************

use pendientes\model\PendienteLista;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qperiodo = (string) \filter_input(INPUT_POST, 'periodo');

$oTabla = new PendienteLista();
$oTabla->setFiltro($Qfiltro);
$oTabla->setPeriodo($Qperiodo);

echo $oTabla->mostrarTabla();