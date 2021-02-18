<?php
use core\ConfigGlobal;
use etiquetas\model\entity\Etiqueta;
use function core\is_true;
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
	    //$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_etiqueta = (integer) strtok($a_sel[0],"#");
            $oEtiqueta = new Etiqueta($Qid_etiqueta);
            if ($oEtiqueta->DBEliminar() === false) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oEtiqueta->getErrorTxt();
            }
	    }
		break;
	case "nuevo":
	case "guardar":
        $Qid_etiqueta = (integer) \filter_input(INPUT_POST, 'id_etiqueta');
        $Qnom_etiqueta = (string) \filter_input(INPUT_POST, 'nom_etiqueta');
        $Qoficina = (string) \filter_input(INPUT_POST, 'oficina');
        
        if (is_true($Qoficina)) {
            $oficina = 't';
            // buscar el id de la oficina:
            $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
            $id_cargo = $oCargo->getId_oficina();
        } else {
            $oficina = 'f';
            $id_cargo = ConfigGlobal::role_id_cargo();
        }

		if (empty($Qnom_etiqueta)) { echo _("debe poner un nombre"); }

		if(empty($Qid_etiqueta)) {
            $oEtiqueta = new Etiqueta();
		} else {
            $oEtiqueta = new Etiqueta(array('id_etiqueta' => $Qid_etiqueta));
		}
        $oEtiqueta->DBCarregar();
        $oEtiqueta->setNom_etiqueta($Qnom_etiqueta);
        $oEtiqueta->setOficina($oficina);
        $oEtiqueta->setId_cargo($id_cargo);
		if ($oEtiqueta->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oEtiqueta->getErrorTxt();
		}
        break;
}