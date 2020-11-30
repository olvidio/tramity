<?php
use core\ConfigGlobal;
use core\ViewTwig;
use etherpad\model\Etherpad;
use expedientes\model\Escrito;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorCargo;
use web\Desplegable;
use web\Protocolo;
use web\ProtocoloArray;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

// ----------- Sigla local -------------------
$sigla_local = $_SESSION['oConfig']->getSigla();
$id_lugar_local = '';
$txt_option_ref = '';
$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
    if ($sigla == $sigla_local) {
        $id_lugar_local = $id_lugar;
    }
}

$oProtLocal = new Protocolo();
$oProtLocal->setEtiqueta('De');
$oProtLocal->setNombre('origen');
$oProtLocal->setOpciones($a_posibles_lugares);
$oProtLocal->setBlanco(TRUE);
$oProtLocal->setTabIndex(10);

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

$oEscrito = new Escrito($Qid_escrito);
// categoria
$aOpciones = $oEscrito->getArrayCategoria();
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
$aOpciones = $oEscrito->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setAction("fnjs_cambiar_reservado('$secretari')");
$oDesplVisibilidad->setTabIndex(81);

if (!empty($Qid_escrito)) {
    
    $f_aprobacion = $oEscrito->getF_aprobacion();
    if (!empty($f_aprobacion)) {
        $tipo_documento = '';
        // si es un escrito, hay que generar el protocolo local:
        if ($tipo_documento == Escrito::ACCION_ESCRITO) {
            $json_prot_origen = $oEscrito->getJson_prot_local();
            if (!empty(get_object_vars($json_prot_origen))) {
                $oProtLocal->setLugar($json_prot_origen->lugar);
                $oProtLocal->setProt_num($json_prot_origen->num);
                $oProtLocal->setProt_any($json_prot_origen->any);
                if (property_exists($json_prot_origen, 'mas')) {
                    $oProtLocal->setMas($json_prot_origen->mas);
                }
            } else {
                $any = date ('y');
                $oProtLocal->setLugar($id_lugar_local);
                $oProtLocal->setProt_num('345');
                $oProtLocal->setProt_any($any);
                $oProtLocal->setMas('res');
            }
        }
    }
    
    $json_prot_destino = $oEscrito->getJson_prot_destino();
    $oArrayProtDestino = new ProtocoloArray($json_prot_destino,$a_posibles_lugares,'destino');
    $oArrayProtDestino->setEtiqueta('Para');
    
    $json_prot_ref = $oEscrito->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares,'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setRef(TRUE);
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias(event)');
    
    $entradilla = $oEscrito->getEntradilla();
    $asunto = $oEscrito->getAsunto();
    $detalle = $oEscrito->getDetalle();
    
    //Ponente;
    $id_ponente = $oEscrito->getCreador();
    $ponente_txt = $a_posibles_cargos[$id_ponente];

    $a_resto_of = $oEscrito->getResto_oficinas();
    
    $oArrayDesplFirmas = new web\DesplegableArray($a_resto_of,$a_posibles_cargos,'oficinas');
    $oArrayDesplFirmas->setBlanco('t');
    $oArrayDesplFirmas->setAccionConjunto('fnjs_mas_oficinas(event)');
    
    $categoria = $oEscrito->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    $visibilidad = $oEscrito->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    
    //$a_adjuntos = $oEscrito->getArrayIdAdjuntos();
    $a_adjuntos = [];
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
    $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
    
    //$tipo_documento = $oEscrito->getTipo();
    $titulo = _("revisar");
    switch ($Qaccion) {
        case Escrito::ACCION_ESCRITO:
            $titulo = _("revisar escrito");
            break;
        case Escrito::ACCION_PROPUESTA:
            $titulo = _("revisar propuesta");
            break;
        case Escrito::ACCION_PLANTILLA:
            $titulo = _("revisar plantilla");
            break;
    }
    
    
    $oEtherpad = new Etherpad();
    $oEtherpad->setId (Etherpad::ID_ESCRITO,$Qid_escrito);
    $padID = $oEtherpad->getPadId();
    $url = $oEtherpad->getUrl();
    
    $iframe = "<iframe src='$url/p/$padID?showChat=false&showLineNumbers=false' width=1020 height=500></iframe>";
    
} else {
    $entradilla = '';
    $asunto = '';
    $detalle = '';
    $f_escrito = '';
    $oficinas = '';
    $initialPreview = '';
    $json_config = '{}';
    //$tipo_documento = '';
    $titulo = _("nuevo");
    switch ($Qaccion) {
        case Escrito::ACCION_ESCRITO:
            $titulo = _("nuevo escrito");
            break;
        case Escrito::ACCION_PROPUESTA:
            $titulo = _("nueva propuesta");
            break;
        case Escrito::ACCION_PLANTILLA:
            $titulo = _("nueva plantilla");
            break;
    }
    
    $oArrayProtRef = new web\ProtocoloArray('',$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');

    $oArrayDesplFirmas = new web\DesplegableArray('',$a_posibles_cargos,'oficinas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_oficinas(event)');
    
    $id_ponente = ConfigGlobal::mi_id_cargo();
    $iframe = '';
}


$url_update = 'apps/expedientes/controller/escrito_update.php';
$a_cosas = ['id_expediente' => $Qid_expediente,
            'filtro' => $Qfiltro
];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
$pagina_nueva = web\Hash::link('apps/expedientes/controller/entrada_form.php?'.http_build_query(['filtro' => $Qfiltro]));

$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Qid_expediente,
    'id_escrito' => $Qid_escrito,
    'accion' => $Qaccion,
    'id_ponente' => $id_ponente,
    //'oHash' => $oHash,
    'oProtLocal' => $oProtLocal,
    'oArrayProtDestino' => $oArrayProtDestino,
    'oArrayProtRef' => $oArrayProtRef,
    'f_escrito' => $f_escrito,
    'entradilla' => $entradilla,
    'asunto' => $asunto,
    'detalle' => $detalle,
    'iframe' => $iframe,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    'txt_option_ref' => $txt_option_ref,
    
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('escrito_rev.html.twig',$a_campos);