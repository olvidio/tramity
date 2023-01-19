<?php

use core\ViewTwig;
use envios\model\Enviar;
use escritos\model\Escrito;
use oasis_as4\model\As4Remove;
use usuarios\model\entity\Cargo;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


/* NO VA PORQUE... da error headers already sent. 
// Para enseñar el mensaje antes de terminar el script
ob_end_flush();
ob_implicit_flush();
echo "<div id=\"slow_load\" style=\"display: flex; justify-content: center; align-items: center; text-align: center;\">";
echo _("preparando escritos para enviar...");
echo "<br>";
echo "<img class=\"mb-4\" src=\"../images/loading.gif\" alt=\"cargando\" width=\"32\" height=\"32\">";
echo "</div>";
*/

$Q_id_escrito = (integer)filter_input(INPUT_GET, 'id');
$f_salida = date(DateTimeInterface::ATOM);
// Comprobar si tiene clave para enviar un xml, o hay que generar un pdf.

$rta_txt = '';
$oAS4Remove = new As4Remove();
// borrar los ya enviados:
$rta_txt = $oAS4Remove->remove_accepted();
if (!empty($rta_txt)) {
    exit(_("No puedo eliminar los ya enviados"));
}
// borrar los errores:
$rta_txt = $oAS4Remove->remove_rejected();
if (!empty($rta_txt)) {
    $rta_txt .= "<br>";
    $rta_txt .= _("No puedo eliminar los rechazados");
    exit($rta_txt);
}

// Primero intento enviar, sólo guardo la f_salida si tengo éxito
// Ahora cambio y pongo la f_salida si puedo enviar 1 o más. Los que no, salen como 
// error, pero el escrito se marca como enviado.
$oEnviar = new Enviar($Q_id_escrito, 'escrito');

$a_rta = $oEnviar->enviar();

if ($a_rta['marcar'] === TRUE) {
    $oEscrito = new Escrito($Q_id_escrito);
    if ($oEscrito->DBCargar() === FALSE ){
        $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_cargar);
    }
    $oEscrito->setF_salida($f_salida, FALSE);
    if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
        $oEscrito->setF_aprobacion($f_salida, FALSE);
    }
    $oEscrito->setOk(Escrito::OK_SECRETARIA);
    if ($oEscrito->DBGuardar() === FALSE) {
        exit($oEscrito->getErrorTxt());
    }
}
if ($a_rta['success'] === TRUE) {
    // para que se cierre la ventana que se ha abierto:
    echo "<script type=\"text/javascript\">
             self.close();
          </script>";
} else {
    $txt_alert = $a_rta['mensaje'];
    $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => TRUE];
    $oView = new ViewTwig('expedientes/controller');
    $oView->renderizar('alerta.html.twig', $a_campos);
}