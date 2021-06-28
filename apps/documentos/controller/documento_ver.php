<?php
use core\ViewTwig;
use documentos\model\Documento;
use etherpad\model\Etherpad;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
$Qmethod = (string) \filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if ($Qmethod == 'POST') {
    $Qid_doc = (integer) \filter_input(INPUT_POST, 'id_doc');
}
if ($Qmethod == 'GET') {
    $Qid_doc = (integer) \filter_input(INPUT_GET, 'id_doc');
}

$oDocumento = new Documento($Qid_doc);
if (!empty($Qid_doc)) {
    
    $nom = $oDocumento->getNom();
    $f_upload = $oDocumento->getF_upload()->getFromLocal();
    $tipo_doc = $oDocumento->getTipo_doc();
    switch($tipo_doc) {
        case Documento::DOC_ETHERPAD:
            $oEtherpad = new Etherpad();
            $oEtherpad->setId (Etherpad::ID_DOCUMENTO,$Qid_doc);
            
            $escrito_html = $oEtherpad->generarHtml();
            break;
        case $oDocumento::DOC_UPLOAD:
            break;
    }
    
} else {
    $nom = '';
    $f_upload = '';
    $escrito_html = '';
}


$base_url = core\ConfigGlobal::getWeb();
$url_download_pdf = $base_url.'/apps/documentos/controller/adjunto_download.php';

$a_campos = [
    'id_doc' => $Qid_doc,
    //'oHash' => $oHash,
    'nom' => $nom,
    'f_upload' => $f_upload,
    'base_url' => $base_url,
    'escrito_html' => $escrito_html,
    'url_download_pdf' => $url_download_pdf,
];

$oView = new ViewTwig('documentos/controller');
echo $oView->renderizar('documento_ver.html.twig',$a_campos);