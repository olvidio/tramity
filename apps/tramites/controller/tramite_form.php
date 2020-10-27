<?php
use core\ViewTwig;
use tramites\model\entity\Tramite;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_tramite = (integer) \filter_input(INPUT_POST, 'id_tramite');

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
                $Qid_tramite = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_tramite = $oPosicion2->getParametro('id_tramite');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_tramite = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_tramite'=>$Qid_tramite),1);


$txt_guardar=_("guardar datos trámite");
$oSelects = array();
if (!empty($Qid_tramite)) {
    $que = 'guardar';
    $oTramite = new Tramite();
    $oTramite->setId_tramite($Qid_tramite);
    $oTramite->DBcarregar();
    $tramite = $oTramite->getTramite();
    $orden = $oTramite->getOrden();
    $breve = $oTramite->getBreve();
} else {
    $que = 'nuevo';
    $Qid_tramite = '';
    $tramite = '';
    $orden = '';
    $breve = '';
}

$camposForm = 'que!tramite!descripcion';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('');
$a_camposHidden = array(
        'id_tramite' => $Qid_tramite,
        'que' => $que,
        );
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = 'apps/tramites/controller/tramite_update.php';
$txt_eliminar = _("¿Está seguro que desea borrar esta tramite?");

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_tramite' => $Qid_tramite,
            'oHash' => $oHash,
            'tramite' => $tramite,
            'orden' => $orden,
            'breve' => $breve,
            'url_update' => $url_update,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            ];

$oView = new ViewTwig('tramites/controller');
echo $oView->renderizar('tramite_form.html.twig',$a_campos);
