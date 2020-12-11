<?php
use core\ViewTwig;
use entradas\model\Entrada;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Desplegable;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
$plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
$plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
$error_fecha = $_SESSION['oConfig']->getPlazoError();

$txt_option_ref = '';
$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
}

$oProtOrigen = new Protocolo();
$oProtOrigen->setEtiqueta('De');
$oProtOrigen->setNombre('origen');
$oProtOrigen->setOpciones($a_posibles_lugares);
$oProtOrigen->setBlanco(TRUE);
$oProtOrigen->setTabIndex(10);

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setOpciones($a_posibles_lugares);
$oProtRef->setBlanco(TRUE);

$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}
$oDesplPonente = $gesCargos->getDesplCargos('x');
$oDesplPonente->setNombre('ponente');
$oDesplPonente->setTabIndex(60);

$oEntrada = new Entrada($Qid_entrada);
// tipo
$aOpciones = $oEntrada->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(80);

// soy el secretario
/*
if ($GLOBALS['oPerm']->have_perm("scl") && $GLOBALS['oPerm']->have_perm("dtor") ) {
    $secretari=1; 
} else {
    $secretari=0; 
}
*/
$secretari=0; 

// visibilidad
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setAction("fnjs_cambiar_reservado('$secretari')");
$oDesplVisibilidad->setTabIndex(81);

// Plazo
$aOpciones = [
    'hoy' => ucfirst(_("no")),
    'normal' => ucfirst(sprintf(_("en %s días"),$plazo_normal)),
    'rápido' => ucfirst(sprintf(_("en %s días"),$plazo_rapido)),
    'urgente' => ucfirst(sprintf(_("en %s días"),$plazo_urgente)), 
    'fecha' => ucfirst(_("el día")),
];
$oDesplPlazo = new Desplegable();
$oDesplPlazo->setNombre('plazo');
$oDesplPlazo->setOpciones($aOpciones);
$oDesplPlazo->setAction("fnjs_comprobar_plazo('select')");
$oDesplPlazo->setTabIndex(82);

$oDesplByPass = new Desplegable();
$oDesplByPass->setNombre('bypass');
$oDesplByPass->setOpciones(['f' => _("No"), 't' => _("Sí")]);
    
$oDesplAdmitido = new Desplegable();
$oDesplAdmitido->setNombre('admitir');
$oDesplAdmitido->setOpciones(['f' => _("No"), 't' => _("Sí")]);
$estado = $oEntrada->getEstado();
if ($estado >= Entrada::ESTADO_ADMITIDO) {
    $oDesplAdmitido->setOpcion_sel('t');
} else {
    $oDesplAdmitido->setOpcion_sel('f');
}
if ($Qfiltro == 'en_admitido') {
    $oDesplAdmitido->setOpcion_sel('t');
} else {
    $oDesplAdmitido->setDisabled(TRUE);
}
    
if (!empty($Qid_entrada)) {
    
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    $oProtOrigen->setLugar($json_prot_origen->lugar);
    $oProtOrigen->setProt_num($json_prot_origen->num);
    $oProtOrigen->setProt_any($json_prot_origen->any);
    $oProtOrigen->setMas($json_prot_origen->mas);
    
    $json_prot_ref = $oEntrada->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');
    
    $asunto_e = $oEntrada->getAsunto_entrada();
    $asunto = $oEntrada->getAsunto();
    $detalle = $oEntrada->getDetalle();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();
    
    $ponente = $oEntrada->getPonente();
    $oDesplPonente->setOpcion_sel($ponente);
    $a_oficinas = $oEntrada->getResto_oficinas();
    
    $oArrayDesplFirmas = new web\DesplegableArray($a_oficinas,$a_posibles_cargos,'oficinas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_oficinas(event)');
    
    $categoria = $oEntrada->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    $visibilidad = $oEntrada->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    $bypass = $oEntrada->getBypass();
    if ( core\is_true($bypass) ) { $bypass='t'; } else { $bypass='f'; }
    $oDesplByPass->setOpcion_sel($bypass);
    
    $a_adjuntos = $oEntrada->getArrayIdAdjuntos();
    $preview = [];
    $config = [];
    foreach ($a_adjuntos as $id_item => $nom) {
        $preview[] = "'$nom'";
        $config[] = [
            'key' => $id_item,
            'caption' => $nom,
            'url' => 'apps/entradas/controller/delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',',$preview);
    $json_config = json_encode($config);
    
    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $tipo_documento = $oEntrada->getTipo_documento();
    $titulo = _("modificar entrada");

} else {
    $asunto_e = '';
    $asunto = '';
    $detalle = '';
    $f_entrada = '';
    $f_escrito = '';
    $a_oficinas = [];
    $initialPreview = '';
    $json_config = '{}';
    $tipo_documento = '';
    $titulo = _("nueva entrada");
    
    $oArrayProtRef = new web\ProtocoloArray('',$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');

    $oArrayDesplFirmas = new web\DesplegableArray('',$a_posibles_cargos,'oficinas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_oficinas(event)');
}
if (empty($f_entrada)) {
    $oHoy = new DateTimeLocal();
    $f_entrada = $oHoy->getFromLocal();
}


$url_update = 'apps/entradas/controller/entrada_update.php';
$pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query(['filtro' => $Qfiltro]));
$pagina_nueva = web\Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $Qfiltro]));

$a_campos = [
    'titulo' => $titulo,
    'id_entrada' => $Qid_entrada,
    //'oHash' => $oHash,
    'oProtOrigen' => $oProtOrigen,
    'oArrayProtRef' => $oArrayProtRef,
    //'oProtRef' => $oProtRef,
    'f_escrito' => $f_escrito,
    'tipo_documento' => $tipo_documento,
    'f_entrada' => $f_entrada,
    'asunto_e' => $asunto_e,
    'asunto' => $asunto,
    'detalle' => $detalle,
    'oDesplPonente' => $oDesplPonente,
    'a_oficinas' => $a_oficinas,
    'oArrayDesplFirmas' => $oArrayDesplFirmas,  
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'oDesplPlazo' => $oDesplPlazo,
    'oDesplByPass' => $oDesplByPass,
    'oDesplAdmitido' => $oDesplAdmitido,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    'txt_option_ref' => $txt_option_ref,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    'filtro' => $Qfiltro,
    // para la pagina js
    'plazo_normal' => $plazo_normal,
    'plazo_urgente' => $plazo_urgente,
    'plazo_rapido' => $plazo_rapido,
    'error_fecha' => $error_fecha,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_form.html.twig',$a_campos);