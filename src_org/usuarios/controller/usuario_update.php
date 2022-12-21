<?php

use core\MyCrypt;
use davical\model\Davical;
use usuarios\domain\entity\Usuario;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\repositories\UsuarioRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
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
        $id_cargo = array_search($Q_role, $aPosiblesCargos, true);
        // en el caso se secretaria no tiene id:
        if ($id_cargo !== FALSE) {
            $_SESSION['session_auth']['id_cargo'] = $id_cargo;
            // Oficina actual:
            $CargoRepository = new CargoRepository();
            $oUsuario = $CargoRepository->findById($id_cargo);
            $id_oficina_actual = $oUsuario->getId_oficina();
            $bdirector = $oUsuario->isDirector();
            $bsacd = $oUsuario->isSacd();
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
            $UsuarioRepository = new UsuarioRepository();
            $oUsuario = $UsuarioRepository->findById($Q_id_usuario);
            if ($UsuarioRepository->Eliminar($oUsuario) === FALSE) {
                $error_txt = _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $UsuarioRepository->getErrorTxt();
            }
        }
        break;
    case "buscar":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');

        $UsuarioRepository = new UsuarioRepository();
        $oUser = $UsuarioRepository->getUsuarios(array('usuario' => $Q_usuario));
        $oUsuario = $oUser[0];
        break;
    case "guardar_pwd":
        $Q_id_usuario = (integer)filter_input(INPUT_POST, 'id_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');
        $Q_pass = (string)filter_input(INPUT_POST, 'pass');

        $UsuarioRepository = new UsuarioRepository();
        $oUsuario = $UsuarioRepository->findById($Q_id_usuario);
        if (!empty($Q_password)) {
            $oCrypt = new MyCrypt();
            $my_passwd = $oCrypt->encode($Q_password);
            $oUsuario->setPassword($my_passwd);
        } else {
            $oUsuario->setPassword($Q_pass);
        }
        if ($UsuarioRepository->Guardar($oUsuario) === FALSE) {
            $error_txt = _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $UsuarioRepository->getErrorTxt();
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

        $UsuarioRepository = new UsuarioRepository();
        $oUsuario = $UsuarioRepository->findById($Q_id_usuario);
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
        if ($UsuarioRepository->Guardar($oUsuario) === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $UsuarioRepository->getErrorTxt();
        }
        break;
    case "nuevo":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Q_id_cargo_preferido = (integer)filter_input(INPUT_POST, 'id_cargo_preferido');
        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');

        $UsuarioRepository = new UsuarioRepository();
        if ($Q_usuario) {
            $id_usuario = $UsuarioRepository->getNewId_usuario();
            $oUsuario = new Usuario();
            $oUsuario->setId_usuario($id_usuario);
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
            if ($UsuarioRepository->Guardar($oUsuario) === FALSE) {
                $error_txt .= _("hay un error, no se ha guardado");
                $error_txt .= "\n" . $UsuarioRepository->getErrorTxt();
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
echo json_encode($jsondata, JSON_THROW_ON_ERROR);
exit();