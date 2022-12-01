<?php

use core\MyCrypt;
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

switch ($Q_que) {
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_usuario = (integer)strtok($a_sel[0], "#");
            $oUsuario = new Usuario($Q_id_usuario);
            if ($oUsuario->DBEliminar() === FALSE) {
                echo _("hay un error, no se ha eliminado");
                echo "\n" . $oUsuario->getErrorTxt();
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
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Q_password = (string)filter_input(INPUT_POST, 'password');
        $Q_pass = (string)filter_input(INPUT_POST, 'pass');

        $oUsuario = new Usuario($Q_id_usuario);
        if ($oUsuario->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el usuario en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oUsuario->setEmail($Q_email);
        if (!empty($Q_password)) {
            $oCrypt = new MyCrypt();
            $my_passwd = $oCrypt->encode($Q_password);
            $oUsuario->setPassword($my_passwd);
        } else {
            $oUsuario->setPassword($Q_pass);
        }
        if ($oUsuario->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n" . $oUsuario->getErrorTxt();
        }
        break;
    case "guardar":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');

        if (empty($Q_usuario)) {
            echo _("debe poner un nombre");
        }
        $Q_id_usuario = (integer)filter_input(INPUT_POST, 'id_usuario');
        $Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');
        $Q_pass = (string)filter_input(INPUT_POST, 'pass');

        $oUsuario = new Usuario($Q_id_usuario);
        if ($oUsuario->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el usuario en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oUsuario->setUsuario($Q_usuario);
        $oUsuario->setId_cargo_preferido($Q_id_cargo);
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
            echo _("hay un error, no se ha guardado");
            echo "\n" . $oUsuario->getErrorTxt();
        }
        break;
    case "nuevo":
        $Q_usuario = (string)filter_input(INPUT_POST, 'usuario');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');
        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_password = (string)filter_input(INPUT_POST, 'password');

        if ($Q_usuario && $Q_password) {
            $oUsuario = new Usuario();
            $oUsuario->setUsuario($Q_usuario);
            if (!empty($Q_password)) {
                $oCrypt = new MyCrypt();
                $my_passwd = $oCrypt->encode($Q_password);
                $oUsuario->setPassword($my_passwd);
            }
            $oUsuario->setEmail($Q_email);
            $oUsuario->setId_cargo_preferido($Q_id_cargo);
            $oUsuario->setNom_usuario($Q_nom_usuario);
            if ($oUsuario->DBGuardar() === FALSE) {
                echo _("hay un error, no se ha guardado");
                echo "\n" . $oUsuario->getErrorTxt();
            }
        } else {
            echo _("debe poner un nombre y el password");
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}