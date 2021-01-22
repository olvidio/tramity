<?php
use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\EscritoLista;
use expedientes\model\Expediente;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\Tramite;
use usuarios\model\entity\GestorCargo;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

// En el caso de ajuntos, puedo abrir una nueva ventana para ver el expediente,
// y en ese caso el parametro viene por GET:
$cargar_css = FALSE;
if (empty($Qid_expediente)) {
    $Qid_expediente = (integer) \filter_input(INPUT_GET, 'id_expediente');
    $cargar_css = TRUE;
}

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

$asunto = $oExpediente->getAsuntoEstado();
$entradilla = $oExpediente->getEntradilla();

$oEscritoLista = new EscritoLista();
$oEscritoLista->setId_expediente($Qid_expediente);
$oEscritoLista->setModo($Qmodo);

// Comentarios y Aclaraciones
$gesFirmas = new GestorFirma();
$aRecorrido = $gesFirmas->getRecorrido($Qid_expediente);
$a_recorrido = $aRecorrido['recorrido'];
$comentarios = $aRecorrido['comentarios'];

// Etiquetas
$ver_etiquetas = FALSE;
$etiquetas = []; // No hay ninguna porque en archivar es cuando se añaden.
$etiquetas = $oExpediente->getEtiquetasVisiblesArray();
if ($_SESSION['session_auth']['role_actual'] != 'secretaria') {
    $gesEtiquetas = new GestorEtiqueta();
    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    }
    $oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas,$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas(event)');
    $ver_etiquetas = TRUE;
} else {
    $oArrayDesplEtiquetas = new web\DesplegableArray('',[],'etiquetas');
}
$txt_btn_etiquetas = _("Guardar etiquetas");

$lista_antecedentes = $oExpediente->getHtmlAntecedentes(FALSE);

$url_update = 'apps/expedientes/controller/expediente_update.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query(['filtro' => $Qfiltro,'modo' => $Qmodo]));
$pagina_actualizar = web\Hash::link('apps/expedientes/controller/expediente_distribuir.php?'.http_build_query(['id_expediente' => $Qid_expediente,'filtro' => $Qfiltro, 'modo' => $Qmodo]));
$base_url = ConfigGlobal::getWeb(); //http://tramity.local

if ($Qfiltro == 'distribuir') {
    $btn_action = 'distribuir';
    $txt_btn_success = _("Distribuir");
    $oEscritoLista->setFiltro('distribuir');
    $oDesplOficiales = new Desplegable('id_oficial',[],$id_ponente,TRUE);
    $ver_encargar = FALSE;

    $perm_d = $_SESSION['oConfig']->getPerm_distribuir();
    if (ConfigGlobal::mi_usuario_cargo() === 'scdl' OR is_true($perm_d)) {
        $perm_distribuir = TRUE;
    } else {
        $perm_distribuir = FALSE;
    }
} else {
    $perm_distribuir = TRUE;
    $btn_action = 'archivar';
    $txt_btn_success = _("Archivar");
    $oEscritoLista->setFiltro('acabados');
    // para encargar a los oficiales
    $id_oficina = ConfigGlobal::mi_id_oficina();
    $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
    $oDesplOficiales = new Desplegable('id_oficial',$a_cargos_oficina,$id_ponente,TRUE);
    $ver_encargar = TRUE;
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
    
    'lista_antecedentes' => $lista_antecedentes,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    'oDesplOficiales' => $oDesplOficiales, 
    'ver_encargar' => $ver_encargar,

    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'pagina_actualizar' => $pagina_actualizar,
    // para la pagina js
    'base_url' => $base_url,
    'cargar_css' => $cargar_css,
    //acciones
    'oEscritoLista' => $oEscritoLista,
    'filtro' => $Qfiltro,
    'modo' => $Qmodo,
    'perm_distribuir' => $perm_distribuir,
    'btn_action' => $btn_action,
    'txt_btn_success' => $txt_btn_success,
    'txt_btn_etiquetas' => $txt_btn_etiquetas,
    'ver_etiquetas' => $ver_etiquetas,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_distribuir.html.twig',$a_campos);