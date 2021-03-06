<?php
use core\ViewTwig;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\Usuario;
use davical\model\entity\GestorUser;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();
	

$Qid_sel = (string) \filter_input(INPUT_POST, 'id_sel');
$Qscroll_id = (string) \filter_input(INPUT_POST, 'scroll_id');	

//Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
	$stack = \filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
	if ($stack != '') {
		$oPosicion2 = new web\Posicion();
		if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
			$Qid_sel=$oPosicion2->getParametro('id_sel');
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
}

/*
 * ESTA EN /usr/share/awl/inc# vim AWLUtilities.php
 */

// Para crear el password y guardar en la db:
/**
 * Make a salted SHA1 string, given a string and (possibly) a salt.  PHP5 only (although it
 * could be made to work on PHP4 (@see http://www.openldap.org/faq/data/cache/347.html). The
 * algorithm used here is compatible with OpenLDAP so passwords generated through this function
 * should be able to be migrated to OpenLDAP by using the part following the second '*', i.e.
 * the '{SSHA}....' part.
 *
 * If no salt is supplied we will generate a random one.
 *
 * @param string $instr The string to be salted and SHA1'd
 * @param string $salt Some salt to sprinkle into the string to be SHA1'd so we don't get the same PW always hashing to the same value.
 * @return string A *, the salt, a * and the SHA1 of the salted string, as in *SALT*SALTEDHASH
 */
function session_salted_sha1( $instr, $salt = "" ) {
}

// para vlidar:
/**
 * Checks what a user entered against the actual password on their account.
 * @param string $they_sent What the user entered.
 * @param string $we_have What we have in the database as their password.  Which may (or may not) be a salted MD5.
 * @return boolean Whether or not the users attempt matches what is already on file.
 */
function session_validate_password( $they_sent, $we_have ) {
}



//$oPosicion->setParametros(array('username'=>$Qusername),1);

$aWhere['_ordre'] = 'username';
$aOperador = [];

$oGesUsers = new GestorUser();
$oUsersColeccion= $oGesUsers->getUsers($aWhere,$aOperador);

echo "<pre>";
echo print_r($oUsersColeccion);
echo "</pre>";

//default:
$id_usuario='';
$usuario='';
$nom_usuario='';
$email='';
$cargo='';
$permiso = 1;

$a_cabeceras = [ 'usuario','nombre a mostrar','cargo','email',array('name'=>'accion','formatter'=>'clickFormatter') ];
$a_botones = [ ['txt'=> _("borrar"), 'click'=>"fnjs_eliminar()"],
               ['txt'=> _("cambiar password"), 'click'=>"fnjs_cmb_passwd()"],
               ['txt'=> _("modificar"), 'click'=>"fnjs_editar()"],
            ];
/*
$a_valores=array();
$i=0;
//$oCargo = new Cargo();
foreach ($oUsersColeccion as $oUser) {
	$i++;
	$id_usuario=$oUsuario->getId_usuario();
	$usuario=$oUsuario->getUsuario();
	$nom_usuario=$oUsuario->getNom_usuario();
	$email=$oUsuario->getEmail();
	/*
	$id_cargo=$oUsuario->getId_cargo();

	if (!empty($id_cargo)) {
        $oCargo->setId_cargo($id_cargo);
        $oCargo->DBCarregar();
        $cargo= $oCargo->getCargo();
	} else {
	    $cargo = '?';
	}
    */
/*
	$pagina=web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form.php?'.http_build_query(array('quien'=>'usuario','id_usuario'=>$id_usuario)));

	$a_valores[$i]['sel']="$id_usuario#";
	$a_valores[$i][1]=$usuario;
	$a_valores[$i][2]=$nom_usuario;
	$a_valores[$i][3]=$cargo;
	$a_valores[$i][5]=$email;
	$a_valores[$i][6]= array( 'ira'=>$pagina, 'valor'=>'editar');
}
if (isset($Qid_sel) && !empty($Qid_sel)) { $a_valores['select'] = $Qid_sel; }
if (isset($Qscroll_id) && !empty($Qscroll_id)) { $a_valores['scroll_id'] = $Qscroll_id; }

$oTabla = new web\Lista();
$oTabla->setId_tabla('usuario_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que'=>''));

$aQuery = [ 'nuevo' => 1, 'quien' => 'usuario' ];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form.php?'.http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form.php');
$url_form_pwd = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form_pwd.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_lista.php');

$a_campos = [
            'oPosicion' => $oPosicion,
			'oHash' => $oHash,
			'oTabla' => $oTabla,
			'permiso' => $permiso,
			'url_nuevo' => $url_nuevo,
			'url_form' => $url_form,
			'url_form_pwd' => $url_form_pwd,
			'url_eliminar' => $url_eliminar,
			'url_actualizar' => $url_actualizar,
 			];

$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('usuario_lista.html.twig',$a_campos);
*/
