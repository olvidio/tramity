<?php

use core\ViewTwig;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use expedientes\model\Expediente;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (string)filter_input(INPUT_GET, 'id_expediente');

$oExpediente = new Expediente($Q_id_expediente);

$aAntecedentes = $oExpediente->getJson_antecedentes(TRUE);
if (!empty($aAntecedentes)) {
    $html = '<ol>';
    foreach ($aAntecedentes as $antecedente) {
        $id = $antecedente['id'];
        $tipo = $antecedente['tipo'];
        switch ($tipo) {
            case 'entrada':
                $oEntrada = new Entrada($id);
                $asunto = $oEntrada->getAsuntoDetalle();
                $prot_local = $oEntrada->cabeceraDerecha();
                $nom = empty($prot_local) ? '' : $prot_local;
                $nom .= empty($nom) ? "$asunto" : ": $asunto";
                $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada($id);\" >$nom</span>";
                $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                break;
            case 'expediente':
                $oExpediente = new Expediente($id);
                $asunto = $oExpediente->getAsunto();
                $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente($id);\" >$asunto</span>";
                $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                break;
            case 'escrito':
                $oEscrito = new Escrito($id);
                $asunto = $oEscrito->getAsuntoDetalle();
                $prot_local = $oEscrito->cabeceraDerecha();
                $nom = empty($prot_local) ? '' : $prot_local;
                $nom .= empty($nom) ? "$asunto" : ": $asunto";
                $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito($id);\" >$nom</span>";
                $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";

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
                break;
            case 'documento':
                $oDocumento = new Documento($id);
                $tipo_doc = $oDocumento->getTipo_doc();
                $nom = $oDocumento->getNom();
                $nom = empty($nom) ? _("este documento se ha eliminado") : $nom;
                $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_documento($id,$tipo_doc);\" >$nom</span>";
                $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
    }
}

exit();
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