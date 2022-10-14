<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use escritos\model\Escrito;
use escritos\model\GestorEscrito;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use usuarios\model\Categoria;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\DateTimeLocal;
use web\Desplegable;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$msg = '';
$post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();
////////////////////  buscar si ya existe  ////////////////////////////////
$Q_prot_num = (integer)filter_input(INPUT_POST, 'buscar_prot_num');
$Q_prot_any = (integer)filter_input(INPUT_POST, 'buscar_prot_any');
if (!empty($Q_prot_num) && !empty($Q_prot_any)) {
    $gesLugares = new GestorLugar();
    $id_lugar_local = $gesLugares->getId_sigla_local();
    $aProt_local = ['id_lugar' => $id_lugar_local,
        'num' => $Q_prot_num,
        'any' => $Q_prot_any,
    ];
    $gesEscritos = new GestorEscrito();
    $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_local);
    if (!empty($cEscritos)) {
        $oEscrito = $cEscritos[0];
        $oEscrito->DBCarregar();
        $Q_id_escrito = $oEscrito->getId_escrito();
    }
    if (count($cEscritos) > 1) {
        $msg = ' ' . _("Protocolo repetido");
    }
}
////////////

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
/*
$txt_option_ref = '';
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
}
*/

$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$id_ponente = '';
$oDesplPonente = new web\Desplegable('id_ponente', $a_posibles_cargos, $id_ponente, TRUE);
$oDesplPonente->setTabIndex(130);

$oArrayDesplFirmas = new web\DesplegableArray([], $a_posibles_cargos, 'oficinas');
$oArrayDesplFirmas->setBlanco('t');
$oArrayDesplFirmas->setAccionConjunto('fnjs_mas_firmas()');
$oArrayDesplFirmas->setTabIndex(140);

$oEscrito = new Escrito($Q_id_escrito);
// categoria
$oCategoria = new Categoria();
$aOpciones = $oCategoria->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(150);


$gesGrupo = new GestorGrupo();
$a_posibles_grupos = $gesGrupo->getArrayGrupos();

$chk_grupo_dst = '';
$descripcion = '';

// visibilidad
$oVisibilidad = new Visibilidad();
$aOpciones = $oVisibilidad->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setTabIndex(155);

if (!empty($Q_id_escrito)) {
    // destinos individuales
    $json_prot_dst = $oEscrito->getJson_prot_destino(TRUE);
    $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst, $a_posibles_lugares, 'destinos');
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
    $oArrayDesplGrupo = new web\DesplegableArray($a_grupos, $a_posibles_grupos, 'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');

    $json_prot_ref = $oEscrito->getJson_prot_ref();
    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref, $a_posibles_lugares, 'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

    $asunto = $oEscrito->getAsunto();
    $detalle = $oEscrito->getDetalle();
    $oficinas = $oEscrito->getResto_oficinas();
    $oArrayDesplFirmas->setSeleccionados($oficinas);

    // Ponente
    $id_ponente = $oEscrito->getCreador();
    $oDesplPonente->setOpcion_sel($id_ponente);
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
            'url' => 'apps/escritos/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',', $preview);
    $json_config = json_encode($config);

    // mirar si tienen escrito
    $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
    $tipo_doc = $oEscrito->getTipo_doc();
    $f_aprobacion = $oEscrito->getF_aprobacion()->getFromLocal();

    $oProt = $oEscrito->getJson_prot_local();
    $oProtocolo = new Protocolo();
    $oProtocolo->setJson($oProt);
    $titulo = $oProtocolo->ver_txt();
    $titulo .= $msg;

    $oPermisoregistro = new PermRegistro();
    $perm_asunto = $oPermisoregistro->permiso_detalle($oEscrito, 'asunto');
    $perm_detalle = $oPermisoregistro->permiso_detalle($oEscrito, 'detalle');
    $asunto_readonly = ($perm_asunto < PermRegistro::PERM_MODIFICAR) ? 'readonly' : '';
    $detalle_readonly = ($perm_detalle < PermRegistro::PERM_MODIFICAR) ? 'readonly' : '';

    $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oEscrito, 'cambio');
    if ($perm_cambio_visibilidad < PermRegistro::PERM_MODIFICAR) {
        $oDesplVisibilidad->setDisabled(TRUE);
    }
} else {
    // Puedo venir como respuesta a una entrada. Hay que copiar algunos datos de la entrada
    $Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
    if (!empty($Q_id_entrada)) {
        $oEntrada = new Entrada($Q_id_entrada);
        $asunto = $oEntrada->getAsunto();
        $detalle = $oEntrada->getDetalle();
        // ProtocoloArray espera un array.
        $json_prot_dst[] = $oEntrada->getJson_prot_origen();
        $oArrayProtDestino = new web\ProtocoloArray($json_prot_dst, $a_posibles_lugares, 'destinos');
        $oArrayProtDestino->setBlanco('t');
        $oArrayProtDestino->setTabIndex(50);

        // los escritos van por cargos, las entradas por oficinas: pongo al director de la oficina:
        // Ponente
        $id_of_ponente = $oEntrada->getPonente();
        if (!empty($id_of_ponente)) {
            $gesCargos = new GestorCargo();
            $id_ponente = $gesCargos->getDirectorOficina($id_of_ponente);
            $oDesplPonente->setOpcion_sel($id_ponente);
        }
        // oficinas
        $a_oficinas = $oEntrada->getResto_oficinas();
        $a_resto_cargos = [];
        foreach ($a_oficinas as $id_oficina) {
            $a_resto_cargos[] = $gesCargos->getDirectorOficina($id_oficina);
        }
        $oArrayDesplFirmas->setSeleccionados($a_resto_cargos);

        $categoria = $oEntrada->getCategoria();
        $oDesplCategoria->setOpcion_sel($categoria);
        $visibilidad = $oEntrada->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);

        $f_escrito = '';
        $f_aprobacion = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';
    } else {
        $asunto = '';
        $detalle = '';
        $f_escrito = '';
        $f_aprobacion = '';
        $initialPreview = '';
        $json_config = '{}';
        $tipo_doc = '';

        $oArrayProtDestino = new web\ProtocoloArray('', $a_posibles_lugares, 'destinos');
        $oArrayProtDestino->setBlanco('t');
        $oArrayProtDestino->setTabIndex(50);

    }
    $titulo = _("nuevo");

    $oArrayDesplGrupo = new web\DesplegableArray('', $a_posibles_grupos, 'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');

    $oArrayProtRef = new web\ProtocoloArray('', $a_posibles_lugares, 'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setTabIndex(95);

    $id_ponente = ConfigGlobal::role_id_cargo();

    $asunto_readonly = '';
    $detalle_readonly = '';
}


$url_update = 'apps/escritos/controller/escrito_update.php';
$a_cosas = [
    'filtro' => $Q_filtro,
    'modo' => $Q_modo,
];

$ver_revisado = FALSE;

$a_condicion = [];
$str_condicion = (string)filter_input(INPUT_POST, 'condicion');
parse_str($str_condicion, $a_condicion);
$a_condicion['filtro'] = $Q_filtro;
$pagina_cancel = web\Hash::link('apps/escritos/controller/salida_escrito.php?' . http_build_query($a_condicion));

$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
$url_escrito = 'apps/escritos/controller/salida_escrito.php';

$b_guardar_txt = empty($Q_id_escrito) ? _("Generar protocolo") : _("Pasar a secretaría");

// para cambiar destinos en nueva ventana.
$a_cosas = [
    'filtro' => $Q_filtro,
    'id_escrito' => $Q_id_escrito,
];
$pagina_actualizar = web\Hash::link('apps/escritos/controller/salida_escrito.php?' . http_build_query($a_cosas));

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;
$error_fecha = $_SESSION['oConfig']->getPlazoError();
$error_fecha_txt = 'P' . $error_fecha . 'D';
$oHoy = new DateTimeLocal();
$oHoy->sub(new DateInterval($error_fecha_txt));
$minIso = $oHoy->format('Y-m-d');

$a_campos = [
    'titulo' => $titulo,
    'b_guardar_txt' => $b_guardar_txt,
    'id_escrito' => $Q_id_escrito,
    'filtro' => $Q_filtro,
    'modo' => $Q_modo,
    'id_ponente' => $id_ponente,
    //'oHash' => $oHash,
    'chk_grupo_dst' => $chk_grupo_dst,
    'oArrayDesplGrupo' => $oArrayDesplGrupo,
    'oArrayProtDestino' => $oArrayProtDestino,
    'oArrayProtRef' => $oArrayProtRef,
    'f_escrito' => $f_escrito,
    'f_aprobacion' => $f_aprobacion,
    'tipo_doc' => $tipo_doc,
    'asunto' => $asunto,
    'asunto_readonly' => $asunto_readonly,
    'detalle' => $detalle,
    'detalle_readonly' => $detalle_readonly,
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'post_max_size' => $post_max_size,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    //'txt_option_ref' => $txt_option_ref,
    'url_update' => $url_update,
    'url_escrito' => $url_escrito,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    'ver_revisado' => $ver_revisado,
    'oArrayDesplFirmas' => $oArrayDesplFirmas,
    'oDesplPonente' => $oDesplPonente,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    'minIso' => $minIso,
    // para cambiar destinos en nueva ventana
    'pagina_actualizar' => $pagina_actualizar,
    'descripcion' => $descripcion,
];

$oView = new ViewTwig('escritos/controller');
echo $oView->renderizar('salida_escrito.html.twig', $a_campos);