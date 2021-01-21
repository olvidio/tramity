<?php
use core\ViewTwig;
use entradas\model\Entrada;
use entradas\model\entity\GestorEntradaBypass;
use lugares\model\entity\GestorGrupo;
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

$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargosDirector();

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();

$oEntrada = new Entrada($Qid_entrada);
// categoria
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

// por defecto va por grupos.
$chk_grupo_dst = 'checked';
$id_grupo = 0;
$f_salida = '';

$gesGrupo = new GestorGrupo();
$a_posibles_grupos = $gesGrupo->getArrayGrupos();

$json_prot_origen = $oEntrada->getJson_prot_origen();
$oProtOrigen = new Protocolo();
$oProtOrigen->setEtiqueta('De');
$oProtOrigen->setNombre('origen');
$oProtOrigen->setBlanco(TRUE);
$oProtOrigen->setTabIndex(10);
$oProtOrigen->setLugar($json_prot_origen->lugar);
$oProtOrigen->setProt_num($json_prot_origen->num);
$oProtOrigen->setProt_any($json_prot_origen->any);
$oProtOrigen->setMas($json_prot_origen->mas);

$json_prot_ref = $oEntrada->getJson_prot_ref();
$oArrayProtRef = new web\ProtocoloArray($json_prot_ref,$a_posibles_lugares,'referencias');
$oArrayProtRef->setRef('t');
$oArrayProtRef->setBlanco('t');
$oArrayProtRef->setAccionConjunto('fnjs_mas_referencias(event)');

$asunto_e = $oEntrada->getAsunto_entrada();
$asunto = $oEntrada->getAsunto();
$f_entrada = $oEntrada->getF_entrada()->getFromLocal();
// oficinas:
$oficinas_txt = '';
$id_ponente = $oEntrada->getPonente();
$ponente = $a_posibles_cargos[$id_ponente];
$oficinas_txt .= $ponente;
$a_oficinas = $oEntrada->getResto_oficinas();
foreach ($a_oficinas as $id_cargo) {
    $cargo = $a_posibles_cargos[$id_cargo];
    $oficinas_txt .= empty($oficinas_txt)? '' : ', '; 
    $oficinas_txt .= $cargo;
}

$a_adjuntos = $oEntrada->getArrayIdAdjuntos();

$oArrayProtDestino = new web\ProtocoloArray('',$a_posibles_lugares,'destinos');
$oArrayProtDestino->setBlanco('t');
$oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos(event)');
    
// a ver si ya está
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
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos(event)');
    
} else {
    $oArrayDesplGrupo = new web\DesplegableArray('',$a_posibles_grupos,'grupos');
    $oArrayDesplGrupo->setBlanco('t');
    $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos(event)');
}

$titulo = _("salida distribución cr");
if (empty($f_salida)) {
    $oHoy = new DateTimeLocal();
    $f_salida = $oHoy->getFromLocal();
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$base_url = core\ConfigGlobal::getWeb();
$url_download = $base_url.'/apps/entradas/controller/download.php?plugin=1';
$url_update = 'apps/entradas/controller/entrada_update.php';
$pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query(['filtro' => 'bypass']));

$a_campos = [
    'titulo' => $titulo,
    'id_entrada' => $Qid_entrada,
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
echo $oView->renderizar('entrada_bypass.html.twig',$a_campos);