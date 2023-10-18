<?php
/*
var_dump( xdebug_info( 'mode' ) );
xdebug_info();
die();
*/

// INICIO Cabecera global de URL de controlador *********************************
use config\model\entity\ConfigSchema;
use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


// Para salir de la sesión.
if (isset($_REQUEST['logout']) && $_REQUEST['logout'] === 'si') {
    // Destruir todas las variables de sesión.
    $_SESSION = array();
    //$GLOBALS = array(); # error en php8.1
    // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
    // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        $arr_cookie_options = array(
            'Expires' => time() - 42000,
            'Path' => $params["path"],
            'Domain' => $params["domain"],
            'Secure' => $params["secure"],
            'HttpOnly' => true,
            'SameSite' => 'None' // None || Lax  || Strict
        );
        setcookie(session_name(), '', $arr_cookie_options);
    }
    // Finalmente, destruir la sesión.
    session_regenerate_id();
    session_destroy();
    header("Location: index.php");
    die();
}

$username = $_SESSION['session_auth']['username'];

if (empty($_SESSION['session_auth']['role_actual'])) {
    // para el usuario admin, y system
    if ($username === 'manager' || $username === 'admin') {
        $_SESSION['session_auth']['role_actual'] = 'admin';
    } else {
        $_SESSION['session_auth']['role_actual'] = $username;
    }
}
$role_actual = $_SESSION['session_auth']['role_actual'];


$oConfigSchema = new ConfigSchema('ambito');
$valor_ambito = (int)$oConfigSchema->getValor();
$id_ambito_dl = FALSE;
$a_roles_posibles = [];
if ($valor_ambito === Cargo::AMBITO_DL) {
    $id_ambito_dl = TRUE;
    // role de 'secretaria' para los oficiales de secretaria:
    $id_oficina_secretaria = '';
    $gesOficinas = new GestorOficina();
    $cOficinas = $gesOficinas->getOficinas(['sigla' => 'scdl']);
    if (!empty($cOficinas)) {
        $id_oficina_secretaria = $cOficinas[0]->getId_oficina();
    }
    $mi_id_oficina = ConfigGlobal::role_id_oficina();
    if ($id_oficina_secretaria === $mi_id_oficina) {
        $a_roles_posibles[] = 'secretaria';
    }
}

//oficinas adicionales (suplencias..)
if ($role_actual !== 'admin') {
    $aPosiblesCargos = $_SESSION['session_auth']['aPosiblesCargos'];
    foreach ($aPosiblesCargos as $id_cargo => $cargo) {
        $a_roles_posibles[] = $cargo;
        // si es de secretaria, lo añado
        if ($valor_ambito === Cargo::AMBITO_DL) {
            $oCargo = new Cargo($id_cargo);
            $id_oficina_cargo = $oCargo->getId_oficina();
            if ($id_oficina_cargo === $id_oficina_secretaria) {
                $a_roles_posibles[] = 'secretaria';
            }
        }
    }
}

$_SESSION['session_auth']['a_roles'] = array_unique($a_roles_posibles);
?>
<!DOCTYPE html>
<head>
    <script>
        fnjs_is_active = function () {
            url = 'apps/usuarios/controller/test_status_phpsession.php';
            let xmlHttp = new XMLHttpRequest();
            // a pelo, porque todavía no he cargado el jQuery.
            xmlHttp.onreadystatechange = function () {
                let rta_json;
                if (xmlHttp.readyState === 4 && xmlHttp.status === 200) {
                    rta_json = JSON.parse(xmlHttp.responseText);
                    if (rta_json.success !== true) {
                        fnjs_logout();
                    }
                }
            }
            xmlHttp.open("post", url);
            xmlHttp.send();
        }

        // Cada 5 seg. Comprobar que la sesión php no ha finalizado, para volver al login de entrada
        // Si es en el portátil no lo compruebo, para que haya menos cosas en los logs.
        <?php
        if (!(str_contains(ServerConf::SERVIDOR, 'docker') || ServerConf::SERVIDOR === 'tramity.local')) {
            echo "setInterval( fnjs_is_active, 5000);";
        }
        ?>

        fnjs_procesarError = function () {
            alert("<?= _("Error de página devuelta") ?>");
        }
        fnjs_ref_absoluta = function (base, path) {
            let protocol;
            let url;
            let inicio = "";
            let secure, ini;
            if (secure) {
                protocol = 'https:';
            } else {
                protocol = 'http:';
            }
            // El apache ya ha añadido por su cuenta protocolo+$web. Lo quito:
            ini = protocol + '<?= ConfigGlobal::getWeb() ?>';
            if (path.indexOf(ini) !== -1) {
                path = path.replace(ini, '');
            } else {
                // Pruebo si ha subido un nivel, si ha subido más (../../../) no hay manera. El apache sube hasta nivel de servidor, no más.
                ini = protocol + '<?= ConfigGlobal::getWeb() ?>';
                if (path.indexOf(ini) !== -1) {
                    path = path.replace(ini, '');
                } else {
                    // si el path es una ref. absoluta, no hago nada
                    // si empieza por http://
                    if (path.match(/^http/)) {
                        url = path;
                        return url;
                    } else {
                        if ("<?= ConfigGlobal::mi_usuario() ?>" === "dani") {
                            alert("Este link no va ha funcionar bien, porque tiene una url relativa: ../../\n" + path);
                        }
                    }
                }
            }
            // De la base. Puede ser un directorio o una web:
            //   - cambio el directorio físico por su correspondiente web.
            //   - quito el documento.
            if (base.match(/^<?= addcslashes(ConfigGlobal::$directorio, "/") ?>/)) { // si es un directorio
                base = base.replace('<?= ConfigGlobal::$directorio ?>', '');
                inicio = protocol + '<?= ConfigGlobal::getWeb() ?>';
            } else {
                if (base.match(/^<?= addcslashes(ConfigGlobal::$dir_web, "/") ?>/)) {
                    base = base.replace('<?= ConfigGlobal::$dir_web ?>', '');
                    inicio = protocol + '<?= ConfigGlobal::getWeb() ?>';
                }
            }
            // si es una web:
            if (!inicio) {
                if (base.indexOf(protocol) !== -1) {
                    base = base.replace(protocol, '');
                    inicio = protocol;
                }
            }
            // le quito la página final (si tiene) y la barra (/)
            base = base.replace(/\/(\w+\.\w+$)|\/((\w+-)*(\w+ )*\w+\.\w+$)/, '');
            //elimino la base si ya existe en el path:
            path = path.replace(base, '');
            // si no coincide con ninguno, dejo lo que había.
            if (!inicio) {
                url = path;
            } else {
                url = inicio + base + path;
            }
            //alert ('url: '+url);
            return url;
        }
    </script>
    <title>Tramity</title>
</head>
<body>
<?php
switch ($role_actual) {
    case 'admin':
        $server = $_SESSION['oConfig']->getServerDavical();
        $server .= '/index.php';
        // hay que enviar algún valor, sino el javascript da un error:
        $error_fecha = empty($_SESSION['oConfig']->getPlazoError()) ? 15 : $_SESSION['oConfig']->getPlazoError();
        $pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?' . http_build_query([]));
        $a_campos = [
            'pagina_profile' => $pagina_profile,
            'error_fecha' => $error_fecha,
            'server_davical' => $server,
        ];
        $esquema = ConfigGlobal::getEsquema();
        $nombre_entidad = ConfigGlobal::nombreEntidad();
        if ($esquema === 'admin') {
            $oView = new ViewTwig('usuarios/controller');
            $oView->renderizar('admin_servidor.html.twig', $a_campos);
        } else {
            $a_campos['is_ambito_dl'] = $id_ambito_dl;
            $a_campos['nombre_entidad'] = $nombre_entidad;
            $oView = new ViewTwig('usuarios/controller');
            $oView->renderizar('admin_entidad.html.twig', $a_campos);
        }
        break;
    case 'secretaria';
        include_once 'apps/usuarios/controller/usuario_secretaria.php';
        break;
    default:
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            include_once 'apps/usuarios/controller/usuario_home.php';
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            include_once 'apps/usuarios/controller/usuario_ctr.php';
        }
}
?>
</body>