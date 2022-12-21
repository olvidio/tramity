<?php

use oasis_as4\model\Pmode;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_plataforma = (string)filter_input(INPUT_POST, 'plataforma');
$Q_servidor = (string)filter_input(INPUT_POST, 'servidor');
$Q_accion = (string)filter_input(INPUT_POST, 'accion');
$Q_que = (string)filter_input(INPUT_POST, 'que');

$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

if (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    $Q_filename = (string)strtok($a_sel[0], "#");
    // el scroll id es de la página anterior, hay que guardarlo allí
    $oPosicion->addParametro('id_sel', $a_sel, 1);
    $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
    $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
}


$error_txt = '';
if ($Q_que === 'nuevo') {
    // init
    $oPmode = new Pmode();
    $oPmode->setPlataforma_Destino($Q_plataforma);
    $oPmode->setAccion($Q_accion);
    $oPmode->setHolo_server_dst($Q_servidor);
    $error_txt .= $oPmode->saveInit();
    // resp
    $oPmode = new Pmode();
    $oPmode->setPlataforma_Destino($Q_plataforma);
    $oPmode->setAccion($Q_accion);
    $oPmode->setHolo_server_dst($Q_servidor);
    $error_txt .= $oPmode->saveResp();
}
if ($Q_que === 'eliminar') {
    $dir = $_SESSION['oConfig']->getDock();
    // si es una resp. sólo hay que eliminar este fichero
    $matches = [];
    $pattern = "/(.*)\-resp\.xml/";
    if (preg_match($pattern, $Q_filename, $matches)) {
        $fullfilename = $dir . '/repository/pmodes/' . $Q_filename;
        if (unlink($fullfilename) === FALSE) {
            $error_txt .= sprintf(_("hay un error al eliminar: %s"), $fullfilename);
        }
    } else {
        // si es un init, también habrá que eliminar la resp. en el directorio particular (pmodes_resp)
        // init
        $fullfilename = $dir . '/repository/pmodes/' . $Q_filename;
        if (unlink($fullfilename) === FALSE) {
            $error_txt .= sprintf(_("hay un error al eliminar: %s"), $fullfilename);
        }
        // resp
        $Q_filename_resp = str_replace('-init', '-resp', $Q_filename);
        $fullfilename = $dir . '/repository/pmodes_resp/' . $Q_filename_resp;
        if (unlink($fullfilename) === FALSE) {
            $error_txt .= sprintf(_("hay un error al eliminar: %s"), $fullfilename);
        }
    }
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