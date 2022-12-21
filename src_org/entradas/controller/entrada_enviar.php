<?php

use core\ViewTwig;
use entradas\domain\entity\Entrada;
use entradas\domain\entity\EntradaRepository;
use entradas\domain\repositories\EntradaBypassRepository;
use envios\model\Enviar;
use oasis_as4\model\As4Remove;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

/*
// Para enseñar el mensaje antes de terminar el script
// NO SE PUEDE porque al consultar el etherpad:
// headers already sent...
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
    $Qf_salida = date(DateTimeInterface::ATOM);
}
$oF_salida = DateTimeLocal::createFromLocal($Qf_salida, 'date');

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
$oEnviar = new Enviar($Q_id_entrada, 'entrada');

$a_rta = $oEnviar->enviar();

if ($a_rta['success'] === TRUE) {
    $EntradaBypassRepository = new EntradaBypassRepository();
    $oEntradaBypass = $EntradaBypassRepository->findById($Q_id_entrada);
    $oEntradaBypass->setF_salida($oF_salida);
    if ($EntradaBypassRepository->Guardar($oEntradaBypass) === FALSE) {
        $error_txt = $EntradaBypassRepository->getErrorTxt();
        echo "<script type=\"text/javascript\">
				alert('$error_txt');
			  </script>";
    }
    $EntradaRepository = new EntradaRepository();
    $oEntrada = $EntradaRepository->findById($Q_id_entrada);
    $oEntrada->setEstado(Entrada::ESTADO_ENVIADO_CR);
    if ($EntradaRepository->Guardar($oEntrada) === FALSE) {
        $error_txt = $EntradaRepository->getErrorTxt();
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
    $oView->renderizar('alerta.html.twig', $a_campos);
}