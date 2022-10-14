<?php

use core\ViewTwig;
use envios\model\Enviar;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El download es via GET!!!";
$Q_id_escrito = (integer)filter_input(INPUT_GET, 'id_escrito');

if (!empty($Q_id_escrito)) {

    $oEnviar = new Enviar($Q_id_escrito, 'escrito');

    if (($File = $oEnviar->getPdf()) === FALSE) {
        $txt_alert = $_SESSION['oGestorErrores']->ver();
        if (empty($txt_alert)) {
            $txt_alert = _("Algún error al genrar el pdf. Es posible que no tenga el protocolo.");
        }
        $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => TRUE];
        $oView = new ViewTwig('expedientes/controller');
        echo $oView->renderizar('alerta.html.twig', $a_campos);
        exit();
    }

    $escrito = $File['content'];
    $nom = $File['ext'];

    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: private", false); // required for certain browsers
    header('Content-Type: application/force-download');
    header('Content-Type: application/octet-stream', false);
    header('Content-Type: application/download', false);
    header('Content-disposition: attachment; filename="' . $nom . '"');

    ob_clean();
    flush();
    echo $escrito;
    exit();
} else {
    $error = TRUE;
    $outData = "{'error': $error}";
    echo json_encode($outData); // return json data
}