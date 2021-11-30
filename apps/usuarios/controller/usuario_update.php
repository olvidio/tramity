<?php
use core\MyCrypt;
use davical\model\Davical;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Oficina;
use usuarios\model\entity\Usuario;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

$error_txt = '';
$alert_txt = '';
switch($Qque) {
    case "role":
        // cambiar el role actual:
		$Qrole = (string) \filter_input(INPUT_POST, 'role');
		$_SESSION['session_auth']['role_actual'] = $Qrole;
		$aPosiblesCargos = $_SESSION['session_auth']['aPosiblesCargos'];
		$id_cargo = array_search($Qrole, $aPosiblesCargos);
		// en el caso se secretaria no tiene id:
		if ($id_cargo !== FALSE) {
            $_SESSION['session_auth']['id_cargo'] = $id_cargo;
            // Oficina actual:
            $oUsuario = new Cargo($id_cargo);
            $id_oficina_actual = $oUsuario->getId_oficina();
            $_SESSION['session_auth']['mi_id_oficina'] = $id_oficina_actual;
            // Para el Davical:
            // nombre normalizado del usuario y oficina:
            $oCargo = new Cargo($id_cargo);
            $cargo_role = $oCargo->getCargo();
            $oOficina = new Oficina($id_oficina_actual);
            $oficina_role = $oOficina->getSigla();
            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $username_davical = $oDavical->getNombreUsuario($oficina_role, $cargo_role);
            $_SESSION['session_auth']['username_davical'] = $username_davical;
		}
		$alert_txt .= sprintf(_("role cambiado a %s"),$Qrole);
        break;
	case "eliminar":
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_usuario = (integer) strtok($a_sel[0],"#");
            $oUsuario = new Usuario($Qid_usuario);
            if ($oUsuario->DBEliminar() === FALSE) {
                $error_txt = _("hay un error, no se ha eliminado");
                $error_txt .= "\n".$oUsuario->getErrorTxt();
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
		$Qpassword = (string) \filter_input(INPUT_POST, 'password');
		$Qpass = (string) \filter_input(INPUT_POST, 'pass');
		
		$oUsuario = new Usuario(array('id_usuario' => $Qid_usuario));
		$oUsuario->DBCarregar();
		if (!empty($Qpassword)){
			$oCrypt = new MyCrypt();
			$my_passwd=$oCrypt->encode($Qpassword);
			$oUsuario->setPassword($my_passwd);
		} else {
			$oUsuario->setPassword($Qpass);
		}
		if ($oUsuario->DBGuardar() === FALSE) {
            $error_txt = _("hay un error, no se ha guardado");
            $error_txt .= "\n".$oUsuario->getErrorTxt();
		}
		break;
	case "guardar":
		$Qusuario = (string) \filter_input(INPUT_POST, 'usuario');

		if (empty($Qusuario)) { $error_txt .= _("debe poner un nombre"); }
        $Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qid_cargo_preferido = (integer) \filter_input(INPUT_POST, 'id_cargo_preferido');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qpassword = (string) \filter_input(INPUT_POST, 'password');
        $Qpass = (string) \filter_input(INPUT_POST, 'pass');
        
        $oUsuario = new Usuario(array('id_usuario' => $Qid_usuario));
        $oUsuario->DBCarregar();
        $oUsuario->setUsuario($Qusuario);
        $oUsuario->setId_cargo_preferido($Qid_cargo_preferido);
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
			$error_txt .= _("hay un error, no se ha guardado");
			$error_txt .= "\n".$oUsuario->getErrorTxt();
		}
        break;
	case "nuevo":
        $Qusuario = (string) \filter_input(INPUT_POST, 'usuario');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Qid_cargo_preferido = (integer) \filter_input(INPUT_POST, 'id_cargo_preferido');
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qpassword = (string) \filter_input(INPUT_POST, 'password');
        
        if ($Qusuario) {
            $oUsuario = new Usuario();
            $oUsuario->setUsuario($Qusuario);
            if (!empty($Qpassword)){
                $oCrypt = new MyCrypt();
                $my_passwd=$oCrypt->encode($Qpassword);
                $oUsuario->setPassword($my_passwd);
            } else {
                 $alert_txt .= _("debe añadir un password");
            }
            $oUsuario->setEmail($Qemail);
            $oUsuario->setId_cargo_preferido($Qid_cargo_preferido);
            $oUsuario->setNom_usuario($Qnom_usuario);
            if ($oUsuario->DBGuardar() === FALSE) {
                $error_txt .= _("hay un error, no se ha guardado");
                $error_txt .= "\n".$oUsuario->getErrorTxt();
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