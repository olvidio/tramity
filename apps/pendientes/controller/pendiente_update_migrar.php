<?php

/**
 * Esta página cambia el id_entrada de los pendientes
 *
 *
 * @package    delegacion
 * @subpackage    registro
 * @author    Daniel Serrabou
 * @since        28/9/21.
 *
 */

// INICIO Cabecera global de URL de controlador *********************************
use davical\model\DavicalMigrar;
use entradas\model\GestorEntrada;
use web\Protocolo;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

// ----------------------------------------------------------------------------------------------
/* Resetear valores iniciales */

$Q_id_lugar_org = (string)filter_input(INPUT_POST, 'id_lugar_org');
$Q_prot_num_org = (string)filter_input(INPUT_POST, 'prot_num_org');
$Q_prot_any_org = (string)filter_input(INPUT_POST, 'prot_any_org');

$Q_id_lugar_dst = (string)filter_input(INPUT_POST, 'id_lugar_dst');
$Q_prot_num_dst = (string)filter_input(INPUT_POST, 'prot_num_dst');
$Q_prot_any_dst = (string)filter_input(INPUT_POST, 'prot_any_dst');

$gesEntradas = new GestorEntrada();       //$aProt_orgigen = ['id_lugar', 'num', 'any', 'mas']

// buscar id_entrada del prot origen
$aProt_origen = ['id_lugar' => $Q_id_lugar_org,
    'num' => $Q_prot_num_org,
    'any' => $Q_prot_any_org,
    'mas' => '',
];
$aWhere = ['bypass' => 'f', 'anulado' => 'x'];
$aOperador = ['anulado' => 'IS NULL'];
$cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhere, $aOperador);

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
    $id_reg_org = 'REN' . $id_entrada_org; // REN = Registro Entrada
    $id_of_ponente_org = $oEntrada->getPonente();
    // location
    $location_org = '';
    $oProtLocal = new Protocolo();
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (!empty(get_object_vars($json_prot_origen))) {
        $oProtLocal->setLugar($json_prot_origen->id_lugar);
        $oProtLocal->setProt_num($json_prot_origen->num);
        $oProtLocal->setProt_any($json_prot_origen->any);
        //mas: No cojo el del registro, el pendiente puede tener su propio 'mas'
        $location_org = $oProtLocal->ver_txt();
    }
}

// buscar id_entrada del prot destino
$aProt_dst = ['id_lugar' => $Q_id_lugar_dst,
    'num' => $Q_prot_num_dst,
    'any' => $Q_prot_any_dst,
    'mas' => '',
];
$aWhere = ['bypass' => 'f', 'anulado' => 'x'];
$aOperador = ['anulado' => 'IS NULL'];
$cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_dst, $aWhere, $aOperador);

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
$a_resto_oficinas = [];
if (empty($msg)) {
    $oEntrada = $cEntradas[0];
    $id_entrada_dst = $oEntrada->getId_entrada();
    $id_reg_dst = 'REN' . $id_entrada_dst; // REN = Regitro Entrada
    $id_of_ponente_dst = $oEntrada->getPonente();
    $a_resto_oficinas = $oEntrada->getResto_oficinas();
    // location
    $location_dst = '';
    $oProtLocal = new Protocolo();
    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (!empty(get_object_vars($json_prot_origen))) {
        $oProtLocal->setLugar($json_prot_origen->id_lugar);
        $oProtLocal->setProt_num($json_prot_origen->num);
        $oProtLocal->setProt_any($json_prot_origen->any);
        //mas: No cojo el del registro, el pendiente puede tener su propio 'mas'
        $location_dst = $oProtLocal->ver_txt();
    }
}


if (empty($msg) && ($id_of_ponente_dst != $id_of_ponente_org)) {
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
        $msg .= _("No se ha podido trasladar para la oficina del ponente");
        $msg .= "\n";
    }
}
// para el resto de oficinas:
foreach ($a_resto_oficinas as $id_oficina) {
    $oDavicalMigrar = new DavicalMigrar();
    $oDavicalMigrar->setId_oficina($id_oficina);
    $oDavicalMigrar->setId_reg_org($id_reg_org);
    $oDavicalMigrar->setId_reg_dst($id_reg_dst);
    $oDavicalMigrar->setLocation_org($location_org);
    $oDavicalMigrar->setLocation_dst($location_dst);
    if ($oDavicalMigrar->migrar() === FALSE) {
        $msg .= sprintf(_("No se ha podido trasladar para la oficina: %s"),$id_oficina);
        $msg .= "\n";
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

