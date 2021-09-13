<?php
use config\model\entity\ConfigSchema;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorLocale;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
//	require_once ("classes/personas/ext_web_preferencias_gestor.class");

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$url = 'apps/config/controller/parametros_update.php';
$a_campos = [ 'url' => $url];


// ----------- permiso para el botón distribuir al oficial -------------------
$parametro = 'perm_distribuir';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_perm_distribuir = 't'; 
$chk_perm_distribuir = ($valor == $val_perm_distribuir)? 'checked' : '';

$oHashPD = new Hash();
$oHashPD->setUrl($url);
$oHashPD->setcamposForm('valor');
$oHashPD->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPD'] = $oHashPD;
$a_campos['val_perm_distribuir'] = $val_perm_distribuir;
$a_campos['chk_perm_distribuir'] = $chk_perm_distribuir;

// ----------- permiso para el poder aceptar entradas el oficial -------------------
$parametro = 'perm_aceptar';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_perm_aceptar = 't'; 
$chk_perm_aceptar = ($valor == $val_perm_aceptar)? 'checked' : '';

$oHashPA = new Hash();
$oHashPA->setUrl($url);
$oHashPA->setcamposForm('valor');
$oHashPA->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPA'] = $oHashPA;
$a_campos['val_perm_aceptar'] = $val_perm_aceptar;
$a_campos['chk_perm_aceptar'] = $chk_perm_aceptar;

$a_campos['filtro'] = $Qfiltro;

$oView = new core\ViewTwig('config/controller');
echo $oView->render('parametros_scdl.html.twig',$a_campos);