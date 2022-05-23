<?php
use core\ViewTwig;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

// Anular escrito
$num_orden = 10;
$text = _("anular en otras plataformas");
$explicacion =  _("Envia una orden de anulación para un escrito en otra plataforma");

$active = ''; // no sé si tiene sentido que sea 'active'
$aQuery = [ 'filtro' => $Qfiltro,
		'accion' => 'anular',
];
$pag_lst = web\Hash::link('apps/oasis_as4/controller/buscar_escrito.php?'.http_build_query($aQuery));

$pill = [ 'orden'=> $num_orden,
		'text' => $text,
		'pag_lst' => $pag_lst,
		'active' => $active,
		'class' => 'btn-expediente',
		'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;


// nueva versión escrito
$num_orden = 20;
$text = _("nuevo en otras plataformas");
$explicacion =  _("Envia una orden de  un escrito en otra plataforma");

$active = ''; // no sé si tiene sentido que sea 'active'
$aQuery = [ 'filtro' => $Qfiltro,
		'accion' => 'nv',
];
$pag_lst = web\Hash::link('apps/escritos/controller/anular.php?'.http_build_query($aQuery));

$pill = [ 'orden'=> $num_orden,
		'text' => $text,
		'pag_lst' => $pag_lst,
		'active' => $active,
		'class' => 'btn-expediente',
		'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;




$a_campos = [ 'filtro' => $Qfiltro,
		'btn_cerrar' => TRUE,
		'a_pills' => $a_pills,
	];

$oView = new ViewTwig('escritos/controller');
echo $oView->renderizar('mantenimiento.html.twig',$a_campos);