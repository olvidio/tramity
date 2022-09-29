<?php

use escritos\model\Escrito;
use lugares\model\entity\GestorLugar;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');
switch ($Qque) {
	case 'get_destinos':
		$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
		$oEscrito = new Escrito($Qid_escrito);
		$a_miembros = $oEscrito->getDestinosIds();
		$gesLugares = new GestorLugar();
		$aLugares = $gesLugares->getArrayLugares();
		$destinos_txt = '';
		foreach ($a_miembros as $id_lugar) {
			if (empty($aLugares[$id_lugar])) { continue; }
			$destinos_txt .= empty($destinos_txt)? '' : "\n";
			$destinos_txt .= $aLugares[$id_lugar];
		}
		$mensaje = '';
		
		if (empty($mensaje)) {
			$jsondata['success'] = true;
			$jsondata['destinos'] = $destinos_txt;
		} else {
			$jsondata['success'] = false;
			$jsondata['mensaje'] = $mensaje;
		}
		
		//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($jsondata);
		exit();
		break;
	default:
		$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
		exit ($err_switch);
}