<?php
use usuarios\model\entity\Oficina;
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
		    $Qid_oficina = (integer) strtok($a_sel[0],"#");
		    // el scroll id es de la página anterior, hay que guardarlo allí
		    $oPosicion->addParametro('id_sel',$a_sel,1);
		    $scroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		    $oPosicion->addParametro('scroll_id',$scroll_id,1);
		} else {
		    $Qid_oficina = (integer)  \filter_input(INPUT_POST, 'id_oficina');
		}
		
        $oOficina = new Oficina (array('id_oficina' => $Qid_oficina));
		if ($oOficina->DBEliminar() === false) {
			echo _("hay un error, no se ha eliminado");
			echo "\n".$oOficina->getErrorTxt();
		}
        break;
	case "guardar":
		$Qsigla = (string) \filter_input(INPUT_POST, 'sigla');

		if (empty($Qsigla)) { echo _("debe poner un nombre"); }
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        
        $oOficina = new Oficina (array('id_oficina' => $Qid_oficina));
        $oOficina->DBCarregar();
        $oOficina->setSigla($Qsigla);
        $oOficina->setOrden($Qorden);
		if ($oOficina->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oOficina->getErrorTxt();
		}
        break;
	case "nuevo":
        $Qsigla = (string) \filter_input(INPUT_POST, 'sigla');
        $Qorden = (string) \filter_input(INPUT_POST, 'orden');
        
        $oOficina = new Oficina();
        $oOficina->setSigla($Qsigla);
        $oOficina->setOrden($Qorden);
        if ($oOficina->DBGuardar() === false) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oOficina->getErrorTxt();
        }
		break;
}