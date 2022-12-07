<?php

use documentos\model\Documento;
use web\DateTimeLocal;

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
    $Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');

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
            if ($tmpFilePath != "") {

                $fp = fopen($tmpFilePath, 'rb');
                $contenido_doc = fread($fp, filesize($tmpFilePath));

                $oHoy = new DateTimeLocal();
                $hoy_iso = $oHoy->getIso();

                $oDocumento = new Documento($Q_id_doc);
                if ($oDocumento->DBCargar() === FALSE) {
                    $err_cargar = sprintf(_("OJO! no existe el documento en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_cargar);
                }
                $oDocumento->setNombre_fichero($fileName);
                $oDocumento->setTipo_doc(Documento::DOC_UPLOAD);
                $oDocumento->setDocumento($contenido_doc);
                $oDocumento->setF_upload($hoy_iso, FALSE);

                if ($oDocumento->DBGuardar() !== FALSE) {
                    $preview[] = "'$fileName'";
                    $config[] = [
                        'key' => $Q_id_doc,
                        'caption' => $fileName,
                        'url' => 'apps/documentos/controller/adjunto_delete.php', // server api to delete the file based on key
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