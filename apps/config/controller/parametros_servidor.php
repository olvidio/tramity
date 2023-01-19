<?php

use config\model\entity\ConfigSchema;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorLocale;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
//	require_once ("classes/personas/ext_web_preferencias_gestor.class");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$url = 'apps/config/controller/parametros_update.php';
$a_campos = ['url' => $url];

// ----------- size de los fcheros para poder hacer upload ------------------
/* En /etc/php/7.4/fpm/php.ini
 * post_max_size = 25M
 * upload_max_filesize = 25M
 */
$parametro = 'max_filesize';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashFS = new Hash();
$oHashFS->setUrl($url);
$oHashFS->setcamposForm('valor');
$oHashFS->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashFS'] = $oHashFS;
$a_campos['max_filesize'] = $valor;


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
$chk_ctr = ($valor == $val_ctr) ? 'checked' : '';
$val_dl = Cargo::AMBITO_DL;
$chk_dl = ($valor == $val_dl) ? 'checked' : '';
$val_cr = Cargo::AMBITO_CR;
$chk_cr = ($valor == $val_cr) ? 'checked' : '';

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
$parametro = 'smtp_secure';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_tls = 'tls'; // PHPMailer::ENCRYPTION_STARTTLS
$chk_tls = ($valor == $val_tls) ? 'checked' : '';
$val_ssl = 'ssl'; // PHPMailer::ENCRYPTION_SMTPS
$chk_ssl = ($valor == $val_ssl) ? 'checked' : '';

$oHashSMTP_secure = new Hash();
$oHashSMTP_secure->setUrl($url);
$oHashSMTP_secure->setcamposForm('valor');
$oHashSMTP_secure->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_secure'] = $oHashSMTP_secure;
$a_campos['val_tls'] = $val_tls;
$a_campos['chk_tls'] = $chk_tls;
$a_campos['val_ssl'] = $val_ssl;
$a_campos['chk_ssl'] = $chk_ssl;

$parametro = 'smtp_host';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashSMTP_host = new Hash();
$oHashSMTP_host->setUrl($url);
$oHashSMTP_host->setcamposForm('valor');
$oHashSMTP_host->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_host'] = $oHashSMTP_host;
$a_campos[$parametro] = $valor;

$parametro = 'smtp_port';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashSMTP_port = new Hash();
$oHashSMTP_port->setUrl($url);
$oHashSMTP_port->setcamposForm('valor');
$oHashSMTP_port->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_port'] = $oHashSMTP_port;
$a_campos[$parametro] = $valor;

$parametro = 'smtp_auth';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$val_auth = 'true';
$chk_auth = ($valor == $val_auth) ? 'checked' : '';

$oHashSMTP_auth = new Hash();
$oHashSMTP_auth->setUrl($url);
$oHashSMTP_auth->setcamposForm('valor');
$oHashSMTP_auth->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_auth'] = $oHashSMTP_auth;
$a_campos['val_auth'] = $val_auth;
$a_campos['chk_auth'] = $chk_auth;

$parametro = 'smtp_user';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashSMTP_user = new Hash();
$oHashSMTP_user->setUrl($url);
$oHashSMTP_user->setcamposForm('valor');
$oHashSMTP_user->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_user'] = $oHashSMTP_user;
$a_campos[$parametro] = $valor;

$parametro = 'smtp_pwd';
$oConfigSchema = new ConfigSchema($parametro);
$valor = $oConfigSchema->getValor();

$oHashSMTP_pwd = new Hash();
$oHashSMTP_pwd->setUrl($url);
$oHashSMTP_pwd->setcamposForm('valor');
$oHashSMTP_pwd->setArrayCamposHidden(['parametro' => $parametro]);

$a_campos['oHashSMTP_pwd'] = $oHashSMTP_pwd;
$a_campos[$parametro] = $valor;

// ----------- Servidor de AS4 Deck -----------------
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
$oView->renderizar('parametros_servidor.html.twig', $a_campos);