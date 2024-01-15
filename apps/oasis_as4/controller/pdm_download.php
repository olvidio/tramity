<?php

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


// El download es via GET!!!";
$Q_filename = (string)filter_input(INPUT_GET, 'filename');

if (!empty($Q_filename)) {
    // resp
    $dir = $_SESSION['oConfig']->getDock();
    $fullfileame = $dir . '/repository/pmodes_resp/' . $Q_filename;

    $doc = file_get_contents($fullfileame);

    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: private", false); // required for certain browsers
    header('Content-Type: application/force-download');
    header('Content-Type: application/download', false);
    header('Content-disposition: attachment; filename="' . $Q_filename . '"');
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