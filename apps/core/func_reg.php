<?php
namespace core;

/**
* Funciones más comunes de la aplicación
* Al final sólo queda una...
*/
// INICIO Cabecera global de URL de controlador *********************************

require_once ("global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


/**
*
* Función para poner el año en dos cifras.
*
*/
function any_2($any) {
	//convert to String
	$any = (string) $any;
	if (strlen($any)==4) {
		$a=substr($any,-2);
	} else {
	    // si se ha convertido en numérico, puede faltar el 0 al inicio (05)
	   if (strlen($any)==1) {
		    $a='0'.$any;
	    } else {
		    $a=$any;
	    }
	}
	return $a;
}

