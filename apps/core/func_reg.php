<?php
namespace core;

/**
* Funciones más comunes de la aplicación
*/
// INICIO Cabecera global de URL de controlador *********************************

require_once ("global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


/**
*
* Función quitar la hora de las fechas. 
* va por ref, para modificar los valores de un array...
*
*/
function fecha_sin_time(&$fecha) {
	// si está en formato:'YYYYmmddTHHmmssZ'.
	if (strstr($fecha,'T')) {
		list( $f_fecha,$y,$m,$d) = fecha_array($fecha);
		$f=date("Ymd",mktime(0,0,0,$m,$d,$y));
	} elseif (strstr($fecha,'/')) {
		list( $d,$m,$y) = preg_split('/[\.\/-]/',$fecha);
		$f=date("Ymd",mktime(0,0,0,$m,$d,$y));
	} else {
		$f=$fecha;
	}
	$fecha=$f;
}
/**
*
* Función para buscar los movimientos de un escrito de cancillería.
*
*/
function buscar_mov($id_reg) {
	$oDBR=$GLOBALS['oDBR'];
	// movimientos
	$sql_mov="SELECT id_mov, id_reg, origen, destino, f_mov, observ, id_modo_envio
				FROM cancilleria_mov
				WHERE id_reg=$id_reg
				ORDER BY f_mov
				";
	$j=0;
	$mov="";
	foreach ($oDBR->query($sql_mov) as $row_2) {
		$j++;
		$origen=$row_2["origen"];
		$destino=$row_2["destino"];
		$f_mov=$row_2["f_mov"];
		$observ=$row_2["observ"];
		
		$od=strtolower(substr($origen,0,1).substr($destino,0,1));
		$a_movimientos=array_movimientos();
		
		$txt="- ".$a_movimientos[$od];
		$txt.=" ".$f_mov;
		if (!empty($observ)) $txt.=" (".$observ.").";
		$mov .= $txt."<br>";
	}
	return $mov;
}

/**
*
* Función cambiar obtener dia, mes, año de una fecha tipo 'YYYYmmddTHHmmssZ'.
* Devuelve un array con: fecha sin hora,dia,mes,año.
*/
function fecha_array($fecha) {
	$fecha=substr($fecha,0,8);
	$patterns = '/(\d{1,4})(\d{1,2})(\d{1,2})/';
    //$replace = '\\4/\\3/\\1\\2';
	preg_match($patterns, $fecha, $matches);
	return $matches;
}

function buscar_prot_entrada($id_reg) {
	$oDBR=$GLOBALS['oDBR'];
// protocolo de la entrada
	$sql_ref="SELECT u.sigla, en.prot_num,en.prot_any,en.mas
				FROM entradas en JOIN lugares u USING (id_lugar)
				WHERE en.id_reg=$id_reg 
				";
	$j=0;
	$referencias="";
	foreach ($oDBR->query($sql_ref) as $row) {
		$j++;
		$ref_sigla=$row["sigla"];
		$ref_prot_num=$row["prot_num"];
		$ref_prot_any=any_2($row["prot_any"]);
		$ref_mas=$row["mas"];
		
		$ref=$ref_sigla." ".$ref_prot_num."/".$ref_prot_any;
		if (!empty($ref_mas)) $ref .= " (".$ref_mas.")" ;
		if ($j > 1) $referencias .= "<br>";
		$referencias .= $ref;
	}
	return $referencias;
}


/**
*
* Función para poner el año en cuatro cifras.
*
*/
/*
function any_4($any) {
	if ($any<100) {
		if ($any < 70 ) {
			$a=2000+$any;
		} else {
			$a=1900+$any;
		}
	} else {
		$a=$any;
	}
	return $a;
}
*/

/**
*
* Función para poner el año en dos cifras.
*
*/
function any_2($any) {
	if (strlen($any)==4) {
		$a=substr($any,-2);
	} else {
		$a=$any;
	}
	return $a;
}


/**
*
* creo un array con los posibles movimientos...
*	primero el id, después el texto
*
*/
function array_movimientos() {
	$opciones=array ( "cd" => "recibido de cr el día",
						"dc" =>  "enviado a cr el día",
					 	"ad" => "recibido de asr el día",
						"da" =>  "enviado a asr el día",
						"fd" =>  "recibido de dlbf el día",
						"df" =>  "enviado a dlbf el día",
						"do" =>  "aprobado el día",
						"di" =>  "enviado al IESE el día",
		   	);
	return $opciones;
}
