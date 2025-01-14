<?php

// INICIO Cabecera global de URL de controlador *********************************

use migration\model\Connect2Etherpad;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$centro = 'dlb';

// Conexión al servidor davical (calendarios caldav).
$oConexion = new Connect2Etherpad();
$oDbEtherpad = $oConexion->getPDO();
$host = $oConexion->getHost();

// revision en entradas
$padId = $centro . '\*ent\d+:revs:';
$reg_exp = 'pad.*\$' . $padId;
$sql = "DELETE FROM store WHERE key ~ '$reg_exp'";
if ($oDbEtherpad->Query($sql) === FALSE) {
    echo "Error al borrar revisiones entradas etherpad: $padId<br>";
}

// revision en escritos
$padId = $centro . '\*esc\d+:revs:';
$reg_exp = 'pad.*\$' . $padId;
$sql = "DELETE FROM store WHERE key ~ '$reg_exp'";
if ($oDbEtherpad->Query($sql) === FALSE) {
    echo "Error al borrar revisiones escritos etherpad: $padId<br>";
}

// revision en adjuntos
$padId = $centro . '\*adj\d+:revs:';
$reg_exp = 'pad.*\$' . $padId;
$sql = "DELETE FROM store WHERE key ~ '$reg_exp'";
if ($oDbEtherpad->Query($sql) === FALSE) {
    echo "Error al borrar revisiones adjuntos etherpad: $padId<br>";
}

// revision en documentos
$padId = $centro . '\*doc\d+:revs:';
$reg_exp = 'pad.*\$' . $padId;
$sql = "DELETE FROM store WHERE key ~ '$reg_exp'";
if ($oDbEtherpad->Query($sql) === FALSE) {
    echo "Error al borrar revisiones documentos etherpad: $padId<br>";
}


