<?php
use expedientes\model\ExpedienteLista;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

//$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$oTabla = new ExpedienteLista();
$oTabla->setFiltro($Qfiltro);
//$oTabla->setId_expediente($Qid_expediente);

echo $oTabla->mostrarTabla();