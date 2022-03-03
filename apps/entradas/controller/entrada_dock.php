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
$txt = '';
foreach ($a_files_mmd as $file_mmd) {
	$xmldata = simplexml_load_file($file_mmd);
	$AS4 = new As4Distribuir($xmldata);
	if ($AS4->distribuir() === TRUE) {
		// eliminar el mensaje de la bandeja de entrada
		// nombre del fihero del body:
		$location = $AS4->getLocation();
		if (unlink($location) === FALSE) {
			$txt .= sprintf(_("No se ha podido eliminar el fichero %s"), $location);
		}
		// el mensaje
		if (unlink($file_mmd) === FALSE) {
			$txt .= sprintf(_("No se ha podido eliminar el mensaje %s"), $file_mmd);
		}
	} else {
		$txt .= sprintf(_("No se ha podido entregar el mensaje %s a su destinatario"), $file_mmd);
		
	}
	
}

if (!empty($txt)) {
	echo $txt;	
} else {
	echo _("Todos los mensajes descargados");
}