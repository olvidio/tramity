<?php

use convertirdocumentos\model\DocConverter;
use envios\model\MIMETypeLocal;
use escritos\model\entity\EscritoAdjunto;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Q_id_item = (integer)filter_input(INPUT_GET, 'key');

if (!empty($Q_id_item)) {
    $oEscritoAdjunto = new EscritoAdjunto($Q_id_item);
    $nombre_fichero = $oEscritoAdjunto->getNom();
    $doc = $oEscritoAdjunto->getAdjunto();
    $path_parts = pathinfo($nombre_fichero);
    //$base_name = $path_parts['basename'];
    $file_extension = $path_parts['extension'];
    $file_name = $path_parts['filename'];

    if ($file_extension !== 'pdf') {
        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($nombre_fichero);
        $oDocConverter->setDocIn($doc);
        $doc = $oDocConverter->convert('pdf');
        $nombre_fichero_pdf = $file_name.'.pdf';
        $file_extension = 'pdf';
    }

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
    header('Content-disposition: attachment; filename="' . $nombre_fichero_pdf . '"');
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