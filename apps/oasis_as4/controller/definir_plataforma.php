<?php

use config\model\entity\ConfigSchema;
use web\Hash;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$url = 'apps/config/controller/parametros_update.php';
$a_campos = ['url' => $url];


// ----------- Servidor de AS4 Deck -----------------
$parametro = 'nomdock';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_nomdock = $valor;

$oHashNomDock = new Hash();
$oHashNomDock->setUrl($url);
$oHashNomDock->setcamposForm('valor');
$oHashNomDock->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashNomDock'] = $oHashNomDock;
$a_campos['nomdock'] = $val_nomdock;

$parametro = 'dock';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_dock = $valor;

$oHashDock = new Hash();
$oHashDock->setUrl($url);
$oHashDock->setcamposForm('valor');
$oHashDock->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashDock'] = $oHashDock;
$a_campos['dock'] = $val_dock;


$oView = new core\ViewTwig('oasis_as4/controller');
$oView->renderizar('definir_plataforma.html.twig', $a_campos);