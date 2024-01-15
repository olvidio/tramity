<?php

use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\EntradaCompartidaAdjunto;
use envios\model\MIMETypeLocal;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Qid_item = (integer)filter_input(INPUT_GET, 'key');
$Qcompartida = (integer)filter_input(INPUT_GET, 'compartida');

if (!empty($Qid_item)) {
    if (is_true($Qcompartida)) {
        $oEntradaAdjunto = new EntradaCompartidaAdjunto($Qid_item);
    } else {
        $oEntradaAdjunto = new EntradaAdjunto($Qid_item);
    }
    $nombre_fichero = $oEntradaAdjunto->getNom();
    $doc = $oEntradaAdjunto->getAdjunto();

    $file_extension = strtolower(substr(strrchr($nombre_fichero, "."), 1));
    $oMimeType = new MIMETypeLocal();
    $ctype = $oMimeType->getMimeType($file_extension);
    $ctype = empty($ctype) ? "application/octet-stream" : $ctype;

    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: private", false); // required for certain browsers
    header('Content-Type: application/force-download');
    header('Content-Type: application/download', false);
    header('Content-Type: ' . $ctype);
    header('Content-disposition: attachment; filename="' . $nombre_fichero . '"');
    //in case of more output buffers was opened.
    while (ob_get_level()) {
        ob_end_clean();
    }
    flush();
    echo $doc;
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}