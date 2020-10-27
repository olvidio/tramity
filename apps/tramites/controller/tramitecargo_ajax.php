<?php
use tramites\model\entity\GestorTramiteCargo;
use tramites\model\entity\TramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
    case 'info_firmas':
	    $Qid_tramite = (integer) \filter_input(INPUT_POST, 'id_tramite');
        $error_txt = '';
        $oficiales = FALSE;
        $aWhere = ['id_tramite' => $Qid_tramite, 'id_cargo' => Cargo::CARGO_OFICIALES];
	    $gesTramiteCargo = new GestorTramiteCargo();
	    $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
	    if (count($cTramiteCargos) > 0) {
	        $oficiales = TRUE;
	    }
        $varias = FALSE;
        $aWhere = ['id_tramite' => $Qid_tramite, 'id_cargo' => Cargo::CARGO_VARIAS];
	    $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
	    if (count($cTramiteCargos) > 0) {
	        $varias = TRUE;
	    }
	    
	    $a_info=[ 'oficiales' => $oficiales,
	        'varias' => $varias,
	    ];
	    
	    $jsondata['data'] = json_encode($a_info);
	    if (!empty($error_txt)) {
	        $jsondata['success'] = FALSE;
	        $jsondata['error_txt'] = $error_txt;
	    } else {
	        $jsondata['success'] = TRUE;
	    }
	    //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
	    header('Content-type: application/json; charset=utf-8');
	    echo json_encode($jsondata);
	    
        break;
    case 'info':
	    $Qid_item = (integer) \filter_input(INPUT_POST, 'id_item');
        $error_txt = '';
        $oGesCargo = new GestorCargo();
        $oDesplCargos = $oGesCargo->getDesplCargos();
        $oDesplCargos->setNombre('id_cargo');
        $oDesplCargos->setBlanco(true);
	    $oTramiteCargo = new TramiteCargo(array('id_item'=>$Qid_item));
	    $orden_tramite = $oTramiteCargo->getOrden_tramite();
	    $id_cargo = $oTramiteCargo->getId_cargo();
	    $oDesplCargos->setOpcion_sel($id_cargo);
	    $cargos = $oDesplCargos->desplegable();
	    $multiple = $oTramiteCargo->getMultiple();
	    
	    $a_info=[ 'orden' => $orden_tramite,
	        'multiple' => $multiple,
	        'cargos' => $cargos,
	        'item' => $Qid_item,
	    ];
	    
	    $jsondata['data'] = json_encode($a_info);
	    if (!empty($error_txt)) {
	        $jsondata['success'] = FALSE;
	        $jsondata['error_txt'] = $error_txt;
	    } else {
	        $jsondata['success'] = TRUE;
	    }
	    //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
	    header('Content-type: application/json; charset=utf-8');
	    echo json_encode($jsondata);
	    
        break;
	case 'get_listado':
	    $Qid_tramite = (integer) \filter_input(INPUT_POST, 'id_tramite');

		$gesTramiteCargo = new GestorTramiteCargo();
		$cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite'=>$Qid_tramite,'_ordre'=>'orden_tramite']);
		$txt = '<table class="table table-striped" >';
        $txt .= '<tr><th>'._("orden").'</th><th>'._("cargo").'</th><th>'._("multiple").'</th></tr>';
		$i=0;
		foreach ($cTramiteCargos as $oTramiteCargo) {
			$id_item = $oTramiteCargo->getId_item();
			$orden = $oTramiteCargo->getOrden_tramite();
			$id_cargo = $oTramiteCargo->getId_cargo();
			$multiple = $oTramiteCargo->getMultiple();
			
			$oCargo = new Cargo($id_cargo);
			$cargo = $oCargo->getCargo();
			
			$txt.="<tr><td>($orden)</td><td>$cargo</td><td>$multiple</td>";
			
			$txt.='<td>';
			$txt.='<button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalBuscar" onclick=fnjs_cargar_item('.$id_item.')>';
			$txt.= _("modificar");
			$txt.='</button>';
			$txt.='</td>';
			$txt.='<td>';
			$txt.='<button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#ModalBuscar" onclick=fnjs_eliminar(event,'.$id_item.')>';
			$txt.= _("eliminar");
			$txt.='</button>';
			$txt.='</td><tr>';
		}
		$txt.='</table><br>';
        $txt.='<button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalBuscar" onClick="fnjs_nuevo()">';
        $txt.= _("nuevo");
        $txt.='</button>';
		echo $txt;
		break;
	case 'update':
	    $Qid_item = (integer) \filter_input(INPUT_POST, 'id_item');
	    $Qid_tramite = (integer) \filter_input(INPUT_POST, 'id_tramite');
	    $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
	    $Qorden_tramite = (integer) \filter_input(INPUT_POST, 'orden_tramite');
	    $Qmultiple = (integer) \filter_input(INPUT_POST, 'multiple');

		$oTramiteCargo = new TramiteCargo(array('id_item'=>$Qid_item));
		$oTramiteCargo->setId_tramite($Qid_tramite);	
		$oTramiteCargo->setId_cargo($Qid_cargo);	
		$oTramiteCargo->setOrden_tramite($Qorden_tramite);	
		$oTramiteCargo->setMultiple($Qmultiple);	
		if ($oTramiteCargo->DBGuardar() === false) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oTramiteCargo->getErrorTxt();
		}
		break;
	case 'eliminar':
	    $Qid_item = (integer) \filter_input(INPUT_POST, 'id_item');
		$oTramiteCargo = new TramiteCargo(array('id_item'=>$Qid_item));
		if ($oTramiteCargo->DBEliminar() === false) {
			echo _("hay un error, no se ha eliminado");
			echo "\n".$oTramiteCargo->getErrorTxt();
		}
		break;
}