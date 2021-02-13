<?php
namespace core;

/**
* Funciones más comunes de la aplicación
*/
// INICIO Cabecera global de URL de controlador *********************************
	use core\ConfigGlobal;
use usuarios\model\entity\GestorUsuario;
use usuarios\model\entity\Usuario;
use web\Lista;

require_once ("global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$num_max_ctr=1;
$num_max_cr=1;
$rango_inf_dl=1;
$rango_sup_dl=1;
$rango_inf_cr=1;
$rango_sup_cr=1;
$error_prot=1;
$error_fecha=1;
$plazo_normal=1;
$plazo_urgente=1;
$plazo_muy_urgente=1;


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
* Función para buscar las fechas repetidas segun una rrule dentro de un periodo
*
*/
function recurrencias($rrule,$dtstart,$dtend,$f_limite) {
	if (empty($rrule)) { exit; }
	$a_dias_w=array("MO"=>"Monday","TU"=>"Tuesday","WE"=>"Wednesday","TH"=>"Thursday","FR"=>"Friday","SA"=>"Saturday","SU"=>"Sunday");
	
	$rta=desmontar_rule($rrule);
	if (empty($rta['tipo'])) { echo _('No hay tipo en recurrencias'); return array(); }
   	// si hay un "UNTIL", lo pongo como fecha fin.  
	if (!empty($rta['until'])) $dtend=$rta['until']; // si hay un "UNTIL", lo pongo como fecha fin.

	$any_actual=date("Y");
	if (strstr($f_limite,'T')) {
		list( $f_limite,$y,$m,$d) = fecha_array($f_limite);
		$f_limite=date("Ymd",mktime(0,0,0,$m,$d,$y));
	}
	// Si no existe f_fin del periódico, hago que sea igual al fin del periodo escogido:
	if (empty($dtend)) {
		$dtend=$f_limite;
	} else {
		if ($dtend > $f_limite) $dtend=$f_limite;
	}
	// paso del formato YmdT000000Z a Y,m,d
	list( $dtend,$any_fin,$month_fin,$day_fin) = fecha_array($dtend);
	list( $dtstart,$any_ini,$month_ini,$day_ini) = fecha_array($dtstart);

	switch ($rta['tipo']) {
		case "d_a":
			switch ($rta['tipo_dia']) {
				case "num_ini":
					$dias_db=$day_ini; // cojo el dia de la fecha inicio.
					$meses_db=array($month_ini);
					$tipo_dia="num_ini";
				break;
				case "num":
					$dias_db=explode(",",$rta['dias']);
					$meses_db=explode(",",$rta['meses']);
					$tipo_dia="num";
				break;
				case "ref":
					$dia_w_db=$rta['dia_semana'];
					$ordinal_db=$rta['ordinal'];
					$meses_db=explode(",",$rta['meses']);
					$tipo_dia="ref";
				break;
				case "num_dm":
					$meses_db=explode(",",$rta['meses']);
					$dias_db=explode(",",$rta['dias']);
					$tipo_dia="num";
				break;
			}
		break;
		case "d_m":
			$meses_db=array(1,2,3,4,5,6,7,8,9,10,11,12);
			switch ($rta['tipo_dia']) {
				case "num_ini":
					$dias_db=$day_ini; // cojo el dia de la fecha inicio.
					$tipo_dia="num_ini";
				break;
				case "num":
					$dias_db=explode(",",$rta['dias']);
					$tipo_dia="num";
				break;
				case "ref":
					$dia_w_db=$rta['dia_semana'];
					$ordinal_db=$rta['ordinal'];
					$tipo_dia="ref";
				break;
			}
		break;
		case "d_s":
			$meses_db=array(1,2,3,4,5,6,7,8,9,10,11,12);
			$tipo_dia="semana";
			switch ($rta['tipo_dia']) {
				case "num_ini":
					// busco la letra del dia de la fecha inicio.
					$dias_w_db=array(strtoupper(substr(date("D",mktime(0,0,0,$month_ini,$day_ini,$any_ini)),0,2)));
					$tipo_dia="semana";
				break;
				case "ref":
					$dias_w_db=explode(",",$rta['dias']);
					$tipo_dia="semana";
				break;
			}
		break;
		case "d_d":
			$meses_db=array(1,2,3,4,5,6,7,8,9,10,11,12);
			$dias_db=$day_ini; // cojo el dia de la fecha inicio.
			$tipo_dia="todos";
		break;
	}
	// caso de dias y meses
	// antes tendría que mirar por cada año. Desde el actual hasta el fin de la condicion.
	$f_recurrencias=array();
	for ($any=$any_actual;$any<=$any_fin;$any++) {
		// Me salto los años anteriores a la fecha de inicio
		if ( $any < $any_ini || $any > $any_fin) { continue;}
		// por cada mes miro que dias
		if (!is_array($meses_db)) continue;
		foreach ($meses_db as $mes) {
			// Me salto los meses anteriores a la fecha de inicio y los posteriores a la de fin
			if (($mes < $month_ini && $any == $any_ini) || ($mes > $month_fin && $any == $any_fin)) { continue;}
			switch ($tipo_dia) {
				case "num_ini":
					$dia=$dias_db;
						// Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
						if (($dia < $day_ini && $mes == $month_ini && $any==$any_ini) || ($dia > $day_fin  && $mes == $month_fin && $any==$any_fin)) { break; }
						$f_recurrencias[]="$dia/$mes/$any";
					break;
				case "num":
					foreach ($dias_db as $dia) {
						// Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
						if (($dia < $day_ini && $mes == $month_ini && $any==$any_ini) || ($dia > $day_fin  && $mes == $month_fin && $any==$any_fin)) { break; }
						$f_recurrencias[]="$dia/$mes/$any";
					}
					break;
				case "ref":
					$ordinal=0;
					//echo "bd: $ordinal_db, $dia_w_db<br>";
					if ($ordinal_db > 0) {
						$dia_w_txt=$a_dias_w[$dia_w_db];
						$txt="$ordinal_db $dia_w_txt";
						$dia=date("d",strtotime($txt,mktime(0,0,0,$mes,0,$any)));
					} 
					if ($ordinal_db < 0) {
						$dia_w_txt=$a_dias_w[$dia_w_db];
						$txt="$ordinal_db $dia_w_txt";
						$dia=date("d",strtotime($txt,mktime(0,0,0,$mes+1,1,$any)));
					} 
					// Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
					if (($dia < $day_ini && $mes == $month_ini && $any==$any_ini) || ($dia > $day_fin  && $mes == $month_fin && $any==$any_fin)) { break; }
					$f_recurrencias[]=$dia."/".$mes."/".$any;
				break;
				case "semana":
					$dias_del_mes=date("d",mktime(0,0,0,$mes+1,0,$any));
					for ($dia=1;$dia<=$dias_del_mes;$dia++) {
						$letras=strtoupper(substr(date("D",mktime(0,0,0,$mes,$dia,$any)),0,2));
						if (in_array($letras,$dias_w_db)) {
							// Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
							if (($dia < $day_ini && $mes == $month_ini && $any==$any_ini) || ($dia > $day_fin  && $mes == $month_fin && $any==$any_fin)) { break; }
							$f_recurrencias[]=$dia."/".$mes."/".$any;
						}
					}
				break;
				case "todos":
					$dias_del_mes=date("d",mktime(0,0,0,$mes+1,0,$any));
					for ($dia=1;$dia<=$dias_del_mes;$dia++) {
						// Me salto los dias del mes anteriores a la fecha de inicio y los posteriores a la de fin
						if (($dia < $day_ini && $mes == $month_ini && $any==$any_ini) || ($dia > $day_fin  && $mes == $month_fin && $any==$any_fin)) { continue;}
						$f_recurrencias[]=$dia."/".$mes."/".$any;
					}
				break;
			}
		}
	}
	//print_r($f_recurrencias);
	//return $f_recurrencias;
	/*********** Ordenar  **********************/
	if (count($f_recurrencias)) {
		// ordenar por f_plazo:
		// Obtain a list of columns
		foreach ($f_recurrencias as $key => $row) {
			$f_Ymd=fecha_DMY2YMD($row);
			$f_dmY=fecha_YMD2DMY($f_Ymd); // para poner los '0'
			$fechas[$f_Ymd] = $f_dmY;
		}
		// Sort the data with fechas descending
		ksort($fechas);
		return $fechas;
	} else {
		return array();
	}
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
* Deshago un RRULE del calendario y develvo un vector con los
*  valores necesarios para dar las opciones en el formulario web.
*
*/
function desmontar_rule($rrule) {
	$rta='';
	$error=0;
	$meses='';
	$dias='';
	$dia_semana='';
	$freq='';
	$reglas=explode(";", $rrule);
	foreach ($reglas as $regla) {
		list($opcion,$param)=explode("=",$regla);
		switch($opcion){
			case "FREQ":
				$freq=$param;
				break;
			case "INTERVAL":
				if ($param!=1) $error=1;
				break;
			case "BYMONTHDAY":
				$dias=$param;
				break;
			case "BYMONTH":
				$meses=$param;
				break;
			case "BYDAY":
				$dia_semana=$param;
				break;
			case "UNTIL":
				$f_until=$param;
				break;
		}
	}
	if (!empty($f_until)) $rta['until']=$f_until;

	if (!$error && $freq=="YEARLY" && !$dias && !$meses && !$dia_semana) {
		$rta['tipo']="d_a";
		$rta['tipo_dia']="num_ini";
		$rta['dias']="";
	}
	if (!$error && $freq=="YEARLY" && $dias && $meses && !$dia_semana) {
		$rta['tipo']="d_a";
		$rta['tipo_dia']="num";
		$rta['meses']=$meses;
		$rta['dias']=$dias;
	}
	if (!$error && $freq=="YEARLY" && $dia_semana && !$dias && $meses) {
		$rta['tipo']="d_a";
		$rta['tipo_dia']="ref";
		preg_match('/([\+\-]*)(\d)(\w\w)/', $dia_semana, $matches);
		//Array ( [0] => -1SU [1] => - [2] => 1 [3] => SU )
		$signo=$matches[1];
		$rta['ordinal']=$signo.$matches[2];
		$rta['dia_semana']=$matches[3];
		$rta['meses']=$meses;
	}
	if (!$error && $freq=="DAILY" && $dias && $meses) {
		$rta['tipo']="d_a";
		$rta['tipo_dia']="num_dm";
		$rta['meses']=$meses;
		$rta['dias']=$dias;
	}
	if (!$error && $freq=="MONTHLY" && !$dias && !$meses && !$dia_semana) {
		$rta['tipo']="d_m";
		$rta['tipo_dia']="num_ini";
		$rta['dias']="";
	}
	if (!$error && $freq=="MONTHLY" && $dias && !$meses) {
		$rta['tipo']="d_m";
		$rta['tipo_dia']="num";
		$rta['dias']=$dias;
	}
	if (!$error && $freq=="MONTHLY" && $dia_semana && !$dias && !$meses) {
		$rta['tipo']="d_m";
		$rta['tipo_dia']="ref";
		preg_match('/([\+\-]*)(\d)(\w\w)/', $dia_semana, $matches);
		//Array ( [0] => -1SU [1] => - [2] => 1 [3] => SU )
		$signo=$matches[1];
		$rta['ordinal']=$signo.$matches[2];
		$rta['dia_semana']=$matches[3];
	}
	if (!$error && $freq=="WEEKLY" && !$dia_semana && !$meses) {
		$rta['tipo']="d_s";
		$rta['tipo_dia']="num_ini";
	}
	if (!$error && $freq=="WEEKLY" && $dia_semana && !$meses) {
		$rta['tipo']="d_s";
		$rta['tipo_dia']="ref";
		$rta['dias']=$dia_semana;
	}
	if (!$error && $freq=="DAILY" && !$dias && !$meses) {
		$rta['tipo']="d_d";
		$rta['meses']="";
		$rta['dias']="";
	}
	return $rta;
}

/**
*
* Genero la RRULE para el calendario, a partir de un vector con los
*  valores del formulario web.
*
*/
function montar_rrule($request) {
	//print_r($request);
	switch($request['tipo']){
		case "d_a":
			switch($request['tipo_dia']){
				case "num_ini":
					$rrule="FREQ=YEARLY";
				break;
				case "num":
					$meses=$request['meses'];
					if ($request['dias'] && $meses) {
						$rrule="FREQ=YEARLY;BYMONTHDAY=${request['dias']};BYMONTH=$meses";
					} else {
						$rrule="";
					}
				break;
				case "ref":
					$meses=$request['meses'];
					$ordinal=$request['ordinal'];
					if ($ordinal>0) { $ordinal="+".$ordinal; } else { $ordinal="-".$ordinal; }
					$dia_semana=$request['dia_semana'];
					if ($dia_semana && $meses) {
						$rrule="FREQ=YEARLY;BYDAY=$ordinal$dia_semana;BYMONTH=$meses";
					} else {
						$rrule="";
					}
				break;
				case "num_dm":
					$dias=implode(",",$request['dias']);
					$meses=implode(",",$request['meses']);
					if ($dias || $meses) {
						$rrule="FREQ=DAILY;BYMONTH=$meses;BYMONTHDAY=$dias";
					} else {
						$rrule="";
					}
				break;
			}
		break;
		case "d_m":
			switch($request['tipo_dia']){
				case "num_ini":
					$rrule="FREQ=MONTHLY";
				break;
				case "num":
					if (!empty($request['dias'])) {
						$rrule="FREQ=MONTHLY;BYMONTHDAY=${request['dias']}";
					} else {
						$rrule="";
					}
				break;
				case "ref":
					$ordinal=$request['ordinal'];
					if ($ordinal>0) { $ordinal="+".$ordinal; } else { $ordinal="-".$ordinal; }
					$dia_semana=$request['dia_semana'];
					if (!empty($dia_semana)) {
						$rrule="FREQ=MONTHLY;BYDAY=$ordinal$dia_semana";
					} else {
						$rrule="";
					}
				break;
			}
		break;
		case "d_s":
			switch($request['tipo_dia']){
				case "num_ini":
					$rrule="FREQ=WEEKLY";
				break;
				case "ref":
					$dias=implode(",",$request['dias']);
					if (!empty($dias)) {
						$rrule="FREQ=WEEKLY;BYDAY=$dias";
					} else {
						$rrule="";
					}
				break;
			}
		break;
		case "d_d":
			$rrule="FREQ=DAILY";
		break;
	}
	//echo "rrule: $rrule<br>";
	if (!empty($request['until'])) {
		list($d_f_until,$m_f_until,$a_f_until) = preg_split('/[\.\/-]/', $request['until']);
		$f_cal_until=date("Ymd",mktime(0,0,0,$m_f_until,$d_f_until,$a_f_until));
		$rrule.=";UNTIL=$f_cal_until";
	}

	return $rrule;
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

/**
*
* creo un array con los meses
*	primero el id, después la sigla
*
*/
function array_meses() {
	
	$m1=_("enero");
	$m2=_("febrero");
	$m3=_("marzo");
	$m4=_("abril");
	$m5=_("mayo");
	$m6=_("junio");
	$m7=_("julio");
	$m8=_("agosto");
	$m9=_("septiembre");
	$m10=_("octubre");
	$m11=_("noviembre");
	$m12=_("diciembre");
	
	$opciones=array('1'=>$m1,
					'2'=>$m2,
					'3'=>$m3,
					'4'=>$m4,
					'5'=>$m5,
					'6'=>$m6,
					'7'=>$m7,
					'8'=>$m8,
					'9'=>$m9,
					'10'=>$m10,
					'11'=>$m11,
					'12'=>$m12 );
		
	return $opciones;
}
/**
*
* creo un array con los dias de la semana
*	primero el id (en inglés), después el nombre
*
*/
function array_dias_semana() {
	
	$d1=_("lunes");
	$d2=_("martes");
	$d3=_("miércoles");
	$d4=_("jueves");
	$d5=_("viernes");
	$d6=_("sábado");
	$d7=_("domingo");
	
	$opciones=array("MO"=>$d1,
					"TU"=>$d2,
					"WE"=>$d3,
					"TH"=>$d4,
					"FR"=>$d5,
					"SA"=>$d6,
					"SU"=>$d7 );

	return $opciones;
}

