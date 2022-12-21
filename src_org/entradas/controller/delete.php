<?php

use entradas\domain\repositories\EntradaAdjuntoRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El delete es via POST!!!";

$Qid_item = (integer)filter_input(INPUT_POST, 'key');

if (!empty($Qid_item)) {
    $entradaAdjuntoRepository = new EntradaAdjuntoRepository();
    $oEntradaAdjunto = $entradaAdjuntoRepository->findById($Qid_item);

    /* The deleteUrl server action must send data via AJAX request as a JSON response {error: BOOLEAN_VALUE} */
    $error = FALSE;
    if ($entradaAdjuntoRepository->Eliminar($oEntradaAdjunto) === FALSE) {
        $error = TRUE;
    }
} else {
    $error = TRUE;
}

$outData = "{'error': $error}";
echo json_encode($outData); // return json data