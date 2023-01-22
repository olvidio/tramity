<?php

use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use oasis_as4\model\As4CollaborationInfo;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

// Anular escrito
$num_orden = 10;
$text = _("anular en otras plataformas");
$explicacion = _("Envía una orden de anulación para un escrito en otra plataforma");

$active = ''; // no sé si tiene sentido que sea 'active'
$aQuery = ['filtro' => $Q_filtro,
    'accion' => As4CollaborationInfo::ACCION_ORDEN_ANULAR,
];
$pag_lst = web\Hash::link('apps/oasis_as4/controller/buscar_escrito.php?' . http_build_query($aQuery));

$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;


// nueva versión escrito
$num_orden = 20;
$text = _("reemplazar en otras plataformas");
$explicacion = _("Envía un escrito que reemplaza a otro en otra plataforma");

$active = ''; // no sé si tiene sentido que sea 'active'
$aQuery = ['filtro' => $Q_filtro,
    'accion' => As4CollaborationInfo::ACCION_REEMPLAZAR,
];
$pag_lst = web\Hash::link('apps/oasis_as4/controller/buscar_escrito.php?' . http_build_query($aQuery));

$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

$gesLugares = new GestorLugar();
$a_plataformas = $gesLugares->getPlataformas();

$plataforma_mantenimiento = $_SESSION['oConfig']->getPlataformaMantenimiento();

$oDesplPlataformas = new Desplegable();
$oDesplPlataformas->setNombre('plataforma');
$oDesplPlataformas->setOpciones($a_plataformas);
$oDesplPlataformas->setBlanco(TRUE);
$oDesplPlataformas->setAction("fnjs_guardar_plataforma()");
$oDesplPlataformas->setOpcion_sel($plataforma_mantenimiento);

$url_ajax = 'apps/escritos/controller/mantenimiento_ajax.php';

$a_campos = ['filtro' => $Q_filtro,
    'btn_cerrar' => TRUE,
    'a_pills' => $a_pills,
    'oDesplPlataformas' => $oDesplPlataformas,
    'url_ajax' => $url_ajax,
];

$oView = new ViewTwig('escritos/controller');
$oView->renderizar('mantenimiento.html.twig', $a_campos);