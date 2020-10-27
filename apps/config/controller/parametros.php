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


$url = 'apps/config/controller/parametros_update.php';
$a_campos = [ 'url' => $url];


// ----------- plazos contestar -------------------
/*
 - Urgente (3 días)
 - Rápido (1 semana)
 - Normal (2 semanas)
 - A determinar
 */
//const PRIORIDAD_URGENTE = 3;
$parametro = 'plazo_urgente';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;

$oHashPU = new Hash();
$oHashPU->setUrl($url);
$oHashPU->setcamposForm('valor');
$oHashPU->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPU'] = $oHashPU;
$a_campos['plazo_urgente'] = $val_sigla;

// const PRIORIDAD_RAPIDO = 7;
$parametro = 'plazo_rapido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = "7";
}
$val_sigla = $valor;

$oHashPR = new Hash();
$oHashPR->setUrl($url);
$oHashPR->setcamposForm('valor');
$oHashPR->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPR'] = $oHashPR;
$a_campos['plazo_rapido'] = $val_sigla;

// const PRIORIDAD_NORMAL = 14;
$parametro = 'plazo_normal';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = "14";
}
$val_sigla = $valor;

$oHashPN = new Hash();
$oHashPN->setUrl($url);
$oHashPN->setcamposForm('valor');
$oHashPN->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPN'] = $oHashPN;
$a_campos['plazo_normal'] = $val_sigla;

// Error en fecha
$parametro = 'plazo_error';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = "15";
}
$val_sigla = $valor;

$oHashPE = new Hash();
$oHashPE->setUrl($url);
$oHashPE->setcamposForm('valor');
$oHashPE->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashPE'] = $oHashPE;
$a_campos['plazo_error'] = $val_sigla;

// ----------- Nombre Sigla -------------------
$parametro = 'sigla';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = "dlb";
}
$val_sigla = $valor;

$oHashRL = new Hash();
$oHashRL->setUrl($url);
$oHashRL->setcamposForm('valor');
$oHashRL->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashRL'] = $oHashRL;
$a_campos['sigla'] = $val_sigla;

// ----------- Servidor de Etherpad -------------------
$parametro = 'server_etherpad';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_server = $valor;

$oHashSE = new Hash();
$oHashSE->setUrl($url);
$oHashSE->setcamposForm('valor');
$oHashSE->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSE'] = $oHashSE;
$a_campos['server_etherpad'] = $val_server;

// ----------- Idioma por defecto de la dl -------------------
$parametro = 'idioma_default';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$gesIdiomas = new GestorLocale();
$oDeplIdiomas = $gesIdiomas->getListaLocales();
$oDeplIdiomas->setNombre('valor');
$oDeplIdiomas->setOpcion_sel($valor);

if (empty($valor)) {
    //$valor = "es_ES.UTF-8";
    $oDeplIdiomas->setOpcion_sel('es_ES.UTF-8');
}
$val_idioma_default = $oDeplIdiomas;

$oHashI = new Hash();
$oHashI->setUrl($url);
$oHashI->setcamposForm('valor');
$oHashI->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashI'] = $oHashI;
$a_campos['idioma_default'] = $val_idioma_default;

// ----------- Ámbito: ctr, delegación o región -------------------
$parametro = 'ambito';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

if (empty($valor)) {
    $valor = Cargo::AMBITO_DL;  //"dl";
}
$val_ctr = Cargo::AMBITO_CTR;
$chk_ctr = ($valor == $val_ctr)? 'checked' : ''; 
$val_dl = Cargo::AMBITO_DL;
$chk_dl = ($valor == $val_dl)? 'checked' : ''; 
$val_cr = Cargo::AMBITO_CR;
$chk_cr = ($valor == $val_cr)? 'checked' : ''; 

$oHashDLR = new Hash();
$oHashDLR->setUrl($url);
$oHashDLR->setcamposForm('valor');
$oHashDLR->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashDLR'] = $oHashDLR;
$a_campos['val_ctr'] = $val_ctr;
$a_campos['chk_ctr'] = $chk_ctr;
$a_campos['val_dl'] = $val_dl;
$a_campos['chk_dl'] = $chk_dl;
$a_campos['val_cr'] = $val_cr;
$a_campos['chk_cr'] = $chk_cr;

$oView = new core\ViewTwig('config/controller');
echo $oView->render('parametros.html.twig',$a_campos);