<?php

use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Oficina;

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

$aWhere = array();
$aOperador = array();

// Segun la ubicación (config de la instalación)
$aWhere['id_ambito'] = $_SESSION['oConfig']->getAmbito();

$aWhere['_ordre'] = 'director DESC, cargo';

$oGesCargos = new GestorCargo();
$cCargos = $oGesCargos->getCargos($aWhere, $aOperador);

//default:
$id_cargo = '';
$cargo = '';
$descripcion = '';
$permiso = 1;

$a_cabeceras = array('cargo', 'descripcion', 'director', 'oficina', 'titular', 'suplente','activo');
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$a_valores = array();
$i = 0;
$gesUsuarios = new GestorUsuario();
$aUsuarios = $gesUsuarios->getArrayUsuarios();
foreach ($cCargos as $oCargo) {
    $i++;
    $id_cargo = $oCargo->getId_cargo();
    $cargo = $oCargo->getCargo();
    $descripcion = $oCargo->getDescripcion();
    $id_oficina = $oCargo->getId_oficina();
    $director = $oCargo->getDirector();
    $director_txt = ($director === TRUE) ? _("Sí") : _("No");
    $activo = $oCargo->getActivo();
    $activo_txt = ($activo === TRUE) ? _("Sí") : _("No");
    $id_usuario = $oCargo->getId_usuario();
    $id_suplente = $oCargo->getId_suplente();
    $usuario = empty($aUsuarios[$id_usuario]) ? '' : $aUsuarios[$id_usuario];
    $suplente = empty($aUsuarios[$id_suplente]) ? '' : $aUsuarios[$id_suplente];

    if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
        $oOficina = new Oficina($id_oficina);
        $sigla = $oOficina->getSigla();
    } else {
        $sigla = $_SESSION['oConfig']->getSigla();
    }

    $a_valores[$i]['sel'] = "$id_cargo#";
    $a_valores[$i][1] = $cargo;
    $a_valores[$i][2] = $descripcion;
    $a_valores[$i][3] = $director_txt;
    $a_valores[$i][4] = $sigla;
    $a_valores[$i][5] = $usuario;
    $a_valores[$i][6] = $suplente;
    $a_valores[$i][7] = $activo_txt;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('cargo_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('scroll_id');
$oHash->setArraycamposHidden(array('que' => 'eliminar'));

$aQuery = ['nuevo' => 1, 'quien' => 'cargo'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/cargo_form.php?' . http_build_query($aQuery));
$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/cargo_form.php');
$url_ajax = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/cargo_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/cargo_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'permiso' => $permiso,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_ajax' => $url_ajax,
    'url_actualizar' => $url_actualizar,
];
$oView = new ViewTwig('usuarios/controller');
$oView->renderizar('cargo_lista.html.twig', $a_campos);

