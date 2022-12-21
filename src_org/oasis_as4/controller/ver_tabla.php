<?php

use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use lugares\domain\repositories\LugarRepository;
use oasis_as4\model\As4CollaborationInfo;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_accion = (string)filter_input(INPUT_POST, 'accion');
$Q_mas = (integer)filter_input(INPUT_POST, 'mas');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$LugarRepository = new LugarRepository();
$id_sigla_local = $LugarRepository->getId_sigla_local();

$filtro = empty($Q_filtro) ? 'mantenimiento' : $Q_filtro;
$Q_mas = '';
$Q_opcion = 7;
$a_condicion = []; // para poner los parámetros de la búsqueda y poder actualizar la página.
$a_condicion['opcion'] = $Q_opcion;
$a_condicion['accion'] = $Q_accion;

// un protocolo concreto:
$Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
$Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

$Q_prot_any = core\any_2($Q_prot_any);

$a_condicion['id_lugar'] = $Q_id_lugar;
$a_condicion['prot_num'] = $Q_prot_num;
$a_condicion['prot_any'] = $Q_prot_any;
$str_condicion = http_build_query($a_condicion);

$oBuscar = new Buscar();
$oBuscar->setId_sigla($id_sigla_local);
$oBuscar->setId_lugar($Q_id_lugar);
$oBuscar->setProt_num($Q_prot_num);
$oBuscar->setProt_any($Q_prot_any);

$aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);
foreach ($aCollection as $key => $cCollection) {
    if ($Q_accion === As4CollaborationInfo::ACCION_ORDEN_ANULAR) {
        $a_botones = [
            ['txt' => _('enviar orden anular'), 'click' => "fnjs_orden_a_plataforma(\"#$key\",\"$Q_accion\")"],
        ];
    }
    if ($Q_accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
        $a_botones = [
            ['txt' => _('enviar orden reemplazar'), 'click' => "fnjs_orden_a_plataforma(\"#$key\",\"$Q_accion\")"],
        ];
    }

    $oTabla = new VerTabla();
    $oTabla->setKey($key);
    $oTabla->setCondicion($str_condicion);
    $oTabla->setCollection($cCollection);
    $oTabla->setFiltro($filtro);
    $oTabla->setBotones($a_botones);
    $oTabla->setDataTable_options_dom('rt');

    $oTabla->mostrarTabla();
}