<?php

use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayBusquedas();

$oDesplLugar_org = new Desplegable();
$oDesplLugar_org->setNombre('id_lugar_org');
$oDesplLugar_org->setBlanco(TRUE);
$oDesplLugar_org->setOpciones($a_posibles_lugares);

$oDesplLugar_dst = new Desplegable();
$oDesplLugar_dst->setNombre('id_lugar_dst');
$oDesplLugar_dst->setBlanco(TRUE);
$oDesplLugar_dst->setOpciones($a_posibles_lugares);

$prot_num_org = '';
$prot_any_org = '';

$a_cosas = [
    'filtro' => 'pendientes',
    'periodo' => 'hoy',
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?' . http_build_query($a_cosas));
$url_update = 'apps/pendientes/controller/pendiente_update_migrar.php';


$a_campos = [
    'calendario' => 'registro',
    'oDesplLugar_org' => $oDesplLugar_org,
    'prot_num_org' => $prot_num_org,
    'prot_any_org' => $prot_any_org,
    'oDesplLugar_dst' => $oDesplLugar_dst,
    'pagina_cancel' => $pagina_cancel,
    'url_update' => $url_update,
];

$oView = new ViewTwig('pendientes/controller');
$oView->renderizar('pendiente_migrar.html.twig', $a_campos);
