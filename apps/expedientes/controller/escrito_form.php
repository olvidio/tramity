<?php
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use usuarios\model\PermRegistro;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
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

if ($Qfiltro == 'buscar') {
    $Qa_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Qid_escrito = $Qa_sel[0];
}

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

$estado = 0;
$visibilidad = 0;
if (!empty($Qid_expediente)) {
    $oExpediente = new Expediente($Qid_expediente);
    $visibilidad = $oExpediente->getVisibilidad();
    $estado = $oExpediente->getEstado();
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

$chk_grupo_dst = '';
$descripcion = '';

// visibilidad (usar las mismas opciones que en entradas)
$oEntrada = new Entrada();
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setOpcion_sel($visibilidad);

if (!empty($Qid_escrito)) {
    // destinos individuales
    $json_prot_dst = $oEscrito->getJson_prot_destino();
    $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
    $oArrayProtDestino->setBlanco('t');
    $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos(event)');
    // si hay grupos, tienen preferencia
    $a_grupos = $oEscrito->getId_grupos();
    if (!empty($a_grupos)) {
        $chk_grupo_dst = 'checked';
    } else {
        // puede ser un destino personalizado:
        $destinos = $oEscrito->getDestinos();
        if (!empty($destinos)) {
            $a_posibles_grupos['custom'] = _("personalizado");
            $a_grupos = 'custom';
            $chk_grupo_dst = 'checked';
            $descripcion = $oEscrito->getDescripcion();
        }
    }
    $oArrayDesplGrupo = new web\DesplegableArray($a_grupos,$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos(event)');
    
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
    if (!empty($oEscrito->getVisibilidad())) {
        $visibilidad = $oEscrito->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
    }
    
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
    
    $oPermisoregistro = new PermRegistro();
    $perm_asunto = $oPermisoregistro->permiso_detalle($oEscrito, 'asunto');
    $perm_detalle = $oPermisoregistro->permiso_detalle($oEscrito, 'detalle');
    $asunto_readonly = ($perm_asunto < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    $detalle_readonly = ($perm_detalle < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    
    $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oEntrada, 'cambio');
    if ($perm_cambio_visibilidad <= PermRegistro::PERM_MODIFICAR) {
        $oDesplVisibilidad->setDisabled(TRUE);
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

        $visibilidad = empty($oEntrada->getVisibilidad())? $oExpediente->getVisibilidad() : $oEntrada->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
        
        $entradilla = '';
        $f_escrito = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';
        $chk_revisado = '';
    } else {
        // Valors por defecto: los del expediente:
        if (!empty($Qid_expediente)) {
            $oExpediente = new Expediente($Qid_expediente);
            $asunto = $oExpediente->getAsunto();
            $visibilidad = $oExpediente->getVisibilidad();
            $oDesplVisibilidad->setOpcion_sel($visibilidad);
        } else {
            $asunto = '';
            $visibilidad = '';
            $oDesplVisibilidad->setOpcion_sel($visibilidad);
        }
        $entradilla = '';
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

    $id_ponente = ConfigGlobal::role_id_cargo();
    
    $asunto_readonly = '';
    $detalle_readonly = '';
}


$url_update = 'apps/expedientes/controller/escrito_update.php';
$a_cosas = ['id_expediente' => $Qid_expediente, 
            'filtro' => $Qfiltro,
            'modo' => $Qmodo,
        ];

$explotar = FALSE;
$ver_revisado = FALSE;
if ($estado == Expediente::ESTADO_ACABADO_ENCARGADO
    OR ($estado == Expediente::ESTADO_ACABADO_SECRETARIA) ) {
    $ver_revisado = TRUE;
    // Posibilidad de explotar en varios escritos, uno para cada ctr destino.
    $ctr_dest = $oArrayProtDestino->getArray_sel();
    if (count($ctr_dest) > 1 OR !empty($a_grupos)) {
        $explotar = TRUE;
    }
}

switch ($Qfiltro) {
    case 'acabados':
    case 'distribuir':
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_distribuir.php?'.http_build_query($a_cosas));
        break;
    case 'enviar':
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/escrito_lista.php?'.http_build_query($a_cosas));
        break;
    case 'buscar':
        $a_condicion = [];
        $str_condicion = (string) \filter_input(INPUT_POST, 'condicion');
        parse_str($str_condicion, $a_condicion);
        $a_condicion['filtro'] = $Qfiltro;
        $pagina_cancel = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?'.http_build_query($a_condicion));
        break;
    default: 
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
}

$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query(['filtro' => $Qfiltro]));
$url_escrito = 'apps/expedientes/controller/escrito_form.php';

$esEscrtito = ($Qaccion == Escrito::ACCION_ESCRITO)? TRUE : FALSE;

// para cambiar destinos en nueva ventana. 
$a_cosas = [
    'filtro' => $Qfiltro,
    'id_expediente' => $Qid_expediente,
    'id_escrito' => $Qid_escrito,
    'accion' => $Qaccion,
];
$pagina_actualizar = web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
 
// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;
$error_fecha = $_SESSION['oConfig']->getPlazoError();
$error_fecha_txt = 'P'.$error_fecha.'D';
$oHoy = new DateTimeLocal();
$oHoy->sub(new DateInterval($error_fecha_txt));
$minIso = $oHoy->format('Y-m-d');

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
    'oArrayDesplGrupo' => $oArrayDesplGrupo,
    'oArrayProtDestino' => $oArrayProtDestino,
    'oArrayProtRef' => $oArrayProtRef,
    'f_escrito' => $f_escrito,
    'tipo_doc' => $tipo_doc,
    'entradilla' => $entradilla,
    'asunto' => $asunto,
    'asunto_readonly' => $asunto_readonly,
    'detalle' => $detalle,
    'detalle_readonly' => $detalle_readonly,
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'chk_revisado' => $chk_revisado,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    'txt_option_ref' => $txt_option_ref,
    'url_update' => $url_update,
    'url_escrito' => $url_escrito,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    'ver_revisado' => $ver_revisado,
    'explotar' => $explotar,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    'minIso' => $minIso,
    // para cambiar destinos en nueva ventana
    'pagina_actualizar' => $pagina_actualizar, 
    'descripcion' => $descripcion,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('escrito_form.html.twig',$a_campos);