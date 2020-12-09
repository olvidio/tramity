<?php
use envios\model\Enviar;
use expedientes\model\entity\EscritoAdjunto;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Qid_escrito = (integer) \filter_input(INPUT_GET, 'id_escrito');

if (!empty($Qid_escrito)) {

    $oEnviar = new Enviar($Qid_escrito,'escrito');
    
    $File = $oEnviar->getPdf();
    
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
    //header("Content-Type: $ctype");
    //header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
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