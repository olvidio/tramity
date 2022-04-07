<?php
use core\ViewTwig;
use envios\model\Enviar;
use escritos\model\Escrito;
use oasis_as4\model\As4Remove;
use usuarios\model\entity\Cargo;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_escrito = (integer) \filter_input(INPUT_GET, 'id');

$f_salida = date(\DateTimeInterface::ISO8601);
// Comprobar si tiene clave para enviar un xml, o hay que generar un pdf.

$rta_txt = '';
// borrar los ya enviados:
$oAS4Remove =  new As4Remove();
$rta_txt = $oAS4Remove->remove_accepted();
if (!empty($rta_txt)) {
	exit(_("No puedo eliminar los ya enviados"));
}

// borrar los errores:
$oAS4Remove =  new As4Remove();
$rta_txt = $oAS4Remove->remove_rejected();
if (!empty($rta_txt)) {
	exit(_("No puedo eliminar los rechazados"));
}

// Primero intento enviar, sólo guardo la f_salida si tengo éxito
// Ahora cambio y pongo la f_salida si puedo enviar 1 o más. Los que no, salen como 
// error, pero el escrito se marca como enviado.
$oEnviar = new Enviar($Qid_escrito,'escrito');

$a_rta = $oEnviar->enviar();

if ($a_rta['marcar'] === TRUE) {
    $oEscrito = new Escrito($Qid_escrito);
    $oEscrito->DBCarregar();
    $oEscrito->setF_salida($f_salida,FALSE);
    if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
		$oEscrito->setF_aprobacion($f_salida,FALSE);
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
    $a_campos = [ 'txt_alert' => $txt_alert, 'btn_cerrar' => TRUE ];
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('alerta.html.twig',$a_campos);
}