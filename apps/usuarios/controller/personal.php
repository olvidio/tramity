<?php
use usuarios\model\entity\GestorLocale;
use usuarios\model\entity\GestorPreferencia;
use usuarios\model\entity\Preferencia;
use web\Desplegable;
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

$cambio_password=web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form_pwd.php');

$oHash = new web\Hash();
$oHash->setcamposForm('inicio!oficina!estilo_color!tipo_menu!tipo_tabla!ordenApellidos!idioma_nou');

$a_campos = [
			'oHash' => $oHash,
			'oDesplLocales' => $oDesplLocales,
			'cambio_password' => $cambio_password,
 			];

$oView = new core\ViewTwig('usuarios/controller');
echo $oView->render('personal.html.twig',$a_campos);