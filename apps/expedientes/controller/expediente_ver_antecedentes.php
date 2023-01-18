<?php

use convertirdocumentos\model\DocConverter;
use convertirdocumentos\model\MergePdf;
use documentos\model\Documento;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\Entrada;
use envios\model\Enviar;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use expedientes\model\Expediente;
use expedientes\model\VerAntecedentes;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (string)filter_input(INPUT_GET, 'id_expediente');

$oExpediente = new Expediente($Q_id_expediente);

$path_temp = '/tmp/';
$filenameOut = '/tmp/prova.pdf';
$aFiles = [];
$aAntecedentes = $oExpediente->getJson_antecedentes(TRUE);

$VerAntecedentes = new VerAntecedentes($path_temp);
$aFiles = $VerAntecedentes->verEnPdf($aAntecedentes);

$oMergePdf = new MergePdf();
$all_content = $oMergePdf->mergePdfFiles($aFiles, $filenameOut);

header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: public, must-revalidate, max-age=0');
header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: private", false); // required for certain browsers
header('Content-Type: application/force-download');
header('Content-Type: application/octet-stream', false);
header('Content-Type: application/download', false);
header('Content-disposition: attachment; filename="' . $filenameOut . '"');

ob_clean();
flush();
echo $all_content;
exit();