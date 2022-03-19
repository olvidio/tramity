<?php

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$txt_err = '';
if ( session_status() === PHP_SESSION_ACTIVE) {
	$txt_err .= ''; // "active";
} else {
	$txt_err .= "no active";
}

if (empty($txt_err)) {
	$jsondata['success'] = true;
	$jsondata['mensaje'] = 'ok';
} else {
	$jsondata['success'] = false;
	$jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();