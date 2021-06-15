<?php

use documentos\model\Documento;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Qid_doc = (integer) \filter_input(INPUT_GET, 'key');
$Qplugin = (integer) \filter_input(INPUT_GET, 'plugin');

if (!empty($Qid_doc)) {
    $oDocumento = new Documento($Qid_doc);
    $escrito = $oDocumento->getDatosDocumento();
    $nombre_fichero = $oDocumento->getNombre_fichero();

    if (!empty($Qplugin)) {
        $extension = '';
        // Determine Content Type
        switch ($extension) {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "rtf": $ctype="application/msword"; break;
            case "doc": $ctype="application/msword"; break;
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            //default: $ctype="application/force-download";
            default: $ctype="application/octet-stream";
        }
        
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: private",false); // required for certain browsers
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        //header("Content-Type: $ctype");
        //header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
        header('Content-disposition: attachment; filename="' . $nombre_fichero . '"');
    }
    ob_clean();
    flush();
    echo fpassthru($escrito);
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}