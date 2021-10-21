<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use etiquetas\model\entity\GestorEtiqueta;
use lugares\model\entity\GestorLugar;
use pendientes\model\Pendiente;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\DateTimeLocal;
use web\Desplegable;
use pendientes\model\Rrule;

// INICIO Cabecera global de URL de controlador *********************************


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

// Si vengo del formulario de entradas, abro una ventana nueva y
// los parametros vienen por GET.

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qperiodo = (string) \filter_input(INPUT_POST, 'periodo');
$Qnuevo = (string) \filter_input(INPUT_POST, 'nuevo');
$Qcalendario = (string) \filter_input(INPUT_POST, 'calendario');
$Qid_oficina = (string) \filter_input(INPUT_POST, 'id_oficina');
$id_reg = (string) \filter_input(INPUT_POST, 'id_reg');

$cargar_css = FALSE;

// estoy en una ventana independiente
$go = (string) \filter_input(INPUT_GET, 'go');
if (empty($go)) {
    $go = (string) \filter_input(INPUT_POST, 'go');
}
if ($go=="entradas" || $go=="salidas" || $go=="mov_iese") { 
    $id_reg = (integer) \filter_input(INPUT_GET, 'id_reg');
    $Qid_oficina = (integer) \filter_input(INPUT_GET, 'ponente');
    $Qcalendario = 'registro';
    
    $cargar_css = TRUE;
} else {
    $Qcalendario = empty($Qcalendario)? 'oficina' : $Qcalendario;
}

$gesOficinas = new GestorOficina();
$a_posibles_oficinas = $gesOficinas->getArrayOficinas();

// solo secretaría puede ver/crear pendientes de otras oficinas
$role_actual = ConfigGlobal::role_actual();
if ($role_actual === 'secretaria') {
    $secretaria = TRUE;
    $oDesplOficinas= $gesOficinas->getListaOficinas();
    $oDesplOficinas->setNombre('id_oficina');
    $id_oficina = '';
} else {
    $oDesplOficinas = []; // para evitar errores
    $secretaria = FALSE;
    $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
    $id_oficina = $oCargo->getId_oficina();
}

// visibilidad (usar las mismas opciones que en entradas)
$oEntrada = new Entrada();
$aOpciones = $oEntrada->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);

$a_oficinas_actuales = [];
$oArrayDesplOficinas = new web\DesplegableArray($a_oficinas_actuales,$a_posibles_oficinas,'oficinas');
$oArrayDesplOficinas ->setBlanco('t');
$oArrayDesplOficinas ->setAccionConjunto('fnjs_mas_oficinas()');

$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_etiquetas = [];
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}
$oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas,$a_posibles_etiquetas,'etiquetas');
$oArrayDesplEtiquetas ->setBlanco('t');
$oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');

// para encargar a los oficiales
$gesCargos = new GestorCargo();
$a_usuarios_oficina = $gesCargos->getArrayUsuariosOficina($id_oficina);
$oDesplEncargados = new Desplegable('encargado',$a_usuarios_oficina,'',TRUE);

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayLugares();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('ref_id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);

$oDesplLugar1 = new Desplegable();
$oDesplLugar1->setNombre('pendiente_con');
$oDesplLugar1->setBlanco(TRUE);
$oDesplLugar1->setOpciones($a_lugares);

$a_status=Pendiente::getArrayStatus();
$oDesplStatus = new Desplegable();
$oDesplStatus->setNombre('status');
$oDesplStatus->setBlanco(TRUE);
$oDesplStatus->setOpciones($a_status);
$oDesplStatus->setOpcion_sel('NEEDS-ACTION');
$oDesplStatus->setAction('fnjs_actualizar_status()');

$busca_ap_num = empty($_REQUEST['busca_ap_num'])? '' : $_REQUEST['busca_ap_num'];
$busca_ap_any = empty($_REQUEST['busca_ap_any'])? '' : $_REQUEST['busca_ap_any'];
$ref_id_lugar='';
$ref_prot_num='';
$ref_prot_any='';
$ref_prot_mas='';

$nuevo = empty($Qnuevo)? 1 : $Qnuevo;
$hoy = date("d/m/Y");
$hoy_iso = date("Y-m-d");
if ($nuevo == 1) {
	$uid = '';
	$id_oficina = $Qid_oficina;
	if (!empty($id_oficina)) { // En el caso de secretaria puede estar vacío
        $sigla = $a_posibles_oficinas[$Qid_oficina];
        $cal_oficina = "oficina_$sigla";	
	} else {
	    $cal_oficina = '';
	}
	$asunto = '';
	$detalle = '';
	$f_plazo = '';
	$f_acabado = '';
	$encargado = '';
	$observ = '';
	$pendiente_con = '';
	$perm_detalle = '';
	$ref_prot_mas = '';
	$go = ($go != 'entradas')? 'lista': $go;
} else {
    $go = 'lista';
    // Si vengo a form_pendiente, desde un checkbox(sel) o de la tabla de pendientes(link) .
    if (!empty($_POST['sel']) || !empty($_POST['uid_sel']) || !empty($_POST['uid'])) { 
        if (!empty($_POST['sel'])) { //vengo de un checkbox
            $uid=strtok($_POST['sel'][0],'#');
            $cal_oficina=strtok('#');
            // deduzco el calendario:
            $calendario_of = substr($uid, strpos($uid,'@')+1);
            $Qcalendario = substr($calendario_of, 0, strpos($calendario_of,'_'));
        } else {
            empty($_POST['uid'])? $uid="" : $uid=$_POST['uid'];
            empty($_POST['cal_oficina'])? $cal_oficina="" : $cal_oficina=$_POST['cal_oficina'];
        }
        if (empty($uid)) { 
            echo _("No sé a que pendiente se refiere.");
            exit();
        } 
        if (empty($id_reg) && ($pos_ini = strpos($uid, 'REN')) !== FALSE && $pos_ini == 0) { //  Registro entradas
            $pos = strpos($uid, '-') - 3;
            $id_reg=substr($uid,3,$pos);
        }

        $oPendiente = new Pendiente($cal_oficina, $Qcalendario, $role_actual, $uid) ;
        
        $asunto = $oPendiente->getAsunto();
        $status = $oPendiente->getStatus();
        $oDesplStatus->setOpcion_sel($status);
        $f_acabado = $oPendiente->getF_acabado()->getFromLocal();
        $f_plazo = $oPendiente->getF_plazo()->getFromLocal();
        $f_inicio = $oPendiente->getF_inicio()->getFromLocal();
        $rrule = $oPendiente->getRrule();
        $observ = $oPendiente->getObserv();
        $detalle = $oPendiente->getDetalle();
        $ref_prot_mas = $oPendiente->getRef_prot_mas();
        $visibilidad = $oPendiente->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
        
        $pendiente_con = $oPendiente->getPendiente_con();
        $oDesplLugar1->setOpcion_sel($pendiente_con);
        
        $encargado = $oPendiente->getEncargado();
        $oDesplEncargados->setOpcion_sel($encargado);
        
        $a_protOrigen = $oPendiente->getProtocoloOrigen();
        $exdates = $oPendiente->getExdates();
        // las oficinas	implicadas
        $aOficinas = $oPendiente->getOficinasArray();
        $oArrayDesplOficinas->setSeleccionados($aOficinas);
        // las etiquetas	
        $aEtiquetas = $oPendiente->getEtiquetasArray();
        $oArrayDesplEtiquetas->setSeleccionados($aEtiquetas);
        
        $sigla_of=substr($cal_oficina,8);
        $id_oficina = array_search($sigla_of, $a_posibles_oficinas);

        $nuevo=2;
        
        $oPermisoRegistro = new PermRegistro();
        $perm_detalle = $oPermisoRegistro->permiso_detalle($oPendiente, 'detalle');
        
        if (!empty($rrule)) {
            $display_periodico="display:in-line;";
        }
        // para que salga bien la referencia
        if (is_array($a_protOrigen)) {
            $display_completa="display:in-line;";
            $ref_id_lugar=$a_protOrigen['lugar'];
            $ref_prot_num=$a_protOrigen['num'];
            $ref_prot_any=$a_protOrigen['any'];
            //$ref_prot_mas=$a_protOrigen['mas']; // No cojo el del registro, el pendiente puede tener su propio 'mas'
            $oDesplLugar->setOpcion_sel($ref_id_lugar);
        }
    }
}

// si vengo desde el regitro entradas
if ($go == 'entradas') {
	// estas variables quedan igual: asunto, f_acabado, f_plazo, f_retraso, id_reg
	$nuevo=1;
	
    $f_acabado = '';
    $observ =  '';
    $asunto = (string) \filter_input(INPUT_GET, 'asunto');
    $detalle = (string) \filter_input(INPUT_GET, 'detalle'); 
    $visibilidad = (integer) \filter_input(INPUT_GET, 'visibilidad'); 
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    
    // las oficinas	implicadas
    $resto_oficinas = (array)  \filter_input(INPUT_GET, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $oArrayDesplOficinas->setSeleccionados($resto_oficinas);
    
    $f_plazo = (string) \filter_input(INPUT_GET, 'f_plazo');

    $ref_id_lugar = (integer) \filter_input(INPUT_GET, 'origen');
    $ref_prot_num = (integer) \filter_input(INPUT_GET, 'prot_num_origen');
    $ref_prot_any = (integer) \filter_input(INPUT_GET, 'prot_any_origen');
    $ref_prot_mas = (string) \filter_input(INPUT_GET, 'prot_mas_origen');
    $oDesplLugar->setOpcion_sel($ref_id_lugar);
    
	$perm_detalle=3;
	$display_completa="display:in-line;";
}

//////////////////////// PERIODICO ////////////////////////////////

/***
 *  En $rta tengo los parametros que me sirven para ver-no ver las pestañas del periodico
 *
 *
 */
$rta='';
$chk_m_ref='';
$chk_m_num='';
$chk_m_num_ini='';
$chk_a_num_ini="";
$chk_a_num="";
$chk_a_ref="";
$chk_a_num_dm="";
$dia_w_a_db='';
$chk_s_num_ini='';
$chk_s_ref='';
$chk_d_num_ini='';
$dias_w_db='';
$ordinal_db='';
$dia_w_db='';
$dia_num_db='';
$mes_num_db='';
$ordinal_a_db='';
$mes_num_ref_db='';
$a_interval_db = '';
$a_exdates_local = [];
$meses_db = [];
$dias_db = [];
if (empty($rrule)) {
    $f_inicio='';
    $f_until='';
    $exdates='';
    $periodico_tipo='';
} else {
    $rta = Rrule::desmontar_rule($rrule);
    if (empty($rta)) {
        $msg = sprintf (_("pasa algo con el pendiente periódico: %s"),$asunto);
        exit ($msg);
    }
    if (empty($rta['until'])) {
        $f_until = '';
    } else {
        $oF_until = new DateTimeLocal($rta['until']);
        $f_until = $oF_until->getFromLocal();
    }
    switch ($rta['tipo']) {
        case "d_a":
            $display_d_a="display:in-line;";
            $periodico_tipo="periodico_d_a";
            switch ($rta['tipo_dia']) {
                case "num_ini":
                    $chk_a_num_ini="checked";
                    $chk_a_num="";
                    $chk_a_ref="";
                    $chk_a_num_dm="";
                    break;
                case "num":
                    $chk_a_num_ini="";
                    $chk_a_num="checked";
                    $chk_a_ref="";
                    $chk_a_num_dm="";
                    $mes_num_db=empty($rta['meses'])? '' : $rta['meses'];
                    $dia_num_db=empty($rta['dias'])? '' : $rta['dias'];
                    break;
                case "ref":
                    $chk_a_num_ini="";
                    $chk_a_num="";
                    $chk_a_ref="checked";
                    $chk_a_num_dm="";
                    $dia_w_a_db=empty($rta['dia_semana'])? '' : $rta['dia_semana'];
                    $ordinal_a_db=empty($rta['ordinal'])? '' : $rta['ordinal'];
                    $mes_num_ref_db=empty($rta['meses'])? '' : $rta['meses'];
                    break;
                case "num_dm":
                    $chk_a_num_ini="";
                    $chk_a_num="";
                    $chk_a_ref="";
                    $chk_a_num_dm="checked";
                    $meses_db=empty($rta['meses'])? '' : preg_split('/,/',$rta['meses']);
                    $dias_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
                    $a_interval_db = empty($rta['interval'])? 1 : $rta['interval'];
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            break;
        case "d_m":
            $meses_db=empty($rta['meses'])? '' : preg_split('/,/',$rta['meses']);
            $dias_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
            $display_d_m="display:in-line;";
            $periodico_tipo="periodico_d_m";
            switch ($rta['tipo_dia']) {
                case "num_ini":
                    $chk_m_num_ini="checked";
                    $chk_m_num="";
                    $chk_m_ref="";
                    break;
                case "num":
                    $chk_m_num_ini="";
                    $chk_m_num="checked";
                    $chk_m_ref="";
                    $dia_num_db=$rta['dias'];
                    break;
                case "ref":
                    $chk_m_num_ini="";
                    $chk_m_num="";
                    $chk_m_ref="checked";
                    $dia_w_db=empty($rta['dia_semana'])? '' : $rta['dia_semana'];
                    $ordinal_db=empty($rta['ordinal'])? '' : $rta['ordinal'];
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            break;
        case "d_s":
            $display_d_s="display:in-line;";
            $periodico_tipo="periodico_d_s";
            switch ($rta['tipo_dia']) {
                case "num_ini":
                    $chk_s_num_ini="checked";
                    $chk_s_ref="";
                    break;
                case "ref":
                    $chk_s_num_ini="";
                    $chk_s_ref="checked";
                    $dias_w_db=empty($rta['dias'])? '' : preg_split('/,/',$rta['dias']);
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            break;
        case "d_d":
            $display_d_d="display:in-line;";
            $periodico_tipo="periodico_d_d";
            $chk_d_num_ini="checked";
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }
}
if (empty($display_d_a)) { $display_d_a="display:none;"; }
if (empty($display_d_m)) { $display_d_m="display:none;"; }
if (empty($display_d_s)) { $display_d_s="display:none;"; }
if (empty($display_d_d)) { $display_d_d="display:none;"; }

if (is_array($exdates) && !empty($exdates)) {
    foreach ($exdates as $icalprop) {
        // si hay más de uno separados por coma
        $a_fechas=preg_split('/,/',$icalprop->content);
        foreach ($a_fechas as $fecha_iso) {
            $oFecha = new DateTimeLocal($fecha_iso); // hay que quitar 'THHMMSSZ'
            $a_exdates_local[] = $oFecha->getFromLocal();
        }
    }
}

$a_meses = DateTimeLocal::Meses();
$oDesplMesesAnual = new Desplegable('mes_num',$a_meses,'',FALSE);
$oDesplMesesAnual->setAction("fnjs_marcar('#id_a_radio_2')");
$oDesplMesesAnual->setOpcion_sel($mes_num_db);

// ordinales:
$a_ordinales = [
    "1" => _("primer"),
    "2" =>  _("segundo"),
    "3" =>  _("tercer"),
    "4" =>  _("cuarto"),
    "-1" =>  _("último"),
];

$oDesplOrdinalesAnual = new Desplegable('ordinal_a',$a_ordinales,'',FALSE);
$oDesplOrdinalesAnual->setAction("fnjs_marcar('#id_a_radio_3')");
$oDesplOrdinalesAnual->setOpcion_sel($ordinal_a_db);

$oDesplOrdinalesMes = new Desplegable('ordinal',$a_ordinales,'',FALSE);
$oDesplOrdinalesMes->setAction("fnjs_marcar('#id_radio_3')");
$oDesplOrdinalesMes->setOpcion_sel($ordinal_db);

// dias de la semana
$a_dias_semana = DateTimeLocal::arrayDiasSemana();
$oDesplDiasSemanaAnual = new Desplegable('dia_semana_a',$a_dias_semana,'',FALSE);
$oDesplDiasSemanaAnual->setAction("fnjs_marcar('#id_a_radio_3')");
$oDesplDiasSemanaAnual->setOpcion_sel($dia_w_a_db);

$oDesplDiasSemanaMes = new Desplegable('dia_semana',$a_dias_semana,'',FALSE);
$oDesplDiasSemanaMes->setAction("fnjs_marcar('#id_radio_3')");
$oDesplDiasSemanaMes->setOpcion_sel($dia_w_db);

$oDesplDiasSemana = new Desplegable('dias_w',$a_dias_semana,'',FALSE);
$oDesplDiasSemana->setAction("fnjs_marcar('#id_s_radio_2')");
$oDesplDiasSemana->setOpcion_sel($dias_w_db);

$oDesplMesesRef = new Desplegable('mes_num_ref',$a_meses,'',FALSE);
$oDesplMesesRef->setAction("fnjs_marcar('#id_a_radio_3')");
$oDesplMesesRef->setOpcion_sel($mes_num_ref_db);

$oDesplMeses2 = new Desplegable('meses',$a_meses,'',FALSE);
$oDesplMeses2->setAction("fnjs_marcar('#id_a_radio_4')");
$oDesplMeses2->setOpcion_sel($meses_db);

// especifico la clave, porque sino empieza por 0 y no por 1.
$a_dias = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8,
        9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16,
        17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24,
        25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31];
$oDesplDias = new Desplegable('dias',$a_dias,'',FALSE);
$oDesplDias->setAction("fnjs_marcar('#id_a_radio_4')");
$oDesplDias->setOpcion_sel($dias_db);

///////////////////////// FIN PERIODICO ///////////////////////////


// Si no es nuevo, no dejo modificar ni la oficina ni la referencia (protocolo)
if ( $nuevo != 1 ) {
	$disabled = 'disabled';
} else {
	$disabled = '';
}

// por defecto no veo 'mas_opciones'
if (empty($display_completa)) {
	$display_completa="display:none;";
	$simple=0;
	$txt_completa=_("ver ficha completa");
} else {
	$simple=1;
	$txt_completa=_("ver ficha sencilla");
}
if (empty($display_periodico)) {
	$display_plazo="display:in-line;";
	$display_periodico="display:none;";
	$simple_per=0;
	$txt_periodicidad=_("ver periodicidad");
	$txt_plazo= _("plazo para contestar"); 
} else {
	$display_plazo="display:none;";
	$simple_per=1;
	$txt_periodicidad=_("ocultar periodicidad");
	$txt_plazo= _("fecha de final");
}

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$a_cosas = [
    'filtro' => $Qfiltro,
    'periodo' => $Qperiodo,
    'id_oficina' => $id_oficina,
    'calendario' => $Qcalendario,
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($a_cosas));
$base_url_web = ConfigGlobal::getWeb(); //http://tramity.local

if (!empty($oDesplOficinas)) {
    $oDesplOficinas->setOpcion_sel($id_oficina);
}

$oView = new ViewTwig('pendientes/controller');
if (!empty($periodico_tipo) && $Qcalendario == 'registro' && $secretaria === FALSE ) {
    $a_campos = [
        'oPosicion'    => $oPosicion,
        'base_url_web' => $base_url_web,
        'cargar_css'   => $cargar_css,
        'calendario'   => $Qcalendario,
        'secretaria'   => $secretaria,
        'uid'          => $uid,
        'cal_oficina'  => $cal_oficina,
        'go'           => $go,
        'busca_ap_num' => $busca_ap_num,
        'busca_ap_any' => $busca_ap_any,
        'id_reg'       => $id_reg,
        'simple'       => $simple,
        'simple_per'   => $simple_per,
        
        'id_oficina'   => $id_oficina,
        'asunto'       => $asunto,
        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
        'oDesplEncargados' => $oDesplEncargados,

        'pagina_cancel' => $pagina_cancel,
        // para modificar etiquetas
        'status' => $status,
        'nuevo' => 5,
    ];
    echo $oView->renderizar('pendiente_form_etiquetas.html.twig',$a_campos);
} else {
    $a_campos = [
        'oPosicion'    => $oPosicion,
        'base_url_web' => $base_url_web,
        'cargar_css'   => $cargar_css,
        'calendario'   => $Qcalendario,
        'secretaria'   => $secretaria,
        'uid'          => $uid,
        'cal_oficina'  => $cal_oficina,
        'nuevo'        => $nuevo,
        'go'           => $go,
        'busca_ap_num' => $busca_ap_num,
        'busca_ap_any' => $busca_ap_any,
        'id_reg'       => $id_reg,
        'simple'       => $simple,
        'simple_per'   => $simple_per,
        'oDesplOficinas' => $oDesplOficinas,
        'id_oficina'   => $id_oficina,
        'asunto'       => $asunto,
        'oDesplVisibilidad' => $oDesplVisibilidad,
        'oArrayDesplOficinas' => $oArrayDesplOficinas,  
        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
        
        'display_plazo' => $display_plazo,
        'txt_plazo'     => $txt_plazo,
        'f_plazo'       => $f_plazo,
        'oDesplEncargados' => $oDesplEncargados,
        'display_completa' => $display_completa,
        'oDesplLugar'   => $oDesplLugar,
        'ref_prot_num'  => $ref_prot_num,
        'ref_prot_any'  => $ref_prot_any,
        'oDesplLugar1'  => $oDesplLugar1,
        'disabled'      => $disabled,
        'ref_prot_mas'  => $ref_prot_mas,
        'perm_detalle'  => $perm_detalle,
        'detalle'       => $detalle,
        'txt_completa'  => $txt_completa,
        'txt_periodicidad' => $txt_periodicidad,
        'oDesplStatus'  => $oDesplStatus,  
        'f_acabado'     => $f_acabado,
        'observ'        => $observ,
        // Para el js
        'hoy'           => $hoy,
        'hoy_iso'       => $hoy_iso,
        // + el go que ya está arriba.
        // datepicker
        'format' => $format,
        'yearStart' => $yearStart,
        'yearEnd' => $yearEnd,
        'pagina_cancel' => $pagina_cancel,
        // periodico
        'display_periodico' => $display_periodico,
        'periodico_tipo' => $periodico_tipo,
        'f_inicio' => $f_inicio,
        'f_until' => $f_until,
        'display_d_a' => $display_d_a,
        'chk_a_num_ini' => $chk_a_num_ini,
        'chk_a_num' => $chk_a_num,
        'dia_num_db' => $dia_num_db,
        'oDesplMesesAnual' => $oDesplMesesAnual,
        'chk_a_ref' => $chk_a_ref,
        'chk_a_num_dm' => $chk_a_num_dm,
        'oDesplOrdinalesAnual' => $oDesplOrdinalesAnual,
        'oDesplDiasSemanaAnual' => $oDesplDiasSemanaAnual,
        'oDesplMesesRef' => $oDesplMesesRef,
        'oDesplMeses2' => $oDesplMeses2,
        'oDesplDias' => $oDesplDias,
        'a_interval_db' => $a_interval_db,
        'display_d_m' => $display_d_m,
        'chk_m_num_ini' => $chk_m_num_ini,
        'chk_m_num' => $chk_m_num,
        'dia_num_db' => $dia_num_db,
        'chk_m_ref' => $chk_m_ref,
        'oDesplOrdinalesMes' => $oDesplOrdinalesMes,
        'oDesplDiasSemanaMes' => $oDesplDiasSemanaMes,
        'display_d_s' => $display_d_s,
        'chk_s_num_ini' => $chk_s_num_ini,
        'chk_s_ref' => $chk_s_ref,
        'oDesplDiasSemana' => $oDesplDiasSemana,
        'display_d_d' => $display_d_d,
        'chk_d_num_ini' => $chk_d_num_ini,
        'a_exdates_local' => $a_exdates_local,
    ];
    echo $oView->renderizar('pendiente_form.html.twig',$a_campos);
}
