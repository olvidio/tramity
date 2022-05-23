<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use web\Desplegable;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qctr_anulados = (bool) \filter_input(INPUT_POST, 'ctr_anulados');

// vengo de modificar algo, quiero volver a la lista
$Qopcion = (integer) \filter_input(INPUT_POST, 'opcion');
//7
$Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
$Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
// para quitar el '0':
$Qprot_num = empty($Qprot_num)? '' : $Qprot_num;
$Qprot_any = empty($Qprot_any)? '' : $Qprot_any;


$gesLugares = new GestorLugar();
$a_lugares = $gesLugares->getArrayBusquedas($Qctr_anulados);

// Busco el id_lugar de la dl.
$id_siga_local = $gesLugares->getId_sigla_local();
$sigla = $_SESSION['oConfig']->getSigla();
// Busco el id_lugar de cr.
$id_cr = $gesLugares->getId_cr();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setOpcion_sel($Qid_lugar);

$vista = ConfigGlobal::getVista();

$a_campos = [
    //'oHash' => $oHash,
    'oDesplLugar' => $oDesplLugar,
    'id_dl' => $id_siga_local,
    'dele' => $sigla,
    'id_cr' => $id_cr,
    'filtro' => $Qfiltro,
    'opcion' => $Qopcion,
    'prot_num' => $Qprot_num,
    'prot_any' => $Qprot_any,
    // tabs_show
    'vista' => $vista,
    ];

$oView = new ViewTwig('oasis_as4/controller');
echo $oView->renderizar('buscar_escrito.html.twig',$a_campos);