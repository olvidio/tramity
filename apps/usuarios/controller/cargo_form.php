<?php
use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');

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
                $Qid_usuario = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_usuario = $oPosicion2->getParametro('id_usuario');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_usuario = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_cargo'=>$Qid_cargo),1);

$oCargo = new Cargo();

$txt_guardar=_("guardar datos usuario");
$oSelects = array();
if (!empty($Qid_cargo)) {
    $que = 'guardar';
    $oCargo->setId_cargo($Qid_cargo);
    $oCargo->DBcarregar();
    $cargo = $oCargo->getCargo();
    $descripcion = $oCargo->getDescripcion();
    $id_ambito = $oCargo->getId_ambito();
    $id_oficina = $oCargo->getId_oficina();
    $director = $oCargo->getDirector();
    $chk_director = ($director === TRUE)? 'checked' : ''; 
} else {
    $que = 'nuevo';
    $Qid_cargo = '';
    $cargo = '';
    $descripcion = '';
    $id_ambito = $oCargo::AMBITO_DL; // segun configuración de la aplicacion;
    $id_oficina = '';
    $chk_director = '';
}

$oGOficinas = new GestorOficina();
$oDesplOficinas= $oGOficinas->getListaOficinas();
$oDesplOficinas->setOpcion_sel($id_oficina);
$oDesplOficinas->setNombre('id_oficina');

$camposForm = 'que!cargo!descripcion!id_oficina';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('');
$a_camposHidden = array(
        'id_cargo' => $Qid_cargo,
        'que' => $que,
        'id_ambito' => $id_ambito,
        );
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = 'apps/usuarios/controller/cargo_update.php';
$txt_eliminar = _("¿Está seguro que desea quitar este cargo?");

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_cargo' => $Qid_cargo,
            'oHash' => $oHash,
            'cargo' => $cargo,
            'descripcion' => $descripcion,
            'oDesplOficinas' => $oDesplOficinas,
            'chk_director' => $chk_director,
            'url_update' => $url_update,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            ];

$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('cargo_form.html.twig',$a_campos);
