<?php

use documentos\model\Documento;
use envios\model\MIMETypeLocal;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Q_id_doc = (integer)filter_input(INPUT_GET, 'key');

if (!empty($Q_id_doc)) {
    $oDocumento = new Documento($Q_id_doc);
    $nombre_fichero = $oDocumento->getNombre_fichero();
    $doc = $oDocumento->getDocumento();

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
    ob_clean();
    flush();
    echo $doc;

    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}