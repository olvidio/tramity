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
use usuarios\model\entity\GestorUsuario;
use web\DateTimeLocal;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// Si vengo del formulario de entradas, abro una ventana nueva y
// los parametros vienen por GET.

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qperiodo = (string) \filter_input(INPUT_POST, 'periodo');
$Qnuevo = (string) \filter_input(INPUT_POST, 'nuevo');
$Qcalendario = (string) \filter_input(INPUT_POST, 'calendario');
$Qid_oficina = (string) \filter_input(INPUT_POST, 'id_oficina');
$go = (string) \filter_input(INPUT_POST, 'go');
$id_reg = (string) \filter_input(INPUT_POST, 'id_reg');
$Qpermiso = (string) \filter_input(INPUT_POST, 'permiso');

$cargar_css = FALSE;
if (empty($Qid_oficina)) {
    $go = (string) \filter_input(INPUT_GET, 'go');
    $id_reg = (integer) \filter_input(INPUT_GET, 'id_reg');
    $Qid_oficina = (integer) \filter_input(INPUT_GET, 'ponente');
    $Qcalendario = 'registro';
    
    //if ($go=="entradas" || $go=="salidas" || $go=="mov_iese") { 
    $cargar_css = TRUE;
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
$oArrayDesplOficinas ->setAccionConjunto('fnjs_mas_oficinas(event)');

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
$oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas(event)');

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

//echo "mi_of: $mi_oficina<br>";

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

$nuevo = empty($Qnuevo)? 1 : $nuevo=$Qnuevo;
$any=date('y');
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
	//$go = ($go != 'entradas')? 'otro': $go;
	$go = ($go != 'entradas')? 'lista': $go;
} else {
    $go = 'lista';
    // Si vengo a form_pendiente, desde un checkbox(sel) o de la tabla de pendientes(link) .
    if (!empty($_POST['sel']) || !empty($_POST['uid_sel']) || !empty($_POST['uid'])) { 
        if (!empty($_POST['sel'])) { //vengo de un checkbox
            $uid=strtok($_POST['sel'][0],'#');
            $cal_oficina=strtok('#');
        } else {
            empty($_POST['uid'])? $uid="" : $uid=$_POST['uid'];
            empty($_POST['cal_oficina'])? $cal_oficina="" : $cal_oficina=$_POST['cal_oficina'];
        }
        if (empty($uid)) { 
            echo _("No sé a que pendiente se refiere.");
            exit();
        } 
        if (empty($id_reg)) {
            if (($pos_ini = strpos($uid, 'REN')) !== FALSE && $pos_ini == 0) { //  Registro entradas
                $pos = strpos($uid, '-') - 3;
                $id_reg=substr($uid,3,$pos);
            }
        }
            
        $oPendiente = new Pendiente($cal_oficina, $Qcalendario, $role_actual, $uid) ;
        
        $asunto = $oPendiente->getAsunto();
        $status = $oPendiente->getStatus();
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
    // Las entradas son cargos, hay que pasarlos a oficinas para los pendientes:
    /*$a_oficinas = [];
    foreach ($resto_oficinas as $id_oficina) {
        $oCargo = new Cargo($id_cargo);
        $a_oficinas[] = $oCargo->getId_oficina();
    }
    */
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


	/*
// si vengo desde el regitro salidas
if (!empty($_POST['dest_id_lugar'])) {
	// estas variables quedan igual: asunto, f_acabado, f_plazo, f_retraso, id_reg
	if (empty($status)) $status="NEEDES-ACTION";
	//$oficinas_sel=$oficinas;
	$ref_id_lugar=$dest_id_lugar;
	$ref_prot_num=$dest_prot_num;
	$ref_prot_any=$dest_prot_any;
	$ref_prot_mas=$dest_mas;

	// la primera oficina es la que determina el calendario
	$aa_oficinas = explode(',',$_POST['oficinas']);
	$id_oficina = array_shift($aa_oficinas);
	$display_completa="display:in-line;";
	$go ="salidas"; // para que vuelva a salidas.php

	empty($_POST['id_pendiente'])? $uid="" : $uid=$_POST['id_pendiente'];
	$sigla=$a_posibles_oficinas[$id_oficina];
	$cal_oficina="oficina_$sigla";	

	$nuevo=2;

	$base_url=ConfigGlobal::$web_calendaris."/$cal_oficina/registro/";
	
	$cal = new CalDAVClient( $base_url , ConfigGlobal::usuario(), ConfigGlobal::pass() );
	
	$uid2=strtok($uid,'@');
	$nom_fichero="$uid2.ics";
	$oRef=buscar_ref_uid($uid,"object");
	
	// busco todos los datos del TODO
	$todo=$cal->GetEntryByUid($uid);

	$ahora=date("Ymd\THis");
	$etag=$todo[0]['etag'];

	$vcalendar = new iCalComponent($todo[0]['data']);
	$icalComp = $vcalendar->GetComponents('VTODO');
	$icalComp = $icalComp[0];  // If you know there's only 1 of them...

	$exdates = $vcalendar->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');

	$asunto=$icalComp->GetPValue("SUMMARY");
	$status=$icalComp->GetPValue("STATUS");
	$carpeta=$icalComp->GetPValue("CATEGORIES");
	
	$detalle=$icalComp->GetPValue("COMMENT");
	$visibilidad=$icalComp->GetPValue("CLASS");
	$f_plazo=fecha_YMD2DMY($icalComp->GetPValue("DUE"));
	$f_acabado=fecha_YMD2DMY($icalComp->GetPValue("COMPLETED"));
	$observ=$icalComp->GetPValue("DESCRIPTION");
	$encargado=$icalComp->GetPValue("ATTENDEE");
	$pendiente_con=$icalComp->GetPValue("X-DLB-PENDIENTE-CON");
	$ref_prot_mas=$icalComp->GetPValue("X-DLB-REF-MAS");
	$oficinas_txt=$icalComp->GetPValue("X-DLB-OFICINAS");
	
	$perm_detalle=permiso_detalle_pendiente($sigla_of,$visibilidad,"d");

	if (!empty($oficinas_txt)) {
		$aa_oficinas=preg_split('/ /',$oficinas_txt);
	}
	// para que salga bien la referencia
	if (is_object($oRef)) {
		$ref_id_lugar=$oRef->id_lugar;
		$ref_prot_num=$oRef->num;
		$ref_prot_any=$oRefany;
	}
}
*/
/*
// si vengo desde el regitro de cancilleria
if ($go=="mov_iese") {
	
	$uid=$_REQUEST['id_pendiente'];
	$id_reg=substr($uid, 2, strpos($uid, '-')-2);
	$uid2=strtok($_REQUEST['id_pendiente'],'@');
	$nom_fichero="$uid2.ics";
	$cal_oficina=str_replace("registro_","",strtok('@'));
	$ref=buscar_ref_uid($uid2,"object");

	$oficina = str_replace("oficina_","",$cal_oficina);
	$id_oficina=array_search($oficina, $a_posibles_oficinas);

	$display_completa="display:in-line;";
	$go ="mov_iese"; // para que vuelva a reg_iese

	$nuevo=2;

	$base_url=ConfigGlobal::$web_calendaris."/$cal_oficina/registro/";
	
	$cal = new CalDAVClient( $base_url , ConfigGlobal::usuario(), ConfigGlobal::pass() );
	
	// busco todos los datos del TODO
	$todo=$cal->GetEntryByUid($uid);

	$ahora=date("Ymd\THis");
	$etag=$todo[0]['etag'];

	$vcalendar = new iCalComponent($todo[0]['data']);
	$icalComp = $vcalendar->GetComponents('VTODO');
	$icalComp = $icalComp[0];  // If you know there's only 1 of them...

	$exdates = $vcalendar->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');

	$asunto=$icalComp->GetPValue("SUMMARY");
	$status=$icalComp->GetPValue("STATUS");
	$carpeta=$icalComp->GetPValue("CATEGORIES");

	$detalle=$icalComp->GetPValue("COMMENT");
	$visibilidad=$icalComp->GetPValue("CLASS");
	$f_inicio=fecha_YMD2DMY($icalComp->GetPValue("DTSTART"));
	$f_plazo=fecha_YMD2DMY($icalComp->GetPValue("DUE"));
	$f_acabado=fecha_YMD2DMY($icalComp->GetPValue("COMPLETED"));
	$observ=$icalComp->GetPValue("DESCRIPTION");
	$encargado=$icalComp->GetPValue("ATTENDEE");
	$pendiente_con=$icalComp->GetPValue("X-DLB-PENDIENTE-CON");
	$ref_prot_mas=$icalComp->GetPValue("X-DLB-REF-MAS");
	$oficinas_txt=$icalComp->GetPValue("X-DLB-OFICINAS");
	
	$perm_detalle=permiso_detalle_pendiente($sigla_of,$visibilidad,"d");

	if (!empty($oficinas_txt)) {
		$aa_oficinas=preg_split('/ /',$oficinas_txt);
	}
	// para que salga bien la referencia
	if (is_array($ref)) {
		$ref_id_lugar=$ref['id_lugar'];
		$ref_prot_num=$ref['num'];
		$ref_prot_any=$ref['any'];
	}
}

// lugares posibles:
if (!empty($uid) && $uid[0]=="R" && $uid[1]=="C") { //caso de registro cancillería
	//$lugares=array_lugares_can();
} else {
	//$lugares=array_lugares();
}
*/

/*
$a_encargados_sel=array();
$permisos= explode(",",ConfigGlobal::permisos());
$c=0;
foreach ($permisos as $permis) {
	if ($permis!="dtor" && $permis!="pendents") {
		$c++;
		if ($c > 1) array_push($a_encargados_sel,"separador#-------");
		$a_encargados_sel=array_merge($a_encargados_sel,$a_encargados[$permis]);
	}
}
*/

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
    
$a_campos = [
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
];

$oView = new ViewTwig('pendientes/controller');
echo $oView->renderizar('pendiente_form.html.twig',$a_campos);
