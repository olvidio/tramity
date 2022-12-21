<?php


// INICIO Cabecera global de URL de controlador *********************************
use entradas\domain\entity\EntradaAdjunto;
use entradas\domain\repositories\EntradaAdjuntoRepository;
use usuarios\domain\repositories\EntradaCompartidaAdjuntoRepository;

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
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
    $input = 'adjuntos'; // the input name for the fileinput plugin
    if (empty($_FILES[$input])) {
        return [];
    }

    $total = count($_FILES[$input]['name']); // multiple files
    $entradaAdjuntoRepository = new EntradaAdjuntoRepository();
    for ($i = 0; $i < $total; $i++) {
        $tmpFilePath = $_FILES[$input]['tmp_name'][$i]; // the temp file path
        $fileName = $_FILES[$input]['name'][$i]; // the file name

        //Make sure we have a file path
        if ($tmpFilePath !== '' && $Qid_entrada) {

            $fp = fopen($tmpFilePath, 'rb');
            $contenido_doc = fread($fp, filesize($tmpFilePath));

            if (!empty($Qid_item)) {
                // update
                $oEntradaAdjunto = $entradaAdjuntoRepository->findById($Qid_item);
            } else {
                // new
                $id_item = $entradaAdjuntoRepository->getNewId_item();
                $oEntradaAdjunto = new EntradaAdjunto();
                $oEntradaAdjunto->setId_item($id_item);
            }

            $oEntradaAdjunto->setId_entrada($Qid_entrada);
            $oEntradaAdjunto->setNom($fileName);
            $oEntradaAdjunto->setAdjunto($contenido_doc);

            if ($entradaAdjuntoRepository->Guardar($oEntradaAdjunto) !== FALSE) {
                $id_item = $oEntradaAdjunto->getId_item();
                $preview[] = "'$fileName'";
                $config[] = [
                    'key' => $id_item,
                    'caption' => $fileName,
                    'url' => 'src/entradas/controller/delete.php', // server api to delete the file based on key
                ];
            } else {
                $errors[] = $fileName;
            }
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