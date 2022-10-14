<?php

use core\ViewTwig;
use entradas\model\entity\EntradaBypass;
use entradas\model\Entrada;
use envios\model\Enviar;
use oasis_as4\model\As4Remove;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

/*	
// Para enseñar el mesaje antes de terminar el script
// NO SE PUEDE porque al consultar el etherpad:
// headers alredy sent...
ob_end_flush();
ob_implicit_flush();
echo "<div id=\"slow_load\" style=\"display: flex; justify-content: center; align-items: center; text-align: center; margin-top: 100px;\">";
echo _("preparando escritos para enviar...");
echo "<br>";
echo "<img class=\"mb-4\" src=\"../../images/loading.gif\" alt=\"cargando\" width=\"32\" height=\"32\">";
echo "</div>";
*/

$Q_id_entrada = (integer)filter_input(INPUT_GET, 'id');
$Qf_salida = (string)filter_input(INPUT_POST, 'f_salida');

if (empty($Qf_salida)) {
    $Qf_salida = date(\DateTimeInterface::ISO8601);
}

$rta_txt = '';
// borrar los ya enviados:
$oAS4Remove = new As4Remove();
$rta_txt = $oAS4Remove->remove_accepted();
if (!empty($rta_txt)) {
    exit(_("No puedo eliminar los ya enviados"));
}

// borrar los errores:
$oAS4Remove = new As4Remove();
$rta_txt = $oAS4Remove->remove_rejected();
if (!empty($rta_txt)) {
    $rta_txt .= "<br>";
    $rta_txt .= _("No puedo eliminar los rechazados");
    exit($rta_txt);
}

// Primero intento enviar, sólo guardo la f_salida si tengo éxito
$oEnviar = new Enviar($Qid_entrada, 'entrada');

$a_rta = $oEnviar->enviar();

if ($a_rta['success'] === TRUE) {
    $oEntradaBypass = new EntradaBypass($Qid_entrada);
    $oEntradaBypass->DBCarregar();
    $oEntradaBypass->setF_salida($Qf_salida, FALSE);
    if ($oEntradaBypass->DBGuardar() === FALSE) {
        $error_txt = $oEntradaBypass->getErrorTxt();
        echo "<script type=\"text/javascript\">
				alert('$error_txt');
			  </script>";
    }
    $oEntrada = new Entrada($Qid_entrada);
    $oEntrada->setEstado(Entrada::ESTADO_ENVIADO_CR);
    if ($oEntrada->DBGuardar() === FALSE) {
        $error_txt = $oEntrada->getErrorTxt();
        echo "<script type=\"text/javascript\">
				alert('$error_txt');
			  </script>";
    }
    // para que se cierre la ventana que se ha abierto:
    echo "<script type=\"text/javascript\">
			 self.close();
		  </script>";
} else {
    $txt_alert = $a_rta['mensaje'];
    $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => TRUE];
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('alerta.html.twig', $a_campos);
}