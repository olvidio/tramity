<?php

// INICIO Cabecera global de URL de controlador *********************************
use convertirdocumentos\model\DocConverter;
use core\ConfigGlobal;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\EntradaDocDB;
use entradas\model\EntradaProvisionalFromPdf;
use escritos\model\TextoDelEscrito;
use etherpad\model\Etherpad;
use web\DateTimeLocal;

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
    $Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

    $preview = [];
    $config = [];
    $errors = [];
    $input = 'entradas'; // the input name for the fileinput plugin
    if (empty($_FILES[$input])) {
        return [];
    }

    $total = count($_FILES[$input]['name']); // multiple files
    for ($i = 0; $i < $total; $i++) {
        $tmpFilePath = $_FILES[$input]['tmp_name'][$i]; // the temp file path
        $fileName = $_FILES[$input]['name'][$i]; // the file name
        $type = $_FILES[$input]['type'][$i]; // the type file

        //Make sure we have a file path
        if ($tmpFilePath !== '') {

            $fp = fopen($tmpFilePath, 'rb');
            $contenido_doc = fread($fp, filesize($tmpFilePath));

            if ($type !== 'application/pdf') {
                $path_parts = pathinfo($fileName);
                $fileName_sin_extension = $path_parts['filename'];
                $oDocConverterPdf = new DocConverter();
//                $oDocConverter->setPathFicheroOriginal($tmpFilePath);
                $oDocConverterPdf->setNombreFicheroOriginalConExtension($fileName);
                $oDocConverterPdf->setNombreFicheroNuevoSinExtension($fileName_sin_extension);
                $oDocConverterPdf->setDocIn($contenido_doc);
                $contenido_en_pdf = $oDocConverterPdf->convert('pdf', FALSE);
                $filename_pdf = $oDocConverterPdf->getNombreFicheroNuevoConExtension();
            } else {
                $contenido_en_pdf = $contenido_doc;
                $filename_pdf = $tmpFilePath;
            }

            if ($type !== 'application/html') {
                $path_parts = pathinfo($fileName);
                $fileName_sin_extension = $path_parts['filename'];
                $oDocConverterHtml = new DocConverter();
                $oDocConverterHtml->setNombreFicheroOriginalConExtension($fileName);
                $oDocConverterHtml->setNombreFicheroNuevoSinExtension($fileName_sin_extension);
                $oDocConverterHtml->setDocIn($contenido_doc);
                $contenido_en_html = $oDocConverterHtml->convert('html', FALSE);
            } else {
                $contenido_en_html = $contenido_doc;
            }

            $EntradaProvisional = new EntradaProvisionalFromPdf($contenido_en_pdf);
            $EntradaProvisional->setFiltro($Q_filtro);
            $id_entrada = $EntradaProvisional->crear_entrada_provisional($fileName);

            // añadir el contenido convertido en html en el etherpad
            // TODO: cambiar la opción por defecto de guardar entradas como synotext en vez de etherpad
            $oTextDelEscrito = new TextoDelEscrito(TextoDelEscrito::TIPO_ETHERPAD, TextoDelEscrito::ID_ENTRADA, $id_entrada);
            $oTextDelEscrito->crearTexto();
            $oTextDelEscrito->setHTML($contenido_en_html);

            // la relación con la entrada y la fecha
            $oEntradaDocDB = new EntradaDocDB($id_entrada);
            $oFecha = $oEntradaDocDB->getF_doc();
            if ($oFecha === null || !$oFecha instanceof DateTimeLocal) {
                // No puede ser NULL
                $oHoy = new DateTimeLocal();
                $oEntradaDocDB->setF_doc($oHoy);
            }
            $oEntradaDocDB->setTipo_doc(TextoDelEscrito::TIPO_ETHERPAD);
            $oEntradaDocDB->DBGuardar();

            // añadir fichero como adjunto
            $oEntradaAdjunto = new EntradaAdjunto();
            $oEntradaAdjunto->setId_entrada($id_entrada);
            $oEntradaAdjunto->setNom($fileName);
            $oEntradaAdjunto->setAdjunto($contenido_doc);

            if ($oEntradaAdjunto->DBGuardar() !== FALSE) {
                $id_item = $oEntradaAdjunto->getId_item();
            }
            $path_temp = ConfigGlobal::directorio() . '/log/entradas/';
            if (!file_exists($path_temp)) {
                if (!mkdir($path_temp, 0777, true) && !is_dir($path_temp)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path_temp));
                }
            }
            // rename
            $newName = $path_temp . 'entrada_' . $id_entrada . '.pdf';
            rename($filename_pdf, $newName);

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