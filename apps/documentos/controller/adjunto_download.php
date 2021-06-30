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

if (!empty($Qid_doc)) {
    $oDocumento = new Documento($Qid_doc);
    $nombre_fichero = $oDocumento->getNombre_fichero();
    $res_documento = $oDocumento->getDocumentoResource();

    if (!empty($res_documento)) {
        rewind($res_documento);
        $doc_encoded = stream_get_contents($res_documento);
        $doc = base64_decode($doc_encoded);

        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: private",false); // required for certain browsers
        header('Content-Type: application/force-download');
        //header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-disposition: attachment; filename="' . $nombre_fichero . '"');
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