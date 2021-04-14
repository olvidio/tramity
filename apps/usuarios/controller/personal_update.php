<?php
//namespace usuarios\controller;
use usuarios\model\entity\GestorPreferencia;
use usuarios\model\entity\Usuario;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Qque = (string) \filter_input(INPUT_POST, 'que');

$gesPreferencias = new GestorPreferencia();
switch ($Qque) {
	case "slickGrid":
        $Qtabla = (string) \filter_input(INPUT_POST, 'tabla');
        $QsPrefs = (string) \filter_input(INPUT_POST, 'sPrefs');
		$idioma= core\ConfigGlobal::mi_Idioma();
		$tipo = 'slickGrid_'.$Qtabla.'_'.$idioma;
		$oPref = $gesPreferencias->getMiPreferencia($tipo);
		// si no se han cambiado las columnas visibles, pongo las actuales (sino las borra).
		$aPrefs = json_decode($QsPrefs, true);
		if ($aPrefs['colVisible'] == 'noCambia') {
			$sPrefs_old = $oPref->getMiPreferencia();
			$aPrefs_old = json_decode($sPrefs_old, true);
			$aPrefs['colVisible'] = empty($aPrefs_old['colVisible'])? '' : $aPrefs_old['colVisible'];
			$QsPrefs = json_encode($aPrefs, true);
		}

		$oPref->setPreferencia($QsPrefs);
		if ($oPref->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}
		break;
	default:
		// Guardar idioma:
		$Qidioma_nou = (string) \filter_input(INPUT_POST, 'idioma_nou');
		$oPref = $gesPreferencias->getMiPreferencia('idioma');
		$oPref->setPreferencia($Qidioma_nou);
		if ($oPref->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado idioma");
			echo "\n".$oPref->getErrorTxt();
		}

		// Guardar Nombre a Mostrar, mail, cargo preferido
		$id_usuario= core\ConfigGlobal::mi_id_usuario();
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        
        $oUsuario = new Usuario(array('id_usuario' => $id_usuario));
        $oUsuario->DBCarregar();
        $oUsuario->setId_cargo($Qid_cargo);
        $oUsuario->setEmail($Qemail);
        $oUsuario->setNom_usuario($Qnom_usuario);
        if ($oUsuario->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oUsuario->getErrorTxt();
        }
        
}
