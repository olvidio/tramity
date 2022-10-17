<?php

use documentos\model\Documento;
use escritos\model\entity\EscritoAdjunto;

// INICIO Cabecera global de URL de controlador *********************************
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
echo json_encode($outData); // return json data
exit(); // terminate

// main upload function used above
// upload the bootstrap-fileinput files
// returns associative array
function upload()
{
    $Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
    $Q_id_item = (integer)filter_input(INPUT_POST, 'id_item');

    $preview = [];
    $config = [];
    $errors = [];
    $input = 'adjuntos'; // the input name for the fileinput plugin
    if (empty($_FILES[$input])) {
        return [];
    } else {
        $total = count($_FILES[$input]['name']); // multiple files
        for ($i = 0; $i < $total; $i++) {
            $tmpFilePath = $_FILES[$input]['tmp_name'][$i]; // the temp file path
            $fileName = $_FILES[$input]['name'][$i]; // the file name
            $fileSize = $_FILES[$input]['size'][$i]; // the file size
            if ($fileSize > $_SESSION['oConfig']->getMax_filesize_en_bytes()) {
                exit (_("Fichero demasiado grande"));
            }

            //Make sure we have a file path
            if ($tmpFilePath != "" && $Q_id_escrito) {
                $fp = fopen($tmpFilePath, 'rb');
                $contenido_doc = fread($fp, filesize($tmpFilePath));

                if (!empty($Q_id_item)) {
                    // update
                    $oEscritoAdjunto = new EscritoAdjunto($Q_id_item);
                    $oEscritoAdjunto->DBCargar();
                } else {
                    // new
                    $oEscritoAdjunto = new EscritoAdjunto();
                }

                $oEscritoAdjunto->setId_escrito($Q_id_escrito);
                $oEscritoAdjunto->setNom($fileName);
                $oEscritoAdjunto->setTipo_doc(Documento::DOC_UPLOAD);
                $oEscritoAdjunto->setAdjunto($contenido_doc);

                if ($oEscritoAdjunto->DBGuardar() !== FALSE) {
                    $id_item = $oEscritoAdjunto->getId_item();
                    $preview[] = "'$fileName'";
                    $config[] = [
                        'key' => $id_item,
                        'caption' => $fileName,
                        'url' => 'apps/escritos/controller/adjunto_delete.php', // server api to delete the file based on key
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
}