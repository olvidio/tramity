<?php
use envios\model\MimeTypeLocal;
use escritos\model\entity\EscritoAdjunto;

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
    $nombre_fichero = $oEscritoAdjunto->getNom();
    $doc = $oEscritoAdjunto->getAdjunto();
    
    $file_extension = strtolower(substr(strrchr($nombre_fichero,"."),1));
    $oMimeType = new MimeTypeLocal();
    $ctype = $oMimeType->getMimeType($file_extension);
    $ctype = empty($ctype)? "application/octet-stream" : $ctype;
    
	header('Content-Description: File Transfer');
	header('Content-Transfer-Encoding: binary');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: private",false); // required for certain browsers
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