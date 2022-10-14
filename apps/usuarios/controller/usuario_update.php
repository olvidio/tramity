<?php

use core\MyCrypt;
use davical\model\Davical;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Usuario;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$error_txt = '';
$alert_txt = '';
switch ($Q_que) {
    case "role":
        // cambiar el role actual:
        $Q_role = (string)filter_input(INPUT_POST, 'role');
        $_SESSION['session_auth']['role_actual'] = $Q_role;
        $aPosiblesCargos = $_SESSION['session_auth']['aPosiblesCargos'];
        $id_cargo = array_search($Q_role, $aPosiblesCargos);
        // en el caso se secretaria no tiene id:
        if ($id_cargo !== FALSE) {
            $_SESSION['session_auth']['id_cargo'] = $id_cargo;
            // Oficina actual:
            $oUsuario = new Cargo($id_cargo);
            $id_oficina_actual = $oUsuario->getId_oficina();
            $bdirector = $oUsuario->getDirector();
            $bsacd = $oUsuario->getSacd();
            $_SESSION['session_auth']['mi_id_oficina'] = $id_oficina_actual;
            $_SESSION['session_auth']['usuario_dtor'] = $bdirector;
            $_SESSION['session_auth']['usuario_sacd'] = $bsacd;
            // Para el Davical:
            // nombre normalizado del usuario y oficina:
            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $username_davical = $oDavical->getUsernameDavical($id_cargo);
            $_SESSION['session_auth']['username_davical'] = $username_davical;
        }
        $alert_txt .= sprintf(_("role cambiado a %s"), $Q_role);
        break;
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_usuario = (integer)strtok($a_sel[0], "#");
            $oUsuario = new Usuario($Q_id_usuario);
            if ($oUsuario->DBEliminar() === FALSE) {
                $error_txt = _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oUsuario->getErrorTxt();
            }
        }
        break;
    case "buscar":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');

        $oUsuarios = new GestorUsuario();
        $oUser = $oUsuarios->getUsuarios(array('usuario' => $Q_usuario));
        $oUsuario = $oUser[0];
        break;
    case "guardar_pwd":
        $Q_id_usuario = (integer)filter_input(INPUT_POST, 'id_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');
        $Q_pass = (string)filter_input(INPUT_POST, 'pass');

        $oUsuario = new Usuario(array('id_usuario' => $Q_id_usuario));
        $oUsuario->DBCarregar();
        if (!empty($Q_password)) {
            $oCrypt = new MyCrypt();
            $my_passwd = $oCrypt->encode($Q_password);
            $oUsuario->setPassword($my_passwd);
        } else {
            $oUsuario->setPassword($Q_pass);
        }
        if ($oUsuario->DBGuardar() === FALSE) {
            $error_txt = _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oUsuario->getErrorTxt();
        }
        break;
    case "guardar":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');

        if (empty($Q_usuario)) {
            $error_txt .= _("debe poner un nombre");
        }
        $Q_id_usuario = (integer)filter_input(INPUT_POST, 'id_usuario');
        $Q_id_cargo_preferido = (integer)filter_input(INPUT_POST, 'id_cargo_preferido');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');
        $Q_pass = (string)filter_input(INPUT_POST, 'pass');

        $oUsuario = new Usuario(array('id_usuario' => $Q_id_usuario));
        $oUsuario->DBCarregar();
        $oUsuario->setUsuario($Q_usuario);
        $oUsuario->setId_cargo_preferido($Q_id_cargo_preferido);
        $oUsuario->setEmail($Q_email);
        $oUsuario->setNom_usuario($Q_nom_usuario);
        if (!empty($Q_password)) {
            $oCrypt = new MyCrypt();
            $my_passwd = $oCrypt->encode($Q_password);
            $oUsuario->setPassword($my_passwd);
        } else {
            $oUsuario->setPassword($Q_pass);
        }
        if ($oUsuario->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oUsuario->getErrorTxt();
        }
        break;
    case "nuevo":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Q_id_cargo_preferido = (integer)filter_input(INPUT_POST, 'id_cargo_preferido');
        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');

        if ($Q_usuario) {
            $oUsuario = new Usuario();
            $oUsuario->setUsuario($Q_usuario);
            if (!empty($Q_password)) {
                $oCrypt = new MyCrypt();
                $my_passwd = $oCrypt->encode($Q_password);
                $oUsuario->setPassword($my_passwd);
            } else {
                $alert_txt .= _("debe añadir un password");
            }
            $oUsuario->setEmail($Q_email);
            $oUsuario->setId_cargo_preferido($Q_id_cargo_preferido);
            $oUsuario->setNom_usuario($Q_nom_usuario);
            if ($oUsuario->DBGuardar() === FALSE) {
                $error_txt .= _("hay un error, no se ha guardado");
                $error_txt .= "\n" . $oUsuario->getErrorTxt();
            }
        } else {
            $error_txt .= _("debe poner un nombre de usuario");
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (!empty($error_txt)) {
    $jsondata['success'] = FALSE;
    $jsondata['mensaje'] = $error_txt;
} else {
    $jsondata['success'] = TRUE;
    if (!empty($alert_txt)) {
        $jsondata['alert'] = $alert_txt;
    }
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();