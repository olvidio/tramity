<?php
use expedientes\model\Escrito;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_escrito = (integer) \filter_input(INPUT_GET, 'id');
//$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');

$f_salida = date("d/m/Y");
// Comprobar si tiene clave para enviar un xml, o hay que generar un pdf.


$oEscrito = new Escrito($Qid_escrito);
$oEscrito->DBCarregar();
$oEscrito->setF_salida($f_salida);
//$oEscrito->DBGuardar();

$omPdf = $oEscrito->generarPDF();

$omPdf->Output("eee.pdf",'I');