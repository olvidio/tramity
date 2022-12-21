<?php

use core\ViewTwig;
use usuarios\domain\repositories\CargoGrupoRepository;
use usuarios\domain\repositories\CargoRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();


$Q_id_sel = (string)filter_input(INPUT_POST, 'id_sel');
$Q_scroll_id = (string)filter_input(INPUT_POST, 'scroll_id');

//Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack !== '') {
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $Q_id_sel = $oPosicion2->getParametro('id_sel');
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
}

$aWhere['_ordre'] = 'descripcion';
$aOperador = [];

$CargoGrupoRepository = new CargoGrupoRepository();
$cGrupos = $CargoGrupoRepository->getCargoGrupos($aWhere, $aOperador);

//default:
$descripcion = '';
$a_miembros = [];

$a_cabeceras = [
    ['name' => _("cargo ref"), 'width' => 80],
    ['name' => _("descripción"), 'width' => 100],
    ['name' => _("miembros"), 'width' => 250],
];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$CargoRepository = new CargoRepository();
$a_posibles_cargos = $CargoRepository->getArrayCargos();
$a_posibles_cargos_ref = $CargoRepository->getArrayCargosRef();
$a_valores = array();
$i = 0;
foreach ($cGrupos as $oGrupo) {
    $i++;
    $id_grupo = $oGrupo->getId_grupo();
    $id_cargo_ref = $oGrupo->getId_cargo_ref();
    $cargo_ref_txt = $a_posibles_cargos_ref[$id_cargo_ref];
    $descripcion = $oGrupo->getDescripcion();
    $a_miembros = $oGrupo->getMiembros();
    $miembros_txt = '';
    foreach ($a_miembros as $id_cargo) {
        $miembros_txt .= empty($miembros_txt) ? '' : ',';
        $miembros_txt .= $a_posibles_cargos[$id_cargo];
    }

    $a_valores[$i]['sel'] = "$id_grupo#";
    $a_valores[$i][1] = $cargo_ref_txt;
    $a_valores[$i][2] = $descripcion;
    $a_valores[$i][3] = $miembros_txt;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('cargo_grupo_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$aQuery = ['nuevo' => 1, 'quien' => 'grupo'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/src/usuarios/controller/cargo_grupo_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/src/usuarios/controller/cargo_grupo_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/src/usuarios/controller/cargo_grupo_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/src/usuarios/controller/cargo_grupo_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('usuarios/controller');
$oView->renderizar('cargo_grupo_lista.html.twig', $a_campos);

