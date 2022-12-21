<?php

use core\ViewTwig;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use expedientes\domain\repositories\AccionRepository;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepository;
use web\Protocolo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_escrito = (string)filter_input(INPUT_POST, 'id_escrito');
if (empty($Q_id_escrito)) {
    $Q_id_escrito = (string)filter_input(INPUT_GET, 'id_escrito');
}

$sigla = $_SESSION['oConfig']->getSigla();

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setBlanco(TRUE);

if (!empty($Q_id_escrito)) {
    $AccionRepository = new AccionRepository();
    $cAccion = $AccionRepository->getAcciones(['id_escrito' => $Q_id_escrito]);
    $id_expediente = $cAccion[0]->getId_expediente();
    $ExpedienteRepository = new ExpedienteRepository();
    $oExpediente = $ExpedienteRepository->findById($id_expediente);
    $estado = $oExpediente->getEstado();

    $base_url = core\ConfigGlobal::getWeb();
    $url_download = $base_url . '/src/escritos/controller/adjunto_download.php';
    $url_update = 'escrito_update.php';
    // Pueden ser varios escritos separados por comas:
    $a_escritos = explode(',', $Q_id_escrito);
    foreach ($a_escritos as $id_escrito) {
        $oEscrito = new Escrito($id_escrito);

        $destinos = $oEscrito->cabeceraIzquierda();
        $origen_txt = $oEscrito->cabeceraDerecha();

        $asunto = $oEscrito->getAsunto();
        $detalle = $oEscrito->getDetalle();
        // está anulado?
        $anulado = $oEscrito->isAnulado();
        if (is_true($anulado)) {
            $chk_anulado = 'checked';
        } else {
            $chk_anulado = '';
        }
        $anular = FALSE;
        if ($estado == Expediente::ESTADO_ACABADO) {
            $anular = TRUE;
        }

        $a_adjuntos = $oEscrito->getArrayIdAdjuntos();

        // mirar si tienen escrito
        $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
        $tipo_doc = $oEscrito->getTipo_doc();

        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito);

        $escrito_html = $oEtherpad->generarHtml();

        $oView = new ViewTwig('escritos/controller');
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
        $oView->renderizar('escrito_distribuir.html.twig', $a_campos);
    }
} else {
    $txt_alert = _("No hay escritos");
    $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => TRUE];
    $oView = new ViewTwig('expedientes/controller');
    $oView->renderizar('alerta.html.twig', $a_campos);
}