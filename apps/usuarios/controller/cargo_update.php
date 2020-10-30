<?php
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
	        $Qid_cargo = (integer) strtok($a_sel[0],"#");
	        $oCargo = new Cargo($Qid_cargo);
	        if ($oCargo->DBEliminar() === false) {
	            echo _("hay un error, no se ha eliminado");
	            echo "\n".$oCargo->getErrorTxt();
	        }
	    }
	    break;
	case "guardar":
		$Qcargo = (string) \filter_input(INPUT_POST, 'cargo');

		if (empty($Qcargo)) { echo _("debe poner un nombre"); }
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
        $Qid_ambito = (integer) \filter_input(INPUT_POST, 'id_ambito');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qdirector = (bool) \filter_input(INPUT_POST, 'director');
        
        $oCargo = new Cargo (array('id_cargo' => $Qid_cargo));
        $oCargo->DBCarregar();
        $oCargo->setCargo($Qcargo);
        $oCargo->setDescripcion($Qdescripcion);
        $oCargo->setId_ambito($Qid_ambito);
        $oCargo->setId_oficina($Qid_oficina);
        $oCargo->setDirector($Qdirector);
		if ($oCargo->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oCargo->getErrorTxt();
		}
        break;
	case "nuevo":
        $Qcargo = (string) \filter_input(INPUT_POST, 'cargo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
        $Qid_ambito = (integer) \filter_input(INPUT_POST, 'id_ambito');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qdirector = (bool) \filter_input(INPUT_POST, 'director');
        
        $oCargo = new Cargo();
        $oCargo->setCargo($Qcargo);
        $oCargo->setDescripcion($Qdescripcion);
        $oCargo->setId_ambito($Qid_ambito);
        $oCargo->setId_oficina($Qid_oficina);
        $oCargo->setDirector($Qdirector);
        if ($oCargo->DBGuardar() === false) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oCargo->getErrorTxt();
        }
		break;
}