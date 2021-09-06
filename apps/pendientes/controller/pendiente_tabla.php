<?php
use core\ConfigGlobal;
use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use pendientes\model\GestorPendiente;
use pendientes\model\Rrule;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\DateTimeLocal;
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
$role_actual = ConfigGlobal::role_actual();
if ($role_actual === 'secretaria') {
    $secretaria = 1; // NO TRUE, para eljavascript;
    $oDesplOficinas= $oGOficinas->getListaOficinas();
    $oDesplOficinas->setOpcion_sel($Qid_oficina);
    $oDesplOficinas->setNombre('id_oficina');
    $id_oficina = '';
} else {
    $oDesplOficinas = []; // para evitar errores
    $secretaria = 0; // NO FALSE, para eljavascript;
    $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
    $id_oficina = $oCargo->getId_oficina();
}

$oficina = '';
if (!empty($Qid_oficina)) {
    $oficina = $a_oficinas[$Qid_oficina];
} elseif (!empty($id_oficina)) {
    $oficina = $a_oficinas[$id_oficina];
}
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
					ucfirst(_("calendario")),
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
$gesPendientes = new GestorPendiente($cal_oficina,$op_calendario_default,$role_actual);
$cPendientes = $gesPendientes->getPendientes($aWhere);

$a_valores = [];
$t = 0;
$oPermisoregistro = new PermRegistro();
foreach($cPendientes as $oPendiente) {
    $resource = $oPendiente->getResource();
    $id_encargado = $oPendiente->getEncargado();
    $perm_detalle = $oPermisoregistro->permiso_detalle($oPendiente, 'detalle');
    if (!empty($Qencargado)) { 
        if ($id_encargado != $Qencargado) { continue; }
    }
    $encargado = !empty($id_encargado)? $a_usuarios_oficina[$id_encargado] : '';
    $t++;
    $protocolo = $oPendiente->getLocation();
    $rrule = $oPendiente->getRrule();
    $asunto = $oPendiente->getAsuntoDetalle();
    if (!empty($asunto)) $asunto=htmlspecialchars(stripslashes($asunto),ENT_QUOTES,'utf-8');
    $plazo = $oPendiente->getF_plazo()->getFromLocal();
    $plazo_iso = $oPendiente->getF_plazo()->format('Ymd'); // sólo números, para poder ordenar.
    
    $oficinas_txt = $oPendiente->getOficinasTxtcsv();
    
    
    $aEtiquetas = $oPendiente->getEtiquetasArray();
    $str_etiquetas = '';
    foreach ($aEtiquetas as $id_etiqueta) {
        $str_etiquetas .= empty($str_etiquetas)? '' : ', ';
        $str_etiquetas .= empty($a_posibles_etiquetas[$id_etiqueta])? '' : $a_posibles_etiquetas[$id_etiqueta]; 
    }
    
    if (!empty($rrule)) {
        $periodico="p";
        $uid = $oPendiente->getUid();
        // calcular las recurrencias que tocan.
        $dtstart=$oPendiente->getF_inicio()->getIso();
        $dtend=$oPendiente->getF_end()->getIso();
        $a_exdates = $oPendiente->getExdates();
        $f_recurrentes = Rrule::recurrencias($rrule, $dtstart, $dtend, $f_plazo);
        //print_r($f_recurrentes);
        foreach ($f_recurrentes as $key => $f_iso) {
            $oF_recurrente = new DateTimeLocal($f_iso);
            $t++;
            // Quito las excepciones.
            if (is_array($a_exdates) ){
                foreach ($a_exdates as $icalprop) {
                    // si hay más de uno separados por coma
                    $a_fechas=preg_split('/,/',$icalprop->content);
                    foreach ($a_fechas as $f_ex) {
                        $oF_exception = new DateTimeLocal($f_ex);
                        if ($oF_recurrente == $oF_exception)  continue(3);
                    }
                }
            }
            //$a_valores[$t]['sel']="$uid#$cal_oficina#$f_recur";
            if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
                $a_valores[$t]['sel']="$uid#$cal_oficina#$f_iso";
            } else {
                $a_valores[$t]['sel']="x";
            }
            $a_valores[$t][1]=$protocolo;
            $a_valores[$t][2]=$str_etiquetas;
            $a_valores[$t][3]=$periodico;
            $a_valores[$t][4]=$asunto;
            $a_valores[$t][5]=$oF_recurrente->getFromLocal();
            $a_valores[$t][6]=$oficinas_txt;
            $a_valores[$t][7]=$encargado;
            $a_valores[$t][8]=$resource;
            // para el orden
            $a_valores[$t]['order']=$key; // (es la fecha iso sin separador)
        }
    } else {
        $periodico="";
        $uid = $oPendiente->getUid();
        
        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $a_valores[$t]['sel'] = "$uid#$cal_oficina";
        } else {
            $a_valores[$t]['sel'] = "x";
        }
        $a_valores[$t][1]=$protocolo;
        $a_valores[$t][2]=$str_etiquetas;
        $a_valores[$t][3]=$periodico;
        $a_valores[$t][4]=$asunto;
        $a_valores[$t][5]=$plazo;
        $a_valores[$t][6]=$oficinas_txt;
        $a_valores[$t][7]=$encargado;
        $a_valores[$t][8]=$resource;
        // para el orden
        if ($plazo!="x") {
            $a_valores[$t]['order'] = $plazo_iso;
        }
    }
}

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
    'calendario' => $op_calendario_default,
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($a_cosas));

$vista = (ConfigGlobal::role_actual() === 'secretaria')? 'secretaria' : 'home';

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
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('pendientes/controller');
echo $oView->renderizar('pendiente_tabla.html.twig',$a_campos);
