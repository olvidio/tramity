<?php
use core\MyCrypt;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Usuario;
use usuarios\model\entity\Cargo;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
	case "eliminar":
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_usuario = (integer) strtok($a_sel[0],"#");
            $oUsuario = new Usuario($Qid_usuario);
            if ($oUsuario->DBEliminar() === FALSE) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oUsuario->getErrorTxt();
            }
	    }
		
		break;
	case "buscar":
		$Qusuario = (string) \filter_input(INPUT_POST, 'usuario');
		
		$oUsuarios = new GestorUsuario();
		$oUser=$oUsuarios->getUsuarios(array('usuario'=>$Qusuario));
		$oUsuario=$oUser[0];
		break;
	case "guardar_pwd":
		$Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$Qpassword = (string) \filter_input(INPUT_POST, 'password');
		$Qpass = (string) \filter_input(INPUT_POST, 'pass');
		
		$oUsuario = new Usuario(array('id_usuario' => $Qid_usuario));
		$oUsuario->DBCarregar();
		$oUsuario->setEmail($Qemail);
		if (!empty($Qpassword)){
			$oCrypt = new MyCrypt();
			$my_passwd=$oCrypt->encode($Qpassword);
			$oUsuario->setPassword($my_passwd);
		} else {
			$oUsuario->setPassword($Qpass);
		}
		if ($oUsuario->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oUsuario->getErrorTxt();
		}
        break;
	case "guardar":
		$Qusuario = (string) \filter_input(INPUT_POST, 'usuario');

		if (empty($Qusuario)) { echo _("debe poner un nombre"); }
        $Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qpassword = (string) \filter_input(INPUT_POST, 'password');
        $Qpass = (string) \filter_input(INPUT_POST, 'pass');
        
        $oUsuario = new Usuario(array('id_usuario' => $Qid_usuario));
        $oUsuario->DBCarregar();
        $oUsuario->setUsuario($Qusuario);
        $oUsuario->setId_cargo($Qid_cargo);
        $oUsuario->setEmail($Qemail);
        $oUsuario->setNom_usuario($Qnom_usuario);
        if (!empty($Qpassword)){
            $oCrypt = new MyCrypt();
            $my_passwd=$oCrypt->encode($Qpassword);
            $oUsuario->setPassword($my_passwd);
        } else {
            $oUsuario->setPassword($Qpass);
        }
		if ($oUsuario->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oUsuario->getErrorTxt();
		}
        break;
	case "nuevo":
        $Qusuario = (string) \filter_input(INPUT_POST, 'usuario');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qpassword = (string) \filter_input(INPUT_POST, 'password');
        
        if ($Qusuario && $Qpassword) {
            $oUsuario = new Usuario();
            $oUsuario->setUsuario($Qusuario);
            if (!empty($Qpassword)){
                $oCrypt = new MyCrypt();
                $my_passwd=$oCrypt->encode($Qpassword);
                $oUsuario->setPassword($my_passwd);
            }
            $oUsuario->setEmail($Qemail);
            $oUsuario->setId_cargo($Qid_cargo);
            $oUsuario->setNom_usuario($Qnom_usuario);
            if ($oUsuario->DBGuardar() === FALSE) {
                echo _("hay un error, no se ha guardado");
                echo "\n".$oUsuario->getErrorTxt();
            }
        } else { echo _("debe poner un nombre y el password"); }
		break;
	default:
	    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
	    exit ($err_switch);
}