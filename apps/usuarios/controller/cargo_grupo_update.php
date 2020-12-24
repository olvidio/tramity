<?php
use usuarios\model\entity\CargoGrupo;
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
            $Qid_grupo = (integer) strtok($a_sel[0],"#");
            $oGrupo = new CargoGrupo($Qid_grupo);
            if ($oGrupo->DBEliminar() === false) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oGrupo->getErrorTxt();
            }
	    }
		break;
	case "nuevo":
	case "guardar":
        $Qid_grupo = (integer) \filter_input(INPUT_POST, 'id_grupo');
        $Qid_cargo_ref = (integer) \filter_input(INPUT_POST, 'id_cargo_ref');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
	    $Qa_cargos = (array)  \filter_input(INPUT_POST, 'cargos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

		if (empty($Qdescripcion)) { echo _("debe poner un nombre"); }

		if(empty($Qid_grupo)) {
            $oGrupo = new CargoGrupo();
		} else {
            $oGrupo = new CargoGrupo(array('id_grupo' => $Qid_grupo));
		}
        $oGrupo->DBCarregar();
        $oGrupo->setId_cargo_ref($Qid_cargo_ref);
        $oGrupo->setDescripcion($Qdescripcion);
        $oGrupo->setMiembros($Qa_cargos);
		if ($oGrupo->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oGrupo->getErrorTxt();
		}
        break;
}