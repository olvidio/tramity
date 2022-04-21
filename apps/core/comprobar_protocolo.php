<?php

use function core\any_2;
use entradas\model\GestorEntrada;

// INICIO Cabecera global de URL de controlador *********************************


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

$id_reg='';
$aviso_rango='';
$aviso_repe='';
$aviso_origen='';
$aviso_aprobado='';
$aviso_txt='';
$error_txt='';
$aviso_salto='';
$aviso_any='';
$asunto='';
$detalle='';
$visibilidad='';
$anulado='';
$oficinas_txt='';
$dest_id_lugar[0]='';

$txt_err = '';
$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

$Qprot_any=any_2($Qprot_any);
// compruebo el año (actual o -1)
$any=date('y');
if ($Qprot_any != $any && $Qprot_any != $any-1) { $aviso_any=1; }

if ($Qque == 's4') {
    $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
    // compruebo si existe el escrito de referencia (sólo el primero, ordeno por anulado).
    // en entradas:
    $gesEntradas = new GestorEntrada();       //$aProt_orgigen = ['id_lugar', 'num', 'any', 'mas']
    $aProt_origen = [ 'lugar' => $Qid_lugar,
                    'num' => $Qprot_num, 
                    'any' => $Qprot_any,
                    'mas' => '',
                ];
    // No buscar los anulados:
	$aWhere = ['bypass' => 'f', 'anulado' => 'x'];
    $aOperador = ['anulado' => 'IS NULL'];
    $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhere,$aOperador);
    
    foreach($cEntradas as $oEntrada) {
        $id_entrada = $oEntrada->getId_entrada();
        $id_reg = 'REN'.$id_entrada; // REN = Regitro Entrada
        $id_of_ponente = $oEntrada->getPonente();
        // para crear un pendiente, no pongo 'reservado'
        $asunto = $oEntrada->getAsuntoDB();
        $detalle = $oEntrada->getDetalle();
        $visibilidad = $oEntrada->getVisibilidad();
        // El estado de la entrada no tiene nada que ver con el del pendiente
        // $oEntrada->getEstado();
        $anulado = $oEntrada->getAnulado();
        $resto_oficinas = $oEntrada->getResto_oficinas();
        $oficinas_txt = implode(' ', $resto_oficinas);
    }
    $jsondata['id_reg'] = $id_reg;
}


$jsondata["que"] = $Qque;
$jsondata["rango"] = "$aviso_rango"; 
$jsondata["repe"] = "$aviso_repe";
$jsondata["registrado"] = "$aviso_origen";
$jsondata["aprobado"] = "$aviso_aprobado";
$jsondata["txt"] = "$aviso_txt";
$jsondata["error"] = "$error_txt";
$jsondata["salto"] = "$aviso_salto";
$jsondata["any"] ="$aviso_any";
$jsondata["id_of_ponente"] ="$id_of_ponente";
$jsondata["asunto"] = "".str_replace('"','\"',$asunto)."";
$jsondata["detalle"] = "".str_replace('"','\"',$detalle)."";
$jsondata["visibilidad"] = "$visibilidad";
$jsondata["anulado"] =  "".str_replace('"','\"',$anulado)."";
$jsondata["oficinas"] = "$oficinas_txt";
$jsondata["destino"] = "$dest_id_lugar[0]";

if (empty($txt_err)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();

