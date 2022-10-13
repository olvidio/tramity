<?php

use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use lugares\model\entity\GestorLugar;
use oasis_as4\model\As4CollaborationInfo;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Qaccion = (string)\filter_input(INPUT_POST, 'accion');
$Qmas = (integer)\filter_input(INPUT_POST, 'mas');
$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');

$gesLugares = new GestorLugar();
$id_sigla_local = $gesLugares->getId_sigla_local();

$filtro = empty($Qfiltro) ? 'mantenimiento' : $Qfiltro;
$Qmas = '';
$Qopcion = 7;
$a_condicion = []; // para poner los parámetros de la búsqueda y poder actualizar la página.
$a_condicion['opcion'] = $Qopcion;
$a_condicion['accion'] = $Qaccion;

// un protocolo concreto:
$Qid_lugar = (integer)\filter_input(INPUT_POST, 'id_lugar');
$Qprot_num = (integer)\filter_input(INPUT_POST, 'prot_num');
$Qprot_any = (string)\filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

$Qprot_any = core\any_2($Qprot_any);

$a_condicion['id_lugar'] = $Qid_lugar;
$a_condicion['prot_num'] = $Qprot_num;
$a_condicion['prot_any'] = $Qprot_any;
$str_condicion = http_build_query($a_condicion);

$oBuscar = new Buscar();
$oBuscar->setId_sigla($id_sigla_local);
$oBuscar->setId_lugar($Qid_lugar);
$oBuscar->setProt_num($Qprot_num);
$oBuscar->setProt_any($Qprot_any);

$aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
foreach ($aCollection as $key => $cCollection) {
    if ($Qaccion == As4CollaborationInfo::ACCION_ORDEN_ANULAR) {
        $a_botones = [
            ['txt' => _('enviar orden anular'), 'click' => "fnjs_orden_a_plataforma(\"#$key\",\"$Qaccion\")"],
        ];
    }
    if ($Qaccion == As4CollaborationInfo::ACCION_REEMPLAZAR) {
        $a_botones = [
            ['txt' => _('enviar orden reemplazar'), 'click' => "fnjs_orden_a_plataforma(\"#$key\",\"$Qaccion\")"],
        ];
    }

    $oTabla = new VerTabla();
    $oTabla->setKey($key);
    $oTabla->setCondicion($str_condicion);
    $oTabla->setCollection($cCollection);
    $oTabla->setFiltro($filtro);
    $oTabla->setBotones($a_botones);
    $oTabla->setDataTable_options_dom('rt');

    echo $oTabla->mostrarTabla();
}