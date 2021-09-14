<?php
use expedientes\model\entity\EscritoAdjunto;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";

$Qid_item = (integer) \filter_input(INPUT_GET, 'key');

if (!empty($Qid_item)) {
    $oEscritoAdjunto = new EscritoAdjunto($Qid_item);
    $res_adjunto = $oEscritoAdjunto->getAdjuntoResource();
    $nom = $oEscritoAdjunto->getNom();
        
    if (!empty($res_adjunto)) {
        rewind($res_adjunto);
        $doc_encoded = stream_get_contents($res_adjunto);
        if ( base64_encode(base64_decode($doc_encoded, true)) === $doc_encoded){
            //echo '$data is valid';
            $doc = base64_decode($doc_encoded);
        } else {
            //ºecho '$data is NOT valid';
            $doc = $doc_encoded;
        }
    
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: private",false); // required for certain browsers
        header('Content-Type: application/force-download');
        //header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-disposition: attachment; filename="' . $nom . '"');
        ob_clean();
        flush();
        echo $doc;
    }
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}