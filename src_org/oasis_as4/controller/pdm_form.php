<?php

use core\ConfigGlobal;
use core\ViewTwig;
use oasis_as4\model\Pmode;
use web\Desplegable;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Q_filename = '';
$Q_plataforma = '';
$Q_servidor = '';
$Q_accion = '';

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
    'filename' => $Q_filename,
);
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = ConfigGlobal::getWeb() . '/src/oasis_as4/controller/pdm_update.php';
$pagina_cancel = Hash::link('src/oasis_as4/controller/pdm_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'filename' => $Q_filename,
    'plataforma' => $Q_plataforma,
    'servidor' => $Q_servidor,
    'oDesplAcciones' => $oDesplAcciones,
];

$oView = new ViewTwig('oasis_as4/controller');
$oView->renderizar('pdm_form.html.twig', $a_campos);
