<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use lugares\model\entity\GestorLugar;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\OficinaRepository;
use web\DateTimeLocal;
use web\Desplegable;
use function core\is_true;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_ctr_anulados = (bool)filter_input(INPUT_POST, 'ctr_anulados');

// vengo de modificar algo, quiero volver a la lista
$Q_opcion = (integer)filter_input(INPUT_POST, 'opcion');
//3
$Q_origen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');
$Q_antiguedad = (string)filter_input(INPUT_POST, 'antiguedad');
//2
$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_asunto = urldecode($Q_asunto);
$Q_f_min_enc = (string)filter_input(INPUT_POST, 'f_min');
$Q_f_min = urldecode($Q_f_min_enc);
$Q_f_max_enc = (string)filter_input(INPUT_POST, 'f_max');
$Q_f_max = urldecode($Q_f_max_enc);
$Q_oficina = (string)filter_input(INPUT_POST, 'oficina');
//3
$Q_dest_id_lugar_2 = (integer)filter_input(INPUT_POST, 'dest_id_lugar_2');
//4
$Q_lista_origen = (string)filter_input(INPUT_POST, 'lista_origen');
$Q_lista_lugar = (integer)filter_input(INPUT_POST, 'lista_lugar');
//7
$Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
$Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
// para uitar el '0':
$Q_prot_num = empty($Q_prot_num) ? '' : $Q_prot_num;
$Q_prot_any = empty($Q_prot_any) ? '' : $Q_prot_any;

//8 
$Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
$Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$a_etiquetas_filtered = array_filter($Q_a_etiquetas);

$chk_or = ($Q_andOr === 'OR') ? 'checked' : '';
// por defecto 'AND':
$chk_and = (($Q_andOr === 'AND') || empty($Q_andOr)) ? 'checked' : '';


$chk_lo_1 = '';
$chk_lo_2 = '';
$chk_lo_3 = '';
$chk_lo_4 = '';
switch ($Q_lista_origen) {
    case 'dl':
        $chk_lo_1 = 'checked';
        break;
    case 'de':
        $chk_lo_2 = 'checked';
        break;
    case 'cr_dl':
        $chk_lo_3 = 'checked';
        break;
    case 'cr_ctr':
        $chk_lo_4 = 'checked';
        break;
    default:
        // no hace falta, ya se borran todas los $chk_ antes del switch
}

if (is_true($Q_ctr_anulados)) {
    $chk_ctr_anulados = 'checked';
} else {
    $chk_ctr_anulados = '';
}

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayBusquedas($Q_ctr_anulados);

// Busco el id_lugar de la dl.
$id_siga_local = $gesLugares->getId_sigla_local();
$sigla = $_SESSION['oConfig']->getSigla();
// Busco el id_lugar de cr.
$id_cr = $gesLugares->getId_cr();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setAction("fnjs_aviso_IESE('#id_lugar')");
$oDesplLugar->setOpcion_sel($Q_id_lugar);

//select id='lista_lugar' name='lista_lugar' class=contenido onchange="fnjs_activar_lugar(2)">
$oDesplLugar4 = new Desplegable();
$oDesplLugar4->setNombre('lista_lugar');
$oDesplLugar4->setBlanco(TRUE);
$oDesplLugar4->setOpciones($a_lugares);
$oDesplLugar4->setAction("fnjs_activar_lugar(2)");
$oDesplLugar4->setOpcion_sel($Q_lista_lugar);

$oDesplOrigen = new Desplegable();
$oDesplOrigen->setNombre('origen_id_lugar');
$oDesplOrigen->setBlanco(TRUE);
$oDesplOrigen->setOpciones($a_lugares);
$oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");
$oDesplOrigen->setOpcion_sel($Q_origen_id_lugar);

//<select id="origen_id_lugar_2" name="origen_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#origen_id_lugar_2')">
$oDesplOrigen2 = new Desplegable();
$oDesplOrigen2->setNombre('origen_id_lugar');
$oDesplOrigen2->setId('origen_id_lugar_2');
$oDesplOrigen2->setBlanco(TRUE);
$oDesplOrigen2->setOpciones($a_lugares);
$oDesplOrigen2->setAction("fnjs_sel_periodo('#origen_id_lugar_2')");
$oDesplOrigen2->setOpcion_sel($Q_origen_id_lugar);

$OficinaRepository = new OficinaRepository();
$a_oficinas = $OficinaRepository->getArrayOficinas();
$oDesplOficinas2 = new Desplegable();
$oDesplOficinas2->setNombre('oficina');
$oDesplOficinas2->setId('oficina_2');
$oDesplOficinas2->setBlanco(TRUE);
$oDesplOficinas2->setOpciones($a_oficinas);
$oDesplOficinas2->setOpcion_sel($Q_oficina);

$oDesplOficinas3 = new Desplegable();
$oDesplOficinas3->setNombre('oficina');
$oDesplOficinas3->setId('oficina_3');
$oDesplOficinas3->setBlanco(TRUE);
$oDesplOficinas3->setOpciones($a_oficinas);
$oDesplOficinas3->setOpcion_sel($Q_oficina);

$oDesplOficinas4 = new Desplegable();
$oDesplOficinas4->setNombre('oficina');
$oDesplOficinas4->setId('oficina_4');
$oDesplOficinas4->setBlanco(TRUE);
$oDesplOficinas4->setOpciones($a_oficinas);
$oDesplOficinas4->setOpcion_sel($Q_oficina);

$oDesplOficinas6 = new Desplegable();
$oDesplOficinas6->setNombre('oficina');
$oDesplOficinas6->setId('oficina_6');
$oDesplOficinas6->setBlanco(TRUE);
$oDesplOficinas6->setOpciones($a_oficinas);
$oDesplOficinas6->setOpcion_sel($Q_oficina);

$oDesplOficinas9 = new Desplegable();
$oDesplOficinas9->setNombre('oficina');
$oDesplOficinas9->setId('oficina_9');
$oDesplOficinas9->setBlanco(TRUE);
$oDesplOficinas9->setOpciones($a_oficinas);
$oDesplOficinas9->setOpcion_sel($Q_oficina);

//<select id="dest_id_lugar_2" name="dest_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#dest_id_lugar_2')">
$oDesplDestino2 = new Desplegable();
$oDesplDestino2->setNombre('dest_id_lugar_2');
$oDesplDestino2->setBlanco(TRUE);
$oDesplDestino2->setOpciones($a_lugares);
$oDesplDestino2->setOpcion_sel($Q_dest_id_lugar_2);

$a_antiguedad = [
    "1m" => _("1 mes"),
    "3m" => _("3 meses"),
    "6m" => _("6 meses"),
    "1a" => _("1 año"),
    "2a" => _("2 años"),
    "aa" => _("más de 2 años"),
];
$oDesplAntiguedad = new Desplegable();
$oDesplAntiguedad->setNombre('antiguedad');
$oDesplAntiguedad->setBlanco(TRUE);
$oDesplAntiguedad->setOpciones($a_antiguedad);
$oDesplAntiguedad->setOpcion_sel($Q_antiguedad);

// Opción 8: etiquetas
$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}

$oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
$oArrayDesplEtiquetas->setBlanco('t');
$oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');


if (!empty($Q_opcion)) {
    $simple = 0;
} else {
    $simple = 1;
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha::getFormat();

$vista = ConfigGlobal::getVista();

// para reducir la vista en el caso de los ctr
$vista_dl = TRUE;
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $vista_dl = FALSE;
}

$a_campos = [
    //'oHash' => $oHash,
    'vista_dl' => $vista_dl,
    'oDesplLugar' => $oDesplLugar,
    'oDesplLugar4' => $oDesplLugar4,
    'oDesplOrigen' => $oDesplOrigen,
    'oDesplOrigen2' => $oDesplOrigen2,
    'oDesplOficinas2' => $oDesplOficinas2,
    'oDesplDestino2' => $oDesplDestino2,
    'oDesplAntiguedad' => $oDesplAntiguedad,
    'oDesplOficinas3' => $oDesplOficinas3,
    'oDesplOficinas4' => $oDesplOficinas4,
    'oDesplOficinas6' => $oDesplOficinas6,
    'oDesplOficinas9' => $oDesplOficinas9,
    'id_dl' => $id_siga_local,
    'dele' => $sigla,
    'id_cr' => $id_cr,
    'simple' => $simple,
    'chk_ctr_anulados' => $chk_ctr_anulados,
    'filtro' => $Q_filtro,
    'opcion' => $Q_opcion,
    'asunto' => $Q_asunto,
    'f_min' => $Q_f_min,
    'f_max' => $Q_f_max,
    'chk_lo_1' => $chk_lo_1,
    'chk_lo_2' => $chk_lo_2,
    'chk_lo_3' => $chk_lo_3,
    'chk_lo_4' => $chk_lo_4,
    'prot_num' => $Q_prot_num,
    'prot_any' => $Q_prot_any,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    'chk_and' => $chk_and,
    'chk_or' => $chk_or,
    // datepicker
    'format' => $format,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('busquedas/controller');
$oView->renderizar('buscar_escrito.html.twig', $a_campos);