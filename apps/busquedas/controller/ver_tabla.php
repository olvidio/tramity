<?php

// INICIO Cabecera global de URL de controlador *********************************
use lugares\model\entity\GestorLugar;
use busquedas\model\VerTabla;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qopcion = (integer) \filter_input(INPUT_POST, 'opcion');


$sigla = $_SESSION['oConfig']->getSigla();
$gesLugares = new GestorLugar();
$cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
if (!empty($cLugares)) {
    $id_sigla = $cLugares[0]->getId_lugar();
}


switch ($Qopcion) {
    case 7: // un protocolo concreto:
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lugar');
        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (integer) \filter_input(INPUT_POST, 'prot_any');
        
        $Qprot_any = empty($Qprot_any)? '' : any_4($Qprot_any);
        
        $oTabla = new VerTabla();
        $oTabla->setId_sigla($id_sigla);
        $oTabla->setId_lugar($Qid_lugar);
        $oTabla->setProt_num($Qprot_num);
        $oTabla->setProt_any($Qprot_any);
        
        echo $oTabla->mostrarTabla();
        
}
