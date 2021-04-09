<?php

use core\ConfigGlobal;
use core\ViewTwig;
use plantillas\model\entity\GestorPlantilla;
use web\Lista;
use expedientes\model\Escrito;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// si vengo del expediente, para buscar una:
$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

$gesPlantillas = new GestorPlantilla();
$aWhere = [];
$cPlantillas = $gesPlantillas->getPlantillas($aWhere);

$a_botones = [];
$a_cabeceras = [ _("ver"), _("nombre"), _("adjuntar") ];

$i=0;
$a_valores = [];
foreach ($cPlantillas as $oPlantilla) {
    $i++;
    $id_plantilla = $oPlantilla->getId_plantilla();
    $nombre = $oPlantilla->getNombre();
    
    $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_plantilla('$id_plantilla');\" >";
    $ver .= _("ver");
    $ver .= "</span>";
    $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_plantilla('$id_plantilla','$Qid_expediente');\" >";
    $add .= _("adjuntar");
    $add .= "</span>";
    $a_valores[$i][1]=$ver;
    $a_valores[$i][2]=$nombre;
    $a_valores[$i][3]=$add;
}

$oTabla = new Lista();
$oTabla->setId_tabla('plantillas');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$a_cosas = [
    'id_expediente' => $Qid_expediente,
    'filtro' => $Qfiltro,
    'modo' => $Qmodo,
];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
$url_update = 'apps/plantillas/controller/plantilla_update.php';

$titulo=_("Plantillas de la Delegación");
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
echo $oView->renderizar('plantilla_lista_expediente.html.twig',$a_campos);