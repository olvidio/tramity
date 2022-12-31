<?php

use core\ConfigGlobal;
use core\ViewTwig;
use plantillas\model\entity\GestorPlantilla;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$gesPlantillas = new GestorPlantilla();
$aWhere = [];
$cPlantillas = $gesPlantillas->getPlantillas($aWhere);

$a_botones = [['txt' => _('cambiar nombre'), 'click' => "fnjs_datos_plantilla()"],
    ['txt' => _('eliminar'), 'click' => "fnjs_eliminar_plantilla()"],
];

$a_cabeceras = [_("mod"), _("nombre"),];

$i = 0;
$a_valores = [];
foreach ($cPlantillas as $oPlantilla) {
    $i++;
    $id_plantilla = $oPlantilla->getId_plantilla();
    $nombre = $oPlantilla->getNombre();

    $a_valores[$i]['sel'] = "$id_plantilla";
    $mod = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_plantilla('$id_plantilla');\" >";
    $mod .= _("modificar");
    $mod .= "</span>";
    $a_valores[$i][1] = $mod;
    $a_valores[$i][2] = $nombre;
}

$oTabla = new Lista();
$oTabla->setId_tabla('plantillas');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$aQuery = ['nuevo' => 1, 'quien' => 'plantilla'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/plantillas/controller/plantilla_form.php?' . http_build_query($aQuery));
$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/plantillas/controller/plantilla_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/plantillas/controller/plantilla_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/plantillas/controller/plantilla_lista.php');

$titulo = _("Plantillas de la Delegación");
$server = ConfigGlobal::getWeb(); //http://tramity.local

$a_campos = [
    'titulo' => $titulo,
    'oEntradaLista' => $oTabla,
    //'oHash' => $oHash,
    'filtro' => $Q_filtro,
    'server' => $server,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('plantillas/controller');
$oView->renderizar('plantilla_lista.html.twig', $a_campos);