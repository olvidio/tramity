<?php
//namespace usuarios\controller;
use usuarios\model\entity\GestorPreferencia;
use usuarios\model\entity\Preferencia;
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
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}
		break;
	default:
        $Qoficina = (string) \filter_input(INPUT_POST, 'oficina');
        $Qinicio = (string) \filter_input(INPUT_POST, 'inicio');

		$Qoficina = empty($Qoficina)? 'exterior' : $Qoficina;
		$Qinicio = empty($Qinicio)? 'exterior' : $Qinicio;
		// Guardar página de inicio:
		$inicio=$Qinicio."#".$Qoficina;
		$oPref = $gesPreferencias->getMiPreferencia('inicio');
		$oPref->setPreferencia($inicio);
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}

		// Guardar estilo:
		$Qestilo_color = (string) \filter_input(INPUT_POST, 'estilo_color');
		$Qtipo_menu = (string) \filter_input(INPUT_POST, 'tipo_menu');
		$estilo=$Qestilo_color."#".$Qtipo_menu;
		$oPref = $gesPreferencias->getMiPreferencia('estilo');
		$oPref->setPreferencia($estilo);
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}

		// Guardar presentacion tablas:
		$Qtipo_tabla = (string) \filter_input(INPUT_POST, 'tipo_tabla');
		$oPref = $gesPreferencias->getMiPreferencia('tabla_presentacion');
		$oPref->setPreferencia($Qtipo_tabla);
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}

		// Guardar presentacion nombre Apellidos:
		$QordenApellidos = (string) \filter_input(INPUT_POST, 'ordenApellidos');
		$oPref = $gesPreferencias->getMiPreferencia('ordenApellidos');
		$oPref->setPreferencia($QordenApellidos);
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}

		// Guardar idioma:
		$Qidioma_nou = (string) \filter_input(INPUT_POST, 'idioma_nou');
		$oPref = $gesPreferencias->getMiPreferencia('idioma');
		$oPref->setPreferencia($Qidioma_nou);
		if ($oPref->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPref->getErrorTxt();
		}

		// volver a la página de configuración
		$location=web\Hash::link(core\ConfigGlobal::getWeb().'/index.php?'.http_build_query(array('PHPSESSID'=>session_id())));
		echo "<body onload=\"$location\";></body>";
}
