<?php

use core\ViewTwig;
use web\Hash;
use web\Lista;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();


$Q_id_sel = (string)filter_input(INPUT_POST, 'id_sel');
$Q_scroll_id = (string)filter_input(INPUT_POST, 'scroll_id');

$nomdock = $_SESSION['oConfig']->getNomDock();
$dir = $_SESSION['oConfig']->getDock();
$dir_pmodes = $dir . '/repository/pmodes/';

$a_scan = scandir($dir_pmodes);
$a_files = array_diff($a_scan, ['.', '..']);

$a_cabeceras = ['nombre fichero'];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],];

$a_valores = [];
$i = 0;
foreach ($a_files as $filename) {
    $matches = [];
    $pattern = "/(.*)\-init\.xml/";
    if (preg_match($pattern, $filename, $matches)) {
        $i++;
        $a_valores[$i]['sel'] = "$filename#";
        $a_valores[$i][1] = $matches[0];
    }

    $pattern = "/(.*)\-resp\.xml/";
    if (preg_match($pattern, $filename, $matches)) {
        $i++;
        $a_valores[$i]['sel'] = "$filename#";
        $a_valores[$i][1] = $matches[0];
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

$aQuery = ['nuevo' => 1];
$url_nuevo = Hash::link(core\ConfigGlobal::getWeb() . '/apps/oasis_as4/controller/pdm_form.php?' . http_build_query($aQuery));
$url_eliminar = Hash::link(core\ConfigGlobal::getWeb() . '/apps/oasis_as4/controller/pdm_update.php');
$url_actualizar = Hash::link(core\ConfigGlobal::getWeb() . '/apps/oasis_as4/controller/pdm_lista.php');

$a_campos = [
    'nomdock' => $nomdock,
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oExpedienteLista' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('oasis_as4/controller');
$oView->renderizar('pdm_lista.html.twig', $a_campos);
