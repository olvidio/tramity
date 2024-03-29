<?php

use core\ViewTwig;
use tramites\model\entity\Tramite;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_refresh = (integer)filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Q_refresh);

$Q_id_tramite = (integer)filter_input(INPUT_POST, 'id_tramite');

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
                $Q_id_tramite = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_tramite = $oPosicion2->getParametro('id_tramite');
                $Q_quien = $oPosicion2->getParametro('quien');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    if ($Q_que !== 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
        $Q_id_tramite = (integer)strtok($a_sel[0], "#");
        // el scroll id es de la página anterior, hay que guardarlo allí
        $oPosicion->addParametro('id_sel', $a_sel, 1);
        $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
        $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    }
}
$oPosicion->setParametros(array('id_tramite' => $Q_id_tramite), 1);


$txt_guardar = _("guardar datos trámite");
$oSelects = [];

$oTramite = new Tramite($Q_id_tramite);
if ($oTramite->DBCargar()) {
    $que = 'guardar';
    $tramite = $oTramite->getTramite();
    $orden = $oTramite->getOrden();
    $breve = $oTramite->getBreve();
    $activo = $oTramite->getActivo();
    $chk_activo = ($activo === TRUE) ? 'checked' : '';
} else {
    $que = 'nuevo';
    $tramite = '';
    $orden = '';
    $breve = '';
    $chk_activo = '';
}

$camposForm = 'que!tramite!descripcion';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('');
$a_camposHidden = array(
    'id_tramite' => $Q_id_tramite,
    'que' => $que,
);
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = 'apps/tramites/controller/tramite_update.php';
$txt_eliminar = _("¿Está seguro que desea borrar esta tramite?");

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_tramite' => $Q_id_tramite,
    'oHash' => $oHash,
    'tramite' => $tramite,
    'orden' => $orden,
    'breve' => $breve,
    'chk_activo' => $chk_activo,
    'url_update' => $url_update,
    'txt_guardar' => $txt_guardar,
    'txt_eliminar' => $txt_eliminar,
];

$oView = new ViewTwig('tramites/controller');
$oView->renderizar('tramite_form.html.twig', $a_campos);
