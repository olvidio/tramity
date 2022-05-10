<?php
use function core\is_true;
use envios\model\Enviar;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";
$Qid_entrada = (integer) \filter_input(INPUT_GET, 'id_entrada');
$Qcompartida = (integer) \filter_input(INPUT_GET, 'compartida');

if (!empty($Qid_entrada)) {
	if (is_true($Qcompartida)) {
		$bCompartida = TRUE;
	} else {
		$bCompartida = FALSE;
	}
    $oEnviar = new Enviar($Qid_entrada,'entrada');
    $File = $oEnviar->getPdf($bCompartida);
    
    $escrito = $File['content'];
    $nom = $File['ext'];
    
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: private",false); // required for certain browsers
    header('Content-Type: application/force-download');
    header('Content-Type: application/octet-stream', false);
    header('Content-Type: application/download', false);
    header('Content-disposition: attachment; filename="' . $nom . '"');
    
    ob_clean();
    flush();
    echo $escrito;
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}