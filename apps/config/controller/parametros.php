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


// ----------- plazos vida -------------------
/*
 *  // vida (a criterio del ponente):
    /*
    - Permanente (no borrar)
    - Experiencia (5 años)
    - Normal (1 mes)
    - Temporal (1 semana)
    - Borrable (1 día)
    
    const VIDA_PERMANENTE    = 1;
    const VIDA_EXPERIENCIA   = 2;
    const VIDA_NORMAL        = 3;
    const VIDA_TEMPORAL      = 4;
    const VIDA_BORRABLE      = 5;
 */

$parametro = 'vida_permanente_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_permanente_r = new Hash();
$oHashVIDA_permanente_r->setUrl($url);
$oHashVIDA_permanente_r->setcamposForm('valor');
$oHashVIDA_permanente_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_permanente_r'] = $oHashVIDA_permanente_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_permanente_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_permanente_c = new Hash();
$oHashVIDA_permanente_c->setUrl($url);
$oHashVIDA_permanente_c->setcamposForm('valor');
$oHashVIDA_permanente_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_permanente_c'] = $oHashVIDA_permanente_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_experiencia_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_experiencia_r = new Hash();
$oHashVIDA_experiencia_r->setUrl($url);
$oHashVIDA_experiencia_r->setcamposForm('valor');
$oHashVIDA_experiencia_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_experiencia_r'] = $oHashVIDA_experiencia_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_experiencia_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_experiencia_c = new Hash();
$oHashVIDA_experiencia_c->setUrl($url);
$oHashVIDA_experiencia_c->setcamposForm('valor');
$oHashVIDA_experiencia_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_experiencia_c'] = $oHashVIDA_experiencia_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_normal_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_normal_r = new Hash();
$oHashVIDA_normal_r->setUrl($url);
$oHashVIDA_normal_r->setcamposForm('valor');
$oHashVIDA_normal_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_normal_r'] = $oHashVIDA_normal_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_normal_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_normal_c = new Hash();
$oHashVIDA_normal_c->setUrl($url);
$oHashVIDA_normal_c->setcamposForm('valor');
$oHashVIDA_normal_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_normal_c'] = $oHashVIDA_normal_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_temporal_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_temporal_r = new Hash();
$oHashVIDA_temporal_r->setUrl($url);
$oHashVIDA_temporal_r->setcamposForm('valor');
$oHashVIDA_temporal_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_temporal_r'] = $oHashVIDA_temporal_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_temporal_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_temporal_c = new Hash();
$oHashVIDA_temporal_c->setUrl($url);
$oHashVIDA_temporal_c->setcamposForm('valor');
$oHashVIDA_temporal_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_temporal_c'] = $oHashVIDA_temporal_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_borrable_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_borrable_r = new Hash();
$oHashVIDA_borrable_r->setUrl($url);
$oHashVIDA_borrable_r->setcamposForm('valor');
$oHashVIDA_borrable_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_borrable_r'] = $oHashVIDA_borrable_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'vida_borrable_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashVIDA_borrable_c = new Hash();
$oHashVIDA_borrable_c->setUrl($url);
$oHashVIDA_borrable_c->setcamposForm('valor');
$oHashVIDA_borrable_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashVIDA_borrable_c'] = $oHashVIDA_borrable_c;
$a_campos[$parametro] = $val_sigla;



// ----------- plazos categorias -------------------
/*
 *  const CAT_E12          = 1;
 *	const CAT_NORMAL       = 2;
 *	const CAT_PERMANATE    = 3;
 */

$parametro = 'cat_e12_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_12_r = new Hash();
$oHashCAT_12_r->setUrl($url);
$oHashCAT_12_r->setcamposForm('valor');
$oHashCAT_12_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_12_r'] = $oHashCAT_12_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'cat_e12_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_12_c = new Hash();
$oHashCAT_12_c->setUrl($url);
$oHashCAT_12_c->setcamposForm('valor');
$oHashCAT_12_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_12_c'] = $oHashCAT_12_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'cat_normal_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_normal_r = new Hash();
$oHashCAT_normal_r->setUrl($url);
$oHashCAT_normal_r->setcamposForm('valor');
$oHashCAT_normal_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_normal_r'] = $oHashCAT_normal_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'cat_normal_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_normal_c = new Hash();
$oHashCAT_normal_c->setUrl($url);
$oHashCAT_normal_c->setcamposForm('valor');
$oHashCAT_normal_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_normal_c'] = $oHashCAT_normal_c;
$a_campos[$parametro] = $val_sigla;

$parametro = 'cat_permanente_registro';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_permanente_r = new Hash();
$oHashCAT_permanente_r->setUrl($url);
$oHashCAT_permanente_r->setcamposForm('valor');
$oHashCAT_permanente_r->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_permanente_r'] = $oHashCAT_permanente_r;
$a_campos[$parametro] = $val_sigla;

$parametro = 'cat_permanente_contenido';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();
if (empty($valor)) {
    $valor = "3";
}
$val_sigla = $valor;
$oHashCAT_permanente_c = new Hash();
$oHashCAT_permanente_c->setUrl($url);
$oHashCAT_permanente_c->setcamposForm('valor');
$oHashCAT_permanente_c->setArrayCamposHidden(['parametro' => $parametro]);
$a_campos['oHashCAT_permanente_c'] = $oHashCAT_permanente_c;
$a_campos[$parametro] = $val_sigla;

// ----------- plazos contestar -------------------
/*
 - Urgente (3 días)
 - Rápido (1 semana)
 - Normal (2 semanas)
 - A determinar
 */

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

// ----------- Inicio Contador cr -------------------
$parametro = 'ini_contador_cr';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashC = new Hash();
$oHashC->setUrl($url);
$oHashC->setcamposForm('valor');
$oHashC->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashC'] = $oHashC;
$a_campos['ini_contador_cr'] = $valor;

// ----------- Inicio Contador resto -------------------
$parametro = 'ini_contador';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashC1 = new Hash();
$oHashC1->setUrl($url);
$oHashC1->setcamposForm('valor');
$oHashC1->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashC1'] = $oHashC1;
$a_campos['ini_contador'] = $valor;

// ----------- Inicio Contador IESE -------------------
$parametro = 'ini_contador_iese';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashC2 = new Hash();
$oHashC2->setUrl($url);
$oHashC2->setcamposForm('valor');
$oHashC2->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashC2'] = $oHashC2;
$a_campos['ini_contador_iese'] = $valor;

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

// ----------- Nombre Localidad -------------------
$parametro = 'localidad';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_sigla = $valor;

$oHashL = new Hash();
$oHashL->setUrl($url);
$oHashL->setcamposForm('valor');
$oHashL->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashL'] = $oHashL;
$a_campos['localidad'] = $val_sigla;

// ----------- body del mail -------------------
$parametro = 'bodyMail';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_sigla = $valor;

$oHashBM = new Hash();
$oHashBM->setUrl($url);
$oHashBM->setcamposForm('valor');
$oHashBM->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashBM'] = $oHashBM;
$a_campos[$parametro] = $val_sigla;

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

// ----------- Servidor de Ethercalc -------------------
$parametro = 'server_ethercalc';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_server = $valor;

$oHashSEC = new Hash();
$oHashSEC->setUrl($url);
$oHashSEC->setcamposForm('valor');
$oHashSEC->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSEC'] = $oHashSEC;
$a_campos['server_ethercalc'] = $val_server;

// ----------- Idioma por defecto de la dl -------------------
$parametro = 'idioma_default';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$gesIdiomas = new GestorLocale();
$oDeplIdiomas = $gesIdiomas->getListaLocales();
$oDeplIdiomas->setNombre('valor');
$oDeplIdiomas->setOpcion_sel($valor);

if (empty($valor)) {
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
    $valor = Cargo::AMBITO_DL;  // "dl"
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

// ----------- Servidor SMTP -------------------

$parametro = 'from';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashFrom  = new Hash();
$oHashFrom  ->setUrl($url);
$oHashFrom  ->setcamposForm('valor');
$oHashFrom  ->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashFrom'] = $oHashFrom  ;
$a_campos[$parametro] = $valor;

$parametro = 'reply_to';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashReply  = new Hash();
$oHashReply  ->setUrl($url);
$oHashReply  ->setcamposForm('valor');
$oHashReply  ->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashReply'] = $oHashReply  ;
$a_campos[$parametro] = $valor;

// ----------- Servidor de Davical -------------------
$parametro = 'server_davical';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_server_davical = $valor;

$oHashDavical = new Hash();
$oHashDavical->setUrl($url);
$oHashDavical->setcamposForm('valor');
$oHashDavical->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashDavical'] = $oHashDavical;
$a_campos['server_davical'] = $val_server_davical;





$oView = new core\ViewTwig('config/controller');
echo $oView->render('parametros.html.twig',$a_campos);