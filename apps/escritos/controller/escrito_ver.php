<?php

use core\ViewTwig;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_escrito = (string)filter_input(INPUT_POST, 'id_escrito');

// Para poder moverse de una escrito a otro:
$Q_Slide_mode = (bool)filter_input(INPUT_POST, 'slide_mode', FILTER_VALIDATE_BOOLEAN);

if ($Q_Slide_mode === TRUE) {
    $Q_mov = (string)filter_input(INPUT_POST, 'mov');

    if ($Q_mov === 'prev') {
        --$Q_id_escrito;
    }
    if ($Q_mov === 'next') {
        ++$Q_id_escrito;
    }
} else {
    $Q_id_escrito = (string)filter_input(INPUT_GET, 'id_escrito');
}

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setBlanco(TRUE);

$pagina = core\ConfigGlobal::getWeb() . '/apps/escritos/controller/escrito_ver.php';
$a_cosas = ['id_escrito' => $Q_id_escrito, 'slide_mode' => 'TRUE', 'mov' => 'prev'];
$pagina_prev = web\Hash::link($pagina . '?' . http_build_query($a_cosas));
$a_cosas = ['id_escrito' => $Q_id_escrito, 'slide_mode' => 'TRUE', 'mov' => 'next'];
$pagina_next = web\Hash::link($pagina . '?' . http_build_query($a_cosas));

if (!empty($Q_id_escrito)) {
    $base_url = core\ConfigGlobal::getWeb();
    $url_download = $base_url . '/apps/escritos/controller/adjunto_download.php';
    $url_download_pdf_adjunto = $base_url . '/apps/escritos/controller/adjunto_download_as_pdf.php';
    $url_download_pdf = $base_url . '/apps/escritos/controller/escrito_download.php';
    // Pueden ser varios escritos separados por comas:
    $a_escritos = explode(',', $Q_id_escrito);
    $primero = 1;
    $todosHtml = '';
    $oEtherpad = new Etherpad();
    if (count($a_escritos) > 1) {
        $oEtherpad->setMultiple(TRUE);
    }
    foreach ($a_escritos as $id_escrito) {
        $oEscrito = new Escrito($id_escrito);

        $destinos = $oEscrito->cabeceraIzquierda();
        $origen_txt = $oEscrito->cabeceraDerecha();

        $asunto_e = $oEscrito->getAsunto();

        $a_adjuntos = $oEscrito->getArrayIdAdjuntos();

        // mirar si tienen escrito
        $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
        $tipo_doc = $oEscrito->getTipo_doc();

        $oEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito);

        $escrito_html = $oEtherpad->generarHtml();

        $oView = new ViewTwig('escritos/controller');
        if ($Q_Slide_mode === TRUE) {
            $a_campos = [
                'id_escrito' => $id_escrito,
                //'oHash' => $oHash,
                'destinos' => $destinos,
                'origen_txt' => $origen_txt,
                //'oArrayProtDestino' => $oArrayProtDestino,
                //'oArrayProtRef' => $oArrayProtRef,
                'asunto_e' => $asunto_e,
                'f_escrito' => $f_escrito,
                'tipo_doc' => $tipo_doc,
                'a_adjuntos' => $a_adjuntos,
                'pagina_prev' => $pagina_prev,
                'pagina_next' => $pagina_next,
                'base_url' => $base_url,
                'escrito_html' => $escrito_html,
                'url_download' => $url_download,
            ];
            $todosHtml .= $oView->renderizar('escrito_ver_slide.html.twig', $a_campos);
        } else {
            $a_campos = [
                'primero' => $primero,
                'id_escrito' => $id_escrito,
                //'oHash' => $oHash,
                'destinos' => $destinos,
                'origen_txt' => $origen_txt,
                //'oArrayProtDestino' => $oArrayProtDestino,
                //'oArrayProtRef' => $oArrayProtRef,
                'asunto_e' => $asunto_e,
                'f_escrito' => $f_escrito,
                'tipo_doc' => $tipo_doc,
                'a_adjuntos' => $a_adjuntos,
                'escrito_html' => $escrito_html,
                'base_url' => $base_url,
                'url_download' => $url_download,
                'url_download_pdf' => $url_download_pdf,
                'url_download_pdf_adjunto' => $url_download_pdf_adjunto,
            ];
            $todosHtml .= $oView->renderizar('escrito_ver.html.twig', $a_campos);
        }
        $primero = 0;
    }
    $oEtherpad->setMultiple(FALSE);
    echo $todosHtml;
    exit();
} else {
    $txt_alert = _("No hay escritos");
    $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => TRUE];
    $oView = new ViewTwig('expedientes/controller');
    $oView->renderizar('alerta.html.twig', $a_campos);
}