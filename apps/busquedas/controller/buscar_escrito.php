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
$Qsimple = (integer) \filter_input(INPUT_POST, 'simple');
$Qsimple = 1;

// Busco el id_lugar de la dl.
$id_dl = 12;
// Busco el id_lugar de cr.
$id_cr = 12;

if (is_true($Qctr_anulados)) {
    $chk_ctr_anulados = 'checked';
} else {
    $chk_ctr_anulados = '';
}

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayLugares();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setAction("fnjs_aviso_IESE('#lugar')");

//select id='lista_lugar' name='lista_lugar' class=contenido onchange="fnjs_activar_lugar(2)">
$oDesplLugar4 = new Desplegable();
$oDesplLugar4->setNombre('lista_lugar');
$oDesplLugar4->setBlanco(TRUE);
$oDesplLugar4->setOpciones($a_lugares);
$oDesplLugar4->setAction("fnjs_activar_lugar(2)");

$oDesplOrigen = new Desplegable();
$oDesplOrigen->setNombre('origen_id_lugar');
$oDesplOrigen->setBlanco(TRUE);
$oDesplOrigen->setOpciones($a_lugares);
$oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");

//<select id="origen_id_lugar_2" name="origen_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#origen_id_lugar_2')">
$oDesplOrigen2 = new Desplegable();
$oDesplOrigen2->setNombre('origen_id_lugar');
$oDesplOrigen2->setId('origen_id_lugar_2');
$oDesplOrigen2->setBlanco(TRUE);
$oDesplOrigen2->setOpciones($a_lugares);
$oDesplOrigen2->setAction("fnjs_sel_periodo('#origen_id_lugar_2')");
    
/*
$opciones=array_oficinas("db");
echo "<select name='oficina'  class=contenido>";
pdo_options($opciones,$row_oficinas,1);
echo "</select>";
*/
$gesOficinas = new GestorOficina();
$a_oficinas = $gesOficinas->getArrayOficinas();
$oDesplOficinas = new Desplegable();
$oDesplOficinas->setNombre('oficina');
$oDesplOficinas->setBlanco(TRUE);
$oDesplOficinas->setOpciones($a_oficinas);

//<select id="dest_id_lugar_2" name="dest_id_lugar" class=contenido onchange="fnjs_aviso_IESE('#dest_id_lugar_2')">
$oDesplDestino2 = new Desplegable();
$oDesplDestino2->setNombre('dest_id_lugar_2');
$oDesplDestino2->setBlanco(TRUE);
$oDesplDestino2->setOpciones($a_lugares);

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

$dele = $_SESSION['oConfig']->getSigla();

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$a_campos = [
    //'oHash' => $oHash,
    'oDesplLugar' => $oDesplLugar,
    'oDesplLugar4' => $oDesplLugar4,
    'oDesplOrigen' => $oDesplOrigen,
    'oDesplOrigen2' => $oDesplOrigen2,
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplDestino2' => $oDesplDestino2,
    'oDesplAntiguedad' => $oDesplAntiguedad,
    'id_dl' => $id_dl,
    'dele' => $dele,
    'id_cr' => $id_cr,
    'simple' => $Qsimple,
    'chk_ctr_anulados' => $chk_ctr_anulados,
    // datepicker
    'format' => $format,
    ];

$oView = new ViewTwig('busquedas/controller');
echo $oView->renderizar('buscar_escrito.html.twig',$a_campos);