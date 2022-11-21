<?php

use core\ViewTwig;
use entidades\model\Entidad;
use entidades\model\entity\GestorEntidadesDB;
use function core\is_true;

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
    if ($stack !== '') {
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $Q_id_sel = $oPosicion2->getParametro('id_sel');
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
}

$aWhere['_ordre'] = 'nombre';
$aOperador = [];

$oGesEntidadesDB = new GestorEntidadesDB();
$cEntidadDBes = $oGesEntidadesDB->getEntidadesDB($aWhere, $aOperador);

//default:
$id_entidad = '';
$schema = '';
$nombre = '';
$tipo = '';
$anulado = 1;

$a_cabeceras = ['nombre', 'nombre esquema', 'tipo_entidad', 'anulado'];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$oEntidad = new Entidad();
$a_opciones_tipo = $oEntidad->getArrayTipo();
$a_valores = array();
$i = 0;
foreach ($cEntidadDBes as $oEntidadDB) {
    $i++;
    $id_entidad = $oEntidadDB->getId_entidad();
    $nombre = $oEntidadDB->getNombre();
    $schema = $oEntidadDB->getSchema();
    $anulado = $oEntidadDB->isAnulado();
    $anulado_txt = is_true($anulado) ? _("Sí") : '';
    $tipo = $oEntidadDB->getTipo();
    $tipo_txt = $a_opciones_tipo[$tipo];

    $a_valores[$i]['sel'] = "$id_entidad#";
    $a_valores[$i][1] = $nombre;
    $a_valores[$i][2] = $schema;
    $a_valores[$i][3] = $tipo_txt;
    $a_valores[$i][5] = $anulado_txt;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('entidad_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$aQuery = ['nuevo' => 1];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/entidades/controller/entidad_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/entidades/controller/entidad_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/entidades/controller/entidad_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/entidades/controller/entidad_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('entidades/controller');
$oView->renderizar('entidades_lista.html.twig', $a_campos);

