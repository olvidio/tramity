<?php
use core\ViewTwig;
use usuarios\model\entity\Oficina;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');

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
                $Qid_oficina = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_oficina = $oPosicion2->getParametro('id_oficina');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_oficina = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_oficina'=>$Qid_oficina),1);

$oOficina = new Oficina();

$txt_guardar=_("guardar datos oficina");
if (!empty($Qid_oficina)) {
    $que = 'guardar';
    $oOficina->setId_oficina($Qid_oficina);
    $oOficina->DBcarregar();
    $sigla = $oOficina->getSigla();
    $orden = $oOficina->getOrden();
} else {
    $que = 'nuevo';
    $Qid_oficina = '';
    $sigla = '';
    $orden = '';
}

$camposForm = 'que!oficina!descripcion';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('');
$a_camposHidden = array(
        'id_oficina' => $Qid_oficina,
        'que' => $que,
        );
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = 'apps/usuarios/controller/oficina_update.php';
$txt_eliminar = _("¿Está seguro que desea borrar esta oficina?");

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_oficina' => $Qid_oficina,
            'oHash' => $oHash,
            'sigla' => $sigla,
            'orden' => $orden,
            'url_update' => $url_update,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            ];

$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('oficina_form.html.twig',$a_campos);
