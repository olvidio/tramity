<?php

use core\ViewTwig;
use web\Hash;
use web\Lista;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$nomdock = $_SESSION['oConfig']->getNomDock();
$dir = $_SESSION['oConfig']->getDock();
$dir_pmodes_resp = $dir . '/repository/pmodes_resp/';

$a_scan = scandir($dir_pmodes_resp);
$a_files = array_diff($a_scan, ['.', '..']);

$a_cabeceras = [_("nombre fichero"), _("acción")];
$a_botones = [];
$a_valores = [];
$i = 0;
foreach ($a_files as $filename) {
    $matches = [];
    $pattern = "/(.*)\-resp\.xml/";
    if (preg_match($pattern, $filename, $matches)) {
        $i++;
        $a_valores[$i][1] = $matches[0];

        $url_download = Hash::link('apps/oasis_as4/controller/pdm_download.php?' . http_build_query(['filename' => $filename]));
        $a_valores[$i][2] = "<span role=\"button\" class=\"btn-link\" onclick=\"window.open('$url_download');\" >" . _("descargar") . "</span>";

    }
}


$oTabla = new Lista();
$oTabla->setId_tabla('lista_pdm');
$oTabla->setBotones($a_botones);
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setDatos($a_valores);

$oHash = new Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));


$a_campos = [
    'nomdock' => $nomdock,
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oEntradaLista' => $oTabla,
];

$oView = new ViewTwig('oasis_as4/controller');
$oView->renderizar('pdm_exportar.html.twig', $a_campos);
