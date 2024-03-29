<?php

namespace usuarios\controller;

use core\ConfigDB;
use core\ConfigGlobal;
use core\DBConnection;
use core\MyCrypt;
use core\ServerConf;
use core\ViewTwig;
use PDO;
use function core\cambiar_idioma;

function logout($idioma, $esquema, $error)
{
    $a_campos = [];
    $a_campos['nombre_entidad'] = $esquema;
    $a_campos['error'] = $error;
    $a_campos['idioma'] = $idioma;
    $a_campos['url'] = ConfigGlobal::getWeb();
    $oView = new ViewTwig(__NAMESPACE__);
    $oView->renderizar('login_form.html.twig', $a_campos);
}

function posibles_esquemas()
{
    $oConfigDB = new ConfigDB('tramity');
    $config = $oConfigDB->getEsquema('public');
    $oConexion = new DBConnection($config);
    $oDB = $oConexion->getPDO();

    $sQuery = "select nspname from pg_namespace where nspowner > 1000 AND nspname !~ '^zz' ORDER BY nspname";
    if (($oDblSt = $oDB->query($sQuery)) === false) {
        $sClauError = 'Schemas.lista';
        $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
        return false;
    }
    $a_esquemas = ['admin'];
    if (is_object($oDblSt)) {
        $oDblSt->execute();
        foreach ($oDblSt as $row) {
            $a_esquemas[] = $row[0];
        }
    }
    return $a_esquemas;
}

$esquema_web = getenv('ESQUEMA');

// Si el esquema no se pasa por directorio de la URL,
// está en el nombre del servidor:
if (empty($esquema_web)) {
    $servername = $_SERVER['HTTP_HOST'];
    $host = '.' . ServerConf::getSERVIDOR();
    $esquema_web = str_replace($host, '', $servername);
    // Para el docker, quito el puerto (:8000)
    $pos = strpos($esquema_web, ':');
    if ($pos !== false) {
        $esquema_web = substr($esquema_web, 0, $pos);
    }
}


if (!isset($_SESSION['session_auth'])) {
    //la segunda vez tengo el nombre y el password
    $idioma = '';
    if (isset($_POST['username'], $_POST['password'])) {
        $mail = '';

        $esquema = empty($_POST['esquema']) ? $esquema_web : $_POST['esquema'];

        // Comprobar si existe el esquema:
        $a_esquemas = posibles_esquemas();
        if (!in_array($esquema_web, $a_esquemas, true)) {
            $error = sprintf(_("Todavía NO se ha creado la nombre_entidad: %s"), $esquema_web);
            logout($idioma, $esquema_web, $error);
            die();
        }

        $aWhere = array('usuario' => $_POST['username']);

        $oConfigDB = new ConfigDB('tramity');

        if (!empty($esquema) && $esquema !== 'admin') {
            /* Si para todos los esquemas uso el mismo usuario de conexión a la DB (tramity), no hace falta: */
            $config = $oConfigDB->getEsquema($esquema);
        } else {
            $config = $oConfigDB->getEsquema('public');
        }
        $oConexion = new DBConnection($config);
        $oDB = $oConexion->getPDO();

        $query = "SELECT * FROM aux_usuarios WHERE usuario = :usuario";
        if (($oDBSt = $oDB->prepare($query)) === false) {
            $sClauError = 'login_obj.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
            return false;
        }

        if (($oDBSt->execute($aWhere)) === false) {
            $sClauError = 'login_obj.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
            return false;
        }

        $sPasswd = null;
        $oCrypt = new MyCrypt();
        $oDBSt->bindColumn('password', $sPasswd);
        if ($row = $oDBSt->fetch(PDO::FETCH_ASSOC)) {
            if ($oCrypt->encode($_POST['password'], $sPasswd) === $sPasswd) {
                $id_usuario = $row['id_usuario'];
                $id_cargo_preferido = $row['id_cargo_preferido'];
                // Comprobar que no hay suplente:
                // NO para el admin:
                if ($esquema === 'admin') {
                    $usuario_cargo = '';
                    $usuario_dtor = '';
                    $usuario_sacd = '';
                    $aPosiblesCargos[1] = 'admin';
                    $mi_id_oficina = '';
                    $expire = '';
                } else {
                    $query_cargos = "SELECT * FROM aux_cargos 
                                    WHERE id_cargo = $id_cargo_preferido ";
                    if (($stmt = $oDB->query($query_cargos)) === false) {
                        $sClauError = 'login_obj.prepare';
                        $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
                        return false;
                    }
                    $aCargo = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!empty($aCargo['id_suplente'])) {
                        $id_suplente = $aCargo['id_suplente'];
                        $query = "SELECT * FROM aux_usuarios WHERE id_usuario = $id_suplente";
                        if (($stmt_suplente = $oDB->query($query)) === false) {
                            $sClauError = 'login_obj.suplente';
                            $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
                            return false;
                        }
                        $aSuplente = $stmt_suplente->fetch(PDO::FETCH_ASSOC);
                        $nom_suplente = $aSuplente['nom_usuario'];
                        $txt_alert = sprintf(_("(%s) está asignado como suplente a este cargo"), $nom_suplente);
                        $a_campos = ['txt_alert' => $txt_alert, 'btn_cerrar' => FALSE];
                        $oView = new ViewTwig('expedientes/controller');
                        $oView->renderizar('alerta.html.twig', $a_campos);
                    }

                    // el usuario default, y el admin no tienen cargo:
                    // los cargos que puede tener (suplencias)
                    $query_titular = "SELECT 1 as preferido,cargo,director,sacd,id_cargo,id_oficina,cargo FROM aux_cargos 
                                    WHERE id_usuario = $id_usuario
                                    ";
                    $query_suplentes = "SELECT 2 as preferido,cargo,director,sacd,id_cargo,id_oficina,cargo FROM aux_cargos 
                                    WHERE id_suplente = $id_usuario
                                    ";
                    $query_cargos = "$query_titular UNION $query_suplentes ORDER BY 1,2";
                    if (($oDB->query($query_cargos)) === false) {
                        $sClauError = 'login_obj.prepare';
                        $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
                        return false;
                    }
                    $aPosiblesCargos = [];
                    $usuario_cargo = '';
                    $usuario_dtor = '';
                    $usuario_sacd = '';
                    $mi_id_oficina = '';
                    foreach ($oDB->query($query_cargos) as $aDades) {
                        $id_cargo = $aDades['id_cargo'];
                        $cargo = $aDades['cargo'];
                        if (!empty($id_cargo_preferido)) {
                            if ($aDades['id_cargo'] === $id_cargo_preferido) {
                                $usuario_cargo = $aDades['cargo'];
                                $usuario_dtor = $aDades['director'];
                                $usuario_sacd = $aDades['sacd'];
                                $mi_id_oficina = $aDades['id_oficina'];
                            }
                        } elseif ($aDades['preferido'] === 1) {
                            $usuario_cargo = $aDades['cargo'];
                            $usuario_dtor = $aDades['director'];
                            $usuario_sacd = $aDades['sacd'];
                            $mi_id_oficina = $aDades['id_oficina'];
                            $id_cargo_preferido = $aDades['id_cargo'];
                        }
                        $aPosiblesCargos[$id_cargo] = $cargo;
                    }

                    // si no tiene mail interior, cojo el exterior.
                    $mail = empty($mail) ? $row['email'] : $mail;
                    $expire = "";
                    // Para obligar a cambiar el password
                    if ($_POST['password'] === '1ªVegada') {
                        $expire = 1;
                    }
                }
                // Idioma
                $query_idioma = sprintf("select * from usuario_preferencias where id_usuario = '%s' and tipo = '%s' ", $id_usuario, "idioma");
                $oDBStI = $oDB->query($query_idioma);
                $row = $oDBStI->fetch(PDO::FETCH_ASSOC);
                $idioma = $row === FALSE ? '' : $row['preferencia'];

                // Nombre de la nombre_entidad
                $query_entidad = sprintf("select * from public.entidades where schema = '%s'", $esquema);
                $oDBStE = $oDB->query($query_entidad);
                $row = $oDBStE->fetch(PDO::FETCH_ASSOC);
                $nombreEntidad = $row === FALSE ? '' : $row['nombre'];

                //registro la sesión con los permisos
                $session_auth = array(
                    'id_usuario' => $id_usuario,
                    'usuario_cargo' => $usuario_cargo,
                    'usuario_dtor' => $usuario_dtor,
                    'usuario_sacd' => $usuario_sacd,
                    'id_cargo' => $id_cargo_preferido,
                    'aPosiblesCargos' => $aPosiblesCargos,
                    'role_actual' => $usuario_cargo,
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'esquema' => $esquema,
                    'nombreEntidad' => $nombreEntidad,
                    'mi_id_oficina' => $mi_id_oficina,
                    'expire' => $expire,
                    'mail' => $mail,
                    'idioma' => $idioma,
                );
                $_SESSION['session_auth'] = $session_auth;
                //si existe, registro la sesión con la configuración
                if (!isset($_SESSION['config'])) {
                    $session_config = array(
                        'id_cargo' => $id_cargo_preferido,
                        'username' => $_POST['username'],
                        'password' => $_POST['password'],
                        'mi_id_oficina' => $mi_id_oficina,
                        'expire' => $expire,
                        'mail' => $mail,
                        'idioma' => $idioma,
                    );
                    $_SESSION['config'] = $session_config;
                }
                /* para la traducción. Después de registrar session_auth */
                cambiar_idioma();
            } else {
                $error = _("Password incorrecto");
                logout($idioma, $esquema, $error);
                die();
            }
        } else {
            $error = _("este usuario no existe");
            logout($idioma, $esquema, $error);
            die();
        }
    } else { // la primera vez o tiempo agotado
        $error = '';
        $idioma = 'ca';
        cambiar_idioma($idioma);

        // Comprobar si existe el esquema:
        $a_esquemas = posibles_esquemas();
        if (!in_array($esquema_web, $a_esquemas, true)) {
            $error = sprintf(_("Todavía NO se ha creado la nombre_entidad: %s"), $esquema_web);
        }

        logout($idioma, $esquema_web, $error);
        die();
    }
} else {
    // ya esta registrado;
    /**
     *  parece que los cambios con setLocale son para el proceso,
     *  no para session ni multithreaded, por tanto hay que hacerlo cada vez
     *  para la traducción
     */
    cambiar_idioma();
}
