<?php
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

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
function upload() {
    $preview = [];
    $config = [];
    $errors = [];
    $input = 'pdm_xml'; // the input name for the fileinput plugin
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
            if ($tmpFilePath != ""){
                
                $fp = fopen($tmpFilePath, 'rb');
                $contenido_doc = fread($fp, filesize($tmpFilePath));
                
                $dir = $_SESSION['oConfig']->getDock();
                // si es una resp. sólo hay que eliminar este fichero
				$fullfilename = $dir .'/repository/pmodes/'. $fileName;
                
				if (file_put_contents($fullfilename, $contenido_doc) !== FALSE) {
                    $preview[] = "'$fileName'";
                    $config[] = [
                        'key' => 1,
                        'caption' => $fileName,
                        'url' => 'xxapps/documentos/controller/adjunto_delete.php', // server api to delete the file based on key
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
            $img = count($errors) === 1 ? 'file "' . $errors[0]  . '" ' : 'files: "' . implode('", "', $errors) . '" ';
            $out['error'] = 'Oh snap! We could not upload the ' . $img . 'now. Please try again later.';
        }
        return $out;
    }
}