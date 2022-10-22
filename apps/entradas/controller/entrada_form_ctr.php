<?php

use core\ViewTwig;
use entradas\model\Entrada;
use lugares\model\entity\GestorLugar;
use usuarios\model\Categoria;
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


$Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

if ($Q_filtro === 'en_buscar' && empty($Q_id_entrada)) {
    $Q_a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Q_id_entrada = $Q_a_sel[0];
}

$plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
$plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
$plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
$error_fecha = $_SESSION['oConfig']->getPlazoError();

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();

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

$oEntrada = new Entrada($Q_id_entrada);
// tipo
$oCategoria = new Categoria();
$aOpciones = $oCategoria->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(80);

// visibilidad
$oVisibilidad = new Visibilidad();
$aOpciones = $oVisibilidad->getArrayVisibilidadCtr();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setTabIndex(81);

// Plazo
$aOpcionesPlazo = [
    'hoy' => ucfirst(_("no")),
    'normal' => ucfirst(sprintf(_("en %s días"), $plazo_normal)),
    'rápido' => ucfirst(sprintf(_("en %s días"), $plazo_rapido)),
    'urgente' => ucfirst(sprintf(_("en %s días"), $plazo_urgente)),
    'fecha' => ucfirst(_("el día")),
];
$oDesplPlazo = new Desplegable();
$oDesplPlazo->setNombre('plazo');
$oDesplPlazo->setOpciones($aOpcionesPlazo);
$oDesplPlazo->setAction("fnjs_comprobar_plazo('select')");
$oDesplPlazo->setTabIndex(82);

if (!empty($Q_id_entrada)) {
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    $oProtOrigen->setLugar($json_prot_origen->id_lugar);
    $oProtOrigen->setProt_num($json_prot_origen->num);
    $oProtOrigen->setProt_any($json_prot_origen->any);
    $oProtOrigen->setMas($json_prot_origen->mas);

    $json_prot_ref = $oEntrada->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref, $a_posibles_lugares, 'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

    $asunto_e = $oEntrada->getAsunto_entrada();
    $asunto = $oEntrada->getAsuntoDB();
    $anulado_txt = $oEntrada->getAnulado();
    if (!empty($anulado_txt)) {
        $anulado_txt = _("ANULADO") . "($anulado_txt) ";
    }
    $detalle = $oEntrada->getDetalle();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();

    $categoria = $oEntrada->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);
    $visibilidad = $oEntrada->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    $f_contestar = $oEntrada->getF_contestar()->getFromLocal();
    if (!empty($f_contestar)) {
        $oDesplPlazo->setOpcion_sel('fecha');
    }
    $bypass = $oEntrada->getBypass();
    if (core\is_true($bypass)) {
        $bypass = 't';
    } else {
        $bypass = 'f';
    }

    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $titulo = _("modificar entrada");

} else {
    $asunto_e = '';
    $asunto = '';
    $anulado_txt = '';
    $detalle = '';
    $visibilidad = Visibilidad::V_TODOS;
    $f_entrada = '';
    $f_escrito = '';
    $f_contestar = '';
    $titulo = _("nueva entrada");

    $oArrayProtRef = new web\ProtocoloArray('', $a_posibles_lugares, 'referencias');
    $oArrayProtRef->setBlanco('t');
    $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

}

$oProtOrigen->setTabIndex(50);
$oArrayProtRef->setTabIndex(95);
$oDesplCategoria->setTabIndex(160);
$oDesplVisibilidad->setTabIndex(165);
$oDesplPlazo->setTabIndex(180);

$ver_pendiente = TRUE;
$txt_btn_guardar = _("Guardar");

$url_update = 'apps/entradas/controller/entrada_update.php';
if ($Q_filtro === 'en_buscar') {
    $a_condicion = [];
    $str_condicion = (string)filter_input(INPUT_POST, 'condicion');
    parse_str($str_condicion, $a_condicion);
    $a_condicion['filtro'] = $Q_filtro;
    $pagina_cancel = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?' . http_build_query($a_condicion));
} else {
    $pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query(['filtro' => $Q_filtro]));
    $str_condicion = '';
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha::getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$a_campos = [
    'titulo' => $titulo,
    'id_entrada' => $Q_id_entrada,
    //'oHash' => $oHash,
    'oProtOrigen' => $oProtOrigen,
    'oArrayProtRef' => $oArrayProtRef,
    'f_escrito' => $f_escrito,
    'f_entrada' => $f_entrada,
    'asunto_e' => $asunto_e,
    'asunto' => $asunto,
    'anulado_txt' => $anulado_txt,
    'detalle' => $detalle,
    'oDesplCategoria' => $oDesplCategoria,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'hidden_visibilidad' => $visibilidad,
    'oDesplPlazo' => $oDesplPlazo,
    'f_contestar' => $f_contestar,
    'ver_pendiente' => $ver_pendiente,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'filtro' => $Q_filtro,
    'txt_btn_guardar' => $txt_btn_guardar,
    // para la pagina js
    'plazo_normal' => $plazo_normal,
    'plazo_urgente' => $plazo_urgente,
    'plazo_rapido' => $plazo_rapido,
    'error_fecha' => $error_fecha,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    // si vengo de buscar
    'str_condicion' => $str_condicion,
];

$oView = new ViewTwig('entradas/controller');
$oView->renderizar('entrada_form_ctr.html.twig', $a_campos);