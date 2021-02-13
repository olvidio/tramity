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
* Función para buscar los destinos de un escrito.
*
*/
/*
function buscar_destinos($id_reg) {
	$oDBR=$GLOBALS['oDBR'];
	$sql_dest="SELECT u.sigla, d.id_salida, d.prot_num as dest_prot_num,d.prot_any as dest_prot_any,d.mas as dest_mas
					FROM destinos d ,lugares u
					WHERE d.id_reg=$id_reg AND d.id_lugar=u.id_lugar
					";
	$h=0;
	$destinos="";
	foreach ($oDBR->query($sql_dest) as $row_1) {
		$h++;
		$dest_sigla=$row_1["sigla"];
		$dest_prot_num=$row_1["dest_prot_num"];
		$dest_prot_any=any_2($row_1["dest_prot_any"]);
		$dest_mas=$row_1["dest_mas"];
		
		$dest=$dest_sigla;
		if (!empty($dest_prot_num)) $dest.=" ".$dest_prot_num."/".$dest_prot_any;
		if (!empty($dest_mas)) $dest .= " (".$dest_mas.")" ;
		if ($h > 1) $destinos .= "<br>";
		$destinos .= $dest;
	}
	return $destinos;
}
*/

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

/* ****** ARRAY  de permisos para el registro ***********/
	/* valores de permiso:
		0: para no ver nada.
		1: para no ver el detalle, si el asunto.
		2: leer
		3: escribir.
		4: pasar el escrito a reservado y viceversa. (dtor of responsable.)
	*/
//$array_registro_perm=array()()();
/*
$array_registro_perm['n']['pendents']['a']=2;		// del grupo de pendents (permanente). ASUNTO
$array_registro_perm['n']['pendents']['d']=2;		// del grupo de pendents (permanente). DETALLE
$array_registro_perm['n']['dtor_pral']['a']=2;		// director de la oficna principal.
$array_registro_perm['n']['dtor_pral']['d']=4;		// director de la oficna principal.
$array_registro_perm['n']['dtor_imp']['a']=2;		// directores de las oficinas implicadas.
$array_registro_perm['n']['dtor_imp']['d']=2;		// directores de las oficinas implicadas.
$array_registro_perm['n']['dtor']['a']=2;			// resto de directores.
$array_registro_perm['n']['dtor']['d']=2;			// resto de directores.
$array_registro_perm['n']['secretario']['a']=4;		// secretario de la dl.
$array_registro_perm['n']['secretario']['d']=4;		// secretario de la dl.
$array_registro_perm['n']['of_scl']['a']=4;			// oficiales de secretaría.
$array_registro_perm['n']['of_scl']['d']=0;			// oficiales de secretaría.
$array_registro_perm['n']['of_pral']['a']=2;			// oficiales de la oficina principal.
$array_registro_perm['n']['of_pral']['d']=3;			// oficiales de la oficina principal.
$array_registro_perm['n']['of_imp']['a']=2;			// oficiales de las oficinas implicadas.
$array_registro_perm['n']['of_imp']['d']=2;			// oficiales de las oficinas implicadas.
$array_registro_perm['n']['of']['a']=2;				// resto de oficiales.
$array_registro_perm['n']['of']['d']=0;				// resto de oficiales.
$array_registro_perm['r']['pendents']['a']=2;	
$array_registro_perm['r']['pendents']['d']=2;	
$array_registro_perm['r']['dtor_pral']['a']=2;	
$array_registro_perm['r']['dtor_pral']['d']=4;	
$array_registro_perm['r']['dtor_imp']['a']=2;	
$array_registro_perm['r']['dtor_imp']['d']=2;	
$array_registro_perm['r']['dtor']['a']=2;		
$array_registro_perm['r']['dtor']['d']=2;		
$array_registro_perm['r']['secretario']['a']=4;	
$array_registro_perm['r']['secretario']['d']=4;	
$array_registro_perm['r']['of_scl']['a']=0;		
$array_registro_perm['r']['of_scl']['d']=0;		
$array_registro_perm['r']['of_pral']['a']=0;		
$array_registro_perm['r']['of_pral']['d']=0;		
$array_registro_perm['r']['of_imp']['a']=0;		
$array_registro_perm['r']['of_imp']['d']=0;		
$array_registro_perm['r']['of']['a']=0;			
$array_registro_perm['r']['of']['d']=0;			
*/
/**
*
* Función para buscar el permiso de la oficina actual de los pendientes.
*
*/
/*
function permiso_detalle_pendiente($oficina,$reservado,$tipo="a") {
	$oDBR=$GLOBALS['oDBR'];
	//$perm=$session_auth["perms"];
	// valores de permiso:
	//	1: para no ver el detalle, si el asunto.
	//	2: leer
	//	3: escribir.
	//	4: modificar. (dtor of responsable.)
	//

	if ($reservado=="t") { $modo="r"; } else { $modo="n"; }
	// el secretario puede hacer todo siempre:
	if ($GLOBALS['oPerm']->have_perm("dtor") && $GLOBALS['oPerm']->have_perm("scl")) {
		$soy_2="secretario";
	} else {
		// oficinas implicadas:
		$sql_of="SELECT sigla,permiso FROM x_oficinas
				WHERE sigla='$oficina'";
		//echo "q: $sql_of<br>";
		$t=0;
		$soy="x";
		foreach ($oDBR->query($sql_of) as $row) {
			$t++;
			$of=$row["permiso"];
			//$responsable=$row["responsable"];
			//echo "of: $of, resp: $responsable<br>";
			if ($GLOBALS['oPerm']->have_perm("$of")) { $soy="of_pral"; }
		}
		switch ($soy) {
			case "of_pral":
				if ($GLOBALS['oPerm']->have_perm("dtor")) { 
					$soy_2="dtor_pral";
				} else {
					$soy_2="of_pral";
				}
				break;
			default:
				if ($GLOBALS['oPerm']->have_perm("dtor")) { 
					$soy_2="dtor";
				} else {
					$soy_2="of";
				}
				if ($GLOBALS['oPerm']->have_perm("scl")) { $soy_2="of_scl"; }
				break;
		}
	}
	$permiso=$GLOBALS['array_registro_perm'][$modo][$soy_2][$tipo];	
	//echo "perm: $permiso, r:$modo, $soy::$soy_2, tipo: $tipo<br>";	
	return $permiso;
}
*/
/**
*
* Función para buscar el permiso de la oficina actual.
*
*/
/*
function permiso_detalle($id_reg,$reservado,$tipo="a",$cancilleria="f") {
	$oDBR=$GLOBALS['oDBR'];

	//$perm=$session_auth["perms"];
	// valores de permiso:
	//	1: para no ver el detalle, si el asunto.
	//	2: leer
	//	3: escribir.
	//	4: modificar. (dtor of responsable.)
	
	if (empty($id_reg)) { return 1; }

	if ($reservado=="t") { $modo="r"; } else { $modo="n"; }
	// el secretario puede hacer todo siempre:
	if ($GLOBALS['oPerm']->have_perm("dtor") && $GLOBALS['oPerm']->have_perm("scl")) {
		$soy_2="secretario";
	} else {
		// oficinas implicadas:
		$sql_of="SELECT sigla,responsable,u.permiso FROM oficinas of JOIN x_oficinas u USING (id_oficina)
				WHERE of.id_reg=$id_reg AND cancilleria='$cancilleria'";
		//echo "q: $sql_of<br>";
		$t=0;
		$soy="x";
		foreach ($oDBR->query($sql_of) as $row) {
			$t++;
			$of=$row["permiso"];
			$responsable=$row["responsable"];
			//echo "of: $of, resp: $responsable<br>";
			if ($GLOBALS['oPerm']->have_perm("$of") && $responsable=="t") { $soy="of_pral"; }
			if ($GLOBALS['oPerm']->have_perm("$of") && $soy!="of_pral") { $soy="of_imp"; }
		}
		switch ($soy) {
			case "of_pral":
				if ($GLOBALS['oPerm']->have_perm("dtor")) { 
					$soy_2="dtor_pral";
				} else {
					$soy_2="of_pral";
				}
				break;
			case "of_imp":
				if ($GLOBALS['oPerm']->have_perm("dtor")) { 
					$soy_2="dtor_imp";
				} else {
					$soy_2="of_imp";
				}
				break;
			default:
				if ($GLOBALS['oPerm']->have_perm("dtor")) { 
					$soy_2="dtor";
				} else {
					$soy_2="of";
				}
				if ($GLOBALS['oPerm']->have_perm("scl")) { $soy_2="of_scl"; }
				break;
		}
	}
	$permiso=$GLOBALS['array_registro_perm'][$modo][$soy_2][$tipo];	
	//echo "perm: $permiso, r:$modo, $soy::$soy_2, tipo: $tipo<br>";	
	return $permiso;
}
*/

/**
*
* Definición de variables globales. Las cojo del fichero registro.ini
* La función class... está en el directorio includes.
*/
/*
require_once( 'class.ConfigMagik.php');

// create new ConfigMagik-Object
//$Config = new ConfigMagik( ConfigGlobal::$directorio.'/scdl/registro/registro.ini', true, true);
$Config->SYNCHRONIZE      = false;

$num_max_ctr=$Config->get("num_max_ctr","registro");
$num_max_cr=$Config->get("num_max_cr","registro");
$rango_inf_dl=$Config->get("rango_inf_dl","registro");
$rango_sup_dl=$Config->get("rango_sup_dl","registro");
$rango_inf_cr=$Config->get("rango_inf_cr","registro");
$rango_sup_cr=$Config->get("rango_sup_cr","registro");
$error_prot=$Config->get("error_prot","registro");
$error_fecha=$Config->get("error_fecha","registro");
$plazo_normal=$Config->get("plazo_normal","registro");
$plazo_urgente=$Config->get("plazo_urgente","registro");
$plazo_muy_urgente=$Config->get("plazo_muy_urgente","registro");
*/
/**
*
* Función para buscar las oficinas de un escrito (sólo devuelve el id_oficina).
*
*/
/*
function buscar_oficinas_id($id_reg,$id_e_s,$can) {
	$oDBR=$GLOBALS['oDBR'];
	$oficinas = array();
	// oficinas
	if (!empty($id_e_s)) {
		$sql_of="SELECT of.id_oficina, of.responsable
				FROM oficinas of
				WHERE of.id_reg=$id_reg AND of.id_e_s=$id_e_s AND cancilleria='$can'
				ORDER BY responsable DESC
				";
	} else {
		$sql_of="SELECT u.id_oficina, of.responsable
				FROM oficinas of ,x_oficinas u
				WHERE of.id_reg=$id_reg AND cancilleria='$can' AND of.id_oficina=u.id_oficina
				GROUP BY u.id_oficina,responsable,orden
				ORDER BY responsable DESC, orden
				";
	}
	$k=0;
	foreach ($oDBR->query($sql_of) as $row_3) {
		$k++;
		$id_oficina=$row_3["id_oficina"];
		$oficinas[]=$id_oficina;
	}
	return $oficinas;
}
*/

/**
*
* Función para buscar las oficinas de un escrito.
*
*/
/*
function buscar_oficinas($id_reg,$id_e_s,$cancilleria) {
	$oDBR=$GLOBALS['oDBR'];
	// oficinas
	if (!empty($id_e_s)) {
		$sql_of="SELECT u.sigla, of.responsable
				FROM oficinas of ,x_oficinas u
				WHERE of.id_reg=$id_reg AND of.id_e_s=$id_e_s AND cancilleria='$cancilleria' AND of.id_oficina=u.id_oficina
				ORDER BY responsable DESC, orden
				";
	} else {
		$sql_of="SELECT u.sigla, of.responsable
				FROM oficinas of ,x_oficinas u
				WHERE of.id_reg=$id_reg AND cancilleria='$cancilleria' AND of.id_oficina=u.id_oficina
				GROUP BY sigla,responsable,orden
				ORDER BY responsable DESC, orden
				";
	}
	$k=0;
	$oficinas="";
	foreach ($oDBR->query($sql_of) as $row_3) {
		$k++;
		$of_sigla=$row_3["sigla"];
		$reponsable=$row_3["responsable"];
		if ($reponsable=='t') {
			$oficinas .= "<font style='color: Red;'>".$of_sigla."</font>," ;
		} else {
			$oficinas .= $of_sigla.",";
		}
	}
	$oficinas = substr($oficinas,0,-1); //quitar la última coma
	return $oficinas;
}
*/


/**
*
* Función para buscar el protocolo de referencia de un pendiente.
*
*/
/*
function buscar_ref_uid($uid,$formato="txt"){
    return 'xx';
	$oDBR=$GLOBALS['oDBR'];
	if ($uid[0]=="R" && $uid[1]=="C") { //caso de registro cancillería
		$pos = strpos($uid, '-') - 2;
		$id_reg=substr($uid,2,$pos);
		//echo "ref: $id_reg<br>";
		$sql_ref="SELECT origen, origen_num ,origen_any
				FROM cancilleria_escritos 
				WHERE id_reg=$id_reg
				";
		//echo "sql: $sql_ref<br>";
		$oDBRSt_q2=$oDBR->query($sql_ref);
		if ($oDBRSt_q2->rowCount()) {
			$referencias="";
			$row=$oDBRSt_q2->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$origen_any=any_2($origen_any);
			if($formato=="txt") { $ref=$origen." ".$origen_num."/".$origen_any; }
			if($formato=="array"){ $ref['id_lugar']=$origen; $ref['sigla']=$origen; $ref['num']=$origen_num; $ref['any']=$origen_any; }
			return $ref;
		} else {
			return _("referencia a un escrito eliminado");
		}

	} else if ($uid[0]=="R") {
		$pos = strpos($uid, '-') - 1;
		$id_reg=substr($uid,1,$pos);
		//echo "ref: $id_reg<br>";
		$sql_ref="SELECT u.id_lugar, u.sigla, e.prot_num ,e.prot_any
				FROM entradas e ,lugares u
				WHERE e.id_reg=$id_reg AND e.id_lugar=u.id_lugar
				";
		$oDBRSt_q2=$oDBR->query($sql_ref);
		if ($oDBRSt_q2->rowCount()) {
			$referencias="";
			$row=$oDBRSt_q2->fetch(\PDO::FETCH_ASSOC);
			extract($row);
			$prot_any=any_2($prot_any);
			if($formato=="txt") { $ref=$sigla." ".$prot_num."/".$prot_any; }
			if($formato=="array"){ $ref['id_lugar']=$id_lugar; $ref['sigla']=$sigla; $ref['num']=$prot_num; $ref['any']=$prot_any; }
			return $ref;
		} else {
			// No es una entrada, será una aprobación.
			$sql_ref="SELECT prot_num ,prot_any
					FROM escritos
					WHERE id_reg=$id_reg 
					";
			$oDBRSt_q2=$oDBR->query($sql_ref);
			if ($oDBRSt_q2->rowCount()) {
				$referencias="";
				$row=$oDBRSt_q2->fetch(\PDO::FETCH_ASSOC);
				extract($row);
				$prot_any=any_2($prot_any);
				if($formato=="txt") { $ref=ConfigGlobal::$dele." ".$prot_num."/".$prot_any; }
				if($formato=="array"){ $ref['id_lugar']=$id_lugar; $ref['sigla']=ConfigGlobal::$dele; $ref['num']=$prot_num; $ref['any']=$prot_any; }
				return $ref;
			} else {
				return _("referencia a un escrito eliminado");
			}
		}
	} else {
		return;
	}
}
*/

/**
*
* Función cambiar el orden de una fecha, de 'dd/mm/YYYY' a 'YYYYmmdd'. 
*
*/
/*
function fecha_DMY2YMD($fecha) {
	// si está en formato:'YYYYmmddTHHmmssZ'.
	if (strstr($fecha,'T')) {
		list( $f_fecha,$y,$m,$d) = fecha_array($fecha);
		$f=date("Ymd",mktime(0,0,0,$m,$d,$y));
	} else {
		if ($fecha == 'x') return 'x';
		list( $d,$m,$y) = preg_split('/[\.\/-]/',$fecha);
		$f=date("Ymd",mktime(0,0,0,$m,$d,$y));
	}
	return $f;
}
*/
/**
*
* Función cambiar el orden de una fecha, de 'YYYYmmdd' a 'ddmmYYYY'.
*
*/
/*
function fecha_YMD2DMY($fecha) {
	$fecha=substr($fecha,0,8);
	$patterns = '/(19|20)(\d{2})(\d{1,2})(\d{1,2})/';
	//$patterns = '/(19|20)(\d{2})[\/|\-](\d{1,2})[\/|\-](\d{1,2})/';
    $replace = '\\4/\\3/\\1\\2';
    $fecha=preg_replace($patterns,$replace,$fecha);
	return $fecha;
}
*/

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


/**
*
* Función para buscar las referencias de un escrito.
*
*/
/*
function buscar_ref($id_reg,$cancilleria,$formato="txt") {
	$oDBR=$GLOBALS['oDBR'];
// referencias
	$sql_ref="SELECT u.id_lugar, u.sigla, r.prot_num as ref_prot_num,r.prot_any as ref_prot_any,r.mas as ref_mas
				FROM referencias r ,lugares u
				WHERE r.id_reg=$id_reg AND cancilleria='$cancilleria' AND r.id_lugar=u.id_lugar
				";
	$j=0;
	if ($formato=="txt") $referencias="";
	if ($formato=="array") $referencias=array();
	foreach ($oDBR->query($sql_ref) as $row) {
		$j++;
		$ref_id_lugar=$row["id_lugar"];
		$ref_sigla=$row["sigla"];
		$ref_prot_num=$row["ref_prot_num"];
		$ref_prot_any=any_2($row["ref_prot_any"]);
		$ref_mas=$row["ref_mas"];

		if($formato=="txt") {
			$ref=$ref_sigla." ".$ref_prot_num."/".$ref_prot_any;
			if (!empty($ref_mas)) $ref .= " (".$ref_mas.")" ;
			if ($j > 1) $referencias .= "<br>";
			$referencias .= $ref;
		}
		if($formato=="array"){ $referencias[]= array('id_lugar'=>$ref_id_lugar, 'sigla'=>$ref_sigla, 'num'=>$ref_prot_num, 'any'=>$ref_prot_any); }
	}
	return $referencias;
}
*/

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
		if ($any < 50 ) {
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
* Función para una fecha (dd/mm/aaaa) poner el año en dos cifras.
*
*/
/*
function date_any_2($fecha) {
	if (!empty($fecha)) {
		list($day, $month, $year) = preg_split('/[\.\/-]/', $fecha);
		$year_2=any_2($year);
		return "$day/$month/$year_2";
	} 
}
*/

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
* creo un array con los posibles usuarios para cada oficina.
*	pongo: "mail#sn" (para que la función options_2 vaya bien.)
*  Añado el tipo para crear un array con sólo los mails y los alias.
* 
*/
/*
function array_encargados($tipo="oficinas"){
	$oMiUsuario = new Usuario(ConfigGlobal::id_usuario());
	$miSfsv=$oMiUsuario->getSfsv();

	$a_encargados=array();
	if (ConfigGlobal::$auth_method == 'database') {
		// filtro por sf/sv
		$cond=array();
		$operator = array();

		$cond['sfsv'] = $miSfsv;
		$cond['id_role'] = "[146]";
		$operator['id_role'] = '~';

		$oGesUsuarios = new GestorUsuario();
		$oUsuarioColeccion= $oGesUsuarios->getUsuarios($cond,$operator);
		foreach ($oUsuarioColeccion as $oUsuario) {
			$id_usuario=$oUsuario->getId_usuario();
			$usuario=$oUsuario->getUsuario();
			$nom_usuario=$oUsuario->getNom_usuario();
			$perm_oficinas=$oUsuario->getPerm_oficinas();
			$perm_activ=$oUsuario->getPerm_activ();
			$seccion=$oUsuario->getSfsv();
			$mail=$oUsuario->getEmail();
			$id_role=$oUsuario->getId_role();

			$sn=!empty($nom_usuario)? $nom_usuario : $usuario;
			$mail=empty($mail)? 'x' : $mail;
			switch ($tipo) {
				case "oficinas":
					if (!empty($perm_oficinas)) {
						$xxx="$mail#$sn";
						$a_perms=explode(",",$perm_oficinas);
						foreach($a_perms as $p) {
							if (empty($a_encargados[$p]) || !is_array($a_encargados[$p])) { $a_encargados[$p]=array(); }
							array_push($a_encargados[$p],$xxx);
						}
					}
					break;
				case "alias":
					//echo "$mail = $sn<br>";
					if ($mail != 'x') $a_encargados[$mail]=$sn;
				break;
			}
		}
	} else { // valores en el ldap
		$ds=ldap_connect("192.168.33.7");
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		$r=ldap_bind($ds); // Autentificacion anonima, tipicamente con
						   // acceso de lectura
		$dn = "ou=Users,dc=dlb,dc=es";
		$filter="uid=*";
		$solo_esto = array("uid", "sn", "title", "mail");
		$sr=ldap_search($ds,$dn,$filter,$solo_esto);

		$info = ldap_get_entries($ds, $sr);
		for ($i=0; $i<$info["count"]; $i++) {
			$sn=$info[$i]["sn"][0];
			$mail=empty($info[$i]["mail"])? 'x' : $info[$i]["mail"][0];
			switch ($tipo) {
				case "oficinas":
					if (!empty($info[$i]["title"])) {
						$perm_oficinas=$info[$i]["title"][0];
						$xxx="$mail#$sn";
						$a_perms=explode(",",$perm_oficinas);
						foreach($a_perms as $p) {
							if (empty($a_encargados[$p]) || !is_array($a_encargados[$p])) { $a_encargados[$p]=array(); }
							array_push($a_encargados[$p],$xxx);
						}
					}
					break;
				case "alias":
					if ($mail != 'x') $a_encargados[$mail]=$sn;
				break;
			}
		}
		ldap_close($ds);
	}
	return $a_encargados;
}
*/

/**
*
* creo un array con las posibles categorias...
*	primero el id, después el texto
*
*/
/*
function array_categorias($calendario) {
	$oDBR=$GLOBALS['oDBR'];
	$query_categorias="SELECT id_categoria, categoria
					FROM x_categorias
					WHERE calendario='$calendario'
					ORDER BY orden ";
	$opciones=array();
	$i=0;
	foreach ($oDBR->query($query_categorias) as $row) {
		$i++;
		extract($row);
		$opciones[$id_categoria]=$categoria;
	}
	return $opciones;
}
*/

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

/**
*
* creo un array con la conversión de oficinas
*	del sistema a oficinas del registro (agd -> dagd...)
*
*/
/*
$array_of_sistema=array( "adl" => "adl",
						"dir" => "vcd",
						"vcsd" => "vcsd",
						"scl" => "scdl",
						"sm" => "vsm",
						"sg" => "vsg",
						"sr" => "vsr",
						"est" => "vest",
						"adl" => "adl",
						"agd" => "dagd",
						"des" => "des",
						"aop" => "aop",
						"ocs" => "ocs",
						"soi" => "soi",
						"admin" => "scdl"
						);
*/
/**
*
* creo un array con las posibles oficinas
*	primero el id, después la sigla
*
*/
/*
function array_oficinas($tipo="db",$condicion="") {
	$oDBR=$GLOBALS['oDBR'];
	if (!empty($condicion)) { $condicion="WHERE ".$condicion; }
	$query_of="SELECT id_oficina, sigla
					FROM x_oficinas 
					$condicion
					ORDER BY orden";
	switch ($tipo) {
		case "db":
			return $oDBR->query($query_of);
			break;
		case "array_id":
			$of=0;
			foreach ($oDBR->query($query_of) as $row) {
				$of++;
				extract($row);
				$a_oficinas[$id_oficina]=$sigla;
			}
			return $a_oficinas; 
			break;
		case "array_sigla":
			$of=0;
			foreach ($oDBR->query($query_of) as $row) {
				$of++;
				extract($row);
				$a_oficinas[$sigla]=$id_oficina;
			}
			return $a_oficinas; 
			break;
	}
}
*/

/**
*
* creo un array con los lugares posibles para cancillería...
*	primero el id_lugar, después la sigla y tercero el nombre
*
*/
/*
function array_lugares_can() {
	$lugares[]=array ( 'id_lugar' => "cr",
						'sigla' =>  "Cancillería (cr)",
						'nombre' => "" );
	$lugares[]=array ( 'id_lugar' => "asr",
						'sigla' =>  "Cancillería (asr)",
						'nombre' => "" );
	$lugares[]=array ( 'id_lugar' => "of",
						'sigla' =>  "Cancillería (dlb)",
						'nombre' => "" );
	$lugares[]=array ( 'id_lugar' => "fdl",
						'sigla' =>  "Cancillería (dlbf)",
						'nombre' => "" );
	$lugares[]=array ( 'id_lugar' => "iese",
						'sigla' =>  "IESE",
						'nombre' => "" );
return $lugares;
}

function array_lugares_ref_can() {
	$lugares[]=array ( 'id_lugar' => 633,
						'sigla' =>  "Cancillería",
						'nombre' => "" );
	$lugares[]=array ( 'id_lugar' => 610,
						'sigla' =>  "IESE",
						'nombre' => "" );
return $lugares;
}

/**
*
* creo un array con los lugares posibles.
*	primero el id_lugar, después la sigla
*
*/
/*
function array_id_lugares() {
	$a_lugares=array();
	$lugares=array_lugares();
	reset ($lugares);
	while (list ($clave, $val) = each ($lugares)) {
		$row_1=$val["id_lugar"];
		$row_2=$val["sigla"];
		if ($row_1 > 10) {
			$a_lugares[$row_1]=$row_2;
		}
	}
	return $a_lugares;
}
*/
/**
*
* creo un array con los lugares posibles para centros...
*	primero el id_lugar, después la sigla y tercero el nombre
*
*/
/*
function array_lugares($ctr_anulados='f') {
	$oDBR=$GLOBALS['oDBR'];
	// 0º dlb y cr
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE (sigla='cr' OR sigla='".ConfigGlobal::$dele."')  AND anulado='$ctr_anulados'
							ORDER BY sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
	// separación
	$lugares[]=array ( 'id_lugar' => "1",
							'sigla' =>  "--------",
							'nombre' => "" );
	// 1º ctr de dl
	$query_ctr="SELECT id_lugar, sigla, nombre, substring(tipo_ctr from 1 for 1) as tipo FROM lugares
							WHERE dl='".ConfigGlobal::$dele."' AND tipo_ctr ~ '^(a|n|s)' AND anulado='$ctr_anulados'
							ORDER BY tipo,sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
	// 2º oc de dlb
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE dl='".ConfigGlobal::$dele."' AND tipo_ctr ~ 'oc' AND anulado='$ctr_anulados'
							ORDER BY tipo_ctr,sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
	// 3º separación
	$lugares[]=array ( 'id_lugar' => "1",
							'sigla' =>  "--------",
							'nombre' => "" );
	// 4º dl de H
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE tipo_ctr='dl' AND region='H'  AND anulado='$ctr_anulados'
							ORDER BY tipo_ctr,sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
	// 5º separación
	$lugares[]=array ( 'id_lugar' => "2",
							'sigla' =>  "--------",
							'nombre' => "" );
	// 6º cr
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE tipo_ctr='cr' AND anulado='$ctr_anulados'
							ORDER BY tipo_ctr,sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}					
	// 7º separación
	$lugares[]=array ( 'id_lugar' => "3",
							'sigla' =>  "--------",
							'nombre' => "" );
	// 8º dl ex
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE tipo_ctr='dl' AND region != 'H' AND sigla != 'ro'  AND anulado='$ctr_anulados'
							ORDER BY tipo_ctr,sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
	// 9º separación
	$lugares[]=array ( 'id_lugar' => "4",
							'sigla' =>  "--------",
							'nombre' => "" );
	// 10º cg
	$query_ctr="SELECT id_lugar, sigla, nombre FROM lugares
							WHERE sigla='cg' AND anulado='$ctr_anulados'
							ORDER BY sigla";
	$i=0;
	foreach ($oDBR->query($query_ctr) as $row_ctr) {
		$i++;
		$lugares[]=array ( 'id_lugar' => $row_ctr[0],
							'sigla' =>  $row_ctr[1],
							'nombre' => $row_ctr[2] );
	}
				
	return $lugares;
} // fin funcion
*/

// ---------------------------------- tablas ----------------------------
/*
function tabla_entradas($donde,$sql,$orden,$txt_titulo="",$atras="") {
	$oDBR=$GLOBALS['oDBR'];
	// entradas
	$e_s="e";

	$go_to="registro_tabla.php?tabla=entradas&donde=".urlencode($donde)."&sql=".urlencode($sql);
	if ($orden && $sql) $sql .= "ORDER BY " .$orden;
	if ($orden && $donde) $donde .= "ORDER BY " .$orden;

	if ($GLOBALS['oPerm']->have_perm("scl")) { 
		$a_botones=array( array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados_e\")" ) ,
					array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados_e\")" ) 
					);
	}

	$a_botones[]=array( 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det(\"#seleccionados_e\")" ) ;

	$a_cabeceras=array( array('name'=>ucfirst(_("protocolo")),'formatter'=>'clickFormatter'),
						ucfirst(_("origen")),
						ucfirst(_("ref.")),
						array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
						ucfirst(_("ofic.")),
						array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
						array('name'=>ucfirst(_("fecha entrada")),'class'=>'fecha')
						);
	
	if (!empty($donde)) $donde="AND ".$donde;
	if (empty($sql)) {	
	$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
			en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
			u.sigla,en.f_doc_entrada
			FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
			WHERE en.id_lugar=u.id_lugar $donde
			";
	}
	//echo "query: $sql<br>";
	$a_valores = array();
	$i=0;
	if (($oDBRSt = $oDBR->query($sql)) !== false) {
	foreach ($oDBRSt as $row) {
		$i++;
		$id_reg=$row["id_reg"];
		$prot_num=$row["prot_num"];
		$prot_any=any_2($row["prot_any"]);
		$asunto=$row["asunto"];
		$f_doc_entrada=date_any_2($row["f_doc_entrada"]);
		$anulado=$row["anulado"];
		$reservado=$row["reservado"];
		$detalle=$row["detalle"];
		$distribucion_cr=$row["distribucion_cr"];
		
		$protocolo="dlb ".$prot_num."/".$prot_any;
		
		$perm_asunto=permiso_detalle($id_reg,$reservado,"a");
		$perm_detalle=permiso_detalle($id_reg,$reservado,"d");

		$id_entrada=$row["id_entrada"];
		$f_entrada=date_any_2($row["f_entrada"]);
		$origen_sigla=$row["sigla"];
		$origen_prot_num=$row["o_prot_num"];
		$origen_prot_any=any_2($row["o_prot_any"]);
		$origen_mas=$row["mas"];
		
		$pagina_mod="scdl/registro/registro_modificar.php?id_reg=$id_reg&e_s=$e_s";
		$pagina="scdl/registro/asunto_of.php?nuevo=2&id_reg=$id_reg&e_s=$e_s&atras=$atras";


		$origen=$origen_sigla." ".$origen_prot_num."/".$origen_prot_any;
		if (!empty($origen_mas)) $origen .= " (".$origen_mas.")" ;
		
		// referencias
		$referencias=buscar_ref($id_reg,"f");
		
		// permisos para el asunto
		if ($perm_asunto==0) $asunto=_("reservado");
		// oficinas
		$oficinas = buscar_oficinas($id_reg,$id_entrada,"f");

		// permisos para el detalle
		if ($perm_detalle==0) $detalle=_("reservado");
		if ($reservado=="t" && $perm_asunto>1) $asunto=_("RESERVADO")." $asunto";
		if ($detalle && $perm_detalle>1 && $perm_asunto) $asunto.=" [".$detalle."].";

		if (!empty($anulado)) $asunto=_("ANULADO")." ($anulado) $asunto";
		
		if ($distribucion_cr=='t') {
			$sql_1= "SELECT m.descripcion
				FROM destino_multiple m 
				WHERE m.id_reg=$id_reg
				";
			//echo "query: $sql<br>";
			$oDBRSt_query_1=$oDBR->query($sql_1);
			$descripcion=$oDBRSt_query_1->fetchColumn();
			$asunto.=" <font style='color: Green;'>"._("dl y")." $descripcion</font>";
		}

		$a_valores[$i]['sel']="$id_reg#$e_s";
		//$a_valores[$i][1]=$protocolo;
		if ( $GLOBALS['oPerm']->have_perm("scdl")) {
			$a_valores[$i][1]=array( 'ira'=>$pagina_mod, 'valor'=>$protocolo);
		} else {
			$a_valores[$i][1]=$protocolo;
		}
		$a_valores[$i][2]=$origen;
		$a_valores[$i][3]=$referencias;

		$a_valores[$i][4]= array( 'ira2'=>$pagina, 'valor'=>$asunto);

		//$a_valores[$i][4]=$asunto;
		$a_valores[$i][5]=$oficinas;
		$a_valores[$i][6]=$f_doc_entrada;
		$a_valores[$i][7]=$f_entrada;
	}
	}
	// ---------------------------------- html --------------------------------------- 
	if (empty($txt_titulo)) $txt_titulo= _("escritos recibidos en la Delegación");
	$txt="<h2 class=subtitulo>$txt_titulo</h2>";
	if ($i==0) {
		$txt.=_("no hay");
	} else {
		$txt.="<form id='seleccionados_e' name='seleccionados_e' action='' method='post'>
			<input type='hidden' name='permiso' value='3'>
			<input type='Hidden' name='go_to' value='$go_to' >
			<input type='Hidden' name='atras' value='$atras' >
			<input type='Hidden' name='mod' value='' >";
		$oTabla = new Lista();
		$oTabla->setId_tabla('func_reg_entradas');
		$oTabla->setCabeceras($a_cabeceras);
		$oTabla->setBotones($a_botones);
		$oTabla->setDatos($a_valores);
		$txt.=$oTabla->mostrar_tabla();
		$txt.="</form><br>";
	}
	return $txt;
}
*/

/*
function tabla_salidas($donde,$sql,$orden,$txt_titulo="",$atras="") {
	$oDBR=$GLOBALS['oDBR'];
	// salidas
	$e_s="s";

	$go_to="registro_tabla.php?tabla=salidas&donde=".urlencode($donde)."&sql=".urlencode($sql);
	if ($orden && $sql) $sql .= "ORDER BY " .$orden;
	if ($orden && $donde) $donde .= "ORDER BY " .$orden;

	if ($GLOBALS['oPerm']->have_perm("scl")) { 
		$a_botones=array( array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados_s\")" ) ,
					array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados_s\")" ) 
					);
	}

	$a_botones[]=array( 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det(\"#seleccionados_s\")" ) ;
			
	$a_cabeceras=array( array('name'=>ucfirst(_("protocolo")),'formatter'=>'clickFormatter'), ucfirst(_("destinos")),  ucfirst(_("ref.")), 
			array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
		   	ucfirst(_("ofic.")),
			array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
			array('name'=>ucfirst(_("aprobado")),'class'=>'fecha'),
			ucfirst(_("enviado")) // no puede ser class fecha, porque a veces se añade el modo de envio.
		   	);
	
	if (!empty($donde)) $donde="AND ".$donde;
	//echo "sql 1: $sql<br>";
	if (empty($sql)) {
		$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
				WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio $donde
				";
	}
	//echo "sql: $sql<br>";
	
	$i=0;
	foreach ($oDBR->query($sql) as $row) {
		$i++;
		$id_reg=$row["id_reg"];
		$prot_num=$row["prot_num"];
		$prot_any=any_2($row["prot_any"]);
		$asunto=$row["asunto"];
		$anulado=$row["anulado"];
		$reservado=$row["reservado"];
		$detalle=$row["detalle"];
		$distribucion_cr=$row["distribucion_cr"];
		$f_doc=date_any_2($row["f_doc"]);
		$protocolo="dlb ".$prot_num."/".$prot_any;
		
		$perm_asunto=permiso_detalle($id_reg,$reservado,"a");
		$perm_detalle=permiso_detalle($id_reg,$reservado,"d");
		
		if ($distribucion_cr=='t') {
			$sql_1= "SELECT en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,u.sigla
			FROM entradas en LEFT JOIN lugares u USING (id_lugar) 
			WHERE en.id_reg=$id_reg
			";
			//echo "query: $sql<br>";
			$oEntrada=$oDBR->query($sql_1)->fetch(\PDO::FETCH_OBJ);
			$origen_prot_num=$oEntrada->o_prot_num;
			$origen_prot_any=any_2($oEntrada->o_prot_any);
			$origen_sigla=$oEntrada->sigla;
			$protocolo=$origen_sigla." ".$origen_prot_num."/".$origen_prot_any;
		}
		
		$id_salida=$row["id_salida"];
		$f_aprobacion=date_any_2($row["f_aprobacion"]);
		$f_salida=date_any_2($row["f_salida"]);
		if ($row["id_modo_envio"]) $f_salida.=" (".$row["modo_envio"].")";
		
		$descripcion=$row["descripcion"];
		
		$pagina_mod="scdl/registro/registro_modificar.php?id_reg=$id_reg&e_s=$e_s";
		$pagina="scdl/registro/asunto_of.php?nuevo=2&id_reg=$id_reg&e_s=$e_s&atras=$atras";
		
		// destinos
		if (empty($descripcion)) {
			$destinos=buscar_destinos($id_reg);
		} else {
			$destinos=$descripcion;
		}
		// referencias
		$referencias=buscar_ref($id_reg,"f");
		
		// permisos para el asunto
		if ($perm_asunto==0) $asunto=_("reservado");
		// oficinas
		$oficinas = buscar_oficinas($id_reg,$id_salida,"f");
		// permisos para el detalle
		if ($perm_detalle==0) $detalle=_("reservado");
		if ($reservado=="t" && $perm_asunto>1 ) $asunto=_("RESERVADO")." $asunto";
		if ($detalle && $perm_detalle>1 && $perm_asunto) $asunto.=" [".$detalle."].";
		if (!empty($anulado)) $asunto=_("ANULADO")." ($anulado) $asunto";

		$a_valores[$i]['sel']="$id_reg#$e_s";
		//$a_valores[$i][1]=$protocolo;
		if ( $GLOBALS['oPerm']->have_perm("scdl")) {
			$a_valores[$i][1]=array( 'ira'=>$pagina_mod, 'valor'=>$protocolo);
		} else {
			$a_valores[$i][1]=$protocolo;
		}
		$a_valores[$i][2]=$destinos;
		$a_valores[$i][3]=$referencias;
		//$a_valores[$i][4]=$asunto;
		$a_valores[$i][4]= array( 'ira2'=>$pagina, 'valor'=>$asunto);
		$a_valores[$i][5]=$oficinas;
		$a_valores[$i][6]=$f_doc;
		$a_valores[$i][7]=$f_aprobacion;
		$a_valores[$i][8]=$f_salida;
	}
	// ---------------------------------- html --------------------------------------- 
	if (empty($txt_titulo)) $txt_titulo=_("escritos aprobados en la Delegación");
	$txt="<h2 class=subtitulo>$txt_titulo</h2>";
	if ($i==0) {
		$txt.=_("no hay");
	} else {
		$txt.="<form id='seleccionados_s' name='seleccionados_s' action='' method='post'>
			<input type='hidden' name='permiso' value='3'>
			<input type='Hidden' name='go_to' value='$go_to' >
			<input type='Hidden' name='atras' value='$atras' >
			<input type='Hidden' name='mod' value='' >";
		$oTabla = new Lista();
		$oTabla->setId_tabla('func_reg_salidas');
		$oTabla->setCabeceras($a_cabeceras);
		$oTabla->setBotones($a_botones);
		$oTabla->setDatos($a_valores);
		$txt.=$oTabla->mostrar_tabla();
		$txt.="</form><br>";
	}
	return $txt;
}
*/
