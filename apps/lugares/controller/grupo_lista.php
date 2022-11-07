<?php

use core\ViewTwig;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;

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

$aWhere['_ordre'] = 'descripcion';
$aOperador = [];

$gesGrupos = new GestorGrupo();
$cGrupos = $gesGrupos->getGrupos($aWhere, $aOperador);

//default:
$id_grupo = '';
$descripcion = '';
$a_miembros = [];

$a_cabeceras = [['name' => _("descripción"), 'width' => 30], _("miembros")];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
$a_valores = array();
$i = 0;
foreach ($cGrupos as $oGrupo) {
    $i++;
    $id_grupo = $oGrupo->getId_grupo();
    $descripcion = $oGrupo->getDescripcion();
    $a_miembros = $oGrupo->getMiembros();
    $miembros_txt = '';
    foreach ($a_miembros as $id_lugar) {
        $miembros_txt .= empty($miembros_txt) ? '' : ',';
        $miembros_txt .= empty($a_posibles_lugares[$id_lugar]) ? '?' : $a_posibles_lugares[$id_lugar];
    }

    $a_valores[$i]['sel'] = "$id_grupo#";
    $a_valores[$i][1] = $descripcion;
    $a_valores[$i][2] = $miembros_txt;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('grupo_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$aQuery = ['nuevo' => 1, 'quien' => 'grupo'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/grupo_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/grupo_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/grupo_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/lugares/controller/grupo_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oExpedienteLista' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('lugares/controller');
$oView->renderizar('grupo_lista.html.twig', $a_campos);

