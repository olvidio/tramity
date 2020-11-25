<?php
use core\ConfigGlobal;
use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\EscritoLista;
use expedientes\model\Expediente;
use tramites\model\entity\Firma;
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

$plazo_normal = 15;
$plazo_urgente = 5;
$plazo_muy_urgente = 3;
$error_fecha = 15;

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$gesCargos = new GestorCargo();
$aCargos =$gesCargos->getArrayCargos();

$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

if (empty($Qid_expediente)) {
    exit ("Error, no existe el expediente");
}
$oExpediente = new Expediente();

$oExpediente->setId_expediente($Qid_expediente);
$oExpediente->DBCarregar();

$ponente_txt = '?';
$id_ponente = $oExpediente->getPonente();
$ponente_txt = $aCargos[$id_ponente];

if ($id_ponente == ConfigGlobal::mi_id_cargo()) {
    $aclaracion = _("Responder aclaración");
    $aclaracion_event = 'respuesta';
} else {
    $aclaracion = _("Pedir aclaración");
    $aclaracion_event = 'nueva';
}

$id_tramite = $oExpediente->getId_tramite();
$oTramite = new Tramite($id_tramite);
$tramite_txt = $oTramite->getTramite();

$estado = $oExpediente->getEstado();
$a_estado = $oExpediente->getArrayEstado();
$estado_txt = $a_estado[$estado];

// Valores posibles para la firma
$oFirma = new Firma();
$a_firmas = [];
$rango = 'voto';
if (ConfigGlobal::mi_usuario_cargo() === 'vcd') {
    if ($estado == Expediente::ESTADO_CIRCULANDO){
        $rango = 'vb_vcd';
    } else {
        $rango = 'vcd';
    }
}
foreach ($oFirma->getArrayValor($rango) as $key => $valor) {
    $a_voto['id'] = $key;
    $a_voto['valor'] = $valor;
    $a_firmas[] = $a_voto;
}
    
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
$oEscritoLista->setFiltro('lista');

// Comentarios y Aclaraciones
$gesFirmas = new GestorFirma();
$aRecorrido = $gesFirmas->getRecorrido($Qid_expediente);
$a_recorrido = $aRecorrido['recorrido'];
$comentarios = $aRecorrido['comentarios'];

// Etiquetas
$cEtiquetas = $oExpediente->getEtiquetasVisibles();
/*
$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
*/
$a_etiquetas = [];
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    $a_etiquetas[] = $id_etiqueta;
}
$oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas,$a_posibles_etiquetas,'etiquetas');

$oficinas = $oExpediente->getResto_oficinas();

$oArrayDesplFirmas = new web\DesplegableArray($oficinas,$a_posibles_cargos,'oficinas');
$oArrayDesplFirmas ->setBlanco('t');
$oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_oficinas(event)');

$lista_antecedentes = $oExpediente->getHtmlAntecedentes(FALSE);

$url_update = 'apps/expedientes/controller/expediente_update.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query(['filtro' => $Qfiltro]));
$server = ConfigGlobal::getWeb(); //http://tramity.local


$add_del_txt = '';
if ($Qfiltro == 'seg_reunion') {
    $add_del = 'del';
    // solo secretaria tiene permiso
    if ($_SESSION['session_auth']['role_actual'] == 'secretaria') {
        $add_del_txt = _("Quitar Firmas");
    }
} else {
    $add_del = 'add';
    $add_del_txt = _("Añadir Firmas");
}

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
    
    'oficinas' => $oficinas,
    'oArrayDesplFirmas' => $oArrayDesplFirmas, 
    'txt_option_cargos' => $txt_option_cargos,
    'lista_antecedentes' => $lista_antecedentes,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas, 
    
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    // para la pagina js
    'plazo_normal' => $plazo_normal,
    'plazo_urgente' => $plazo_urgente,
    'plazo_muy_urgente' => $plazo_muy_urgente,
    'error_fecha' => $error_fecha,
    //acciones
    'oEscritoLista' => $oEscritoLista,
    //'a_acciones' => $a_acciones,
    //'ver_todo' => $ver_todo,
    'a_firmas' => $a_firmas,
    'server' => $server,
    'aclaracion' => $aclaracion,
    'aclaracion_event' => $aclaracion_event,
    'add_del' => $add_del,
    'add_del_txt' => $add_del_txt,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_ver.html.twig',$a_campos);