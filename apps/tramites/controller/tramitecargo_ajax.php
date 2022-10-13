<?php

use tramites\model\entity\GestorTramiteCargo;
use tramites\model\entity\TramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string)\filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Qque) {
    case 'info_firmas':
        $Qid_tramite = (integer)\filter_input(INPUT_POST, 'id_tramite');
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

        $a_info = ['oficiales' => $oficiales,
            'varias' => $varias,
        ];

        $jsondata['data'] = json_encode($a_info);
        break;
    case 'info':
        $Qid_item = (integer)\filter_input(INPUT_POST, 'id_item');
        $oGesCargo = new GestorCargo();
        $oDesplCargos = $oGesCargo->getDesplCargos();
        $oDesplCargos->setNombre('id_cargo');
        $oDesplCargos->setBlanco(true);
        $oTramiteCargo = new TramiteCargo(array('id_item' => $Qid_item));
        $orden_tramite = $oTramiteCargo->getOrden_tramite();
        $id_cargo = $oTramiteCargo->getId_cargo();
        $oDesplCargos->setOpcion_sel($id_cargo);
        $cargos = $oDesplCargos->desplegable();
        $multiple = $oTramiteCargo->getMultiple();

        $a_info = ['orden' => $orden_tramite,
            'multiple' => $multiple,
            'cargos' => $cargos,
            'item' => $Qid_item,
        ];

        $jsondata['data'] = json_encode($a_info);
        break;
    case 'get_listado':
        $Qid_tramite = (integer)\filter_input(INPUT_POST, 'id_tramite');

        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $Qid_tramite, '_ordre' => 'orden_tramite']);
        $txt = '<table class="table table-striped" >';
        $txt .= '<tr><th>' . _("orden") . '</th><th>' . _("cargo") . '</th><th>' . _("multiple") . '</th></tr>';
        $i = 0;
        foreach ($cTramiteCargos as $oTramiteCargo) {
            $id_item = $oTramiteCargo->getId_item();
            $orden = $oTramiteCargo->getOrden_tramite();
            $id_cargo = $oTramiteCargo->getId_cargo();
            $multiple = $oTramiteCargo->getMultiple();

            $oCargo = new Cargo($id_cargo);
            $cargo = $oCargo->getCargo();

            $txt .= "<tr><td>($orden)</td><td>$cargo</td><td>$multiple</td>";

            $txt .= '<td>';
            $txt .= '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ModalBuscar" onclick=fnjs_cargar_item(' . $id_item . ')>';
            $txt .= _("modificar");
            $txt .= '</button>';
            $txt .= '</td>';
            $txt .= '<td>';
            $txt .= '<button type="button" class="btn btn-outline-danger" onclick=fnjs_eliminar(' . $id_item . ')>';
            $txt .= _("eliminar");
            $txt .= '</button>';
            $txt .= '</td><tr>';
        }
        $txt .= '</table><br>';
        $txt .= '<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#ModalBuscar" onClick="fnjs_nuevo()">';
        $txt .= _("nuevo");
        $txt .= '</button>';
        echo $txt;
        exit();
        break;
    case 'update':
        $Qid_item = (integer)\filter_input(INPUT_POST, 'id_item');
        $Qid_tramite = (integer)\filter_input(INPUT_POST, 'id_tramite');
        $Qid_cargo = (integer)\filter_input(INPUT_POST, 'id_cargo');
        $Qorden_tramite = (integer)\filter_input(INPUT_POST, 'orden_tramite');
        $Qmultiple = (integer)\filter_input(INPUT_POST, 'multiple');

        $oTramiteCargo = new TramiteCargo(array('id_item' => $Qid_item));
        $oTramiteCargo->setId_tramite($Qid_tramite);
        $oTramiteCargo->setId_cargo($Qid_cargo);
        $oTramiteCargo->setOrden_tramite($Qorden_tramite);
        $oTramiteCargo->setMultiple($Qmultiple);
        if ($oTramiteCargo->DBGuardar() === FALSE) {
            $error_txt .= $oTramiteCargo->getErrorTxt();
        }
        break;
    case 'eliminar':
        $Qid_item = (integer)\filter_input(INPUT_POST, 'id_item');
        $oTramiteCargo = new TramiteCargo(array('id_item' => $Qid_item));
        if ($oTramiteCargo->DBEliminar() === FALSE) {
            $error_txt .= $oTramiteCargo->getErrorTxt();
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (empty($error_txt)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $error_txt;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();