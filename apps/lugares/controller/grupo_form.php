<?php

use core\ViewTwig;
use escritos\model\Escrito;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Grupo;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_refresh = (integer)filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Q_refresh);

$Q_id_grupo = (integer)filter_input(INPUT_POST, 'id_grupo');
$Q_quien = (string)filter_input(INPUT_POST, 'quien');

// Para una ventana nueva (personalizar), vengo por GET
$a_grupos_filtered = [];
$Q_lista_grupos = (string)filter_input(INPUT_GET, 'lista_grupos');
if (!empty($Q_lista_grupos)) {
    $a_lista_grupos = explode(',', $Q_lista_grupos);
    $a_grupos_filtered = array_filter($a_lista_grupos);
}
$Q_id_escrito = (integer)filter_input(INPUT_GET, 'id_escrito');


$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack != '') {
        // No me sirve el de global_object, sino el de la session
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $a_sel = $oPosicion2->getParametro('id_sel');
            if (!empty($a_sel)) {
                $Q_id_grupo = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_grupo = $oPosicion2->getParametro('id_usuario');
                $Q_quien = $oPosicion2->getParametro('quien');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    if ($Q_que !== 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
        $Q_id_grupo = (integer)strtok($a_sel[0], "#");
        // el scroll id es de la página anterior, hay que guardarlo allí
        $oPosicion->addParametro('id_sel', $a_sel, 1);
        $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
        $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    }
}
$oPosicion->setParametros(array('id_grupo' => $Q_id_grupo), 1);

$gesLugares = new GestorLugar();
$a_posibles_lugares_ctr = $gesLugares->getArrayLugaresCtr();
$a_posibles_lugares_dl = $gesLugares->getArrayLugaresTipo('dl');
$a_posibles_lugares_cr = $gesLugares->getArrayLugaresTipo('cr');

if (!empty($Q_id_grupo)) {
    $que = 'guardar';
    $oGrupo = new Grupo($Q_id_grupo);

    $descripcion = $oGrupo->getDescripcion();
    $a_miembros = $oGrupo->getMiembros();
} else {
    $que = 'nuevo';
    $descripcion = '';
    $a_miembros = [];
}
$nueva_ventana = FALSE;
// para el caso de ventana nueva: ver destinos
if (!empty($Q_id_escrito)) {
    $nueva_ventana = TRUE;
    $que = 'guardar_escrito';
    $a_miembros = [];
    $descripcion = '';
    foreach ($a_grupos_filtered as $id_grupo) {
        if ($id_grupo === 'custom') {
            $oEscrito = new Escrito($Q_id_escrito);
            $a_miembros = $oEscrito->getDestinosIds();
            $descripcion = $oEscrito->getDescripcion();
        } else {
            $oGrupo = new Grupo($id_grupo);

            $descripcion .= empty($descripcion) ? '' : ', ';
            $descripcion .= $oGrupo->getDescripcion();
            $a_miembros = array_merge($a_miembros, $oGrupo->getMiembros());
        }
    }
}

$oDesplLugaresCtr = new Desplegable('lugares', $a_posibles_lugares_ctr, $a_miembros);
$oDesplLugaresDl = new Desplegable('lugares', $a_posibles_lugares_dl, $a_miembros);
$oDesplLugaresCr = new Desplegable('lugares', $a_posibles_lugares_cr, $a_miembros);

$camposForm = 'que!sigla!dl!region!nombre!tipo_ctr!tipo_labor';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('anulado');
$a_camposHidden = array(
    'id_grupo' => $Q_id_grupo,
    'quien' => $Q_quien,
    'que' => $que,
    // ventana nueva
    'id_escrito' => $Q_id_escrito,
);
$oHash->setArraycamposHidden($a_camposHidden);

$base_url = core\ConfigGlobal::getWeb();

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_grupo' => $Q_id_grupo,
    'quien' => $Q_quien,
    'oHash' => $oHash,
    'descripcion' => $descripcion,
    'oDesplLugaresCtr' => $oDesplLugaresCtr,
    'oDesplLugaresDl' => $oDesplLugaresDl,
    'oDesplLugaresCr' => $oDesplLugaresCr,
    'nueva_ventana' => $nueva_ventana,
    'base_url' => $base_url,
];

$oView = new ViewTwig('lugares/controller');
$oView->renderizar('grupo_form.html.twig', $a_campos);
