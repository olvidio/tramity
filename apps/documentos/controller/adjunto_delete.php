<?php

use documentos\domain\repositories\DocumentoRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El delete es via POST!!!";

$Q_id_doc = (integer)filter_input(INPUT_POST, 'key');

if (!empty($Q_id_doc)) {
    $documentoRepository = new DocumentoRepository();
    $oDocumento = $documentoRepository->findById($Q_id_doc);
    if ($oDocumento === null) {
        $err_cargar = sprintf(_("OJO! no existe el documento en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_cargar);
    }
    /* The deleteUrl server action must send data via AJAX request as a JSON response {error: BOOLEAN_VALUE} */
    $error = FALSE;
    $oDocumento->setDocumento();
    if ($documentoRepository->Guardar($oDocumento) === FALSE) {
        $error = TRUE;
    }
} else {
    $error = TRUE;
}

$outData = "{'error': $error}";
echo json_encode($outData); // return json data