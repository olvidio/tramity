<?php

namespace core;

use usuarios\model\entity\Cargo;

class ConfigGlobal extends ServerConf
{

    public static function getHost()
    {
        // Coger el nombre del host, con el dominio tengo lios con otros servidores.
        // para que sirva para tramity.red.local y etherpad.red.local
        return $_SERVER['HTTP_HOST'];
    }

    public static function getDomain()
    {
        // Coger el nombre del dominio para que sirva para tramity.red.local y etherpad.red.local
        $regs = [];
        $host = $_SERVER['HTTP_HOST'];
        preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $host, $regs);

        // Para el docker, quito el puerto (:8000)
        $pos = strpos($host, ':');
        if ($pos !== false) {
            $host = substr($host, 0, $pos);
        }
        return $host;
    }

    public static function getEsquema()
    {
        $servername = $_SERVER['HTTP_HOST'];
        $host = '.' . self::SERVIDOR;
        $esquema_web = str_replace($host, '', $servername);
        // Para el docker, quito el puerto (:8000)
        $pos = strpos($esquema_web, ':');
        if ($pos !== false) {
            $esquema_web = substr($esquema_web, 0, $pos);
        }
        return $esquema_web;
    }

    public static function getWeb_NodeScripts()
    {
        return self::getWeb() . '/node_modules';
    }

    public static function getWeb()
    {
        // parece que el host ya lleva el puerto
        //return '//' . $_SERVER['HTTP_HOST'] . self::getWebPort() . self::getWebPath();
        return '//' . $_SERVER['HTTP_HOST'] . self::getWebPath();
    }

    /*
    public static function getWebPort()
    {
        return self::$web_port;
    }
    */

    public static function getWebPath()
    {
        $path = '';
        if (!empty($_SERVER['ESQUEMA'])) {
            $path .= '/' . $_SERVER['ESQUEMA'];
        }
        return $path;
    }

    public static function getWeb_public()
    {
        return self::getWeb() . '/public';
    }

    public static function getWeb_icons()
    {
        return self::getWeb() . '/images';
    }

    public static function is_debug_mode()
    {
        return self::$debug;
    }

    /**
     * Se cambia al cambiar el role
     * @return integer
     */
    public static function role_id_cargo()
    {
        $id_cargo = (int)$_SESSION['session_auth']['id_cargo'];
        if (empty($id_cargo)) {
            $msg = _("No tiene asignado un cargo válido");
            $msg .= ".<br><br>";
            $msg .= '<script>fnjs_logout = function () {
                var parametros = "logout=si";
                top.location.href = "index.php?" + parametros;
            }</script>';
            $msg .= '<a class="nav-link" href="#" onclick="fnjs_logout()">';
            $msg .= _("Salir");
            $msg .= '</a>';
            exit ($msg);
        }
        return $id_cargo;
    }

    /**
     * Se cambia al cambiar el role
     * @return integer
     */
    public static function role_id_oficina()
    {
        return $_SESSION['session_auth']['mi_id_oficina'];
    }

    public static function mi_id_usuario()
    {
        return $_SESSION['session_auth']['id_usuario'];
    }

    public static function mi_usuario_cargo()
    {
        return $_SESSION['session_auth']['usuario_cargo'];
    }

    public static function soy_dtor()
    {
        return is_true($_SESSION['session_auth']['usuario_dtor']);
    }

    public static function soy_sacd()
    {
        return is_true($_SESSION['session_auth']['usuario_sacd']);
    }

    public static function getVista()
    {
        if (ConfigGlobal::role_actual() === 'secretaria') {
            $vista = 'secretaria';
        } else {
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
                $vista = 'home';
            }
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                $vista = 'ctr';
            }
        }
        return $vista;
    }

    /**
     * Se cambia al cambiar el role
     * @return string
     */
    public static function role_actual()
    {
        return $_SESSION['session_auth']['role_actual'];
    }

    public static function mi_usuario()
    {
        return $_SESSION['session_auth']['username'];
    }

    public static function mi_pass()
    {
        return $_SESSION['session_auth']['password'];
    }

    public static function mi_schema()
    {
        return $_SESSION['session_auth']['esquema'];
    }

    public static function mi_mail()
    {
        return $_SESSION['session_auth']['mail'];
    }

    // ----------- Entidad -------------------
    public static function nombreEntidad()
    {
        return $_SESSION['session_auth']['nombreEntidad'];
    }
    // ----------- Idioma -------------------
    //es_ES.UTF-8
    public static function mi_Idioma()
    {
        return $_SESSION['session_auth']['idioma'];
    }

    //es
    public static function mi_Idioma_short()
    {
        return substr($_SESSION['session_auth']['idioma'], 0, 2);
    }
}