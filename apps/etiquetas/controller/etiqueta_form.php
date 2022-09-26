<?php
use core\ViewTwig;
use function core\is_true;
use etiquetas\model\entity\Etiqueta;
use usuarios\model\entity\Cargo;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_etiqueta = (integer) \filter_input(INPUT_POST, 'id_etiqueta');
$Qquien = (string) \filter_input(INPUT_POST, 'quien');

$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
	$stack = \filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
	if ($stack != '') {
		// No me sirve el de global_object, sino el de la session
		$oPosicion2 = new web\Posicion();
		if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
			$a_sel=$oPosicion2->getParametro('id_sel');
			if (!empty($a_sel)) {
                $Qid_etiqueta = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_etiqueta = $oPosicion2->getParametro('id_usuario');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_etiqueta = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_etiqueta'=>$Qid_etiqueta),1);

$chk_oficina = 'checked';
$chk_personal = '';
if (!empty($Qid_etiqueta)) {
    $que_user='guardar';
    $oEtiqueta = new Etiqueta(array('id_etiqueta'=>$Qid_etiqueta));

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
if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
	$entorno = _("del centro");
}


$camposForm = 'que!nom_etiqueta';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('oficina');
$a_camposHidden = array(
        'id_etiqueta' => $Qid_etiqueta,
        'quien' => $Qquien,
        'que' => 'guardar',
        );
$oHash->setArraycamposHidden($a_camposHidden);

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_etiqueta' => $Qid_etiqueta,
            'que_user' => $que_user,
            'quien' => $Qquien,
            'oHash' => $oHash,
            'nom_etiqueta' => $nom_etiqueta,
            'chk_oficina' => $chk_oficina,
            'chk_personal' => $chk_personal,
			'entorno' => $entorno,
            ];

$oView = new ViewTwig('etiquetas/controller');
echo $oView->renderizar('etiqueta_form.html.twig',$a_campos);
