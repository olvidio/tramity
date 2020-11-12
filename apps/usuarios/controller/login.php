<?php
namespace usuarios\controller;

// INICIO Cabecera global de URL de controlador *********************************
//require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
//require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
//
// FIN de  Cabecera global de URL de controlador ********************************

use core\ConfigDB;
use core\ConfigGlobal;
use core\MyCrypt;
use core\ViewTwig;
use function core\cambiar_idioma;
use core\dbConnection;


function logout($idioma,$esquema,$error) {
    $a_campos = [];
    $a_campos['error'] = $error;
    $a_campos['idioma'] = $idioma;
    $a_campos['url'] = ConfigGlobal::getWeb();
    $oView = new ViewTwig(__NAMESPACE__);
    echo $oView->render('login_form.html.twig',$a_campos);
}

if ( !isset($_SESSION['session_auth'])) { 
	//el segon cop tinc el nom i el password
    $idioma='';
	if (isset($_POST['username']) && isset($_POST['password'])) {
        $mail='';

        $aWhere = array('usuario'=>$_POST['username']);
        $esquema = empty($_POST['esquema'])? '' : $_POST['esquema'];
        $oConfigDB = new ConfigDB('tramity'); 
        $config = $oConfigDB->getEsquema('public'); 
        $oConexion = new dbConnection($config);
        $oDB = $oConexion->getPDO();
        $query="SELECT * FROM aux_usuarios WHERE usuario = :usuario";
        if (($oDBSt= $oDB->prepare($query)) === false) {
            $sClauError = 'login_obj.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
            return false;
        }

        if (($oDBSt->execute($aWhere)) === false) {
            $sClauError = 'loguin_obj.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
            return false;
        }

        $idioma='';
        $sPasswd = null;
        $oCrypt = new MyCrypt();
        $oDBSt->bindColumn('password', $sPasswd, \PDO::PARAM_STR);
        if ($row=$oDBSt->fetch(\PDO::FETCH_ASSOC)) {
            if ($oCrypt->encode($_POST['password'],$sPasswd) == $sPasswd) {
                $id_usuario = $row['id_usuario'];
                $id_cargo_default = $row['id_cargo'];
                // el usuario default, y el admin no tienen cargo:
                // los cargos que puede tener (suplencias)
                $query_cargos="SELECT * FROM aux_cargos 
                                WHERE id_usuario = $id_usuario OR id_suplente = $id_usuario
                                ORDER BY cargo";
                if (($oDB->query($query_cargos)) === false) {
                    $sClauError = 'login_obj.prepare';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
                    return false;
                }
                $aPosiblesCargos = [];
                $usuario_cargo = '';
                $usuario_dtor = '';
                foreach ($oDB->query($query_cargos) as $aDades) {
                    $usuario_cargo = $aDades['cargo'];
                    $usuario_dtor = $aDades['director'];
                    $id_cargo = $aDades['id_cargo'];
                    $cargo = $aDades['cargo'];
                    $aPosiblesCargos[$id_cargo] = $cargo;
                }
                
                $perms_activ='';
                $mi_oficina = '';
                $mi_oficina_menu = '';

                // si no tiene mail interior, cojo el exterior.
                $mail = empty($mail)? $row['email'] : $mail;
                $expire=""; //de moment, per fer servir més endevant...
                // Para obligar a cambiar el password
                if ($_POST['password'] == '1ªVegada') {
                    $expire=1;
                }
                
                
                // Idioma
                $idioma = '';
                $query_idioma = sprintf( "select * from usuario_preferencias where id_usuario = '%s' and tipo = '%s' ",$id_usuario,"idioma");
                $oDBStI=$oDB->query($query_idioma);
                $row = $oDBStI->fetch(\PDO::FETCH_ASSOC);
                if ($row === FALSE) { // No existe la fila.
                    $idioma = '';
                } else {
                    $idioma = $row['preferencia'];
                }

                //si existe, registro la sesion con los permisos
                if ( !isset($_SESSION['session_auth'])) { 
                    $session_auth=array (
                        'id_usuario'=>$id_usuario,
                        'usuario_cargo'=>$usuario_cargo,
                        'usuario_dtor'=>$usuario_dtor,
                        'id_cargo'=>$id_cargo_default,
                        'aPosiblesCargos'=>$aPosiblesCargos,
                        'username'=>$_POST['username'],
                        'password'=>$_POST['password'],
                        'esquema'=>$esquema,
                        'perms_activ'=>$perms_activ,
                        'mi_oficina'=>$mi_oficina,
                        'mi_oficina_menu'=>$mi_oficina_menu,
                        'expire'=>$expire,
                        'mail'=>$mail,
                        'idioma'=>$idioma,
                         );
                    $_SESSION['session_auth']=$session_auth;
                }
                //si existe, registro la sesion con la configuración
                if ( !isset($_SESSION['config'])) { 
                    $session_config=array (
                        'id_cargo'=>$id_cargo,
                        'username'=>$_POST['username'],
                        'password'=>$_POST['password'],
                        'perms_activ'=>$perms_activ,
                        'mi_oficina'=>$mi_oficina,
                        'mi_oficina_menu'=>$mi_oficina_menu,
                        'expire'=>$expire,
                        'mail'=>$mail,
                        'idioma'=>$idioma,
                         );
                    $_SESSION['config']=$session_config;
                }
                /* para la traducción. Después de registrar session_auth */
                cambiar_idioma();
            } else {
                $error = _("Password incorrecto");
                logout($idioma,$esquema,$error);
                die();
            }
        } else {
            $error = _("este usuario no existe");
            logout($idioma,$esquema,$error);
            die();
        }
	} else { // el primer cop
        $error = 0;
        $idioma = 'ca';
        $esquema = '';
		cambiar_idioma($idioma);	
        logout($idioma,$esquema,$error);
		die();
	}
} else {
	// ya esta registrado;
	/**
	 *  parece que los cambios con setlocale son para el proceso,
	 *  no para session ni multithreaded, por tanto hay que hacerlo cada vez
	 *  para la traducción 
	 */
	cambiar_idioma();
}
