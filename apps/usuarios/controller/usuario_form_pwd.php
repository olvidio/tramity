<?php

use usuarios\model\entity as usuarios;

/**
 * Formulario para cambiar el password por parte del usuario.
 */
// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_personal = (integer)filter_input(INPUT_POST, 'personal');

$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
if (!empty($a_sel)) { //vengo de un checkbox
    $id_usuario = (integer)strtok($a_sel[0], "#");
    // el scroll id es de la página anterior, hay que guardarlo allí
    $oPosicion->addParametro('id_sel', $a_sel, 1);
    $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
    $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    $expire = '';
} else {
    $expire = $_SESSION['session_auth']['expire'];

    $oMiUsuario = new usuarios\Usuario(core\ConfigGlobal::mi_id_usuario());
    $id_usuario = $oMiUsuario->getId_usuario();
}

$txt_guardar = _("guardar datos");
$txt_ok = _("se ha cambiado el password");

$oUsuario = new usuarios\Usuario(array('id_usuario' => $id_usuario));

$id_usuario = $oUsuario->getId_usuario();
$usuario = $oUsuario->getUsuario();
$pass = $oUsuario->getPassword();
$email = $oUsuario->getEmail();

$oHash = new web\Hash();
$oHash->setcamposForm('que!password!password1!email');
$oHash->setcamposNo('que');
$a_camposHidden = array(
    'pass' => $pass,
    'id_usuario' => $id_usuario,
    'quien' => 'usuario',
    'que' => 'guardar_pwd',
);
$oHash->setArraycamposHidden($a_camposHidden);

if ($Q_personal == 1) {
    $pagina_cancel = web\Hash::link('apps/usuarios/controller/personal.php?' . http_build_query([]));
} else {
    $pagina_cancel = web\Hash::link('apps/usuarios/controller/usuario_lista.php?' . http_build_query([]));
}

$a_campos = [
    'usuario' => $usuario,
    'expire' => $expire,
    'oHash' => $oHash,
    'email' => $email,
    'txt_guardar' => $txt_guardar,
    'txt_ok' => $txt_ok,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new core\ViewTwig('usuarios/controller');
$oView->renderizar('usuario_form_pwd.html.twig', $a_campos);