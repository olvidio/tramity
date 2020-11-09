<?php
use envios\model\Enviar;


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');

echo "<h1>KKKKKKKKKKK $Qid_entrada KKKKKKKKK</h1>";


$oEnviar = new Enviar($Qid_entrada,'entrada');
$oEnviar->enviar();

/*
function renombrar($name){
    //cambiar '/' por '_':
    $new = str_replace('/', '_', $name);
    
    return $new;
}


$Qid_entrada = 13;

// a ver si ya está
$gesEntradasBypass = new GestorEntradaBypass();
$cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
if (count($cEntradasBypass) > 0) {
    // solo debería haber una:
    $oEntradaBypass = $cEntradasBypass[0];
    $id_item = $oEntradaBypass->getId_item();
}
    
$oEntradaBypass = new EntradaBypass($id_item);
// poner los destinos

$a_grupos = $oEntradaBypass->getId_grupos();

$aMiembros = [];
if (!empty($a_grupos)) {
    //(segun los grupos seleccionados)
    $aMiembros = $oEntradaBypass->getDestinos();
    $destinos_txt = $oEntradaBypass->getDescripcion();
} else {
    //(segun individuales)
    $destinos_txt = '';
    $a_json_prot_dst = $oEntradaBypass->getJson_prot_destino();
    foreach ($a_json_prot_dst as $json_prot_dst) {
        $aMiembros[] = $json_prot_dst->lugar;
        $oLugar = new Lugar($json_prot_dst->lugar);
        $destinos_txt .= empty($destinos_txt)? '' : ', ';
        $destinos_txt .= $oLugar->getNombre();
    }
}

$oProtOrigen = new Protocolo();
$oEntrada = new Entrada($Qid_entrada);
$json_prot_origen = $oEntrada->getJson_prot_origen();
if (count(get_object_vars($json_prot_origen)) == 0) {
    exit (_("No hay más"));
}
$oProtOrigen->setLugar($json_prot_origen->lugar);
$oProtOrigen->setProt_num($json_prot_origen->num);
$oProtOrigen->setProt_any($json_prot_origen->any);
$oProtOrigen->setMas($json_prot_origen->mas);
$filename = renombrar($oProtOrigen->ver_txt());

// Attachments
// escrito:
$oEntrada = new Entrada($Qid_entrada);
$oEntrada->DBCarregar();

$json_prot_origen = $oEntrada->getJson_prot_origen();
$oProtOrigen = new Protocolo();
$oProtOrigen->setLugar($json_prot_origen->lugar);
$oProtOrigen->setProt_num($json_prot_origen->num);
$oProtOrigen->setProt_any($json_prot_origen->any);
$oProtOrigen->setMas($json_prot_origen->mas);

$a_header = [ 'left' => $destinos_txt,
    'center' => '',
    'right' => $oProtOrigen->ver_txt(),
];

$oEtherpad = new Etherpad();
$oEtherpad->setId (Etherpad::ID_ENTRADA,$Qid_entrada);

$f_salida = $oEntradaBypass->getF_salida()->getFromLocal('.');

$omPdf = $oEtherpad->generarPDF($a_header,$f_salida);
$filename_ext = $filename.'.pdf';
$StringFile = $omPdf->Output($filename_ext,'S');

try {
    ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );
    
    // generar el mail, con todos los destino en cco:
    $subject = "$filename, Fent proves";
    $message = "Ver archivos adjuntos";
    
    $oMail = new TramityMail(TRUE); //passing `true` enables exceptions
    $oLugar = new Lugar();
    $err_mail = '';
    $mails_validos = 0;
    foreach ($aMiembros as $id_lugar) {
        $oLugar->setId_lugar($id_lugar);
        $oLugar->DBCarregar(); // obligar a recargar después de cambiar el id.
        $email = $oLugar->getE_mail();
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $oMail->addBCC($email);
            $mails_validos++;
        } else {
            $err_mail .= empty($err_mail)? '' : '<br>';
            $err_mail .= $oLugar->getNombre() ."($email)";
        }
    }
    if (empty($aMiembros)) {
        $err_mail = _("No hay destinos para este escrito").':<br>'.$filename;
        
    } else {
        $err_mail = empty($err_mail)? '' : _("mail no válido para").':<br>'.$err_mail;
    }
    
    if ($mails_validos > 0) {
        // Attachments
        //$oMail->addAttachment($File, $filename);    // Optional name
        $oMail->addStringAttachment($StringFile, $filename_ext);    // Optional name
        //$oMail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$oMail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        
        // adjuntos:
        $a_adjuntos = $oEntrada->getArrayIdAdjuntos();
        foreach ($a_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = new EntradaAdjunto($item);
            $escrito = $oEntradaAdjunto->getAdjunto();
            $escrito_txt = stream_get_contents($escrito);
            $oMail->addStringAttachment($escrito_txt, $adjunto_filename);    // Optional name
        }

        
        // Content
        $oMail->isHTML(true);                                  // Set email format to HTML
        $oMail->Subject = $subject;
        $oMail->Body    = $message;
        //$oMail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
        $oMail->send();
        echo 'Message has been sent<br>';
    }
    echo $err_mail;
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$oMail->ErrorInfo}";
}
*/