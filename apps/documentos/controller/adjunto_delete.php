<?php

// INICIO Cabecera global de URL de controlador *********************************
use documentos\model\Documento;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El delete es via POST!!!";

$Qid_doc = (integer) \filter_input(INPUT_POST, 'key');

if (!empty($Qid_doc)) {
    $oDocumento = new Documento($Qid_doc);
    $oDocumento->DBCarregar('');

    /* The deleteUrl server action must send data via AJAX request as a JSON response {error: BOOLEAN_VALUE} */
    $error = FALSE;
    $oDocumento->setDocumento('');
    if ($oDocumento->DBGuardar() === FALSE) {
        $error = TRUE;
    }
} else {
    $error = TRUE;
}

$outData = "{'error': $error}";
echo json_encode($outData); // return json data