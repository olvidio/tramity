<?php

use core\ConfigGlobal;
use davical\model\Davical;
use usuarios\domain\entity\Cargo;
use usuarios\model\entity\Oficina;

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
            $Q_id_oficina = (integer)strtok($a_sel[0], "#");
            // el scroll id es de la página anterior, hay que guardarlo allí
            $oPosicion->addParametro('id_sel', $a_sel, 1);
            $scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
            $oPosicion->addParametro('scroll_id', $scroll_id, 1);
        } else {
            $Q_id_oficina = (integer)filter_input(INPUT_POST, 'id_oficina');
        }

        $oOficina = new Oficina (array('id_oficina' => $Q_id_oficina));
        // hay que coger la información antes de borrar:
        if ($oOficina->DBEliminar() === false) {
            $error_txt .= _("hay un error, no se ha eliminado");
            $error_txt .= "\n" . $oOficina->getErrorTxt();
        } else {
            // Eliminar el usuario en davical.
            // Para dl, Hace falta el nombre de la oficina:
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
                $oOficina = new Oficina($Q_id_oficina);
                $oficina = $oOficina->getSigla();
            } else {
                $oficina = ConfigGlobal::nombreEntidad();
            }
            //$oficina = 'ocs';

            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $error_txt .= $oDavical->eliminarOficina($oficina);
        }
        break;
    case "guardar":
        $Q_sigla = (string)filter_input(INPUT_POST, 'sigla');

        if (empty($Q_sigla)) {
            echo _("debe poner un nombre");
        }
        $Q_id_oficina = (integer)filter_input(INPUT_POST, 'id_oficina');
        $Q_orden = (string)filter_input(INPUT_POST, 'orden');

        $oOficina = new Oficina ($Q_id_oficina);
        $sigla_old = $oOficina->getSigla();
        $oOficina->setSigla($Q_sigla);
        $oOficina->setOrden($Q_orden);
        if ($oOficina->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oOficina->getErrorTxt();
        } else {
            if ($sigla_old !== $Q_sigla) {
                // Cambiar el nombre en davical.
                $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                $oDavical->cambioNombreOficina($Q_sigla, $sigla_old);
            } else {
                // revisar que existe:
                $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                $oDavical->crearOficina($Q_sigla);
            }
        }
        break;
    case "nuevo":
        $Q_sigla = (string)filter_input(INPUT_POST, 'sigla');
        $Q_orden = (string)filter_input(INPUT_POST, 'orden');

        $oOficina = new Oficina();
        $oOficina->setSigla($Q_sigla);
        $oOficina->setOrden($Q_orden);
        if ($oOficina->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oOficina->getErrorTxt();
        } else {
            // Crear la oficina en davical.
            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $oDavical->crearOficina($Q_sigla);
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