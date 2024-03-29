<?php

use core\ConfigGlobal;
use etiquetas\model\entity\Etiqueta;
use usuarios\model\entity\Cargo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

switch ($Q_que) {
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_etiqueta = (integer)strtok($a_sel[0], "#");
            $oEtiqueta = new Etiqueta($Q_id_etiqueta);
            if ($oEtiqueta->DBEliminar() === FALSE) {
                echo _("hay un error, no se ha eliminado");
                echo "\n" . $oEtiqueta->getErrorTxt();
            }
        }
        break;
    case "nuevo":
    case "guardar":
        $Q_id_etiqueta = (integer)filter_input(INPUT_POST, 'id_etiqueta');
        $Q_nom_etiqueta = (string)filter_input(INPUT_POST, 'nom_etiqueta');
        $Q_oficina = (string)filter_input(INPUT_POST, 'oficina');

        if (is_true($Q_oficina)) {
            $oficina = 't';
            // buscar el id de la oficina:
            $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
            $id_cargo = $oCargo->getId_oficina();
        } else {
            $oficina = 'f';
            $id_cargo = ConfigGlobal::role_id_cargo();
        }

        if (empty($Q_nom_etiqueta)) {
            echo _("debe poner un nombre");
        }

        $oEtiqueta = new Etiqueta($Q_id_etiqueta);
        $oEtiqueta->DBCargar();
        $oEtiqueta->setNom_etiqueta($Q_nom_etiqueta);
        $oEtiqueta->setOficina($oficina);
        $oEtiqueta->setId_cargo($id_cargo);
        if ($oEtiqueta->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n" . $oEtiqueta->getErrorTxt();
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}