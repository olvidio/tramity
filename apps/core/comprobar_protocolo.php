<?php

use entradas\model\GestorEntrada;
use function core\any_2;

// INICIO Cabecera global de URL de controlador *********************************


require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

$id_reg = '';
$aviso_rango = '';
$aviso_repe = '';
$aviso_origen = '';
$aviso_aprobado = '';
$aviso_txt = '';
$error_txt = '';
$aviso_salto = '';
$aviso_any = '';
$id_of_ponente = '';
$asunto = '';
$detalle = '';
$visibilidad = '';
$anulado = '';
$oficinas_txt = '';
$dest_id_lugar[0] = '';

$txt_err = '';
$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
$Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');

// compruebo el año (actual o -1)
$Q_prot_any = any_2($Q_prot_any);
$any = date('y'); //A two digit representation of a year (Examples: 99 or 03)
$any_anterior = any_2(date('Y') - 1);
if (($Q_prot_any !== $any) && ($Q_prot_any !== $any_anterior)) {
    $aviso_any = 1;
}

if ($Q_que === 's4') {
    // compruebo si existe el escrito de referencia (sólo el primero, ordeno por anulado).
    // en entradas:
    $gesEntradas = new GestorEntrada();       //$aProt_origen = ['id_lugar', 'num', 'any', 'mas']
    $aProt_origen = ['id_lugar' => $Q_id_lugar,
        'num' => $Q_prot_num,
        'any' => $Q_prot_any,
        'mas' => '',
    ];
    // No buscar los anulados:
    $aWhere = ['bypass' => 'f', 'anulado' => 'x'];
    $aOperador = ['anulado' => 'IS NULL'];
    $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhere, $aOperador);

    foreach ($cEntradas as $oEntrada) {
        $id_entrada = $oEntrada->getId_entrada();
        $id_reg = 'REN' . $id_entrada; // REN = Registro Entrada
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

if ($Q_que === 'entrada') {
    // compruebo si está repetido
    $gesEntradas = new GestorEntrada();       //$aProt_origen = ['id_lugar', 'num', 'any', 'mas']
    $aProt_origen = ['id_lugar' => $Q_id_lugar,
        'num' => $Q_prot_num,
        'any' => $Q_prot_any,
        'mas' => '',
    ];
    // No buscar los anulados:
    $aWhere = ['bypass' => 'f', 'anulado' => 'x'];
    $aOperador = ['anulado' => 'IS NULL'];
    $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhere, $aOperador);
    if (count($cEntradas) > 0) {
        $prot_num = "";
        $aviso_repe = 1;
    }
}


$jsondata["que"] = $Q_que;
$jsondata["rango"] = "$aviso_rango";
$jsondata["repe"] = "$aviso_repe";
$jsondata["registrado"] = "$aviso_origen";
$jsondata["aprobado"] = "$aviso_aprobado";
$jsondata["txt"] = "$aviso_txt";
$jsondata["error"] = "$error_txt";
$jsondata["salto"] = "$aviso_salto";
$jsondata["any"] = "$aviso_any";
$jsondata["id_of_ponente"] = "$id_of_ponente";
$jsondata["asunto"] = "" . str_replace('"', '\"', $asunto) . "";
$jsondata["detalle"] = "" . str_replace('"', '\"', $detalle) . "";
$jsondata["visibilidad"] = "$visibilidad";
$jsondata["anulado"] = "" . str_replace('"', '\"', $anulado) . "";
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

