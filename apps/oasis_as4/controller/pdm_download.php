<?php

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


// El download es via GET!!!";
$Qfilename = (string) \filter_input(INPUT_GET, 'filename');

if (!empty($Qfilename)) {
	// resp
	$dir = $_SESSION['oConfig']->getDock();
	$fullfileame = $dir .'/repository/pmodes_resp/'. $Qfilename;
	
	$doc = file_get_contents($fullfileame);

	header('Content-Description: File Transfer');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: private",false); // required for certain browsers
	header('Content-Type: application/force-download');
	header('Content-Type: application/download', false);
	header('Content-disposition: attachment; filename="' . $Qfilename . '"');
	ob_clean();
	flush();
	echo $doc;
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}