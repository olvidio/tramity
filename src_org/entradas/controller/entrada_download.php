<?php

use envios\model\Enviar;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";
$Q_id_entrada = (integer)filter_input(INPUT_GET, 'id_entrada');
$Q_compartida = (integer)filter_input(INPUT_GET, 'compartida');

if (!empty($Q_id_entrada)) {
    if (is_true($Q_compartida)) {
        $bCompartida = TRUE;
    } else {
        $bCompartida = FALSE;
    }
    $oEnviar = new Enviar($Q_id_entrada, 'entrada');
    $File = $oEnviar->getPdf($bCompartida);

    $escrito = $File['content'];
    $nom = $File['ext'];

    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: private", false); // required for certain browsers
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