<?php

// INICIO Cabecera global de URL de controlador *********************************
use convertirdocumentos\model\DocConverter;
use entradas\model\EntradaProvisionalFromPdf;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// example of a PHP server code that is called in `uploadUrl` above
// file-upload-batch script
header('Content-Type: application/json'); // set json response headers
$outData = upload(); // a function to upload the bootstrap-fileinput files
echo json_encode($outData, JSON_THROW_ON_ERROR); // return json data
exit(); // terminate

// main upload function used above
// upload the bootstrap-fileinput files
// returns associative array
function upload(): array
{
    $Qid_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
    $Qid_item = (integer)filter_input(INPUT_POST, 'id_item');

    $preview = [];
    $config = [];
    $errors = [];
    $input = 'entradas'; // the input name for the fileinput plugin
    if (empty($_FILES[$input])) {
        return [];
    }

    $base_name = 'entradas';
    $total = count($_FILES[$input]['name']); // multiple files
    for ($i = 0; $i < $total; $i++) {
        $tmpFilePath = $_FILES[$input]['tmp_name'][$i]; // the temp file path
        $fileName = $_FILES[$input]['name'][$i]; // the file name
        $type = $_FILES[$input]['type'][$i]; // the file name

        //Make sure we have a file path
        if ($tmpFilePath !== '') {

            $fp = fopen($tmpFilePath, 'rb');
            $contenido_doc = fread($fp, filesize($tmpFilePath));

            if ($type !== 'application/pdf') {
                $oDocConverter = new DocConverter();
                $oDocConverter->setBaseName($base_name);
                $oDocConverter->setFileName($fileName);
                $oDocConverter->setDocIn($contenido_doc);
                $doc = $oDocConverter->convert();
                $nombre_fichero = $fileName . '.pdf';
                $file_extension = 'pdf';
            }

            $EntradaProvisional = new EntradaProvisionalFromPdf($pdf);


        } else {
            $errors[] = $fileName;
        }
    }
    $out = ['initialPreview' => $preview, 'initialPreviewConfig' => $config];
    if (!empty($errors)) {
        $img = count($errors) === 1 ? 'file "' . $errors[0] . '" ' : 'files: "' . implode('", "', $errors) . '" ';
        $out['error'] = 'Oh snap! We could not upload the ' . $img . 'now. Please try again later.';
    }
    return $out;
}