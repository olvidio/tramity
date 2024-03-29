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

$Q_que = (string)filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Q_que) {
    case 'info_firmas':
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $oficiales = TRUE;
            $varias = FALSE;
        } else {
            $Q_id_tramite = (integer)filter_input(INPUT_POST, 'id_tramite');
            $oficiales = FALSE;
            $aWhere = ['id_tramite' => $Q_id_tramite, 'id_cargo' => Cargo::CARGO_OFICIALES];
            $gesTramiteCargo = new GestorTramiteCargo();
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
            if (count($cTramiteCargos) > 0) {
                $oficiales = TRUE;
            }
            $varias = FALSE;
            $aWhere = ['id_tramite' => $Q_id_tramite, 'id_cargo' => Cargo::CARGO_VARIAS];
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
            if (count($cTramiteCargos) > 0) {
                $varias = TRUE;
            }
        }
        $a_info = ['oficiales' => $oficiales,
            'varias' => $varias,
        ];

        $jsondata['data'] = json_encode($a_info);
        break;
    case 'info':
        $Q_id_item = (integer)filter_input(INPUT_POST, 'id_item');
        $oGesCargo = new GestorCargo();
        $oDesplCargos = $oGesCargo->getDesplCargos();
        $oDesplCargos->setNombre('id_cargo');
        $oDesplCargos->setBlanco(TRUE);
        $oTramiteCargo = new TramiteCargo($Q_id_item);
        $orden_tramite = $oTramiteCargo->getOrden_tramite();
        $id_cargo = $oTramiteCargo->getId_cargo();
        $oDesplCargos->setOpcion_sel($id_cargo);
        $cargos = $oDesplCargos->desplegable();
        $multiple = $oTramiteCargo->getMultiple();

        $a_info = ['orden' => $orden_tramite,
            'multiple' => $multiple,
            'cargos' => $cargos,
            'item' => $Q_id_item,
        ];

        $jsondata['data'] = json_encode($a_info);
        break;
    case 'get_listado':
        $Q_id_tramite = (integer)filter_input(INPUT_POST, 'id_tramite');

        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $Q_id_tramite, '_ordre' => 'orden_tramite']);
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
    case 'update':
        $Q_id_item = (integer)filter_input(INPUT_POST, 'id_item');
        $Q_id_tramite = (integer)filter_input(INPUT_POST, 'id_tramite');
        $Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');
        $Q_orden_tramite = (integer)filter_input(INPUT_POST, 'orden_tramite');
        $Q_multiple = (integer)filter_input(INPUT_POST, 'multiple');

        $oTramiteCargo = new TramiteCargo($Q_id_item);
        $oTramiteCargo->setId_tramite($Q_id_tramite);
        $oTramiteCargo->setId_cargo($Q_id_cargo);
        $oTramiteCargo->setOrden_tramite($Q_orden_tramite);
        $oTramiteCargo->setMultiple($Q_multiple);
        if ($oTramiteCargo->DBGuardar() === FALSE) {
            $error_txt .= $oTramiteCargo->getErrorTxt();
        }
        break;
    case 'eliminar':
        $Q_id_item = (integer)filter_input(INPUT_POST, 'id_item');
        $oTramiteCargo = new TramiteCargo($Q_id_item);
        if ($oTramiteCargo->DBEliminar() === FALSE) {
            $error_txt .= $oTramiteCargo->getErrorTxt();
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (empty($error_txt)) {
    $jsondata['success'] = TRUE;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = FALSE;
    $jsondata['mensaje'] = $error_txt;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();