<?php
namespace core;

Class ServerConf {
    
	/* Fichero de configuraciones del sistema.
		La idea es poner en forma de variables todos los parámetros propios del systema
		
		Este fichero debe estar en algun lugar del Path del Apache? php?
	*/
	const SERVIDOR = 'tramity.local';
    const WEBDIR = 'tramity';
	const DIR = '/home/dani/tramity_local/tramity';
	const DIR_PWD= '/home/dani/tramity_local/conf';
	/*
	public static $auth_method="ldap";
	public static $auth_ldap_server="192.168.33.7";
	*/
	public static $auth_method='database';
	
	/**
	 * En el caso de estar en la dmz, se puede corregir el valor 
	 * por la variable del servidor
	 * (para poder entrar como interior desde el exterior, para sf)
	 * 
	 * @var boolean
	 */
	public static $dmz=FALSE;  // valores: FALSE para interior, TRUE para exterior.
	public static $debug=TRUE;

	public static $web_server='//'.self::SERVIDOR;
	public static $web_port=':443';
	public static $web_path='/'.self::WEBDIR;
	
	public static $dir_web=self::DIR;
	public static $directorio=self::DIR;
	public static $dir_libs=self::DIR.'/libs';
	public static $dir_estilos=self::DIR.'/css';
	public static $dir_scripts=self::DIR.'/scripts';
	public static $dir_icons=self::DIR.'/images';
	public static $dir_languages=self::DIR.'/languages';
}