<?php

// INICIO Cabecera global de URL de controlador *********************************

use web\DateTimeLocal;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oHoy = new DateTimeLocal();
$oFCorte = (new web\DateTimeLocal)->sub(interval: new DateInterval('P3Y'));
$f_corte_iso = $oFCorte->getIso();

$oDbl = $GLOBALS['oDBT'];
$centro = 'dlb';

$sql = "DELETE FROM $centro.expedientes WHERE f_ini_circulacion < '$f_corte_iso' ;";

if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
    echo "Error de algún tipo..."."<br>";
}

// Expediente_firmas y etiquetas se debería eliminar (foreing key)

echo "fet!";