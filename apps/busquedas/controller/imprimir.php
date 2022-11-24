<?php

use busquedas\model\ImprimirTabla;
use core\ConverterDate;
use entradas\model\GestorEntrada;
use escritos\model\GestorEscrito;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_opcion = (integer)filter_input(INPUT_POST, 'opcion');
$Q_mas = (integer)filter_input(INPUT_POST, 'mas');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$filtro = empty($Q_filtro) ? 'en_buscar' : $Q_filtro;
$Q_mas = '';
// buscar por periodo

$Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
$Q_f_max = (string)filter_input(INPUT_POST, 'f_max');


$oConverter = new ConverterDate('date', $Q_f_min);
$f_min = $oConverter->toPg();
if (empty($Q_f_max)) {
    $oHoy = new web\DateTimeLocal();
    $f_max = $oHoy->format("Y-m-d");
} else {
    $oConverter = new ConverterDate('date', $Q_f_max);
    $f_max = $oConverter->toPg(); //iso
}

// Entradas
$aWhere = [];
$aOperador = [];
if (!empty($f_min) && !empty($f_max)) {
    $aWhere ['f_entrada'] = "'$f_min','$f_max'";
    $aOperador ['f_entrada'] = 'BETWEEN';
} else {
    $aWhere['f_entrada'] = 'x';
    $aOperador['f_entrada'] = 'IS NOT NULL';
}
$gesEntradas = new GestorEntrada();
$cEntradas = $gesEntradas->getEntradasNumeradas($aWhere, $aOperador);
$aCollection['entradas'] = $cEntradas;
// EntradasBypass

// Escritos
$aWhere = [];
$aOperador = [];
if (!empty($f_min) && !empty($f_max)) {
    $aWhere ['f_salida'] = "'$f_min','$f_max'";
    $aOperador ['f_salida'] = 'BETWEEN';
} else {
    $aWhere['f_salida'] = 'x';
    $aOperador['f_salida'] = 'IS NOT NULL';
}
$gesEscritos = new GestorEscrito();
$cEscritos = $gesEscritos->getEscritosNumerados($aWhere, $aOperador);
$aCollection['escritos'] = $cEscritos;


$oImprimirTabla = new ImprimirTabla();
$oImprimirTabla->setKey('imprimir');
$oImprimirTabla->mostrarTabla($aCollection);
