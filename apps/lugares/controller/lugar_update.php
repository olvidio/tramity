<?php
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;
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
            $Qid_lugar = (integer) strtok($a_sel[0],"#");
            $oLugar = new Lugar($Qid_lugar);
            if ($oLugar->DBEliminar() === false) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oLugar->getErrorTxt();
            }
	    }
		
		break;
	case "buscar":
		$Qsigla = (string) \filter_input(INPUT_POST, 'sigla');
		
		$oLugares = new GestorLugar();
		$cLugares = $oLugares->getLugars(array('sigla'=>$Qsigla));
		$oLugar = $cLugares[0];
		break;
	case "guardar":
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
		$Qsigla = (string) \filter_input(INPUT_POST, 'sigla');

		if (empty($Qsigla)) { echo _("debe poner un nombre"); }

        $Qdl = (string) \filter_input(INPUT_POST, 'dl');
        $Qregion = (string) \filter_input(INPUT_POST, 'region');
        $Qnombre = (string) \filter_input(INPUT_POST, 'nombre');
        $Qtipo_ctr = (string) \filter_input(INPUT_POST, 'tipo_ctr');
        $Qe_mail = (string) \filter_input(INPUT_POST, 'e_mail');
        $Qmodo_envio = (integer) \filter_input(INPUT_POST, 'modo_envio');
        $Qanulado = (bool) \filter_input(INPUT_POST, 'anulado');
        
        $oLugar = new Lugar(array('id_lugar' => $Qid_lugar));
        $oLugar->DBCarregar();
        $oLugar->setSigla($Qsigla);
        $oLugar->setDl($Qdl);
        $oLugar->setRegion($Qregion);
        $oLugar->setNombre($Qnombre);
        $oLugar->setTipo_ctr($Qtipo_ctr);
        $oLugar->setE_mail($Qe_mail);
        $oLugar->setModo_envio($Qmodo_envio);
        $oLugar->setAnulado($Qanulado);
		if ($oLugar->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oLugar->getErrorTxt();
		}
        break;
	case "nuevo":
		$Qsigla = (string) \filter_input(INPUT_POST, 'sigla');
		if (empty($Qsigla)) { echo _("debe poner un nombre"); }
        $Qdl = (string) \filter_input(INPUT_POST, 'dl');
        $Qregion = (string) \filter_input(INPUT_POST, 'region');
        $Qnombre = (string) \filter_input(INPUT_POST, 'nombre');
        $Qtipo_ctr = (string) \filter_input(INPUT_POST, 'tipo_ctr');
        $Qe_mail = (string) \filter_input(INPUT_POST, 'e_mail');
        $Qmodo_envio = (integer) \filter_input(INPUT_POST, 'modo_envio');
        
        $oLugar = new Lugar(array('id_lugar' => $Qid_lugar));
        $oLugar->DBCarregar();
        $oLugar->setSigla($Qsigla);
        $oLugar->setDl($Qdl);
        $oLugar->setRegion($Qregion);
        $oLugar->setNombre($Qnombre);
        $oLugar->setTipo_ctr($Qtipo_ctr);
        $oLugar->setE_mail($Qe_mail);
        $oLugar->setModo_envio($Qmodo_envio);
		if ($oLugar->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oLugar->getErrorTxt();
		}
		break;
}