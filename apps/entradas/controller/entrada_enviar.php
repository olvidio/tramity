<?php
use core\ViewTwig;
use entradas\model\entity\GestorEntradaBypass;
use envios\model\Enviar;
use expedientes\model\Escrito;
use entradas\model\Entrada;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_entrada = (integer) \filter_input(INPUT_GET, 'id');
$Qf_salida = (string) \filter_input(INPUT_POST, 'f_salida');

if (empty($Qf_salida)) {
    $Qf_salida = date(\DateTimeInterface::ISO8601);
}
// Comprobar si tiene clave para enviar un xml, o hay que generar un pdf.

// Primero intento enviar, sólo guardo la f_salida si tengo éxito
$oEnviar = new Enviar($Qid_entrada,'entrada');

$a_rta = $oEnviar->enviar();

if ($a_rta['success'] === TRUE) {
    $gesEntradasBypass = new GestorEntradaBypass();
    $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
    if (!empty($cEntradasBypass)) {
        // solo debería haber una:
        $oEntradaBypass = $cEntradasBypass[0];
        $oEntradaBypass->DBCarregar();
        $oEntradaBypass->setF_salida($Qf_salida,FALSE);
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
    }
} else {
    $txt_alert = $a_rta['mensaje'];
    $a_campos = [ 'txt_alert' => $txt_alert, 'btn_cerrar' => TRUE ];
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('alerta.html.twig',$a_campos);
}