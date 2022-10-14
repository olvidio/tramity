<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entidades\model\Entidad;
use entidades\model\entity\EntidadDB;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_refresh = (integer)filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Q_refresh);

$Q_id_entidad = (integer)filter_input(INPUT_POST, 'id_entidad');

$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack != '') {
        // No me sirve el de global_object, sino el de la session
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $a_sel = $oPosicion2->getParametro('id_sel');
            if (!empty($a_sel)) {
                $Q_id_entidad = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_entidad = $oPosicion2->getParametro('id_entidad');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    if ($Q_que != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
        $Q_id_entidad = (integer)strtok($a_sel[0], "#");
        // el scroll id es de la página anterior, hay que guardarlo allí
        $oPosicion->addParametro('id_sel', $a_sel, 1);
        $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
        $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
    }
}
$oPosicion->setParametros(array('id_entidad' => $Q_id_entidad), 1);

$oEntidad = new Entidad(); // para los tipos
$a_opciones_tipos = $oEntidad->getArrayTipo();
$oDesplTipos = new Desplegable();
$oDesplTipos->setNombre('tipo_entidad');
$oDesplTipos->setOpciones($a_opciones_tipos);

if (!empty($Q_id_entidad)) {
    $que_user = 'guardar';
    $oEntidadDB = new EntidadDB(array('id_entidad' => $Q_id_entidad));

    $nombre = $oEntidadDB->getnombre();
    $schema = $oEntidadDB->getSchema();
    $tipo = $oEntidadDB->getTipo();
    $oDesplTipos->setOpcion_sel($tipo);
    $anulado = $oEntidadDB->isAnulado();
    $chk_anulado = ($anulado === TRUE) ? 'checked' : '';

} else {
    $que_user = 'nuevo';
    $nombre = '';
    $schema = '';
    $tipo = '';
    $anulado = '';
    $chk_anulado = '';
}


$camposForm = 'que!nombre!schema!tipo_entidad!anulado';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$a_camposHidden = array(
    'id_entidad' => $Q_id_entidad,
    'que' => $que_user,
);
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = ConfigGlobal::getWeb() . '/apps/entidades/controller/entidad_update.php';

$txt_guardar = _("guardar datos entidad");

$pagina_cancel = web\Hash::link('apps/entidades/controller/entidad_lista.php?' . http_build_query([]));

$a_campos = [
    'oPosicion' => $oPosicion,
    'url_update' => $url_update,
    'id_entidad' => $Q_id_entidad,
    'nombre' => $nombre,
    'oHash' => $oHash,
    'schema' => $schema,
    'oDesplTipos' => $oDesplTipos,
    'chk_anulado' => $chk_anulado,
    'txt_guardar' => $txt_guardar,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('entidades/controller');
echo $oView->renderizar('entidad_form.html.twig', $a_campos);
