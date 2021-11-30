<?php
// INICIO Cabecera global de URL de controlador *********************************
use config\model\entity\ConfigSchema;
use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


// Para salir de la sesión.
if (isset($_REQUEST['logout']) && $_REQUEST['logout'] == 'si') {
    // Destruir todas las variables de sesión.
    $_SESSION = array();
    $GLOBALS = array();
    // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
    // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
            );
    }
    // Finalmente, destruir la sesión.
    session_regenerate_id();
    session_destroy();
    header("Location: index.php");
    die();
}

$username = $_SESSION['session_auth']['username'];

if (empty($_SESSION['session_auth']['role_actual'])) {
    $_SESSION['session_auth']['role_actual'] = $username;
}
$role_actual = $_SESSION['session_auth']['role_actual'];


$oConfigSchema = new ConfigSchema('ambito');
$valor_ambito = $oConfigSchema->getValor();
$id_ambito_dl = FALSE;
$a_roles_posibles = [];
if ($valor_ambito == Cargo::AMBITO_DL) {
    $id_ambito_dl = TRUE;
    // role de 'secretaria' para los oficiales de secretaria:
    $id_oficina_secretaria = '';
    $gesOficinas = new GestorOficina();
    $cOficinas = $gesOficinas->getOficinas(['sigla' => 'scdl']);
    if (!empty($cOficinas)) {
        $id_oficina_secretaria = $cOficinas[0]->getId_oficina();
    }
    $mi_id_oficina = ConfigGlobal::role_id_oficina();
    if ($id_oficina_secretaria == $mi_id_oficina) {
        $a_roles_posibles[] = 'secretaria';
    }
}

//oficinas adicionales (suplencias..)
if ($role_actual != 'admin') {
    $aPosiblesCargos = $_SESSION['session_auth']['aPosiblesCargos'];
    foreach($aPosiblesCargos as $id_cargo => $cargo) {
        $a_roles_posibles[] = $cargo;
        // si es de secretaria, lo añado
        if ($valor_ambito === Cargo::AMBITO_DL) {
            $oCargo = new Cargo($id_cargo);
            $id_oficina_cargo = $oCargo->getId_oficina();
            if ($id_oficina_cargo == $id_oficina_secretaria) {
                $a_roles_posibles[] = 'secretaria';
            }
        }
    }
}

$_SESSION['session_auth']['a_roles'] = $a_roles_posibles;
?>
<head>
<script>
fnjs_procesarError=function() {
	alert("<?= _("Error de página devuelta") ?>");
}
fnjs_ref_absoluta=function(base,path) {
    var url="";
    var inicio="";
    var base1= base;
    var path1= path;
    var secure = <?php if (!empty($_SERVER["HTTPS"])) { echo 1; } else {echo 0;} ?> ;
	if (secure) {
		var protocol = 'https:';
	} else {
		var protocol = 'http:';
	}
	// El apache ya ha añadido por su cuenta protocolo+$web. Lo quito:
	ini=protocol+'<?= ConfigGlobal::getWeb() ?>';
	if (path.indexOf(ini) != -1) {
		path=path.replace(ini,'');
	} else {
		// pruebo si ha subido un nivel, si ha subido más (../../../) no hay manera. El apache sube hasta nivel de servidor, no más.
        ini=protocol+'<?= ConfigGlobal::getWeb() ?>';
        if (path.indexOf(ini) != -1) {
            path=path.replace(ini,'');
        } else {
            // si el path es una ref. absoluta, no hago nada
            // si empieza por http://
            if  ( path.match(/^http/) ) {
                url=path;
                return url;
            } else {
                if ("<?= ConfigGlobal::mi_usuario() ?>"=="dani") {
                    alert("Este link no va ha funcionar bien, porque tiene una url relativa: ../../\n"+path);
                }
            }
        }
	}
	// De la base. puede ser un directorio o una web:
	//   - cambio el directorio físico por su correspondiente web.
	//   - quito el documento.
	
	a=0;
	if ( base.match(/^<?= addcslashes(ConfigGlobal::$directorio,"/") ?>/) ) {	// si es un directorio
		base=base.replace('<?= ConfigGlobal::$directorio ?>','');
		inicio=protocol+'<?= ConfigGlobal::getWeb() ?>';
    } else { if ( base.match(/^<?= addcslashes(ConfigGlobal::$dir_web,"/") ?>/) ){
        base=base.replace('<?= ConfigGlobal::$dir_web ?>','');
        inicio=protocol+'<?= ConfigGlobal::getWeb() ?>';
        a=5;
		}
    }
	// si es una web:
	if (!inicio) {
		if (base.indexOf(protocol) != -1) {
			base=base.replace(protocol,'');
			inicio=protocol;
            a=6;
		}
	}
	// le quito la página final (si tiene) y la barra (/)
	base=base.replace(/\/(\w+\.\w+$)|\/((\w+-)*(\w+ )*\w+\.\w+$)/,''); 
	//elimino la base si ya existe en el path:
	path=path.replace(base,'');
	if ("<?= ConfigGlobal::mi_usuario() ?>"=="dani") {
		//alert ("base1: "+base1+"\npath1: "+path1+"\npath: "+path+"\nAA: "+a +" base: "+base);	
	}
	// sino coincide con niguno, dejo lo que había.
	if (!inicio) {
		url=path;
	} else {
		url=inicio+base+path;
	}
	//alert ('url: '+url);
	return url;
}
</script>
</head>
<body>
<?php
switch ($role_actual) {
    case 'admin':
        $server =  $_SESSION['oConfig']->getServerDavical();
        $server = $server.'/index.php';
        $a_campos = [
            'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
            'server_davical' => $server,
        ];
        if (ConfigGlobal::getEsquema() === 'admin') {
            $oView = new ViewTwig('usuarios/controller');
            echo $oView->renderizar('admin_servidor.html.twig',$a_campos);
        } else {
            $a_campos['is_ambito_dl'] = $id_ambito_dl;
            $oView = new ViewTwig('usuarios/controller');
            echo $oView->renderizar('admin_entidad.html.twig',$a_campos);
        }
        break; 
    case 'secretaria';
        include_once 'apps/usuarios/controller/usuario_secretaria.php';
        break;
    default:
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
            include_once 'apps/usuarios/controller/usuario_home.php';
        }
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            include_once 'apps/usuarios/controller/usuario_ctr.php';
        }
}
?>
</body>