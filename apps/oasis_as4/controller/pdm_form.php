<?php
use core\ConfigGlobal;
use core\ViewTwig;
use oasis_as4\model\Pmode;
use web\Desplegable;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qfilename = '';
$Qplataforma = '';
$Qservidor = '';
$Qaccion = '';

$oPmode = new Pmode(); // para los tipos
$a_opciones_accion = $oPmode->getArrayAccion();
$oDesplAcciones = new Desplegable();
$oDesplAcciones->setNombre('accion');
$oDesplAcciones->setOpciones($a_opciones_accion);

// Solo se puede nuevo. Para modificar: borrar y crear
$camposForm = 'plataforma!servidor!accion';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$a_camposHidden = array(
		'que' => '',
		'filename' => $Qfilename,
);
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = ConfigGlobal::getWeb().'/apps/oasis_as4/controller/pdm_update.php';
$pagina_cancel = Hash::link('apps/oasis_as4/controller/pdm_lista.php');

$a_campos = [
		'oPosicion' => $oPosicion,
		'oHash' => $oHash,
		'url_update' => $url_update,
		'pagina_cancel' => $pagina_cancel,
		'filename' => $Qfilename,
		'plataforma' => $Qplataforma,
		'servidor' => $Qservidor,
		'oDesplAcciones' => $oDesplAcciones,
];

$oView = new ViewTwig('oasis_as4/controller');
echo $oView->renderizar('pdm_form.html.twig',$a_campos);
