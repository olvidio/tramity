<?php

use core\ViewTwig;
use entradas\model\entity\EntradaBypass;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use usuarios\model\Categoria;
use usuarios\model\entity\GestorOficina;
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

$gesOficinas = new GestorOficina();
$a_posibles_oficinas = $gesOficinas->getArrayOficinas();

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayBusquedas();

$oEntradaBypass = new EntradaBypass($Q_id_entrada);
// categoria
$oCategoria = new Categoria();
$aOpciones = $oCategoria->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(80);

// visibilidad
$oVisibilidad = new Visibilidad();
$aOpciones = $oVisibilidad->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setTabIndex(81);

// por defecto va por grupos.
$chk_grupo_dst = 'checked';
$id_grupo = 0;
$f_salida = '';

$gesGrupo = new GestorGrupo();
$a_posibles_grupos = $gesGrupo->getArrayGrupos();

$json_prot_origen = $oEntradaBypass->getJson_prot_origen();
$oProtOrigen = new Protocolo();
$oProtOrigen->setEtiqueta('De');
$oProtOrigen->setNombre('origen');
$oProtOrigen->setBlanco(TRUE);
$oProtOrigen->setTabIndex(10);
$oProtOrigen->setLugar($json_prot_origen->id_lugar);
$oProtOrigen->setProt_num($json_prot_origen->num);
$oProtOrigen->setProt_any($json_prot_origen->any);
$oProtOrigen->setMas($json_prot_origen->mas);

$json_prot_ref = $oEntradaBypass->getJson_prot_ref();
$oArrayProtRef = new web\ProtocoloArray($json_prot_ref, $a_posibles_lugares, 'referencias');
$oArrayProtRef->setRef('t');
$oArrayProtRef->setBlanco('t');
$oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

$asunto_e = $oEntradaBypass->getAsunto_entrada();
$asunto = $oEntradaBypass->getAsunto();
$f_entrada = $oEntradaBypass->getF_entrada()->getFromLocal();
// oficinas:
$oficinas_txt = '';
$id_of_ponente = $oEntradaBypass->getPonente();
$oficinas_txt .= empty($a_posibles_oficinas[$id_of_ponente]) ? '?' : $a_posibles_oficinas[$id_of_ponente];
$a_oficinas = $oEntradaBypass->getResto_oficinas();
foreach ($a_oficinas as $id_id_oficina) {
    $sigla_of = $a_posibles_oficinas[$id_id_oficina];
    $oficinas_txt .= empty($oficinas_txt) ? '' : ', ';
    $oficinas_txt .= $sigla_of;
}

$a_adjuntos = $oEntradaBypass->getArrayIdAdjuntos();

$oArrayProtDestino = new web\ProtocoloArray('', $a_posibles_lugares, 'destinos');
$oArrayProtDestino->setBlanco('t');
$oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');

$f_salida = $oEntradaBypass->getF_salida()->getFromLocal();
$a_grupos = $oEntradaBypass->getId_grupos();
if (!empty($a_grupos)) {
    $oArrayDesplGrupo = new web\DesplegableArray($a_grupos, $a_posibles_grupos, 'grupos');
    $chk_grupo_dst = 'checked';
} else {
    $oArrayDesplGrupo = new web\DesplegableArray('', $a_posibles_grupos, 'grupos');
    $chk_grupo_dst = '';
    if (!empty($oEntradaBypass->getJson_prot_destino())) {
        $json_prot_dst = $oEntradaBypass->getJson_prot_destino();
        $oArrayProtDestino->setArray_sel($json_prot_dst);
    }
}
$oArrayDesplGrupo->setBlanco('t');
$oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');

$titulo = _("salida distribución cr");
if (empty($f_salida)) {
    $oHoy = new DateTimeLocal();
    $f_salida = $oHoy->getFromLocal();
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha::getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$base_url = core\ConfigGlobal::getWeb();
$url_download = $base_url . '/apps/entradas/controller/download.php';
$url_update = 'apps/entradas/controller/entrada_update.php';
$pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query(['filtro' => 'bypass']));

$a_campos = [
    'titulo' => $titulo,
    'id_entrada' => $Q_id_entrada,
    'asunto_e' => $asunto_e,
    'asunto' => $asunto,
    'f_entrada' => $f_entrada,
    'oProtOrigen' => $oProtOrigen,
    'oArrayProtRef' => $oArrayProtRef,
    'oficinas_txt' => $oficinas_txt,
    //'oHash' => $oHash,
    'chk_grupo_dst' => $chk_grupo_dst,
    'id_grupo' => $id_grupo,
    'oArrayDesplGrupo' => $oArrayDesplGrupo,
    'oArrayProtDestino' => $oArrayProtDestino,

    'f_salida' => $f_salida,
    'a_adjuntos' => $a_adjuntos,
    'url_download' => $url_download,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,

    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
];

$oView = new ViewTwig('entradas/controller');
$oView->renderizar('entrada_bypass.html.twig', $a_campos);