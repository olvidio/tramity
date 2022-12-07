<?php

use core\ViewTwig;
use usuarios\domain\repositories\CargoGrupoRepository;
use usuarios\domain\repositories\CargoRepository;
use web\Desplegable;
use web\Hash;

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

$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack !== '') {
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

$CargoRepository = new CargoRepository();
$a_posibles_cargos = $CargoRepository->getArrayCargos();
$a_posibles_cargos_ref = $CargoRepository->getArrayCargosRef();

if (!empty($Q_id_grupo)) {
    $que_user = 'guardar';
    $CargoGrupoRepository = new CargoGrupoRepository();
    $oCargoGrupo = $CargoGrupoRepository->findById($Q_id_grupo);

    $id_cargo_ref = $oCargoGrupo->getId_cargo_ref();
    $descripcion = $oCargoGrupo->getDescripcion();
    $a_miembros = $oCargoGrupo->getMiembros();
} else {
    $que_user = 'nuevo';
    $descripcion = '';
    $a_miembros = [];
    $id_cargo_ref = '';
}

$oDesplCargosRef = new Desplegable('id_cargo_ref', $a_posibles_cargos_ref, $id_cargo_ref);
$oDesplCargos = new Desplegable('cargos', $a_posibles_cargos, $a_miembros);

$camposForm = 'que!nombre';
$oHash = new Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('anulado');
$a_camposHidden = array(
    'id_grupo' => $Q_id_grupo,
    'quien' => $Q_quien,
    'que' => 'guardar',
);
$oHash->setArraycamposHidden($a_camposHidden);

$txt_guardar = _("guardar datos grupo");
$txt_eliminar = _("¿Está seguro que desea quitar este grupo?");

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_grupo' => $Q_id_grupo,
    'que_user' => $que_user,
    'quien' => $Q_quien,
    'oHash' => $oHash,
    'descripcion' => $descripcion,
    'txt_guardar' => $txt_guardar,
    'txt_eliminar' => $txt_eliminar,
    'oDesplCargos' => $oDesplCargos,
    'oDesplCargosRef' => $oDesplCargosRef,
];

$oView = new ViewTwig('usuarios/controller');
$oView->renderizar('cargo_grupo_form.html.twig', $a_campos);
