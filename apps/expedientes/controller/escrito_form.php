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
use documentos\model\Documento;
use usuarios\model\entity\Cargo;

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

if (empty($Qid_escrito) && $Qfiltro == 'en_buscar') {
    $Qa_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Qid_escrito = $Qa_sel[0];
}

$gesLugares = new GestorLugar();
if ($_SESSION['oConfig']->getAmbito() != Cargo::AMBITO_CTR) {
    $a_posibles_lugares = $gesLugares->getArrayLugares();
    $a_posibles_lugares_ref = $gesLugares->getArrayLugares();
    /*
    $txt_option_ref = '';
    foreach ($a_posibles_lugares as $id_lugar => $sigla) {
        $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
    }
    */
    
    $gesGrupo = new GestorGrupo();
    $a_posibles_grupos = $gesGrupo->getArrayGrupos();
    $json_prot_dst = [];
} else {
    $a_posibles_grupos = [];
    
    $sigla_local = $_SESSION['oConfig']->getSigla();
    $id_sigla_local = $gesLugares->getId_sigla_local();
    
    $id_sup = $gesLugares->getSigla_superior($sigla_local,TRUE);
    $sigla_sup = $gesLugares->getSigla_superior($sigla_local);
    
    $id_sup2 = $gesLugares->getSigla_superior($sigla_sup,TRUE);
    $sigla_sup2 = $gesLugares->getSigla_superior($sigla_sup);
    
    $a_posibles_lugares = [$id_sup => $sigla_sup];
    
    $oJSON = new stdClass;
    
    $oJSON->lugar = $id_sup;
    $oJSON->any = date('y');
    $oJSON->num = '';
    $oJSON->mas = '';
    $json_prot_dst[0] = $oJSON;
        
    $a_posibles_lugares_ref = [
                                $id_sigla_local => $sigla_local,
                                $id_sup => $sigla_sup,
                                $id_sup2 => $sigla_sup2,
                            ];
}


$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$estado = 0;
$visibilidad = 0;
$visibilidad_dst = Entrada::V_DST_TODOS;
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


$chk_grupo_dst = '';
$descripcion = '';
$comentario = '';
$anulado_txt = '';

// visibilidad (usar las mismas opciones que en entradas)
$oEntrada = new Entrada();
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setOpcion_sel($visibilidad);

$aOpciones_dst = $oEntrada->getArrayVisibilidadDst();
$oDesplVisibilidad_dst = new Desplegable();
$oDesplVisibilidad_dst->setNombre('visibilidad_dst');
$oDesplVisibilidad_dst->setOpciones($aOpciones_dst);
$oDesplVisibilidad_dst->setOpcion_sel($visibilidad_dst);

// plazo para contestar al enviar.
$plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
$plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
$plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
$error_fecha = $_SESSION['oConfig']->getPlazoError();
// Plazo
$aOpcionesPlazo = [
		'hoy' => ucfirst(_("no")),
		'normal' => ucfirst(sprintf(_("en %s días"),$plazo_normal)),
		'rápido' => ucfirst(sprintf(_("en %s días"),$plazo_rapido)),
		'urgente' => ucfirst(sprintf(_("en %s días"),$plazo_urgente)),
		'fecha' => ucfirst(_("el día")),
];
$oDesplPlazo = new Desplegable();
$oDesplPlazo->setNombre('plazo');
$oDesplPlazo->setOpciones($aOpcionesPlazo);
$oDesplPlazo->setAction("fnjs_comprobar_plazo('select')");

if (!empty($Qid_escrito)) {
    // destinos individuales
    $json_prot_dst = $oEscrito->getJson_prot_destino();
    $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
    $oArrayProtDestino->setBlanco('t');
    $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');
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
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');
    
    $json_prot_ref = $oEscrito->getJson_prot_ref();
    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares_ref,'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');
    
    $asunto = $oEscrito->getAsunto();
    $anulado = $oEscrito->getAnulado();
    if ($anulado === TRUE) {
        $anulado_txt = _("ANULADO");
    }
    $detalle = $oEscrito->getDetalle();
    
    // Ponente
    $id_ponente = $oEscrito->getCreador();
    $categoria = $oEscrito->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    if (!empty($oEscrito->getVisibilidad())) {
        $visibilidad = $oEscrito->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
    }
    if (!empty($oEscrito->getVisibilidad_dst())) {
        $visibilidad_dst = $oEscrito->getVisibilidad_dst();
        $oDesplVisibilidad_dst->setOpcion_sel($visibilidad_dst);
    }
    
    // Adjuntos Upload
    $a_adjuntos = $oEscrito->getArrayIdAdjuntos(Documento::DOC_UPLOAD);
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
    
    $f_contestar = $oEscrito->getF_contestar()->getFromLocal();
    if (!empty($f_contestar)) {
    	$oDesplPlazo->setOpcion_sel('fecha');
    }
    // mirar si tienen escrito
    $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
    $tipo_doc = $oEscrito->getTipo_doc();
    
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
        default:
            $titulo = _("modificar entrada");
    }
    
    $oPermisoregistro = new PermRegistro();
    $perm_asunto = $oPermisoregistro->permiso_detalle($oEscrito, 'asunto');
    $perm_detalle = $oPermisoregistro->permiso_detalle($oEscrito, 'detalle');
    $asunto_readonly = ($perm_asunto < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    $detalle_readonly = ($perm_detalle < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    
    $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oEscrito, 'cambio');
    if ($perm_cambio_visibilidad < PermRegistro::PERM_MODIFICAR) {
        $oDesplVisibilidad->setDisabled(TRUE);
    }
    $comentario = $oEscrito->getComentarios();
} else {
    // Puedo venir como respuesta a una entrada. Hay que copiar algunos datos de la entrada
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
    if (!empty($Qid_entrada)) {
        $Qaccion = Escrito::ACCION_ESCRITO;
        $oEntrada = new Entrada($Qid_entrada);
        $asunto = $oEntrada->getAsunto();
        $detalle = $oEntrada->getDetalle();
        // ProtocoloArray espera un array.
        $json_prot_dst = []; // inicializar variable. Puede tener cosas.
        $json_prot_dst[] = $oEntrada->getJson_prot_origen();
        $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
        $oArrayProtDestino->setBlanco('t');
        $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');

        $visibilidad = empty($oEntrada->getVisibilidad())? $oExpediente->getVisibilidad() : $oEntrada->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
        
		$f_contestar = '';
        $f_escrito = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';
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
        $detalle = '';
		$f_contestar = '';
        $f_escrito = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';

        $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst,$a_posibles_lugares,'destinos');
        $oArrayProtDestino ->setBlanco('t');
        $oArrayProtDestino ->setAccionConjunto('fnjs_mas_destinos()');
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $oArrayProtDestino->setAdd(FALSE);
        }

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
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }
    
    $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');
    
    $oArrayProtRef = new web\ProtocoloArray('',$a_posibles_lugares_ref,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias()');

    $id_ponente = ConfigGlobal::role_id_cargo();
    
    $asunto_readonly = '';
    $detalle_readonly = '';
}

// Adjuntos Etherpad
$lista_adjuntos_etherpad = $oEscrito->getHtmlAdjuntos();

$url_update = 'apps/expedientes/controller/escrito_update.php';
$a_cosas = ['id_expediente' => $Qid_expediente, 
            'filtro' => $Qfiltro,
            'modo' => $Qmodo,
        ];

$explotar = FALSE;
if ($estado == Expediente::ESTADO_ACABADO_ENCARGADO
    || ($estado == Expediente::ESTADO_ACABADO_SECRETARIA) ) {
    // Posibilidad de explotar en varios escritos, uno para cada ctr destino.
    $ctr_dest = $oArrayProtDestino->getArray_sel();
    if (count($ctr_dest) > 1 || !empty($a_grupos)) {
        $explotar = TRUE;
    }
}

$ver_plazo = TRUE;
$devolver = FALSE;
$str_condicion = '';
switch ($Qfiltro) {
    case 'acabados':
    case 'distribuir':
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_distribuir.php?'.http_build_query($a_cosas));
        break;
    case 'enviar':
        $devolver = TRUE;
        $pagina_cancel = web\Hash::link('apps/expedientes/controller/escrito_lista.php?'.http_build_query($a_cosas));
        break;
    case 'en_buscar':
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
    'condicion' => $str_condicion,
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

if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
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
        'oArrayProtDestino' => $oArrayProtDestino,
        'oArrayProtRef' => $oArrayProtRef,
        'f_escrito' => $f_escrito,
        'tipo_doc' => $tipo_doc,
        'asunto' => $asunto,
        'anulado_txt' => $anulado_txt,
        'asunto_readonly' => $asunto_readonly,
        'detalle' => $detalle,
        'detalle_readonly' => $detalle_readonly,
        'oDesplCategoria' => $oDesplCategoria,
        'oDesplVisibilidad' => $oDesplVisibilidad,
        'hidden_visibilidad' => $visibilidad,
        //'a_adjuntos' => $a_adjuntos,
        'initialPreview' => $initialPreview,
        'lista_adjuntos_etherpad' => $lista_adjuntos_etherpad,
        'json_config' => $json_config,
        'txt_option_cargos' => $txt_option_cargos,
        'url_update' => $url_update,
        'url_escrito' => $url_escrito,
        'pagina_cancel' => $pagina_cancel,
        'pagina_nueva' => $pagina_nueva,
        'explotar' => $explotar,
        'devolver' => $devolver,
        // datepicker
        'format' => $format,
        'yearStart' => $yearStart,
        'yearEnd' => $yearEnd,
        'minIso' => $minIso,
        // para cambiar destinos en nueva ventana
        'pagina_actualizar' => $pagina_actualizar, 
        // si vengo de buscar
        'str_condicion' => $str_condicion,
        // para ver comentario cuando se devuelve a la oficina
        'comentario' => $comentario,
    ];

    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('escrito_form_ctr.html.twig',$a_campos);
} else {
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
        'asunto' => $asunto,
        'anulado_txt' => $anulado_txt,
        'asunto_readonly' => $asunto_readonly,
        'detalle' => $detalle,
        'detalle_readonly' => $detalle_readonly,
        'oDesplCategoria' => $oDesplCategoria,
        'oDesplVisibilidad' => $oDesplVisibilidad,
        'hidden_visibilidad' => $visibilidad,
    	// destino ctr
        'oDesplVisibilidad_dst' => $oDesplVisibilidad_dst,
    	'oDesplPlazo' => $oDesplPlazo,
    	'f_contestar' => $f_contestar,
    	'ver_plazo' => $ver_plazo,
    	// para la pagina js destino ctr
		'plazo_normal' => $plazo_normal,
		'plazo_urgente' => $plazo_urgente,
		'plazo_rapido' => $plazo_rapido,
        //'a_adjuntos' => $a_adjuntos,
        'initialPreview' => $initialPreview,
        'lista_adjuntos_etherpad' => $lista_adjuntos_etherpad,
        'json_config' => $json_config,
        'txt_option_cargos' => $txt_option_cargos,
        //'txt_option_ref' => $txt_option_ref,
        'url_update' => $url_update,
        'url_escrito' => $url_escrito,
        'pagina_cancel' => $pagina_cancel,
        'pagina_nueva' => $pagina_nueva,
        'explotar' => $explotar,
        'devolver' => $devolver,
        // datepicker
        'format' => $format,
        'yearStart' => $yearStart,
        'yearEnd' => $yearEnd,
        'minIso' => $minIso,
        // para cambiar destinos en nueva ventana
        'pagina_actualizar' => $pagina_actualizar, 
        'descripcion' => $descripcion,
        // si vengo de buscar
        'str_condicion' => $str_condicion,
        // para ver comentario cuando se devuelve a la oficina
        'comentario' => $comentario,
    ];

    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('escrito_form.html.twig',$a_campos);
}