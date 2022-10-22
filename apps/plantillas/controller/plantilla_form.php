<?php

use core\ViewTwig;
use plantillas\model\entity\Plantilla;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_plantilla = (integer)filter_input(INPUT_POST, 'id_plantilla');

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
                $Q_id_plantilla = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_plantilla = $oPosicion2->getParametro('id_usuario');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    if ($Q_que !== 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
        $Q_id_plantilla = (integer)strtok($a_sel[0], "#");
        // el scroll id es de la página anterior, hay que guardarlo allí
        $oPosicion->addParametro('id_sel', $a_sel, 1);
        $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
        $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    }
}
$oPosicion->setParametros(array('id_plantilla' => $Q_id_plantilla), 1);

if (!empty($Q_id_plantilla)) {
    $que = 'guardar';
    $oPlantilla = new Plantilla(array('id_plantilla' => $Q_id_plantilla));

    $nombre = $oPlantilla->getNombre();
} else {
    $que = 'nuevo';
    $nombre = '';
}

$camposForm = 'que';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$a_camposHidden = array(
    'id_plantilla' => $Q_id_plantilla,
    'que' => $que,
);
$oHash->setArraycamposHidden($a_camposHidden);

$base_url = core\ConfigGlobal::getWeb();
$pagina_cancel = web\Hash::link('apps/plantillas/controller/plantilla_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_plantilla' => $Q_id_plantilla,
    'oHash' => $oHash,
    'nombre' => $nombre,
    'base_url' => $base_url,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('plantillas/controller');
$oView->renderizar('plantilla_form.html.twig', $a_campos);
