<?php

use oasis_as4\model\As4Entregar;
use oasis_as4\model\As4SignalMessage;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

//$dir = $_SESSION['oConfig']->getDock();
//$dir_dock = $dir . '/data/msg_in';
$dir_entradas = '/home/dani/entradas';

/* Poner una marca para evitar que empiece un nuevo proceso antes
de finalizar el anterior y borrar los ficheros */
/*
$oPid = new Pid();

if ($oPid->existePid()) {
    die(_("Ya existe un proceso en marcha."));
}
$oPid->crearPid();
*/

$a_scan = scandir($dir_dock);
$a_files = array_diff($a_scan, ['.', '..']);

// mensajes de respuesta: <eb3:SignalMessage>
$a_files_mi = [];
foreach ($a_files as $filename) {
    $matches = [];
    $pattern = "/mi-(.*)\.xml/";

    if (preg_match($pattern, $filename, $matches)) {
        $a_files_mi[] = $dir_dock . '/' . $matches[0];
    }
}

$a_files_mmd = [];
foreach ($a_files as $filename) {
    $matches = [];
    $pattern = "/(.*)\.mmd\.xml/";

    if (preg_match($pattern, $filename, $matches)) {
        $a_files_mmd[] = $dir_dock . '/' . $matches[0];
    }
}

// cada mensaje que llega hay que descomponer y poner en su sitio
$txt = '';
foreach ($a_files_mmd as $file_mmd) {
    $xmldata = simplexml_load_string(file_get_contents($file_mmd));
    if ($xmldata === FALSE) {
        $txt .= sprintf(_("No se ha podido crear el xml del fichero %s"), $file_mmd);
    }
    $AS4 = new As4Entregar($xmldata);
    if ($AS4->introducirEnDB() === TRUE) {
        // eliminar el mensaje de la bandeja de entrada
        // nombre del fichero del body:
        $location = $AS4->getLocation();

        if (unlink($location) === FALSE) {
            $txt .= empty($txt) ? '' : '<br>';
            $txt .= sprintf(_("No se ha podido eliminar el fichero %s"), $location);
        }
        // el mensaje
        if (unlink($file_mmd) === FALSE) {
            $txt .= empty($txt) ? '' : '<br>';
            $txt .= sprintf(_("No se ha podido eliminar el mensaje %s"), $file_mmd);
        }
    } else {
        $txt .= empty($txt) ? '' : '<br>';
        $txt .= sprintf(_("No se ha podido entregar el mensaje %s a su destinatario"), $file_mmd);
        if (!empty(($AS4->getMsg()))) {
            $txt .= ': ' . $AS4->getMsg();
        }

    }

}

// control errores <eb3:SignalMessage>
//$txt = '';
foreach ($a_files_mi as $file_mmd) {
    $AS4SignalMessage = new As4SignalMessage($file_mmd);
    if ($AS4SignalMessage->getError() === TRUE) {
        $message_id = $AS4SignalMessage->getErrorRef_to_messsage();
        $a_message_id = $AS4SignalMessage->getInfoMessage($message_id);

        $txt .= $AS4SignalMessage->getTimeStamp()->getFromLocalHora();
        $txt .= ' > ';
        $txt .= _("Error") . ': ';
        $txt .= $AS4SignalMessage->getErrorDetail();
        $txt .= ' (';
        $txt .= $a_message_id['prot_org'];
        $txt .= ')';
        $txt .= '<br>';
    } else {
        // borrarlo
        if (unlink($file_mmd) === FALSE) {
            $txt .= sprintf(_("No se ha podido eliminar el mensaje info %s"), $file_mmd);
            $txt .= '<br>';
        }
    }
}

$oPid->borrarPid();
if (!empty($txt)) {
    echo $txt;
} else {
    echo _("Todos los mensajes descargados");
}