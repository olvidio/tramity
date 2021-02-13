<?php
use usuarios\model\entity\Cargo;
use usuarios\model\entity\Oficina;
use davical\model\Davical;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
    case "suplente":
        $txt_err = '';
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qid_suplente = (integer) \filter_input(INPUT_POST, 'id_suplente');
        $oCargo = new Cargo (array('id_cargo' => $Qid_cargo));
        $oCargo->DBCarregar();
        $oCargo->setId_suplente($Qid_suplente);
        if ($oCargo->DBGuardar() === FALSE ) {
            $txt_err .= _("Hay un error al guardar");
            $txt_err .= "<br>";
        }
        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
	case "eliminar":
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
	        $Qid_cargo = (integer) strtok($a_sel[0],"#");
	        if ($Qid_cargo > Cargo::CARGO_REUNION) { // Al dia de hoy, es el número mayor (7)
                $oCargo = new Cargo($Qid_cargo);
                if ($oCargo->DBEliminar() === false) {
                    echo _("hay un error, no se ha eliminado");
                    echo "\n".$oCargo->getErrorTxt();
                }
	        } else {
	            echo _("No se puede eliminar un cargo tipo.");
	        }
	    }
	    break;
	case "nuevo":
	case "guardar":
		$Qcargo = (string) \filter_input(INPUT_POST, 'cargo');

		if (empty($Qcargo)) { echo _("debe poner un nombre"); }
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
        $Qid_ambito = (integer) \filter_input(INPUT_POST, 'id_ambito');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qdirector = (bool) \filter_input(INPUT_POST, 'director');
        $Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qid_suplente = (integer) \filter_input(INPUT_POST, 'id_suplente');
        
        if (empty($Qid_cargo)) {
            $oCargo = new Cargo();
        } else {
            $oCargo = new Cargo (array('id_cargo' => $Qid_cargo));
            $oCargo->DBCarregar();
        }
        $oCargo->setCargo($Qcargo);
        $oCargo->setDescripcion($Qdescripcion);
        $oCargo->setId_ambito($Qid_ambito);
        $oCargo->setId_oficina($Qid_oficina);
        $oCargo->setDirector($Qdirector);
        $oCargo->setId_usuario($Qid_usuario);
        $oCargo->setId_suplente($Qid_suplente);
		if ($oCargo->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oCargo->getErrorTxt();
		}
		// Crear el usuario en davical. Hace falta el nombre de la oficina:
		$oOficina = new Oficina($Qid_oficina);
		$oficina = $oOficina->getSigla();
		$aDatosCargo = [ 'cargo' => $Qcargo,
                        'descripcion' => $Qdescripcion,
                        'oficina' => $oficina,
		          ];
		$oDavical = new Davical();
		$oDavical->crearUser($aDatosCargo);
		
        break;
}