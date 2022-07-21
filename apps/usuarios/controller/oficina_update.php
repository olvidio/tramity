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

$error_txt = '';
switch($Qque) {
	case "eliminar":
		$a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		if (!empty($a_sel)) { //vengo de un checkbox
		    $Qid_oficina = (integer) strtok($a_sel[0],"#");
		    // el scroll id es de la página anterior, hay que guardarlo allí
		    $oPosicion->addParametro('id_sel',$a_sel,1);
		    $scroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		    $oPosicion->addParametro('scroll_id',$scroll_id,1);
		} else {
		    $Qid_oficina = (integer)  \filter_input(INPUT_POST, 'id_oficina');
		}
		
        $oOficina = new Oficina (array('id_oficina' => $Qid_oficina));
		// hay que coger la información antes de borrar:
		if ($oOficina->DBEliminar() === false) {
		    $error_txt .= _("hay un error, no se ha eliminado");
		    $error_txt .= "\n".$oOficina->getErrorTxt();
		} else {
		    // Eliminar el usuario en davical.
		    // Para dl, Hace falta el nombre de la oficina:
		    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
		        $oOficina = new Oficina($Qid_oficina);
		        $oficina = $oOficina->getSigla();
		    } else {
		        $oficina = ConfigGlobal::getEsquema();
		    }
		    //$oficina = 'ocs';
		    
		    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
		    $error_txt .= $oDavical->eliminarOficina($oficina);
		}
        break;
	case "guardar":
		$Qsigla = (string) \filter_input(INPUT_POST, 'sigla');

		if (empty($Qsigla)) { echo _("debe poner un nombre"); }
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        
        $oOficina = new Oficina (array('id_oficina' => $Qid_oficina));
        $oOficina->DBCarregar();
        $sigla_old = $oOficina->getSigla();
        $oOficina->setSigla($Qsigla);
        $oOficina->setOrden($Qorden);
		if ($oOficina->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n".$oOficina->getErrorTxt();
		} else {
		    if ($sigla_old != $Qsigla) {
                // Cambiar el nombre en davical.
		        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                $oDavical->cambioNombreOficina($Qsigla,$sigla_old);
		    } else {
		        // revisar que existe:
		        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                $oDavical->crearOficina($Qsigla);
		    }
		}
        break;
	case "nuevo":
        $Qsigla = (string) \filter_input(INPUT_POST, 'sigla');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        
        $oOficina = new Oficina();
        $oOficina->setSigla($Qsigla);
        $oOficina->setOrden($Qorden);
        if ($oOficina->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n".$oOficina->getErrorTxt();
		} else {
		    // Crear la oficina en davical.
		    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
		    $oDavical->crearOficina($Qsigla);
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
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();