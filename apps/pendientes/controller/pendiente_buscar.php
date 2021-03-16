<?php

use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use core\ConfigGlobal;
use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use lugares\model\entity\GestorLugar;
use pendientes\model\BuscarPendiente;
use pendientes\model\Pendiente;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;
use usuarios\model\entity\Oficina;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qcalendario = (string) \filter_input(INPUT_POST, 'calendario');
$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qstatus = (string) \filter_input(INPUT_POST, 'status');
$Qid_lugar =  (integer) \filter_input(INPUT_POST, 'id_lugar');
$Qprot_num =  (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any =  (integer) \filter_input(INPUT_POST, 'prot_any');
$Qprot_mas =  (string) \filter_input(INPUT_POST, 'prot_mas');
$Qid_oficina = (string) \filter_input(INPUT_POST, 'id_oficina');
$Qf_min_enc =  (string) \filter_input(INPUT_POST, 'f_min');
$Qf_min = urldecode($Qf_min_enc);
$Qf_max_enc =  (string) \filter_input(INPUT_POST, 'f_max');
$Qf_max = urldecode($Qf_max_enc);

$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayLugares();

$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setOpcion_sel($Qid_lugar);

$a_opciones_status = Pendiente::getArrayStatus();
// añadr la opción de 'caulquiera' al inicio
$all_traducido = _("cualquiera");
$a_opciones_status = array_merge(array("all" => $all_traducido), $a_opciones_status);
$oDesplStatus = new Desplegable();
$oDesplStatus->setNombre('status');
$oDesplStatus->setOpciones($a_opciones_status);
$oDesplStatus->setOpcion_sel($Qstatus);

$gesOficinas = new GestorOficina();
$a_oficinas = $gesOficinas->getArrayOficinas();
// solo secretaría puede ver/crear pendientes de otras oficinas
$role_actual = ConfigGlobal::role_actual();
if ($role_actual === 'secretaria') {
    $secretaria = 1; // NO TRUE, para eljavascript;
    $oDesplOficinas = new Desplegable();
    $oDesplOficinas->setNombre('id_oficina');
    $oDesplOficinas->setOpciones($a_oficinas);
    $oDesplOficinas->setOpcion_sel($Qid_oficina);
    $oDesplOficinas->setBlanco(TRUE);
    $id_oficina = '';
} else {
    $oDesplOficinas = []; // para evitar errores
    $secretaria = 0; // NO FALSE, para eljavascript;
    $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
    $id_oficina = $oCargo->getId_oficina();
}

if ($Qque == 'buscar') {
    $oBuscarPendiente = new BuscarPendiente();
    $oBuscarPendiente->setCalendario($Qcalendario);

    if ($Qcalendario == 'registro' && !empty($Qid_lugar)) {
        // buscar en el registro:
        $Qprot_any = empty($Qprot_any)? '' : core\any_2($Qprot_any);
        
        $oBuscar = new Buscar();
        //$oBuscar->setId_sigla($id_sigla_local);
        $oBuscar->setId_lugar($Qid_lugar);
        $oBuscar->setProt_num($Qprot_num);
        $oBuscar->setProt_any($Qprot_any);
        
        $aCollection = $oBuscar->getCollection(7);
        $aIds = [];
        foreach ($aCollection as $key => $cCollection) {
            foreach ($cCollection as $oEntrada) { // También puede ser un Escrito, pero en principio son entradas.
                if ($key == 'entradas') {
                    $id_reg = $oEntrada->getId_entrada();
                }
                /*
                if ($key == 'escritos') {
                    $id_reg = $oEntrada->getId_escrito();
                }
                */
                $aIds[] = $id_reg;
            }
        }
        if (!empty($aIds)) {
            $oBuscarPendiente->setId_reg($aIds); 
        }
        
    }

    if (!empty($Qid_oficina)) { 
        $oOficina = new Oficina($Qid_oficina);
        $sigla_oficina = $oOficina->getSigla();
        $oBuscarPendiente->setOficina($sigla_oficina); 
    }
    if (!empty($Qasunto)) { $oBuscarPendiente->setAsunto($Qasunto); }
    if (!empty($Qf_min)) { $oBuscarPendiente->setF_min($Qf_min); }
    if (!empty($Qf_max)) { $oBuscarPendiente->setF_max($Qf_max); }
    if (!empty($Qstatus)) { $oBuscarPendiente->setStatus($Qstatus); }

    $gesEtiquetas = new GestorEtiqueta();
    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    }


    $cPendientes = $oBuscarPendiente->getPendientes();
    $a_valores = [];
    $t = 0;
    $oPermisoregistro = new PermRegistro();
    foreach ($cPendientes as $oPendiente) {
        $t++;
        $perm_detalle = $oPermisoregistro->permiso_detalle($oPendiente, 'detalle');
        $uid = $oPendiente->getUid();
        // uid = REN33-20210211T172224@registro_oficina_agd
        $pos = strpos($uid, '_', 1);
        $parent_container = substr($uid, $pos + 1);
        
        $protocolo = $oPendiente->getLocation();
        $rrule = $oPendiente->getRrule();
        $asunto = $oPendiente->getAsuntoDetalle();
        if (!empty($asunto)) $asunto=htmlspecialchars(stripslashes($asunto),ENT_QUOTES,'utf-8');
        $plazo = $oPendiente->getF_plazo()->getFromLocal();
        $plazo_iso = $oPendiente->getF_plazo()->format('Ymd'); // sólo números, para poder ordenar.
        
        $estado = $oPendiente->getStatus();
        $of_ponente = $oPendiente->getPonente();
        $ponente = $a_oficinas[$of_ponente];
        
        $oficinas_txt = $oPendiente->getOficinasTxtcsv();
        
        $aEtiquetas = $oPendiente->getEtiquetasArray();
        $str_etiquetas = '';
        foreach ($aEtiquetas as $id_etiqueta) {
            $str_etiquetas .= empty($str_etiquetas)? '' : ', ';
            $str_etiquetas .= empty($a_posibles_etiquetas[$id_etiqueta])? '' : $a_posibles_etiquetas[$id_etiqueta];
        }
        
        if (!empty($rrule)) {
            $periodico="p";
        } else {
            $periodico="";
        }
        
        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $a_valores[$t]['sel'] = "$uid#$parent_container";
        } else {
            $a_valores[$t]['sel'] = "x";
        }
        $a_valores[$t][1]=$protocolo;
        $a_valores[$t][2]=$str_etiquetas;
        $a_valores[$t][3]=$periodico;
        $a_valores[$t][4]=$asunto;
        $a_valores[$t][5]=$plazo;
        $a_valores[$t][6]=$ponente;
        $a_valores[$t][7]=$oficinas_txt;
        $a_valores[$t][8]=$estado;
        // para el orden
        if ($plazo!="x") {
            $a_valores[$t]['order'] = $plazo_iso;
        }
    }
} else {
    $a_valores = [];
}

$a_botones[]=array( 'txt' => _('nuevo pendiente'), 'click' =>"fnjs_nuevo_pendiente(\"#seleccionados\")" ) ;
$a_botones[]=array( 'txt' => _('marcar como terminado'), 'click' =>"fnjs_marcar(\"#seleccionados\")" ) ;
$a_botones[]=array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados\")" ) ;
$a_botones[]=array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados\")" ) ;

$a_cabeceras=array( ucfirst(_("protocolo")),
    ucfirst(_("etiquetas")),
    _("p"),
    array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter'),
    array('name'=>ucfirst(_("fecha plazo")),'class'=>'fecha'),
    ucfirst(_("ponente")),
    ucfirst(_("oficinas")),
    ucfirst(_("estado")),
);

$oTabla = new Lista();
$oTabla->setId_tabla('pen_tabla');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);


// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();

$a_campos = [
    'calendario'   => $Qcalendario,
    'secretaria'   => $secretaria,
    'oDesplLugar' => $oDesplLugar,
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplStatus' => $oDesplStatus,
    'asunto' => $Qasunto,
    'id_oficina'     => $id_oficina,
    'oTabla' => $oTabla,
    'f_min' => $Qf_min,
    'f_max' => $Qf_max,
    'prot_num' => $Qprot_num,
    'prot_any' => $Qprot_any,
    'prot_mas' => $Qprot_mas,
    // datepicker
    'format' => $format,
];

$oView = new ViewTwig('pendientes/controller');
echo $oView->renderizar('pendiente_buscar.html.twig',$a_campos);


