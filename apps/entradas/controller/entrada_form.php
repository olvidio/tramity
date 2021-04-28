<?php
use core\ViewTwig;
use entradas\model\Entrada;
use entradas\model\entity\GestorEntradaBypass;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use usuarios\model\PermRegistro;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
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

if ($Qfiltro == 'buscar' && empty($Qid_entrada)) {
    $Qa_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Qid_entrada = $Qa_sel[0];
}

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

$oArrayProtDestino = new web\ProtocoloArray('',$a_posibles_lugares,'destinos');
$oArrayProtDestino->setBlanco('t');
$oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');

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


$gesOficinas = new GestorOficina();
$a_posibles_oficinas = $gesOficinas->getArrayOficinas();
$oDesplPonenteOficina = $gesOficinas->getListaOficinas();
$oDesplPonenteOficina->setNombre('of_ponente');
$oDesplPonenteOficina->setTabIndex(80);

$oEntrada = new Entrada($Qid_entrada);
// tipo
$aOpciones = $oEntrada->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(80);

// visibilidad
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setTabIndex(81);

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
$oDesplPlazo->setTabIndex(82);

$oDesplByPass = new Desplegable();
$oDesplByPass->setNombre('bypass');
$oDesplByPass->setOpciones(['f' => _("No"), 't' => _("Sí")]);
$oDesplByPass->setAction("fnjs_distr_cr()");
    
$oDesplAdmitido = new Desplegable();
$oDesplAdmitido->setNombre('admitir');
$oDesplAdmitido->setOpciones(['f' => _("No"), 't' => _("Sí")]);
$oDesplAdmitido->setAction("fnjs_admitir();");
$estado = $oEntrada->getEstado();
if ($estado >= Entrada::ESTADO_ADMITIDO) {
    $badmitido = 't';
    $comprobar_f_entrada = TRUE;
} else {
    $badmitido = 'f';
    $comprobar_f_entrada = FALSE;
}
if ($Qfiltro == 'en_admitido') {
    $badmitido = 't';
} else {
    $oDesplAdmitido->setDisabled(TRUE);
}
$oDesplAdmitido->setOpcion_sel($badmitido);
    
$gesGrupo = new GestorGrupo();
$a_posibles_grupos = $gesGrupo->getArrayGrupos();
    
if (!empty($Qid_entrada)) {
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    $oProtOrigen->setLugar($json_prot_origen->lugar);
    $oProtOrigen->setProt_num($json_prot_origen->num);
    $oProtOrigen->setProt_any($json_prot_origen->any);
    $oProtOrigen->setMas($json_prot_origen->mas);
    
    $json_prot_ref = $oEntrada->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias()');
    
    $asunto_e = $oEntrada->getAsunto_entrada();
    $asunto = $oEntrada->getAsuntoDB();
    $anulado_txt = $oEntrada->getAnulado();
    if (!empty($anulado_txt)) {
        $anulado_txt = _("ANULADO") . "($anulado_txt) ";
    }
    $detalle = $oEntrada->getDetalle();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();
    
    $id_of_ponente = $oEntrada->getPonente();
    $oDesplPonenteOficina->setOpcion_sel($id_of_ponente);
    $a_oficinas = $oEntrada->getResto_oficinas();
    
    $oArrayDesplOficinas = new web\DesplegableArray($a_oficinas,$a_posibles_oficinas,'oficinas');
    $oArrayDesplOficinas->setBlanco('t');
    $oArrayDesplOficinas->setAccionConjunto('fnjs_mas_oficinas()');
    
    $categoria = $oEntrada->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    $visibilidad = $oEntrada->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    $f_contestar = $oEntrada->getF_contestar()->getFromLocal();
    if (!empty($f_contestar)) {
        $oDesplPlazo->setOpcion_sel('fecha');
    }
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
    
    // a ver si ya está
    $chk_grupo_dst = '';
    $id_grupo = 0;
    $gesEntradasBypass = new GestorEntradaBypass();
    $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
    if (count($cEntradasBypass) > 0) {
        // solo debería haber una:
        $oEntradaBypass = $cEntradasBypass[0];
        $f_salida = $oEntradaBypass->getF_salida()->getFromLocal();
        $a_grupos = $oEntradaBypass->getId_grupos();
        if (!empty($a_grupos)) {
            $oArrayDesplGrupo = new web\DesplegableArray($a_grupos,$a_posibles_grupos,'grupos');
            $chk_grupo_dst = 'checked';
        } else {
            $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
            $chk_grupo_dst = '';
            $json_prot_dst = $oEntradaBypass->getJson_prot_destino();
            $oArrayProtDestino->setArray_sel($json_prot_dst);
        }
        $oArrayDesplGrupo->setBlanco('t');
        $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');
        
    } else {
        $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
        $oArrayDesplGrupo->setBlanco('t');
        $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');
    }
    
    $oPermisoregistro = new PermRegistro();
    $perm_asunto = $oPermisoregistro->permiso_detalle($oEntrada, 'asunto');
    $perm_detalle = $oPermisoregistro->permiso_detalle($oEntrada, 'detalle');
    $asunto_readonly = ($perm_asunto < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    $detalle_readonly = ($perm_detalle < PermRegistro::PERM_MODIFICAR)? 'readonly' : '';
    
    $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oEntrada, 'cambio');
    if ($perm_cambio_visibilidad < PermRegistro::PERM_MODIFICAR) {
        $oDesplVisibilidad->setDisabled(TRUE);
    }

} else {
    $chk_grupo_dst = '';
    $id_grupo = 0;
    $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');
        
    $asunto_e = '';
    $asunto = '';
    $anulado_txt = '';
    $detalle = '';
    $visibilidad = Entrada::V_TODOS;
    $f_entrada = '';
    $f_escrito = '';
    $f_contestar = '';
    $a_oficinas = [];
    $initialPreview = '';
    $json_config = '{}';
    $tipo_documento = '';
    $titulo = _("nueva entrada");
    
    $oArrayProtRef = new web\ProtocoloArray('',$a_posibles_lugares,'referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias()');

    $oArrayDesplOficinas = new web\DesplegableArray('',$a_posibles_oficinas,'oficinas');
    $oArrayDesplOficinas->setBlanco('t');
    $oArrayDesplOficinas->setAccionConjunto('fnjs_mas_oficinas()');
    
    $asunto_readonly = '';
    $detalle_readonly = '';
}

$oProtOrigen->setTabIndex(50);
$oArrayProtRef->setTabIndex(95);
$oDesplPonenteOficina->setTabIndex(130);
$oArrayDesplOficinas->setTabIndex(140);
$oDesplCategoria->setTabIndex(160);
$oDesplVisibilidad->setTabIndex(165);
$oDesplAdmitido->setTabIndex(170);
$oDesplPlazo->setTabIndex(180);
$oDesplByPass->setTabIndex(190);
$oArrayProtDestino->setTabIndex(200);

$ver_pendiente = FALSE;
switch ($Qfiltro) {
    case 'en_admitido':
        $txt_btn_guardar = _("Asignar");
        break;
    case 'en_asignado':
        $txt_btn_guardar = _("Aceptar");
        $ver_pendiente = TRUE;
        break;
    default:
        $txt_btn_guardar = _("Guardar");
        
}

$url_update = 'apps/entradas/controller/entrada_update.php';
$pagina_nueva = web\Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $Qfiltro]));
if ($Qfiltro == 'buscar') {
    $a_condicion = [];
    $str_condicion = (string) \filter_input(INPUT_POST, 'condicion');
    parse_str($str_condicion, $a_condicion);
    $a_condicion['filtro'] = $Qfiltro;
    $pagina_cancel = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?'.http_build_query($a_condicion));
} else {
    $pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query(['filtro' => $Qfiltro]));
    $str_condicion = '';
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

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
    'anulado_txt' => $anulado_txt,
    'asunto_readonly' => $asunto_readonly,
    'detalle' => $detalle,
    'detalle_readonly' => $detalle_readonly,
    'oDesplPonenteOficina' => $oDesplPonenteOficina,
    'a_oficinas' => $a_oficinas,
    'oArrayDesplOficinas' => $oArrayDesplOficinas,  
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'hidden_visibilidad' => $visibilidad,
    'oDesplPlazo' => $oDesplPlazo,
    'f_contestar' => $f_contestar,
    'ver_pendiente' => $ver_pendiente,
    'oDesplByPass' => $oDesplByPass,
    'oDesplAdmitido' => $oDesplAdmitido,
    'badmitido' => $badmitido,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    //'txt_option_oficinas' => $txt_option_oficinas,
    'txt_option_ref' => $txt_option_ref,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    'filtro' => $Qfiltro,
    'txt_btn_guardar' => $txt_btn_guardar,
    // para la pagina js
    'plazo_normal' => $plazo_normal,
    'plazo_urgente' => $plazo_urgente,
    'plazo_rapido' => $plazo_rapido,
    'error_fecha' => $error_fecha,
    'comprobar_f_entrada' => $comprobar_f_entrada,
    'cat_e12' => Entrada::CAT_E12,
    // grupo destinos
    'chk_grupo_dst' => $chk_grupo_dst,
    'id_grupo' => $id_grupo,
    'oArrayDesplGrupo' => $oArrayDesplGrupo,
    'oArrayProtDestino' => $oArrayProtDestino,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    // si vengo de buscar
    'str_condicion' => $str_condicion,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_form.html.twig',$a_campos);