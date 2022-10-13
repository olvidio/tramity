<?php

use entidades\model\Entidad;
use entidades\model\entity\EntidadDB;
use web\StringLocal;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string)\filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Qque) {
    case "eliminar":
        $a_sel = (array)\filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_entidad = (integer)strtok($a_sel[0], "#");
            $oEntidad = new Entidad (array('id_entidad' => $Qid_entidad));
            // antes de eliminar la entidaddb, hay que eliminar el schema, etherpad i davical.
            // después perderé el nombre del esquema.
            $error_txt .= $oEntidad->eliminarEsquema();
            // etherpad?
            // davical?

            if (empty($error_txt) && $oEntidad->DBEliminar() === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oEntidad->getErrorTxt();
            }
        }
        break;
    case "nuevo":
    case "guardar":
        $Qnombre = (string)\filter_input(INPUT_POST, 'nombre');

        if (empty($Qnombre)) {
            $error_txt = _("debe poner un nombre");
        } else {
            $Qid_entidad = (integer)\filter_input(INPUT_POST, 'id_entidad');
            $Qschema = (string)\filter_input(INPUT_POST, 'schema');
            $Qtipo_entidad = (integer)\filter_input(INPUT_POST, 'tipo_entidad');
            $Qanulado = (bool)\filter_input(INPUT_POST, 'anulado');

            if (empty($Qid_entidad)) {
                $oEntidadDB = new EntidadDB();
            } else {
                $oEntidadDB = new EntidadDB (array('id_entidad' => $Qid_entidad));
                $oEntidadDB->DBCarregar();
            }

            $Qschema = empty($Qschema) ? $Qnombre : $Qschema;
            // El nombre del esquema es en minúsculas porque si se accede via nombre del 
            // servidor, éste está en minúscula (agdmontagut.tramity.local)
            // http://www.ietf.org/rfc/rfc2616.txt: Field names are case-insensitive. 
            $schema = strtolower($Qschema);
            // tambien lo normalizo:
            $schema = StringLocal::toRFC952($schema);

            $oEntidadDB->setNombre($Qnombre);
            $oEntidadDB->setSchema($schema);
            $oEntidadDB->setTipo($Qtipo_entidad);
            $oEntidadDB->setAnulado($Qanulado);
            if ($oEntidadDB->DBGuardar() === FALSE) {
                $error_txt .= _("hay un error al guardar");
                $error_txt .= "\n" . $oEntidadDB->getErrorTxt();
            } else {
                // En el caso de nuevo, crear el esquema:
                // Crear el calendario davical ??¿?¿:
                if ($Qque == 'nuevo') {
                    $id = $oEntidadDB->getId_entidad();
                    $oEntidad = new Entidad($id);
                    $oEntidad->DBCarregar();
                    $error_txt = $oEntidad->nuevoEsquema();
                }
            }
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
        