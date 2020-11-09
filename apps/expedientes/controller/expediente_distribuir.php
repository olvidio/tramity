<?php
use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\EscritoLista;
use expedientes\model\Expediente;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\Tramite;
use usuarios\model\entity\GestorCargo;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

if (empty($Qid_expediente)) {
    exit ("Error, no existe el expediente");
}
$oExpediente = new Expediente();

$oExpediente->setId_expediente($Qid_expediente);
$oExpediente->DBCarregar();

$ponente_txt = '?';
$id_ponente = $oExpediente->getPonente();
$gesCargos = new GestorCargo();
$aCargos =$gesCargos->getArrayCargos();
$ponente_txt = $aCargos[$id_ponente];

$id_tramite = $oExpediente->getId_tramite();
$oTramite = new Tramite($id_tramite);
$tramite_txt = $oTramite->getTramite();

$estado = $oExpediente->getEstado();
$a_estado = $oExpediente->getArrayEstado();
$estado_txt = $a_estado[$estado];

$prioridad = $oExpediente->getPrioridad();
$a_prioridad = $oExpediente->getArrayPrioridad();
$prioridad_txt = $a_prioridad[$prioridad];

$vida = $oExpediente->getVida();
$a_vida = $oExpediente->getArrayVida();
$vida_txt = $a_vida[$vida];

$f_contestar = $oExpediente->getF_contestar()->getFromLocal();
$f_ini_circulacion = $oExpediente->getF_ini_circulacion()->getFromLocal();
$f_reunion = $oExpediente->getF_reunion()->getFromLocal();
$f_aprobacion = $oExpediente->getF_aprobacion()->getFromLocal();

$asunto = $oExpediente->getAsunto();
$entradilla = $oExpediente->getEntradilla();

$oEscritoLista = new EscritoLista();
$oEscritoLista->setId_expediente($Qid_expediente);

if ($Qfiltro == 'distribuir') {
    $btn_action = 'distribuir';
    $txt_btn_success = _("Distribuir");
    $oEscritoLista->setFiltro('lista');
} else {
    $btn_action = 'archivar';
    $txt_btn_success = _("Archivar");
    $oEscritoLista->setFiltro('acabados');
}


// Comentarios y Aclaraciones
$gesFirmas = new GestorFirma();
$aRecorrido = $gesFirmas->getRecorrido($Qid_expediente);
$a_recorrido = $aRecorrido['recorrido'];
$comentarios = $aRecorrido['comentarios'];

$lista_antecedentes = $oExpediente->getHtmlAntecedentes(FALSE);

$url_update = 'apps/expedientes/controller/expediente_update.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query(['filtro' => $Qfiltro]));
$server = ConfigGlobal::getWeb(); //http://tramity.local

$a_campos = [
    'id_expediente' => $Qid_expediente,
    //'oHash' => $oHash,
    'ponente_txt' => $ponente_txt,
    'id_ponente' => $id_ponente,
    'tramite_txt' => $tramite_txt,
    'estado_txt' => $estado_txt,
    'prioridad_txt' => $prioridad_txt,
    'vida_txt' => $vida_txt,

    'f_contestar' => $f_contestar,
    'f_ini_circulacion' => $f_ini_circulacion,
    'f_reunion' => $f_reunion,
    'f_aprobacion' => $f_aprobacion,
    
    'asunto' => $asunto,
    'entradilla' => $entradilla,
    'comentarios' => $comentarios,
    'a_recorrido' => $a_recorrido,
    
    'lista_antecedentes' => $lista_antecedentes,
    
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    // para la pagina js
    'server' => $server,
    //acciones
    'oEscritoLista' => $oEscritoLista,
    'firltro' => $Qfiltro,
    'btn_action' => $btn_action,
    'txt_btn_success' => $txt_btn_success,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_distribuir.html.twig',$a_campos);