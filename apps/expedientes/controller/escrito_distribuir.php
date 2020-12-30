<?php
use core\ViewTwig;
use function core\is_true;
use etherpad\model\Etherpad;
use expedientes\model\Escrito;
use web\Protocolo;
use web\ProtocoloArray;
use expedientes\model\Expediente;
use expedientes\model\entity\GestorAccion;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_escrito = (string) \filter_input(INPUT_POST, 'id_escrito');
if (empty($Qid_escrito)) {
    $Qid_escrito = (string) \filter_input(INPUT_GET, 'id_escrito');
}

$sigla = $_SESSION['oConfig']->getSigla();

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setBlanco(TRUE);

if (!empty($Qid_escrito)) {
    $gesAcciones = new GestorAccion();
    $cAccion = $gesAcciones->getAcciones(['id_escrito' => $Qid_escrito]);
    $id_expediente = $cAccion[0]->getId_expediente();
    $oExpediente = new Expediente($id_expediente);
    $estado = $oExpediente->getEstado();
    
    $base_url = core\ConfigGlobal::getWeb();
    $url_download = $base_url.'/apps/expedientes/controller/adjunto_download.php?plugin=1';
    $url_update = 'escrito_update.php';
    // Pueden ser varios escritos separados por comas:
    $a_escritos = explode(',', $Qid_escrito);
    foreach ($a_escritos as $id_escrito) {
        $oEscrito = new Escrito($id_escrito);
        
        $destinos = $oEscrito->cabeceraIzquierda();
        $origen_txt = $oEscrito->cabeceraDerecha();
        
        $asunto = $oEscrito->getAsunto();
        $detalle = $oEscrito->getDetalle();
        // está anulado?
        $anulado = $oEscrito->getAnulado();
        if (is_true($anulado)) {
            $chk_anulado = 'checked'; 
        } else {
            $chk_anulado = '';
        }
        $anular = TRUE;
        if ( $estado == Expediente::ESTADO_ACABADO) {
            $anular = FALSE;
        }
        
        $a_adjuntos = $oEscrito->getArrayIdAdjuntos();
        
        // mirar si tienen escrito
        $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
        $tipo_doc = $oEscrito->getTipo_doc();
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ESCRITO,$id_escrito);
        
        $escrito_html = $oEtherpad->generarHtml();

        $oView = new ViewTwig('expedientes/controller');
        $a_campos = [
            'id_escrito' => $id_escrito,
            //'oHash' => $oHash,
            'destinos' => $destinos,
            'origen_txt' => $origen_txt,
            'asunto' => $asunto,
            'detalle' => $detalle,
            'chk_anulado' => $chk_anulado,
            'anular' => $anular,
            'f_escrito' => $f_escrito,
            'tipo_doc' => $tipo_doc,
            'a_adjuntos' => $a_adjuntos,
            'sigla' => $sigla,
            'escrito_html' => $escrito_html,
            'base_url' => $base_url,
            'url_download' => $url_download,
            'url_update' => $url_update,
        ];
        echo $oView->renderizar('escrito_distribuir.html.twig',$a_campos);
    }
} else {
    $txt_alert = _("No hay escritos");
    $a_campos = [ 'txt_alert' => $txt_alert, 'btn_cerrar' => TRUE ];
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('alerta.html.twig',$a_campos);
}