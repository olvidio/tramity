<?php

/**
* Esta página cambia el id_entrada de los pendientes
*
*
*@package	delegacion
*@subpackage	registro
*@author	Daniel Serrabou
*@since		28/9/21.
*		
*/
// INICIO Cabecera global de URL de controlador *********************************
use davical\model\DavicalMigrar;
use entradas\model\GestorEntrada;
use usuarios\model\entity\Oficina;
use web\Protocolo;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

// ----------------------------------------------------------------------------------------------
/* Resetear valores iniciales */

$Qid_lugar_org  = (string) \filter_input(INPUT_POST, 'id_lugar_org');
$Qprot_num_org  = (string) \filter_input(INPUT_POST, 'prot_num_org');
$Qprot_any_org  = (string) \filter_input(INPUT_POST, 'prot_any_org');

$Qid_lugar_dst  = (string) \filter_input(INPUT_POST, 'id_lugar_dst');
$Qprot_num_dst  = (string) \filter_input(INPUT_POST, 'prot_num_dst');
$Qprot_any_dst  = (string) \filter_input(INPUT_POST, 'prot_any_dst');

$gesEntradas = new GestorEntrada();       //$aProt_orgigen = ['id_lugar', 'num', 'any', 'mas']

// busacr id_entrada del prot origen
$aProt_origen = [ 'lugar' => $Qid_lugar_org,
    'num' => $Qprot_num_org,
    'any' => $Qprot_any_org,
    'mas' => '',
];
$aWhere = ['bypass' => 'f', 'anulado' => 'x'];
$aOperador = ['anulado' => 'IS NULL'];
$cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhere,$aOperador);

$msg = '';
if (is_array($cEntradas)) {
    if (empty($cEntradas)) {
        $msg .= _("No se encuentra ninguna entrada con el protocolo origen");
        $msg .= "\n";
    } elseif (count($cEntradas) > 1) {
        $msg .= _("Existen más de una entrada con el protocolo origen");
        $msg .= "\n";
    }
} else {
    $msg .= _("Error en la búsqueda del origen");
    $msg .= "\n";
}
// Sólo debe haber una entrada:
if (empty($msg)) {
    $oEntrada = $cEntradas[0];
    $id_entrada_org = $oEntrada->getId_entrada();
    $id_reg_org = 'REN'.$id_entrada_org; // REN = Regitro Entrada
    $id_of_ponente_org = $oEntrada->getPonente();
    // location
    $location_org = '';
    $oProtLocal = new Protocolo();
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (!empty(get_object_vars($json_prot_origen))) {
        $oProtLocal->setLugar($json_prot_origen->lugar);
        $oProtLocal->setProt_num($json_prot_origen->num);
        $oProtLocal->setProt_any($json_prot_origen->any);
        //mas: No cojo el del registro, el pendiente puede tener su propio 'mas'
        $location_org = $oProtLocal->ver_txt();
    }
}

// buscar id_entrada del prot destino
$aProt_dst = [ 'lugar' => $Qid_lugar_dst,
    'num' => $Qprot_num_dst,
    'any' => $Qprot_any_dst,
    'mas' => '',
];
$aWhere = ['bypass' => 'f', 'anulado' => 'x'];
$aOperador = ['anulado' => 'IS NULL'];
$cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_dst,$aWhere,$aOperador);

if (is_array($cEntradas)) {
    if (empty($cEntradas)) {
        $msg .= _("No se encuentra ninguna entrada con el protocolo destino");
        $msg .= "\n";
    } elseif (count($cEntradas) > 1) {
        $msg .= _("Existen más de una entrada con el protocolo destino");
        $msg .= "\n";
    }
} else {
    $msg .= _("Error en la búsqueda del destino");
    $msg .= "\n";
}
// Sólo debe haber una entrada:
if (empty($msg)) {
    $oEntrada = $cEntradas[0];
    $id_entrada_dst = $oEntrada->getId_entrada();
    $id_reg_dst = 'REN'.$id_entrada_dst; // REN = Regitro Entrada
    $id_of_ponente_dst = $oEntrada->getPonente();
    // location
    $location_dst = '';
    $oProtLocal = new Protocolo();
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (!empty(get_object_vars($json_prot_origen))) {
        $oProtLocal->setLugar($json_prot_origen->lugar);
        $oProtLocal->setProt_num($json_prot_origen->num);
        $oProtLocal->setProt_any($json_prot_origen->any);
        //mas: No cojo el del registro, el pendiente puede tener su propio 'mas'
        $location_dst = $oProtLocal->ver_txt();
    }
}


if (empty($msg) && ($id_of_ponente_dst != $id_of_ponente_org) ) {
    $msg .= _("No se puede cambiar el pendiente de una oficina a otra");
    $msg .= "\n";
}


if (empty($msg)) {
    //$oOficina = new Oficina($id_of_ponente_org);
    //$oficina_txt = $oOficina->getSigla();
    
    $oDavicalMigrar = new DavicalMigrar();
    $oDavicalMigrar->setId_oficina($id_of_ponente_org);
    $oDavicalMigrar->setId_reg_org($id_reg_org);
    $oDavicalMigrar->setId_reg_dst($id_reg_dst);
    $oDavicalMigrar->setLocation_org($location_org);
    $oDavicalMigrar->setLocation_dst($location_dst);
    if ($oDavicalMigrar->migrar() === FALSE) {
        $msg .= _("No se ha podido trasladar...");
    }
}



if (empty($msg)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $msg;
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();

