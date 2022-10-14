<?php

use tramites\model\entity\Tramite;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Q_que) {
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_tramite = (integer)strtok($a_sel[0], "#");
            $oTramite = new Tramite($Q_id_tramite);
            if ($oTramite->DBEliminar() === FALSE) {
                $error_txt .= $oTramite->getErrorTxt();
            }
        }
        break;
    case "guardar":
        $Q_tramite = (string)filter_input(INPUT_POST, 'tramite');

        if (empty($Q_tramite)) {
            echo _("debe poner un nombre");
        }
        $Q_id_tramite = (integer)filter_input(INPUT_POST, 'id_tramite');
        $Q_orden = (string)filter_input(INPUT_POST, 'orden');
        $Q_breve = (string)filter_input(INPUT_POST, 'breve');

        $oTramite = new Tramite (array('id_tramite' => $Q_id_tramite));
        $oTramite->DBCarregar();
        $oTramite->setTramite($Q_tramite);
        $oTramite->setOrden($Q_orden);
        $oTramite->setBreve($Q_breve);
        if ($oTramite->DBGuardar() === FALSE) {
            $error_txt .= $oTramite->getErrorTxt();
        }
        break;
    case "nuevo":
        $Q_tramite = (string)filter_input(INPUT_POST, 'tramite');
        $Q_orden = (string)filter_input(INPUT_POST, 'orden');
        $Q_breve = (string)filter_input(INPUT_POST, 'breve');

        $oTramite = new Tramite();
        $oTramite->setTramite($Q_tramite);
        $oTramite->setOrden($Q_orden);
        $oTramite->setBreve($Q_breve);
        if ($oTramite->DBGuardar() === FALSE) {
            $error_txt .= $oTramite->getErrorTxt();
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