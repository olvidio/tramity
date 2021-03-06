<?php
use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\Usuario;
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

$Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
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
$oPosicion->setParametros(array('id_usuario'=>$Qid_usuario),1);

$oGCargos = new GestorCargo();
$oDesplCargos= $oGCargos->getDesplCargosUsuario($Qid_usuario);
$oDesplCargos->setNombre('id_cargo_preferido');

if (!empty($Qid_usuario)) {
    $que_user='guardar';
    $oUsuario = new Usuario(array('id_usuario'=>$Qid_usuario));

    $usuario=$oUsuario->getUsuario();
    $nom_usuario=$oUsuario->getNom_usuario();
    $pass=$oUsuario->getPassword();
    $email=$oUsuario->getEmail();
    $id_cargo_preferido=$oUsuario->getId_cargo_preferido();
    
    $oDesplCargos->setOpcion_sel($id_cargo_preferido);
} else {
    $que_user='nuevo';
    $id_cargo_preferido='';
    $Qid_usuario='';
    $usuario='';
    $nom_usuario='';
    $pass='';
    $email='';
}
$camposForm = 'que!usuario!nom_usuario!password!email!id_cargo_preferido';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('pass!password!id_ctr!id_nom!casas');
$a_camposHidden = array(
        'id_usuario' => $Qid_usuario,
        'quien' => $Qquien,
        'que' => $que_user,
        );
$oHash->setArraycamposHidden($a_camposHidden);

$url_usuario_ajax = ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_ajax.php';
$oHash1 = new web\Hash();
$oHash1->setUrl($url_usuario_ajax);
$oHash1->setCamposForm('que!id_usuario'); 
$oHash1->setCamposNo('scroll_id'); 
$h1 = $oHash1->linkSinVal();

$txt_guardar=_("guardar datos usuario");
$txt_eliminar = _("¿Está seguro que desea quitar este permiso?");

$pagina_cancel = web\Hash::link('apps/usuarios/controller/usuario_lista.php?'.http_build_query([]));

$a_campos = [
            'oPosicion' => $oPosicion,
            'url_usuario_ajax' => $url_usuario_ajax,
            'id_usuario' => $Qid_usuario,
            'h1' => $h1,
            'quien' => $Qquien,
            'usuario' => $usuario,
            'oHash' => $oHash,
            'pass' => $pass,
            'usuario' => $usuario,
            'nom_usuario' => $nom_usuario,
            'oDesplCargos' => $oDesplCargos,
            'email' => $email,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            'pagina_cancel' => $pagina_cancel,
            ];

$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('usuario_form.html.twig',$a_campos);
