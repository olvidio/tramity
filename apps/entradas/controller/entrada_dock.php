<?php
use entradas\model\As4Distribuir;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qslide_mode = (string) \filter_input(INPUT_POST, 'slide_mode');

$dir = $_SESSION['oConfig']->getDock();
$dir_dock = $dir . '/data/msg_in';  


$a_scan = scandir($dir_dock);
$a_files = array_diff($a_scan, ['.','..']);

$a_files_mmd = [];
foreach ($a_files as $filename) {
	$matches = [];
	$pattern = "/(.*)\.mmd\.xml/";

	if (preg_match($pattern, $filename, $matches)) {
		$a_files_mmd[] = $dir_dock.'/'.$matches[0];
	}
}

// cada mensaje que llega hay que descomponer y poner en su sitio
foreach ($a_files_mmd as $file_mmd) {
	echo $file_mmd;
	$xmldata = simplexml_load_file($file_mmd);
	
	$AS4 = new As4Distribuir($xmldata);
	
	$AS4->distribuir();
}