<?php

use core\ConfigGlobal;
use core\ViewTwig;
use escritos\model\Escrito;
use plantillas\model\entity\GestorPlantilla;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// si vengo del expediente, para buscar una:
$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$gesPlantillas = new GestorPlantilla();
$aWhere = [];
$cPlantillas = $gesPlantillas->getPlantillas($aWhere);

$a_botones = [];
$a_cabeceras = [_("ver"), _("nombre"), _("adjuntar")];

$i = 0;
$a_valores = [];
foreach ($cPlantillas as $oPlantilla) {
    $i++;
    $id_plantilla = $oPlantilla->getId_plantilla();
    $nombre = $oPlantilla->getNombre();

    $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_plantilla('$id_plantilla');\" >";
    $ver .= _("ver");
    $ver .= "</span>";
    $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_plantilla('$id_plantilla','$Q_id_expediente');\" >";
    $add .= _("adjuntar");
    $add .= "</span>";
    $a_valores[$i][1] = $ver;
    $a_valores[$i][2] = $nombre;
    $a_valores[$i][3] = $add;
}

$oTabla = new Lista();
$oTabla->setId_tabla('plantillas');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$a_cosas = [
    'id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
    'modo' => $Q_modo,
];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
$url_update = 'apps/plantillas/controller/plantilla_update.php';

$titulo = _("Plantillas de la Delegación");
$server = ConfigGlobal::getWeb(); //http://tramity.local

$a_campos = [
    'titulo' => $titulo,
    'oTabla' => $oTabla,
    //'oHash' => $oHash,
    'server' => $server,
    'accion' => Escrito::ACCION_PLANTILLA,
    'pagina_cancel' => $pagina_cancel,
    'url_update' => $url_update,
];

$oView = new ViewTwig('plantillas/controller');
$oView->renderizar('plantilla_lista_expediente.html.twig', $a_campos);