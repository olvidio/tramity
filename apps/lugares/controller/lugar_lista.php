<?php

use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();


$Q_id_sel = (string)filter_input(INPUT_POST, 'id_sel');
$Q_scroll_id = (string)filter_input(INPUT_POST, 'scroll_id');

//Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack != '') {
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $Q_id_sel = $oPosicion2->getParametro('id_sel');
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
}


//$oPosicion->setParametros(array('username'=>$Q_username),1);

$aWhere['_ordre'] = 'sigla';
$aOperador = [];

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
$autorizacion = '';

$a_cabeceras = [_("sigla"),
    _("dl"),
    _("región"),
    _("nombre"),
    _("tipo_ctr"),
    _("modo envío"),
    _("plataforma/e-mail/autorización"),
    _("anulado")
];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
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
    $autorizacion = $oLugar->getAutorizacion();

    switch ($modo_envio) {
        case Lugar::MODO_AS4:
            $donde = $plataforma;
            break;
        case Lugar::MODO_ODT:
        case Lugar::MODO_DOCX:
        case Lugar::MODO_PDF:
            $donde = $e_mail;
            break;
        case Lugar::MODO_RDP:
            $donde = $autorizacion;
            break;
        default:
            $donde = '?';
    }
    $a_valores[$i]['sel'] = "$id_lugar#";
    $a_valores[$i][1] = $sigla;
    $a_valores[$i][2] = $dl;
    $a_valores[$i][3] = $region;
    $a_valores[$i][4] = $nombre;
    $a_valores[$i][5] = $tipo_ctr;
    $a_valores[$i][7] = empty($a_modos_envio[$modo_envio])? '?' :$a_modos_envio[$modo_envio];
    $a_valores[$i][8] = $donde;
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

$aQuery = ['nuevo' => 1, 'quien' => 'lugar'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/lugar_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/lugar_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/lugar_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/lugar_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('lugares/controller');
$oView->renderizar('lugar_lista.html.twig', $a_campos);
