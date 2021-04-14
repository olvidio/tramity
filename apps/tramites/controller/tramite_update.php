<?php
use tramites\model\entity\Tramite;
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
            $Qid_tramite = (integer) strtok($a_sel[0],"#");
            $oTramite = new Tramite($Qid_tramite);
            if ($oTramite->DBEliminar() === FALSE) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oTramite->getErrorTxt();
            }
        }
        break;
	case "guardar":
		$Qtramite = (string) \filter_input(INPUT_POST, 'tramite');

		if (empty($Qtramite)) { echo _("debe poner un nombre"); }
        $Qid_tramite = (integer) \filter_input(INPUT_POST, 'id_tramite');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        $Qbreve = (string) \filter_input(INPUT_POST, 'breve');
        
        $oTramite = new Tramite (array('id_tramite' => $Qid_tramite));
        $oTramite->DBCarregar();
        $oTramite->setTramite($Qtramite);
        $oTramite->setOrden($Qorden);
        $oTramite->setBreve($Qbreve);
		if ($oTramite->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oTramite->getErrorTxt();
		}
        break;
	case "nuevo":
        $Qtramite = (string) \filter_input(INPUT_POST, 'tramite');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        $Qbreve = (string) \filter_input(INPUT_POST, 'breve');
        
        $oTramite = new Tramite();
        $oTramite->setTramite($Qtramite);
        $oTramite->setOrden($Qorden);
        $oTramite->setBreve($Qbreve);
        if ($oTramite->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oTramite->getErrorTxt();
        }
		break;
}