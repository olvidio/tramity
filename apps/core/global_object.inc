<?php
namespace core;

use config\model\Config;
use web\Hash;
use web\Posicion;

if ( session_status()  !== PHP_SESSION_ACTIVE) {
	session_start(); // if no active session we start a new one
}

require_once('func_tablas.php'); 
require_once('func_reg.php'); 

require_once('apps/usuarios/controller/login.php');


$_SESSION['oGestorErrores'] = new  GestorErrores();

/*
 * Definir Conexiones
 *
 * No conviene que las conexiones sean persistentes, pues en caso de algún bloqueo
 *  o error afectan a toda la aplicación.
 * De esta manera (no persistente) se cierra la conexión cada vez que acaba el script.
 * 
 */
$oConfigDB = new ConfigDB('tramity'); //de la database comun
$config = $oConfigDB->getEsquema('public');
$oConexion = new dbConnection($config);
$oDBT = $oConexion->getPDO();

// Conexión al servicdor davical (calendarios caldav).
$oConfigDB = new ConfigDB('davical'); //de la database comun
$config = $oConfigDB->getEsquema('public');
$oConexion = new dbConnection($config);
$oDBDavical = $oConexion->getPDO();

// Datos de configuración propios de la aplicación
$_SESSION['oConfig'] = new Config();

// Para validar los parametros enviados via POST
$oValidator = new Hash();

$oPosicion = new Posicion($_SERVER['PHP_SELF'],$_POST);
