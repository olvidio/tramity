<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ViewTwig;
use function core\is_true;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorOficina;
use web\DateTimeLocal;
use web\Desplegable;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qctr_anulados = (bool) \filter_input(INPUT_POST, 'ctr_anulados');
//$Qsimple = (integer) \filter_input(INPUT_POST, 'simple');
//$Qsimple = 1;

// vengo de modificar algo, quiero volver a la lista
$Qopcion =  (integer) \filter_input(INPUT_POST, 'opcion');
//3
$Qorigen_id_lugar =  (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
$Qantiguedad =  (string) \filter_input(INPUT_POST, 'antiguedad');
//2
$Qasunto =  (string) \filter_input(INPUT_POST, 'asunto');
$Qf_min_enc =  (string) \filter_input(INPUT_POST, 'f_min');
$Qf_min = urldecode($Qf_min_enc);
$Qf_max_enc =  (string) \filter_input(INPUT_POST, 'f_max');
$Qf_max = urldecode($Qf_max_enc);
$Qoficina =  (string) \filter_input(INPUT_POST, 'oficina');
//3
$Qdest_id_lugar_2 =  (integer) \filter_input(INPUT_POST, 'dest_id_lugar_2');
//4
$Qlista_origen =  (string) \filter_input(INPUT_POST, 'lista_origen');
$Qlista_lugar =  (integer) \filter_input(INPUT_POST, 'lista_lugar');
//7
$Qid_lugar =  (integer) \filter_input(INPUT_POST, 'id_lugar');
$Qprot_num =  (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any =  (integer) \filter_input(INPUT_POST, 'prot_any');
// para uitar el '0':
$Qprot_num = empty($Qprot_num)? '' : $Qprot_num;
$Qprot_any = empty($Qprot_any)? '' : $Qprot_any;


$chk_lo_1 = '';
$chk_lo_2 = '';
$chk_lo_3 = '';
$chk_lo_4 = '';
switch ($Qlista_origen) {
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
}

if (is_true($Qctr_anulados)) {
    $chk_ctr_anulados = 'checked';
} else {
    $chk_ctr_anulados = '';
}

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayBusquedas($Qctr_anulados);

// Busco el id_lugar de la dl.
$id_siga_local = $gesLugares->getId_sigla_local();
$sigla = $_SESSION['oConfig']->getSigla();
// Busco el id_lugar de cr.
$id_cr = $gesLugares->getId_cr();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setAction("fnjs_aviso_IESE('#lugar')");
$oDesplLugar->setOpcion_sel($Qid_lugar);

//select id='lista_lugar' name='lista_lugar' class=contenido onchange="fnjs_activar_lugar(2)">
$oDesplLugar4 = new Desplegable();
$oDesplLugar4->setNombre('lista_lugar');
$oDesplLugar4->setBlanco(TRUE);
$oDesplLugar4->setOpciones($a_lugares);
$oDesplLugar4->setAction("fnjs_activar_lugar(2)");
$oDesplLugar4->setOpcion_sel($Qlista_lugar);

$oDesplOrigen = new Desplegable();
$oDesplOrigen->setNombre('origen_id_lugar');
$oDesplOrigen->setBlanco(TRUE);
$oDesplOrigen->setOpciones($a_lugares);
$oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");
$oDesplOrigen->setOpcion_sel($Qorigen_id_lugar);

//<select id="origen_id_lugar_2" name="origen_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#origen_id_lugar_2')">
$oDesplOrigen2 = new Desplegable();
$oDesplOrigen2->setNombre('origen_id_lugar');
$oDesplOrigen2->setId('origen_id_lugar_2');
$oDesplOrigen2->setBlanco(TRUE);
$oDesplOrigen2->setOpciones($a_lugares);
$oDesplOrigen2->setAction("fnjs_sel_periodo('#origen_id_lugar_2')");
$oDesplOrigen2->setOpcion_sel($Qorigen_id_lugar);
    
$gesOficinas = new GestorOficina();
$a_oficinas = $gesOficinas->getArrayOficinas();
$oDesplOficinas = new Desplegable();
$oDesplOficinas->setNombre('oficina');
$oDesplOficinas->setBlanco(TRUE);
$oDesplOficinas->setOpciones($a_oficinas);
$oDesplOficinas->setOpcion_sel($Qoficina);

//<select id="dest_id_lugar_2" name="dest_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#dest_id_lugar_2')">
$oDesplDestino2 = new Desplegable();
$oDesplDestino2->setNombre('dest_id_lugar_2');
$oDesplDestino2->setBlanco(TRUE);
$oDesplDestino2->setOpciones($a_lugares);
$oDesplDestino2->setOpcion_sel($Qdest_id_lugar_2);

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
$oDesplAntiguedad->setOpcion_sel($Qantiguedad);

if (!empty($Qopcion)) {
    $simple = 0;
} else {
    $simple = 1;
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();

$a_campos = [
    //'oHash' => $oHash,
    'oDesplLugar' => $oDesplLugar,
    'oDesplLugar4' => $oDesplLugar4,
    'oDesplOrigen' => $oDesplOrigen,
    'oDesplOrigen2' => $oDesplOrigen2,
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplDestino2' => $oDesplDestino2,
    'oDesplAntiguedad' => $oDesplAntiguedad,
    'id_dl' => $id_siga_local,
    'dele' => $sigla,
    'id_cr' => $id_cr,
    'simple' => $simple,
    'chk_ctr_anulados' => $chk_ctr_anulados,
    'opcion' => $Qopcion,
    'simple' => $simple,
    'asunto' => $Qasunto,
    'f_min' => $Qf_min,
    'f_max' => $Qf_max,
    'chk_lo_1' => $chk_lo_1, 
    'chk_lo_2' => $chk_lo_2, 
    'chk_lo_3' => $chk_lo_3, 
    'chk_lo_4' => $chk_lo_4,
    'prot_num' => $Qprot_num,
    'prot_any' => $Qprot_any,
    // datepicker
    'format' => $format,
    ];

$oView = new ViewTwig('busquedas/controller');
echo $oView->renderizar('buscar_escrito.html.twig',$a_campos);