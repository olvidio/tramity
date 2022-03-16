<?php
use core\ConfigGlobal;
use davical\model\Davical;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\Oficina;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

$txt_err = '';
switch($Qque) {
    case "suplente":
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qid_suplente = (integer) \filter_input(INPUT_POST, 'id_suplente');
        $oCargo = new Cargo (array('id_cargo' => $Qid_cargo));
        $oCargo->DBCarregar();
        $oCargo->setId_suplente($Qid_suplente);
        if ($oCargo->DBGuardar() === FALSE ) {
            $txt_err .= _("Hay un error al guardar");
            $txt_err .= "<br>";
        }
        break;
	case "eliminar":
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
	        $Qid_cargo = (integer) strtok($a_sel[0],"#");
	        if ($Qid_cargo > Cargo::CARGO_REUNION) { // A dia de hoy, es el número mayor (7)
                $oCargo = new Cargo($Qid_cargo);
                // hay que coger la información antes de borrar:
                $id_oficina = $oCargo->getId_oficina();
                $cargo = $oCargo->getCargo();
                if ($oCargo->DBEliminar() === false) {
                    $txt_err .= _("hay un error, no se ha eliminado");
                    $txt_err .= "\n".$oCargo->getErrorTxt();
                } else {
                    // Eliminar el usuario en davical.
                    $aDatosCargo = [ 'cargo' => $cargo,
                                    'oficina' => $id_oficina,
                              ];
                    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                    $txt_err .= $oDavical->eliminarUser($aDatosCargo);
                }
	        } else {
	            $txt_err .= _("No se puede eliminar un cargo tipo.");
	        }
	    }
	    break;
	case "nuevo":
	case "guardar":
		$Qcargo = (string) \filter_input(INPUT_POST, 'cargo');

		if (empty($Qcargo)) { $txt_err .= _("debe poner un nombre"); }
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qdescripcion = (string) \filter_input(INPUT_POST, 'descripcion');
        $Qid_ambito = (integer) \filter_input(INPUT_POST, 'id_ambito');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qdirector = (bool) \filter_input(INPUT_POST, 'director');
        $Qsacd = (bool) \filter_input(INPUT_POST, 'sacd');
        $Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qid_suplente = (integer) \filter_input(INPUT_POST, 'id_suplente');
        
		if ($_SESSION['oConfig']->getAmbito() != Cargo::AMBITO_DL) {
            $Qid_oficina = Cargo::OFICINA_ESQUEMA; 
		}
		
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
        $oCargo->setSacd($Qsacd);
        $oCargo->setId_usuario($Qid_usuario);
        $oCargo->setId_suplente($Qid_suplente);
		if ($oCargo->DBGuardar() === false) {
			$txt_err .= _("hay un error, no se ha guardado");
			$txt_err .= "\n".$oCargo->getErrorTxt();
		}
		// Crear el usuario en davical. 
        $aDatosCargo = [ 'cargo' => $Qcargo,
                        'descripcion' => $Qdescripcion,
                        'oficina' => $Qid_oficina,
                  ];
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $txt_err .= $oDavical->crearUser($aDatosCargo);
        break;
	default:
	    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
	    exit ($err_switch);
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