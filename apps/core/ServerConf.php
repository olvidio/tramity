<?php

namespace core;

/*
 * poner en el include de php.ini: "/var/www/conf"
 */
include "enviorement.conf";

class ServerConf
{

    /* Fichero de configuraciones del sistema.
        La idea es poner en forma de variables todos los parámetros propios del sistema

        Este fichero debe estar en algún lugar del Path del Apache? php?
    */


    public static function getSERVIDOR(): string
    {
        return $GLOBALS['SERVIDOR'];
    }

    public static function getWEBDIR(): string
    {
        return $GLOBALS['WEBDIR'];
    }

    public static function getDIR(): string
    {
        return $GLOBALS['DIR'];
    }

    public static function getDIR_PWD(): string
    {
        return $GLOBALS['DIR_PWD'];
    }

    public static function getWeb_Port(): string
    {
        return $GLOBALS['web_port'];
    }

    public static function is_debug_mode(): bool
    {
        return $GLOBALS['debug'];
    }

    public static function web_path(): string
    {
        return '/'.self::getDIR();
    }

    public static function dir_web(): string
    {
        return self::getDIR();
    }

    public static function directorio(): string
    {
        return self::getDIR();
    }
    public static function dir_libs(): string
    {
        return self::getDIR();
    }
    public static function dir_languages(): string
    {
        return self::getDIR().'/languages';
    }
}
