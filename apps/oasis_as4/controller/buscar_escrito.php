<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use lugares\domain\repositories\LugarRepository;
use web\Desplegable;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_ctr_anulados = (bool)filter_input(INPUT_POST, 'ctr_anulados');
$Q_accion = (string)filter_input(INPUT_POST, 'accion');

//7
$Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
$Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
// para quitar el '0':
$Q_prot_num = empty($Q_prot_num) ? '' : $Q_prot_num;
$Q_prot_any = empty($Q_prot_any) ? '' : $Q_prot_any;


$LugarRepository = new LugarRepository();
$a_lugares = $LugarRepository->getArrayBusquedas($Q_ctr_anulados);

// Busco el id_lugar de la dl.
$id_siga_local = $LugarRepository->getId_sigla_local();
$sigla = $_SESSION['oConfig']->getSigla();
// Busco el id_lugar de cr.
$id_cr = $LugarRepository->getId_cr();


$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setOpcion_sel($Q_id_lugar);

$vista = ConfigGlobal::getVista();

$a_campos = [
    //'oHash' => $oHash,
    'oDesplLugar' => $oDesplLugar,
    'id_dl' => $id_siga_local,
    'dele' => $sigla,
    'id_cr' => $id_cr,
    'filtro' => $Q_filtro,
    'accion' => $Q_accion,
    'prot_num' => $Q_prot_num,
    'prot_any' => $Q_prot_any,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('oasis_as4/controller');
$oView->renderizar('buscar_escrito.html.twig', $a_campos);