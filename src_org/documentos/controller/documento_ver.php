<?php

use core\ViewTwig;
use documentos\domain\entity\Documento;
use documentos\domain\repositories\DocumentoRepository;
use etherpad\model\Etherpad;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
$Q_method = (string)filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if ($Q_method === 'POST') {
    $Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');
}
if ($Q_method === 'GET') {
    $Q_id_doc = (integer)filter_input(INPUT_GET, 'id_doc');
}


$documentoRepository = new DocumentoRepository();
if (!empty($Q_id_doc)) {
    $oDocumento = $documentoRepository->findById($Q_id_doc);
    $nom = $oDocumento->getNom();
    $f_upload = $oDocumento->getF_upload()->getFromLocal();
    $tipo_doc = $oDocumento->getTipo_doc();
    switch ($tipo_doc) {
        case Documento::DOC_ETHERPAD:
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_DOCUMENTO, $Q_id_doc);

            $escrito_html = $oEtherpad->generarHtml();
            break;
        case $oDocumento::DOC_UPLOAD:
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }

} else {
    $nom = '';
    $f_upload = '';
    $escrito_html = '';
}


$base_url = core\ConfigGlobal::getWeb();

$a_campos = [
    'id_doc' => $Q_id_doc,
    //'oHash' => $oHash,
    'nom' => $nom,
    'f_upload' => $f_upload,
    'base_url' => $base_url,
    'escrito_html' => $escrito_html,
];

$oView = new ViewTwig('documentos/controller');
$oView->renderizar('documento_ver.html.twig', $a_campos);