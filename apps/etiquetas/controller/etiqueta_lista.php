<?php

use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
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
    if ($stack != '') {
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $Q_id_sel = $oPosicion2->getParametro('id_sel');
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
}

$gesEtiquetas = new GestorEtiqueta();
// Etiquetas personales + Etiquetas de la oficina
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();

//default:
$id_etiqueta = '';
$nom_etiqueta = '';

$a_cabeceras = [['name' => _("etiqueta"), 'width' => 800],
    ['name' => _("entorno"), 'width' => 100],
    ['name' => _("cargo"), 'width' => 50]];

$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$gesCargos = new GestorCargo();
$a_cargos = $gesCargos->getArrayCargos(TRUE);
$gesOficinas = new GestorOficina();
$a_oficinas = $gesOficinas->getArrayOficinas();
$a_valores = array();
$i = 0;
foreach ($cEtiquetas as $oEtiqueta) {
    $i++;
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $id_cargo = $oEtiqueta->getId_cargo();
    $oficina = $oEtiqueta->getOficina();
    if (is_true($oficina)) {
        $oficina_txt = _("de la oficina");
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $oficina_txt = _("del centro");
        }
        $cargo_txt = empty($a_oficinas[$id_cargo]) ? $id_cargo : $a_oficinas[$id_cargo];
    } else {
        $oficina_txt = _("personal");
        $cargo_txt = empty($a_cargos[$id_cargo]) ? $id_cargo : $a_cargos[$id_cargo];
    }

    $a_valores[$i]['sel'] = "$id_etiqueta#";
    $a_valores[$i][1] = $nom_etiqueta;
    $a_valores[$i][2] = $oficina_txt;
    $a_valores[$i][3] = $cargo_txt;
}
if (isset($Q_id_sel) && !empty($Q_id_sel)) {
    $a_valores['select'] = $Q_id_sel;
}
if (isset($Q_scroll_id) && !empty($Q_scroll_id)) {
    $a_valores['scroll_id'] = $Q_scroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('etiqueta_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$aQuery = ['nuevo' => 1, 'quien' => 'etiqueta'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/etiquetas/controller/etiqueta_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/etiquetas/controller/etiqueta_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/etiquetas/controller/etiqueta_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/etiquetas/controller/etiqueta_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oExpedienteLista' => $oTabla,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('etiquetas/controller');
$oView->renderizar('etiqueta_lista.html.twig', $a_campos);

