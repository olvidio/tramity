<?php

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\repositories\UsuarioRepository;
use usuarios\model\entity\GestorLocale;
use usuarios\model\entity\GestorPreferencia;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
//	require_once ("classes/personas/ext_web_preferencias_gestor.class");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$oGesPref = new GestorPreferencia();

$id_usuario = ConfigGlobal::mi_id_usuario();
$role_actual = $_SESSION['session_auth']['role_actual'];
$is_admin = FALSE;
if ($role_actual === 'admin') {
    $is_admin = TRUE;
}

// ----------- Color -------------------
$aPref = $oGesPref->getPreferencias(array('id_usuario' => $id_usuario, 'tipo' => 'color'));
if (is_array($aPref) && !empty($aPref)) {
    $oPreferencia = $aPref[0];
    $color = $oPreferencia->getPreferencia();
} else {
    $color = '';
}

// ----------- Idioma -------------------
//Tengo la variable $idioma en ConfigGlobal, pero vuelvo a consultarla 
$aPref = $oGesPref->getPreferencias(array('id_usuario' => $id_usuario, 'tipo' => 'idioma'));
if (is_array($aPref) && !empty($aPref)) {
    $oPreferencia = $aPref[0];
    $idioma = $oPreferencia->getPreferencia();
} else {
    $idioma = '';
}
$oGesLocales = new GestorLocale();
$oDesplLocales = $oGesLocales->getListaLocales();
$oDesplLocales->setNombre('idioma_nou');
$oDesplLocales->setOpcion_sel($idioma);

// ----------- nom usuario y mail -------------------
$UsuarioRepository = new UsuarioRepository();
$oUsuario = $UsuarioRepository->findById($id_usuario);

$usuario = $oUsuario->getUsuario();
$nom_usuario = $oUsuario->getNom_usuario();
$email = $oUsuario->getEmail();
$id_cargo_preferido = $oUsuario->getId_cargo_preferido();

if ($is_admin) {
    $oDesplCargos = '';
} else {
    $CargoRepository = new CargoRepository();
    $oDesplCargos = $CargoRepository->getDesplCargosUsuario($id_usuario);
    $oDesplCargos->setNombre('id_cargo_preferido');
    $oDesplCargos->setOpcion_sel($id_cargo_preferido);
}

$cambio_password = Hash::link('apps/usuarios/controller/usuario_form_pwd.php?' . http_build_query(['personal' => 1]));

$oHash = new Hash();
$oHash->setcamposForm('inicio!oficina!estilo_color!tipo_menu!tipo_tabla!ordenApellidos!idioma_nou');

$a_campos = [
    'oHash' => $oHash,
    'oDesplLocales' => $oDesplLocales,
    'cambio_password' => $cambio_password,
    'usuario' => $usuario,
    'nom_usuario' => $nom_usuario,
    'oDesplCargos' => $oDesplCargos,
    'email' => $email,
    'is_admin' => $is_admin,
];

$oView = new ViewTwig('usuarios/controller');
$oView->renderizar('personal.html.twig', $a_campos);