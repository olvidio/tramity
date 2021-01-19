<?php
use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use entradas\model\Entrada;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
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
$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

$txt_option_ref = '';
$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
}

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


$gesGrupo = new GestorGrupo();
$a_posibles_grupos = $gesGrupo->getArrayGrupos();

// soy el secretario
/*
if ($GLOBALS['oPerm']->have_perm("scl") && $GLOBALS['oPerm']->have_perm("dtor") ) {
    $secretari=1; 
} else {
    $secretari=0; 
}
*/
$secretari=0; 

$chk_grupo_dst = '';
$id_grupo = 0;

// visibilidad (usar las mismas opciones que en entradas)
$oEntrada = new Entrada();
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setAction("fnjs_cambiar_reservado('$secretari')");

if (!empty($Qid_escrito)) {
    $a_grupos = $oEscrito->getId_grupos();
    $oArrayDesplGrupo = new web\DesplegableArray($a_grupos,$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos(event)');
    if (!empty($a_grupos)) {
        $chk_grupo_dst = 'checked';
    }
    
    $json_prot_dst = $oEscrito->getJson_prot_destino();
    $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
    $oArrayProtDestino->setBlanco('t');
    $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos(event)');
    
    $json_prot_ref = $oEscrito->getJson_prot_ref();
    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares,'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias(event)');
    
    $entradilla = $oEscrito->getEntradilla();
    $asunto = $oEscrito->getAsunto();
    $detalle = $oEscrito->getDetalle();
    
    //Ponente;
    $id_ponente = $oEscrito->getCreador();
    $categoria = $oEscrito->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    $visibilidad = $oEscrito->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    
    $a_adjuntos = $oEscrito->getArrayIdAdjuntos();
    $preview = [];
    $config = [];
    foreach ($a_adjuntos as $id_item => $nom) {
        $preview[] = "'$nom'";
        $config[] = [
            'key' => $id_item,
            'caption' => $nom,
            'url' => 'apps/expedientes/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',',$preview);
    $json_config = json_encode($config);
    
    // mirar si tienen escrito
    $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
    $tipo_doc = $oEscrito->getTipo_doc();
    
    if (empty($oEscrito->getOk()) OR $oEscrito->getOk() == Escrito::OK_NO) {
        $chk_revisado = '';
    } else {
        $chk_revisado = 'checked';
    }
    
    $titulo = _("modificar");
    switch ($Qaccion) {
        case Escrito::ACCION_ESCRITO:
            $titulo = _("modificar escrito");
            break;
        case Escrito::ACCION_PROPUESTA:
            $titulo = _("modificar propuesta");
            break;
        case Escrito::ACCION_PLANTILLA:
            $titulo = _("modificar plantilla");
            break;
    }
    
} else {
    // Puedo venir como respuesta a una entrada. Hay que copiar algunos datos de la entrada
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
    if (!empty($Qid_entrada)) {
        $Qaccion = Escrito::ACCION_ESCRITO;
        $oEntrada = new Entrada($Qid_entrada);
        $asunto = $oEntrada->getAsunto();
        $detalle = $oEntrada->getDetalle();
        // ProtocoloArray espera un array.
        $json_prot_dst[] = $oEntrada->getJson_prot_origen();
        $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
        $oArrayProtDestino->setBlanco('t');
        $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos(event)');
        
        $entradilla = '';
        $f_escrito = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';
        $chk_revisado = '';
    } else {
        $entradilla = '';
        $asunto = '';
        $detalle = '';
        $f_escrito = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';
        $chk_revisado = '';

        $oArrayProtDestino = new web\ProtocoloArray('',$a_posibles_lugares,'destinos');
        $oArrayProtDestino ->setBlanco('t');
        $oArrayProtDestino ->setAccionConjunto('fnjs_mas_destinos(event)');

    }
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
    
    $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos(event)');
    
    $oArrayProtRef = new web\ProtocoloArray('',$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');

    $id_ponente = ConfigGlobal::mi_id_cargo();
}


$url_update = 'apps/expedientes/controller/escrito_update.php';
$a_cosas = ['id_expediente' => $Qid_expediente, 
            'filtro' => $Qfiltro,
            'modo' => $Qmodo,
        ];
$ver_revisado = FALSE;
$oExpediente = new Expediente($Qid_expediente);
$estado = $oExpediente->getEstado();
if ($estado == Expediente::ESTADO_ACABADO_ENCARGADO
    OR ($estado == Expediente::ESTADO_ACABADO_SECRETARIA) ) {
    $ver_revisado = TRUE;
}
switch ($Qfiltro) {
    case 'acabados':
    case 'distribuir':
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_distribuir.php?'.http_build_query($a_cosas));
        break;
    case 'enviar':
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/escrito_lista.php?'.http_build_query($a_cosas));
        break;
    default: 
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
}

$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query(['filtro' => $Qfiltro]));

$esEscrtito = ($Qaccion == Escrito::ACCION_ESCRITO)? TRUE : FALSE;
$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Qid_expediente,
    'id_escrito' => $Qid_escrito,
    'accion' => $Qaccion,
    'filtro' => $Qfiltro,
    'modo' => $Qmodo,
    'esEscrito' => $esEscrtito,
    'id_ponente' => $id_ponente,
    //'oHash' => $oHash,
    'chk_grupo_dst' => $chk_grupo_dst,
    'id_grupo' => $id_grupo,
    'oArrayDesplGrupo' => $oArrayDesplGrupo,
    'oArrayProtDestino' => $oArrayProtDestino,
    'oArrayProtRef' => $oArrayProtRef,
    'f_escrito' => $f_escrito,
    'tipo_doc' => $tipo_doc,
    'entradilla' => $entradilla,
    'asunto' => $asunto,
    'detalle' => $detalle,
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'chk_revisado' => $chk_revisado,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    'txt_option_ref' => $txt_option_ref,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    'ver_revisado' => $ver_revisado,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('escrito_form.html.twig',$a_campos);