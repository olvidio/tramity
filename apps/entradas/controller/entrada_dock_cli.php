<?php
/**
 * Este proceso se ejecuta desde cron.
 *
 * Cuando el número de mensajes es alto (> 20) puede ser que se agote el tiempo de ejecución del php
 * para evitarlo, vamos a procesar sólo 5 mensajes cada vez. La idea es poner un cron que lo vaya ejecutando.
 * El error que se produce al haber un timeout es que se genera un mensaje vacío.
 * Posteriormente al volver a ejecutar se crea el correcto, pero el vacío, al no tener fecha no se puede eliminar
 */


// >/usr/bin/php /var/www/tramity/apps/entradas/controller/entrada_dock_cli.php /var/www/tramity /var/www/conf /opt/holodeckb2b/data/msg_in docker_ctr.tramity.local montagut toni system



/**
 * Los directorios físicos para encontrar los archivos, se pasan como argumentos.
 * los valores por defecto son para debug.
 */
$dir_base = "/home/dani/tramity_local/tramity";
$dir_conf = "/home/dani/tramity_local/conf";
$dir_dock = '/home/dani/tramity_local/holodeckb2b/data/msg_in';
$servidor_api = 'docker_ctr.tramity.local'; // sirve para aplicar la APIKEY para el etherpad
$esquema = 'montagut'; // para autorizar al usuario/pwd
$username = 'toni';
$password = 'system';

if (!empty($argv[1])) {
    $dir_base = $argv[1];
    $dir_conf = $argv[2];
    $dir_dock = $argv[3];
    $servidor_api = $argv[4];
    $esquema = $argv[5];
    $username = $argv[6];
    $password = $argv[7];
}

$GLOBALS['DIR'] = $dir_base;
$_SERVER['DOCUMENT_ROOT'] = $dir_base;
$GLOBALS['WEBDIR'] = $dir_base;
$GLOBALS['DIR_PWD'] = $dir_conf;
$_POST['username'] = $username;
$_POST['password'] = $password;
$_POST['esquema'] = $esquema;
$_SERVER['HTTP_HOST'] = $esquema;
$GLOBALS['SERVIDOR'] = $servidor_api;

set_include_path(get_include_path() . PATH_SEPARATOR . $dir_base);
require_once("apps/core/global_header.inc");
require_once("apps/core/global_object.inc");

$dir = $_SESSION['oConfig']->getDock();
$dir_dock = $dir . '/data/msg_in';

use core\ConfigGlobal;
use entradas\model\Pid;
use oasis_as4\model\As4Entregar;
use oasis_as4\model\As4SignalMessage;

/* Poner una marca para evitar que empiece un nuevo proceso antes
de finalizar el anterior y borrar los ficheros */
$oPid = new Pid();

if ($oPid->existePid()) {
    die(_("Ya existe un proceso en marcha."));
}
$oPid->crearPid();

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

// Solamente cojo 5.
$a_files_mmd_truncated = array_slice($a_files_mmd, 0, 5);

// cada mensaje que llega hay que descomponer y poner en su sitio
$txt = '';
foreach ($a_files_mmd_truncated as $file_mmd) {
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
    //echo $txt;
    // escribir en el log
    $log_file =  ConfigGlobal::directorio() . '/log/as4.log';
    $txt_log = "\n\r";
    $txt_log .= date('Y-m-d H:i:s');
    $txt_log .= "\n\r";
    $txt_log .= str_replace("<br>", "\n\r", $txt);
    $txt_log .= "\n\r";
   file_put_contents($log_file, $txt_log, FILE_APPEND);
}