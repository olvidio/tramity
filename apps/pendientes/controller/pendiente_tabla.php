<?php
use core\ConfigGlobal;
use core\ViewTwig;
use function core\fecha_sin_time;
use function core\recurrencias;
use etiquetas\model\entity\GestorEtiqueta;
use lugares\model\entity\GestorLugar;
use pendientes\model\GestorPendiente;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\Desplegable;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
	require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qperiodo = (string) \filter_input(INPUT_POST, 'periodo');
$Qid_oficina = (string) \filter_input(INPUT_POST, 'id_oficina');
$Qdespl_calendario = (string) \filter_input(INPUT_POST, 'despl_calendario');
$Qcalendario = (string) \filter_input(INPUT_POST, 'calendario');
$Qencargado = (string) \filter_input(INPUT_POST, 'encargado');
	
$cargo = $_SESSION['session_auth']['role_actual'];

$aOpciones = [
    'registro' => _("registro"),
    'oficina' => _("oficina"),
];

if (!empty($Qcalendario)) {
    $op_calendario_default = $Qcalendario;
} else {
    $op_calendario_default = empty($Qdespl_calendario)? 'registro' : $Qdespl_calendario;
}

$oDesplCalendarios = new Desplegable();
$oDesplCalendarios->setNombre('despl_calendario');
$oDesplCalendarios->setOpciones($aOpciones);
$oDesplCalendarios->setOpcion_sel($op_calendario_default);
$oDesplCalendarios->setAction('fnjs_calendario()');

$oGOficinas = new GestorOficina();
$a_oficinas = $oGOficinas->getArrayOficinas();

// solo secretaría puede ver/crear pendientes de otras oficinas
$role_actual = $_SESSION['session_auth']['role_actual'];
if ($role_actual === 'secretaria') {
    $secretaria = 1; // NO TRUE, para eljavascript;
    $oDesplOficinas= $oGOficinas->getListaOficinas();
    $oDesplOficinas->setOpcion_sel($Qid_oficina);
    $oDesplOficinas->setNombre('id_oficina');
    $id_oficina = '';
} else {
    $oDesplOficinas = []; // para evitar errores
    $secretaria = 0; // NO FALSE, para eljavascript;
    $oCargo = new Cargo(ConfigGlobal::mi_id_cargo());
    $id_oficina = $oCargo->getId_oficina();
}

$oficina = '';
if (!empty($Qid_oficina)) {
    $oficina = $a_oficinas[$Qid_oficina];
} elseif (!empty($id_oficina)) {
    $oficina = $a_oficinas[$id_oficina];
}
$of_pral=$oficina;
$cal_oficina="oficina_$oficina";

$gesCargos = new GestorCargo();
$a_usuarios_oficina = $gesCargos->getArrayUsuariosOficina($id_oficina);
// para el dialogo de búsquedas:
$oDesplEncargados = new Desplegable('encargado',$a_usuarios_oficina,$Qencargado,TRUE);


$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayLugares();

$sel_hoy="";
$sel_semana="";
$sel_mes="";
$sel_trimestre="";
$sel_any="";
if (!empty($Qperiodo)) {
	$var_sel="sel_".$Qperiodo;
	$$var_sel="selected";
	switch ($Qperiodo) {
		case "hoy":
			$limite = date("Ymd",mktime(0, 0, 0, date("m"), date("d"),date("Y")));
			break;
		case "semana":
			$limite = date("Ymd",mktime(0, 0, 0, date("m"), date("d")+7,date("Y")));
			break;
		case "mes":
			$limite = date("Ymd",mktime(0, 0, 0, date("m")+1, date("d"),date("Y")));
			break;
		case "trimestre":
			$limite = date("Ymd",mktime(0, 0, 0, date("m")+3, date("d"),date("Y")));
			break;
		case "any":
			$limite = date("Ymd",mktime(0, 0, 0, date("m"),date("d"),date("Y")+1));
			break;
	}
}

$a_botones[]=array( 'txt' => _('marcar como contestado'), 'click' =>"fnjs_marcar(\"#seleccionados\")" ) ;
$a_botones[]=array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados\")" ) ;
if ($secretaria) {
    $a_botones[]=array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados\")" ) ;
}

$a_cabeceras=array( ucfirst(_("protocolo")),
					ucfirst(_("etiquetas")),
					_("p"),
					array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter'),
					array('name'=>ucfirst(_("fecha plazo")),'class'=>'fecha'),
					ucfirst(_("oficinas")),
					ucfirst(_("encargado")),
					);

// Fetch all todos
$f_inicio="19950101T000000Z";
if (empty($limite)) {
	//$f_plazo=date("Ymd\T000000\Z");
	$f_plazo=date("Ymd\T230000\Z");
} else {
	$f_plazo=$limite."T000000Z";
} 

//echo "get: ini$f_inicio="19950101T000000Z";: $f_inicio, fin: $f_plazo<br>";
$completed=false; //no veo los "COMPLETED"
$cancelled=false; //no veo los "CANCELLED"

$aWhere = [
        'f_inicio' => $f_inicio,
        'f_plazo' => $f_plazo,
        'completed' => $completed,
        'cancelled' => $cancelled,
    ];
$gesPendientes = new GestorPendiente($cal_oficina,$op_calendario_default,$cargo);
$cPendientes = $gesPendientes->getPendientes($aWhere);

$a_valores = [];
$t = 0;
foreach($cPendientes as $oPendiente) {
    $id_encargado = $oPendiente->getEncargado();
    if (!empty($Qencargado)) { 
        if ($id_encargado != $Qencargado) { continue; }
    }
    $encargado = !empty($id_encargado)? $a_cargos_oficina[$id_encargado] : '';
    $t++;
    $ref = $oPendiente->getReferencias();
    $protocolo = $oPendiente->getLocation();
    $rrule = $oPendiente->getRrule();
    $asunto = $oPendiente->getAsunto();
    if (!empty($asunto)) $asunto=htmlspecialchars(stripslashes($asunto),ENT_QUOTES,'utf-8');
    $plazo = $oPendiente->getF_plazo()->getFromLocal();
    $plazo_iso = $oPendiente->getF_plazo()->getIso();
    
    $oficinas_txt = $oPendiente->getOficinasTxtcsv();
    
    
    $aEtiquetas = $oPendiente->getEtiquetasArray();
    $str_etiquetas = '';
    foreach ($aEtiquetas as $id_etiqueta) {
        $str_etiquetas .= empty($str_etiquetas)? '' : ', ';
        $str_etiquetas .= empty($a_posibles_etiquetas[$id_etiqueta])? '' : $a_posibles_etiquetas[$id_etiqueta]; 
    }
    

    if (!empty($rrule)) {
        $periodico="p";
        // calcular las recurrencias que tocan.
        $dtstart=$oPendiente->getF_inicio();
        $dtend=$icalComp->GetPValue("DTEND");
        $a_exdates = $oPendiente->getEx_dates();
        $f_recurrentes=recurrencias($rrule,$dtstart,$dtend,$f_plazo);
        //print_r($f_recurrentes);
        foreach ($f_recurrentes as $f_recur => $fecha) {
            $recur++;
            // Quito las excepciones.
            if (is_array($a_exdates) ){
                foreach ($a_exdates as $icalprop) {
                    // si hay más de uno separados por coma
                    $a_fechas=preg_split('/,/',$icalprop->content);
                    foreach ($a_fechas as $f_ex) {
                        fecha_sin_time($f_ex); //quito la THHMMSSZ
                        if ($f_recur==$f_ex)  continue(3);
                    }
                }
            }
            //$a_valores[$recur]['sel']="$uid#$cal_oficina#$f_recur";
            if ($perm_asunto==0) {
                $a_valores[$recur]['sel']="x";
            } else {
                $a_valores[$recur]['sel']="$uid#$cal_oficina#$f_recur";
            }
            $a_valores[$recur][1]=$protocolo;
            $a_valores[$recur][2]=$str_etiquetas;
            $a_valores[$recur][3]=$periodico;
            //if ($perm_asunto==0) { // ahora solo pueden modificar los periodicos los de secretaria.
            if (!$GLOBALS['oPerm']->have_perm("scl")) {
                $a_valores[$recur][4]=$asunto;
            } else {
                $a_valores[$recur][4]= array( 'ira'=>$pagina, 'valor'=>$asunto);
            }
            $a_valores[$recur][5]=$fecha;
            $a_valores[$t][6]=$oficinas_txt;
            $a_valores[$recur][7]=$encargado;
            // para el orden
            $a_valores[$recur]['order']=$f_recur;
        }
    } else {
        $periodico="";
        $uid = $oPendiente->getUid();
        $a_valores[$t]['sel']="$uid#$cal_oficina";
        $a_valores[$t][1]=$protocolo;
        $a_valores[$t][2]=$str_etiquetas;
        $a_valores[$t][3]=$periodico;
        /*
        if ($perm_asunto==0) {
            $a_valores[$t][4]=$asunto;
        } else {
            $a_valores[$t][4]= array( 'ira'=>$pagina, 'valor'=>$asunto);
        }
        */
        $a_valores[$t][4]=$asunto;
        $a_valores[$t][5]=$plazo;
        $a_valores[$t][6]=$oficinas_txt;
        $a_valores[$t][7]=$encargado;
        // para el orden
        if ($plazo!="x") {
            $a_valores[$t]['order'] = $plazo_iso;
        }
    }
}


/*
$events = $cal->GetTodos($f_inicio,$f_plazo,$completed,$cancelled);
//print_r($events);
$tt=0;
foreach($events as $k1=>$a_todo) {
	$tt++;
	$vcalendar[$tt] = new \iCalComponent($a_todo['data']);
}

$recur=$tt;
$a_valores = [];
for ($t=1;$t<=$tt;$t++) {
//print_r($vcalendar[$t]);
	$icalComp = $vcalendar[$t]->GetComponents('VTODO');
	$icalComp = $icalComp[0];  // If you know there's only 1 of them...

	$uid=$icalComp->GetPValue("UID");
	$ref=buscar_ref_uid($uid,"txt");
	$ref_mas=$icalComp->GetPValue("X-DLB-REF-MAS");
	if (!empty($ref_mas)) $ref.=", ".$ref_mas;
	$pendiente_con=$icalComp->GetPValue("X-DLB-PENDIENTE-CON");
	if (!empty($pendiente_con)) $ref=$a_lugares[$pendiente_con]." ($ref)";

	$carpeta=$icalComp->GetPValue("CATEGORIES");
	$asunto=$icalComp->GetPValue("SUMMARY");
	if ($asunto=="Busy") continue;
	if (!empty($asunto)) $asunto=htmlspecialchars(stripslashes($asunto),ENT_QUOTES,'utf-8');
	$detalle=$icalComp->GetPValue("COMMENT");
	$rrule=$icalComp->GetPValue("RRULE");
	$reservado=$icalComp->GetPValue("CLASS");
	if ($reservado=="CONFIDENTIAL") {
		$reservado="t";
	} else {
		$reservado="f";
	}
	if ( $plazo=fecha_YMD2DMY($icalComp->GetPValue("DUE")) ) {
	} else {
		$plazo="x";
	}
	$mail=$icalComp->GetPValue("ATTENDEE");
	if (!empty($mail)) { 
		if ($filtro_encargado && $filtro_encargado!=$mail) continue;
		$encargado_nom = empty($a_encargados[$mail])? $mail : $a_encargados[$mail];
		$encargado="<span style='color:red'>$of_pral ($encargado_nom)</span>";
	} else { 
		if (!empty($filtro_encargado)) continue;
		$encargado="<span style='color:red'>$of_pral</span>"; 
	}

	$oficinas=$icalComp->GetPValue("X-DLB-OFICINAS");
	if (!empty($oficinas)) {
		$aa_oficinas=explode(" ",$oficinas);
		$oficinas_txt="";
		foreach($aa_oficinas as $id) {
			if (!empty($a_oficinas[$id])) {
				$oficinas_txt.=",".$a_oficinas[$id];
			}
		}
		$oficinas_txt = substr($oficinas_txt,1);
		if (!empty($encargado)) { $encargado.=", $oficinas_txt"; } else { $encargado=$oficinas_txt; }
	}

	$perm_asunto=1;
	$perm_detalle=1;
	//$perm_asunto=permiso_detalle_pendiente($of_sigla,$reservado,"a");
	//$perm_detalle=permiso_detalle_pendiente($of_sigla,$reservado,"d");
	// permisos para el asunto
	if ($perm_asunto==0) $asunto=_("reservado");
	if ($reservado=="t" && $perm_asunto>1) $asunto= strtoupper(_("reservado"))." $asunto";
	// permiso para el detalle
	if ($detalle && $perm_detalle>1) $asunto.=" [". htmlspecialchars(stripslashes($detalle),ENT_QUOTES,'utf-8') ."]";

	$pagina="apps/pendientes/controller/pendiente_form.php?nuevo=2&uid=$uid&cal_oficina=$cal_oficina&go=lista";

	if (!empty($rrule)) { 
		$periodico="p";
		// calcular las recurrencias que tocan.
		$dtstart=$icalComp->GetPValue("DTSTART");
		$dtend=$icalComp->GetPValue("DTEND");
		$a_exdates = $vcalendar[$t]->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
		$f_recurrentes=recurrencias($rrule,$dtstart,$dtend,$f_plazo);
		//print_r($f_recurrentes);
		foreach ($f_recurrentes as $f_recur => $fecha) {
			$recur++;
			// Quito las excepciones.
			if (is_array($a_exdates) ){
				foreach ($a_exdates as $icalprop) {
					// si hay más de uno separados por coma
					$a_fechas=preg_split('/,/',$icalprop->content);
					foreach ($a_fechas as $f_ex) {
						fecha_sin_time($f_ex); //quito la THHMMSSZ
						if ($f_recur==$f_ex)  continue(3);
					}
				}
			}
			//$a_valores[$recur]['sel']="$uid#$cal_oficina#$f_recur";
			if ($perm_asunto==0) {
				$a_valores[$recur]['sel']="x";
			} else {
				$a_valores[$recur]['sel']="$uid#$cal_oficina#$f_recur";
			}
			$a_valores[$recur][1]=$ref;
			$a_valores[$recur][2]=$carpeta;
			$a_valores[$recur][3]=$periodico;
			//if ($perm_asunto==0) { // ahora solo pueden modificar los periodicos los de secretaria.
			if (!$GLOBALS['oPerm']->have_perm("scl")) {
				$a_valores[$recur][4]=$asunto;
			} else {
				$a_valores[$recur][4]= array( 'ira'=>$pagina, 'valor'=>$asunto);
			}
			$a_valores[$recur][5]=$fecha;
			$a_valores[$recur][6]=$encargado;
			// para el orden
			$a_valores[$recur]['order']=$f_recur;
		}
	} else { 
		$periodico="";
		$a_valores[$t]['sel']="$uid#$cal_oficina";
		if ($perm_asunto==0) {
			$a_valores[$t]['sel']="x";
		} else {
			$a_valores[$t]['sel']="$uid#$cal_oficina";
		}
		$a_valores[$t][1]=$ref;
		$a_valores[$t][2]=$carpeta;
		$a_valores[$t][3]=$periodico;
		if ($perm_asunto==0) {
			$a_valores[$t][4]=$asunto;
		} else {
			$a_valores[$t][4]= array( 'ira'=>$pagina, 'valor'=>$asunto);
		}
		$a_valores[$t][5]=$plazo;
		$a_valores[$t][6]=$encargado;
		// para el orden
		if ($plazo!="x") {
			$a_valores[$t]['order']=fecha_DMY2YMD($plazo);
		}
	}
}
*/
if (!empty($a_valores)) {
	// ordenar por f_plazo:
	// Obtain a list of columns
	foreach ($a_valores as $key => $row) {
		$fechas[$key]  = $row['order'];
	}

	// Sort the data with fechas descending
	// Add $a_valores as the last parameter, to sort by the common key
	array_multisort($fechas,SORT_NUMERIC, SORT_ASC, $a_valores);
}


$oTabla = new Lista();
$oTabla->setId_tabla('pen_tabla');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);


$a_cosas = [
    'filtro' => $Qfiltro,
    'periodo' => $Qperiodo,
    'id_oficina' => $Qid_oficina,
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($a_cosas));


$a_campos = [
    'secretaria'   => $secretaria,
    'sel_hoy'      => $sel_hoy,
    'sel_semana'   => $sel_semana,
    'sel_mes'       => $sel_mes,
    'sel_trimestre' => $sel_trimestre,
    'sel_any'       => $sel_any,
    'oDesplOficinas' => $oDesplOficinas,
    'id_oficina'     => $id_oficina,
    'oDesplCalendarios' => $oDesplCalendarios,
    'oDesplEncargados'  => $oDesplEncargados,
    'oTabla' => $oTabla,
    'filtro' => $Qfiltro,
    'periodo' => $Qperiodo,
    'op_calendario_default' => $op_calendario_default,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('pendientes/controller');
echo $oView->renderizar('pendiente_tabla.html.twig',$a_campos);
