<?php

use escritos\model\Escrito;
use lugares\model\entity\Grupo;

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
    case "guardar_escrito":
        $Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
        $Q_id_grupo = (integer)filter_input(INPUT_POST, 'id_grupo');
        $Q_descripcion = (string)filter_input(INPUT_POST, 'descripcion');
        $Q_a_lugares = (array)filter_input(INPUT_POST, 'lugares', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (empty($Q_descripcion)) {
            $error_txt .= _("debe poner un nombre");
        }

        $oEscrito = new Escrito($Q_id_escrito);
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // borrar destinos existentes
        $oEscrito->setJson_prot_destino([]);
        $oEscrito->setId_grupos();
        // poner nueva selección
        $oEscrito->setDestinos($Q_a_lugares);
        $oEscrito->setDescripcion($Q_descripcion);

        if ($oEscrito->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oEscrito->getErrorTxt();
        }
        break;
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_grupo = (integer)strtok($a_sel[0], "#");
            $oGrupo = new Grupo($Q_id_grupo);
            if ($oGrupo->DBEliminar() === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oGrupo->getErrorTxt();
            }
        }
        break;
    case "nuevo":
    case "guardar":
        $Q_id_grupo = (integer)filter_input(INPUT_POST, 'id_grupo');
        $Q_descripcion = (string)filter_input(INPUT_POST, 'descripcion');
        $Q_autorizacion = (string)filter_input(INPUT_POST, 'autorizacion');
        $Q_a_lugares = (array)filter_input(INPUT_POST, 'lugares', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (empty($Q_descripcion)) {
            $error_txt .= _("debe poner un nombre");
        }

        $oGrupo = new Grupo($Q_id_grupo);
        $oGrupo->DBCargar();
        $oGrupo->setDescripcion($Q_descripcion);
        $oGrupo->setAutorizacion($Q_autorizacion);
        $oGrupo->setMiembros($Q_a_lugares);
        if ($oGrupo->DBGuardar() === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $oGrupo->getErrorTxt();
        }
        break;
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