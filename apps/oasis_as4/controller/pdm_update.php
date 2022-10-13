<?php

use oasis_as4\model\Pmode;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qplataforma = (string)\filter_input(INPUT_POST, 'plataforma');
$Qservidor = (string)\filter_input(INPUT_POST, 'servidor');
$Qaccion = (string)\filter_input(INPUT_POST, 'accion');
$Qque = (string)\filter_input(INPUT_POST, 'que');

$Qscroll_id = (integer)\filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)\filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

if (!empty($a_sel)) { //vengo de un checkbox
    $Qque = (string)\filter_input(INPUT_POST, 'que');
    $Qfilename = (string)strtok($a_sel[0], "#");
    // el scroll id es de la página anterior, hay que guardarlo allí
    $oPosicion->addParametro('id_sel', $a_sel, 1);
    $Qscroll_id = (integer)\filter_input(INPUT_POST, 'scroll_id');
    $oPosicion->addParametro('scroll_id', $Qscroll_id, 1);
}


$error_txt = '';
if ($Qque == 'nuevo') {
    // init
    $oPmode = new Pmode();
    $oPmode->setPlataforma_Destino($Qplataforma);
    $oPmode->setAccion($Qaccion);
    $oPmode->setHolo_server_dst($Qservidor);
    $error_txt .= $oPmode->saveInit();
    // resp
    $oPmode = new Pmode();
    $oPmode->setPlataforma_Destino($Qplataforma);
    $oPmode->setAccion($Qaccion);
    $oPmode->setHolo_server_dst($Qservidor);
    $error_txt .= $oPmode->saveResp();
}
if ($Qque == 'eliminar') {
    $dir = $_SESSION['oConfig']->getDock();
    // si es una resp. sólo hay que eliminar este fichero
    $matches = [];
    $pattern = "/(.*)\-resp\.xml/";
    if (preg_match($pattern, $Qfilename, $matches)) {
        $fullfilename = $dir . '/repository/pmodes/' . $Qfilename;
        if (unlink($fullfilename) === FALSE) {
            $error_txt .= sprintf(_("hay un error al eliminar: %s"), $fullfilename);
        }
    } else {
        // si es un init, también habrá que eliminar la resp. en el directorio particular (pmodes_resp)
        // init
        $fullfilename = $dir . '/repository/pmodes/' . $Qfilename;
        if (unlink($fullfilename) === FALSE) {
            $error_txt .= sprintf(_("hay un error al eliminar: %s"), $fullfilename);
        }
        // resp
        $Qfilename_resp = str_replace('-init', '-resp', $Qfilename);
        $fullfilename = $dir . '/repository/pmodes_resp/' . $Qfilename_resp;
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