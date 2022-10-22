<?php

use core\ViewTwig;
use tramites\model\entity\GestorTramite;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
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

$aWhere = ['_ordre' => 'orden'];
$aOperador = array();

$oGesTramites = new GestorTramite();
$cTramites = $oGesTramites->getTramites($aWhere, $aOperador);

//default:
$id_tramite = '';
$tramite = '';
$orden = '';
$permiso = 1;

$a_cabeceras = array('tramite', 'orden', 'abrev.', array('name' => 'accion', 'formatter' => 'clickFormatter'));
$a_botones[] = array('txt' => _("borrar"), 'click' => "fnjs_eliminar()");

$a_valores = array();
$i = 0;
foreach ($cTramites as $oTramite) {
    $i++;
    $id_tramite = $oTramite->getId_tramite();
    $tramite = $oTramite->getTramite();
    $orden = $oTramite->getOrden();
    $breve = $oTramite->getBreve();

    $pagina = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/tramites/controller/tramite_form.php?' . http_build_query(array('quien' => 'tramite', 'id_tramite' => $id_tramite)));

    $a_valores[$i]['sel'] = "$id_tramite#";
    $a_valores[$i][1] = $tramite;
    $a_valores[$i][2] = $orden;
    $a_valores[$i][3] = $breve;
    $a_valores[$i][4] = array('ira' => $pagina, 'valor' => 'editar');
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('tramite_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('scroll_id');
$oHash->setArraycamposHidden(array('que' => 'eliminar'));

$aQuery = ['nuevo' => 1, 'quien' => 'tramite'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/tramites/controller/tramite_form.php?' . http_build_query($aQuery));
$url_ajax = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/tramites/controller/tramite_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/tramites/controller/tramite_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'permiso' => $permiso,
    'url_nuevo' => $url_nuevo,
    'url_ajax' => $url_ajax,
    'url_actualizar' => $url_actualizar,
];
$oView = new ViewTwig('tramites/controller');
$oView->renderizar('tramite_lista.html.twig', $a_campos);

