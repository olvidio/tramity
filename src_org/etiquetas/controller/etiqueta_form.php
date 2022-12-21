<?php

use core\ViewTwig;
use etiquetas\model\entity\Etiqueta;
use usuarios\domain\entity\Cargo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_refresh = (integer)filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Q_refresh);

$Q_id_etiqueta = (integer)filter_input(INPUT_POST, 'id_etiqueta');
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
                $Q_id_etiqueta = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_etiqueta = $oPosicion2->getParametro('id_usuario');
                $Q_quien = $oPosicion2->getParametro('quien');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    if ($Q_que !== 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
        $Q_id_etiqueta = (integer)strtok($a_sel[0], "#");
        // el scroll id es de la página anterior, hay que guardarlo allí
        $oPosicion->addParametro('id_sel', $a_sel, 1);
        $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
        $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    }
}
$oPosicion->setParametros(array('id_etiqueta' => $Q_id_etiqueta), 1);

$chk_oficina = 'checked';
$chk_personal = '';
if (!empty($Q_id_etiqueta)) {
    $que_user = 'guardar';
    $oEtiqueta = new Etiqueta(array('id_etiqueta' => $Q_id_etiqueta));

    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $oficina = $oEtiqueta->getOficina();
    if (is_true($oficina)) {
        $chk_oficina = 'checked';
        $chk_personal = '';
    } else {
        $chk_oficina = '';
        $chk_personal = 'checked';
    }
} else {
    $que_user = 'nuevo';
    $nom_etiqueta = '';
}

$entorno = _("de la oficina");
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $entorno = _("del centro");
}


$camposForm = 'que!nom_etiqueta';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('oficina');
$a_camposHidden = array(
    'id_etiqueta' => $Q_id_etiqueta,
    'quien' => $Q_quien,
    'que' => 'guardar',
);
$oHash->setArraycamposHidden($a_camposHidden);

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_etiqueta' => $Q_id_etiqueta,
    'que_user' => $que_user,
    'quien' => $Q_quien,
    'oHash' => $oHash,
    'nom_etiqueta' => $nom_etiqueta,
    'chk_oficina' => $chk_oficina,
    'chk_personal' => $chk_personal,
    'entorno' => $entorno,
];

$oView = new ViewTwig('etiquetas/controller');
$oView->renderizar('etiqueta_form.html.twig', $a_campos);
