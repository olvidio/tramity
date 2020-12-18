<?php
use core\ViewTwig;
use envios\model\Enviar;
use expedientes\model\Escrito;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_escrito = (integer) \filter_input(INPUT_GET, 'id');
//$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');

$f_salida = date(\DateTimeInterface::ISO8601);
// Comprobar si tiene clave para enviar un xml, o hay que generar un pdf.

// Primero intento enviar, sólo guardo la f_salida si tengo éxito
$oEnviar = new Enviar($Qid_escrito,'escrito');

$a_rta = $oEnviar->enviar();

if ($a_rta['success'] === TRUE) {
    $oEscrito = new Escrito($Qid_escrito);
    $oEscrito->DBCarregar();
    $oEscrito->setF_salida($f_salida,FALSE);
    $oEscrito->setOk(Escrito::OK_SECRETARIA);
    $oEscrito->DBGuardar();
    // para que se cierre la ventana que se ha abierto:
    echo "<script type=\"text/javascript\">
             self.close();
          </script>";
} else {
    $txt_alert = $a_rta['mensaje'];
    $a_campos = [ 'txt_alert' => $txt_alert, 'btn_cerrar' => TRUE ];
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('alerta.html.twig',$a_campos);
}