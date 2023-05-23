<?php

// INICIO Cabecera global de URL de controlador *********************************

use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

$aWhere['modo_envio'] = Lugar::MODO_AS4;
$aWhere['tipo_ctr'] = 'ok';
$aWhere['_ordre'] = 'sigla';
$aOperador['tipo_ctr'] = '!=';

$oLugar = new Lugar();
$a_modos_envio = $oLugar->getArrayModoEnvio();

//default:
$id_lugar = '';
$sigla = '';
$dl = '';
$region = '';
$nombre = '';
$tipo_ctr = '';
$plataforma = '';
$e_mail = '';
$anulado = '';

$a_cabeceras = [_("sigla"),
    _("dl"),
    _("región"),
    _("nombre"),
    _("tipo_ctr"),
    _("e_mail"),
    _("modo envío"),
    _("plataforma"),
    _("anulado")
];
$a_botones = [
    ['txt' => _("seleccionar"), 'click' => "fnjs_seleccionar()"],
];

$oGesLugares = new GestorLugar();
$cLugares = $oGesLugares->getLugares($aWhere, $aOperador);
$a_valores = [];
$i = 0;
foreach ($cLugares as $oLugar) {
    $i++;
    $id_lugar = $oLugar->getId_lugar();
    $sigla = $oLugar->getSigla();
    $dl = $oLugar->getDl();
    $region = $oLugar->getRegion();
    $nombre = $oLugar->getNombre();
    $tipo_ctr = $oLugar->getTipo_ctr();
    $plataforma = $oLugar->getPlataforma();
    $e_mail = $oLugar->getE_mail();
    $modo_envio = $oLugar->getModo_envio();
    $anulado = $oLugar->getAnulado();

    $a_valores[$i]['sel'] = "$id_lugar#";
    $a_valores[$i][1] = $sigla;
    $a_valores[$i][2] = $dl;
    $a_valores[$i][3] = $region;
    $a_valores[$i][4] = $nombre;
    $a_valores[$i][5] = $tipo_ctr;
    $a_valores[$i][6] = $e_mail;
    $a_valores[$i][7] = $a_modos_envio[$modo_envio];
    $a_valores[$i][8] = $plataforma;
    $a_valores[$i][9] = $anulado;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('lugar_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/migration_ctr/controller/migration_ctr_index.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'url_form' => $url_form,
];

$oView = new ViewTwig('migration_ctr/controller');
$oView->renderizar('lugar_ctr.html.twig', $a_campos);