<?php
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorLocale;
use usuarios\model\entity\GestorPreferencia;
use usuarios\model\entity\Usuario;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
//	require_once ("classes/personas/ext_web_preferencias_gestor.class");

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$oGesPref = new GestorPreferencia();

$id_usuario= core\ConfigGlobal::mi_id_usuario();

// ----------- Color -------------------
$aPref = $oGesPref->getPreferencias(array('id_usuario'=>$id_usuario,'tipo'=>'color'));
if (is_array($aPref) && count($aPref) > 0) {
	$oPreferencia = $aPref[0];
	$color = $oPreferencia->getPreferencia();
} else {
	$color='';
}

// ----------- Idioma -------------------
//Tengo la variable $idioma en ConfigGlobal, pero vuelvo a consultarla 
$aPref = $oGesPref->getPreferencias(array('id_usuario'=>$id_usuario,'tipo'=>'idioma'));
if (is_array($aPref) && count($aPref) > 0) {
	$oPreferencia = $aPref[0];
	$preferencia = $oPreferencia->getPreferencia();
	list($idioma) = preg_split('/#/',$preferencia);
} else {
	$idioma='';
}
$oGesLocales = new GestorLocale();
$oDesplLocales = $oGesLocales->getListaLocales();
$oDesplLocales->setNombre('idioma_nou');
$oDesplLocales->setOpcion_sel($idioma);

// ----------- nom usuario y mail -------------------
$oUsuario = new Usuario(array('id_usuario'=>$id_usuario));

$usuario=$oUsuario->getUsuario();
$nom_usuario=$oUsuario->getNom_usuario();
$email=$oUsuario->getEmail();
$id_cargo_preferido=$oUsuario->getId_cargo_preferido();

$oGCargos = new GestorCargo();
$oDesplCargos= $oGCargos->getDesplCargosUsuario($id_usuario);
$oDesplCargos->setNombre('id_cargo_preferido');
$oDesplCargos->setOpcion_sel($id_cargo_preferido);

$cambio_password = web\Hash::link('apps/usuarios/controller/usuario_form_pwd.php?'.http_build_query(['personal' => 1]));

$oHash = new web\Hash();
$oHash->setcamposForm('inicio!oficina!estilo_color!tipo_menu!tipo_tabla!ordenApellidos!idioma_nou');

$a_campos = [
			'oHash' => $oHash,
			'oDesplLocales' => $oDesplLocales,
			'cambio_password' => $cambio_password,
            'usuario' => $usuario,
            'nom_usuario' => $nom_usuario,
            'oDesplCargos' => $oDesplCargos,
            'email' => $email,
 			];

$oView = new core\ViewTwig('usuarios/controller');
echo $oView->render('personal.html.twig',$a_campos);