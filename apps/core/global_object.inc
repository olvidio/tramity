<?php

namespace core;

use config\model\Config;
use web\Hash;
use web\Posicion;

function logout($idioma, $esquema, $error)
{
    $a_campos = [];
    $a_campos['nombre_entidad'] = $esquema;
    $a_campos['error'] = $error;
    $a_campos['idioma'] = $idioma;
    $a_campos['url'] = ConfigGlobal::getWeb();

    $oView = new ViewTwig('usuarios\controller');
    $oView->renderizar('login_form.html.twig', $a_campos);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Configure timeout to 30 minutes
    $timeout = 1800;
    $maxlifetime = time() + $timeout;

    // Set the maxlifetime of session
    ini_set("session.gc_maxlifetime", $timeout);

    // Also set the session cookie timeout
    ini_set("session.cookie_lifetime", $timeout);

    // Now start the session
    session_set_cookie_params([
        'lifetime' => $maxlifetime,
        'Secure' => false,
        'HttpOnly' => true,
        'SameSite' => 'Lax',
        'Domain' => $domain
        ]);
    session_start();


    /*
// Update the timeout of session cookie
$sessionName = session_name();
$domain = ConfigGlobal::getDomain();
//$domain = ConfigGlobal::getHost();

if (isset($_COOKIE[$sessionName])) {
    $rta = setcookie($sessionName, $_COOKIE[$sessionName], [
        'expires' => time() + $timeout,
        'path' => '/',
        'SameSite' => 'Strict',
        'Secure' => 'false',
        'domain' => $domain,
    ]);
        $rta = setcookie($sessionName, $_COOKIE[$sessionName], [
            'expires' => time() + $timeout
                ]
        );
        if ($rta === FALSE ){
            exit ("Nopuedo cambiar la cookie");
        }
    }
    */
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
$oConfigDB = new ConfigDB('tramity');
$esquema = ConfigGlobal::getEsquema();
if (!empty($esquema) && $esquema !== 'admin') {
    /* Si para todos los esquemas uso el mismo usuario de conexión a la DB (tramity), no hace falta: */
    $config = $oConfigDB->getEsquema($esquema);
} else {
    $config = $oConfigDB->getEsquema('public');
}
$oConexion = new DBConnection($config);
$oDBT = $oConexion->getPDO();

// para las tablas comunes: lugares, entidades, x_locales
$config = $oConfigDB->getEsquema('public');
$oConexion = new DBConnection($config);
$oDBP = $oConexion->getPDO();


// Conexión al servidor davical (calendarios caldav).
$oConfigDB = new ConfigDB('davical');
$config = $oConfigDB->getEsquema('public');
$oConexion = new DBConnection($config);
$oDBDavical = $oConexion->getPDO();

// Datos de configuración propios de la aplicación
$_SESSION['oConfig'] = new Config();

// Para validar los parámetros enviados via POST
$oValidator = new Hash();

$oPosicion = new Posicion($_SERVER['PHP_SELF'], $_POST);