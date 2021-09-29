<?php
use core\ViewTwig;
use davical\model\entity\GestorCalendarItem;
use entradas\model\Entrada;
use pendientes\model\BuscarPendiente;
use usuarios\model\PermRegistro;
use usuarios\model\entity\GestorOficina;
use web\Protocolo;
use web\ProtocoloArray;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


/*
 Buscar pendientes activos de escritos anulados:
Como estan en dos bases de datos distintas, busco primero los pendientes (serán menos), miro los id_entrada
y los busco en entradas para saber si están anulados
*/

// pendientes con id_reg:
$gestorCalendarItem = new GestorCalendarItem();
$aWhere = [ 'uid' => '^REN'];
$aOperador = ['uid' => '~'];
$cCalendarItems = $gestorCalendarItem->getCalendarItems($aWhere,$aOperador);

/* Según RFC 5545 specification
 * statvalue-todo  = "NEEDS-ACTION" ;Indicates to-do needs action.
 *                / "COMPLETED"    ;Indicates to-do completed.
 *                / "IN-PROCESS"   ;Indicates to-do in process of.
 *                / "CANCELLED"    ;Indicates to-do was cancelled.
 *  ;Status values for "VTODO".
 */

$patrón = '/^REN(\d+)-/';
$matches = [];
$a_id_entrada = [];
foreach ($cCalendarItems as $oCalendarItem) {
    $uid = $oCalendarItem->getUid();
    $status = $oCalendarItem->getStatus();
    preg_match($patrón, $uid, $matches);
    if ( !empty($matches[1]) && ($status == 'NEEDS-ACTION' || $status == 'IN-PROCESS') ) {
        $a_id_entrada[] = $matches[1];
    }
}


// Buscar en entradas a ver si están anulados
$cEntradasAnuladas = [];
foreach ($a_id_entrada as $id_entrada) {
    $oEntrada = new Entrada($id_entrada);
    $anulado = $oEntrada->getAnulado();
    if (!empty($anulado)) {
        $cEntradasAnuladas[] = $oEntrada;
    }
}

$gesOficinas = new GestorOficina();
$a_posibles_oficinas = $gesOficinas->getArrayOficinas();

$oBuscarPendiente = new BuscarPendiente();
$oBuscarPendiente->setCalendario('registro');
// tabla
foreach ($cEntradasAnuladas as $oEntrada) {
    $id_entrada = $oEntrada->getId_entrada();
    $row = [];
    $row['id_entrada'] = $id_entrada;
    
    $oProtOrigen = new Protocolo();
    $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
    $row['protocolo'] = $oProtOrigen->ver_txt();
    
    $json_ref = $oEntrada->getJson_prot_ref();
    $oArrayProtRef = new ProtocoloArray($json_ref,'','');
    $oArrayProtRef->setRef(TRUE);
    $row['referencias'] = $oArrayProtRef->ListaTxtBr();
    
    $row['asunto'] = $oEntrada->getAsuntoDetalle();
    
    $id_of_ponente =  $oEntrada->getPonente();
    $a_resto_oficinas = $oEntrada->getResto_oficinas();
    $of_ponente_txt = empty($a_posibles_oficinas[$id_of_ponente])? '?' : $a_posibles_oficinas[$id_of_ponente];
    $oficinas_txt = '';
    $oficinas_txt .= '<span class="text-danger">'.$of_ponente_txt.'</span>';
    foreach ($a_resto_oficinas as $id_oficina) {
        $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
        $oficinas_txt .= $a_posibles_oficinas[$id_oficina];
    }
    $row['oficinas'] = $oficinas_txt;
    
    $row['f_entrada'] = $oEntrada->getF_entrada()->getFromLocal();
    $row['f_contestar'] = $oEntrada->getF_contestar()->getFromLocal();
    
    // Pendientes de la entrada:
    $oBuscarPendiente->setId_reg([$id_entrada]); 
    $cPendientes = $oBuscarPendiente->getPendientes();
    $lst_pendientes = '';
    foreach ($cPendientes as $oPendiente) {
        /*
        $uid = $oPendiente->getUid();
        // uid = REN33-20210211T172224@registro_oficina_agd
        $pos = strpos($uid, '_', 1);
        $parent_container = substr($uid, $pos + 1);
        */
        
        $protocolo = $oPendiente->getLocation();
        $rrule = $oPendiente->getRrule();
        $asunto = $oPendiente->getAsuntoDetalle();
        if (!empty($asunto)) { $asunto=htmlspecialchars(stripslashes($asunto),ENT_QUOTES,'utf-8'); }
        
        $estado = $oPendiente->getStatus();
        
        if (!empty($rrule)) {
            $periodico="p";
        } else {
            $periodico="";
        }
        
        $lst_pendientes .= empty($lst_pendientes)? '' : "<br>";
        $lst_pendientes .= $protocolo."::".$periodico."::".$asunto."::".$estado;
    }
    
    $row['pendientes'] = $lst_pendientes;
    
    // para ordenar. Si no añado id_entrada, sobre escribe.
    $f_entrada_iso = $oEntrada->getF_entrada()->getIso() . $id_entrada;
    $a_entradas[$f_entrada_iso] = $row;
}
// ordenar por f_entrada:
krsort($a_entradas,SORT_STRING);


$a_cosas = [
    'filtro' => 'pendientes',
    'periodo' => 'hoy',
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($a_cosas));

$a_campos = [
    'calendario' => 'registro',
    'a_entradas' => $a_entradas,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('pendientes/controller');
echo $oView->renderizar('pendiente_sanear.html.twig',$a_campos);
