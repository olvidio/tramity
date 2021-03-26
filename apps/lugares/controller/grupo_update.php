<?php
use expedientes\model\Escrito;
use lugares\model\entity\Grupo;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
	case "guardar_escrito":
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qid_grupo = (integer) \filter_input(INPUT_POST, 'id_grupo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
	    $Qa_lugares = (array)  \filter_input(INPUT_POST, 'lugares', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

		if (empty($Qdescripcion)) { echo _("debe poner un nombre"); }
		
		$oEscrito = new Escrito($Qid_escrito);
		$oEscrito->DBCarregar();
		// borrar destinos existentes
		$oEscrito->setJson_prot_destino([]);
		$oEscrito->setId_grupos();
		// poner nueva seleccion
		$oEscrito->setDestinos($Qa_lugares);
		$oEscrito->setDescripcion($Qdescripcion);

		if ($oEscrito->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oEscrito->getErrorTxt();
		}
        break;
	case "eliminar":
	    //$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_grupo = (integer) strtok($a_sel[0],"#");
            $oGrupo = new Grupo($Qid_grupo);
            if ($oGrupo->DBEliminar() === false) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oGrupo->getErrorTxt();
            }
	    }
		break;
	case "nuevo":
	case "guardar":
        $Qid_grupo = (integer) \filter_input(INPUT_POST, 'id_grupo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
	    $Qa_lugares = (array)  \filter_input(INPUT_POST, 'lugares', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

		if (empty($Qdescripcion)) { echo _("debe poner un nombre"); }

		if(empty($Qid_grupo)) {
            $oGrupo = new Grupo();
		} else {
            $oGrupo = new Grupo(array('id_grupo' => $Qid_grupo));
		}
        $oGrupo->DBCarregar();
        $oGrupo->setDescripcion($Qdescripcion);
        $oGrupo->setMiembros($Qa_lugares);
		if ($oGrupo->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oGrupo->getErrorTxt();
		}
        break;
}