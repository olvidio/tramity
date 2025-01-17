<?php

// INICIO Cabecera global de URL de controlador *********************************

use migration\model\Connect2Etherpad;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oDbl = $GLOBALS['oDBT'];
$centro = 'dlb';

// Conexión al servidor davical (calendarios caldav).
$oConexion = new Connect2Etherpad();
$oDbEtherpad = $oConexion->getPDO();
$host = $oConexion->getHost();
$tabla = '';


// escritos reales en tramity. Exportar a nueva taba z_escritos_reales
$tabla_escritos_reales = 'z_escritos_reales';
$sql1 = "DROP TABLE IF EXISTS $tabla_escritos_reales";
if ($oDbl->Query($sql1) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}
$sql2 = "SELECT id_escrito INTO $tabla_escritos_reales FROM $centro.escritos ORDER BY id_escrito";
if (($oDblSt = $oDbl->Query($sql2)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

$num_escritos = $oDblSt->rowCount();

$sql30 = "DROP TABLE IF EXISTS $tabla_escritos_reales";
if (($oDblSt = $oDbEtherpad->Query($sql30)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}
$sql31 = "CREATE TABLE $tabla_escritos_reales (id_escrito int)";
if (($oDblSt = $oDbEtherpad->Query($sql31)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

//de 100 en 100
$init = 0;
$inc = 100;

for ($i = 0; $init < $num_escritos; $i++) {
    $sql32 = "SELECT id_escrito FROM $tabla_escritos_reales LIMIT $inc OFFSET $init";
    // leo de tramity
    if (($oDblSt = $oDbl->Query($sql32)) === FALSE) {
        echo "Error de algún tipo..." . "<br>";
    }
    // inserto en etherpad
    foreach ($oDblSt as $row) {
        $id = $row['id_escrito'];
        $sql33 = "INSERT INTO $tabla_escritos_reales (id_escrito) VALUES ($id);";
        $oDbEtherpad->exec($sql33);
    }
    $init = $init + $inc;
}


// crear tabla en etherpad de las escritos que existen
$tabla_id = 'z_escritos';
$sql4 = "DROP TABLE IF EXISTS $tabla_id";
$oDbEtherpad->exec($sql4);
$regexpCentro = '\\$' . $centro;
$sql5 = 'SELECT regexp_replace(key,\'(pad:.*' . $regexpCentro . '\*ent)(\d+)(:.*)*\', \'\2\')::int as id
        INTO ' . $tabla_id . '
        FROM store 
        WHERE key ~ \'pad:.*' . $regexpCentro . '\*ent\d+\'
        GROUP BY id';
if (($oDblSt = $oDbEtherpad->Query($sql5)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

// crear tabla con los id que no están:
$tabla_escritos_a_eliminar = 'z_escritos_a_eliminar';
$sql6 = "DROP TABLE IF EXISTS $tabla_escritos_a_eliminar";
$oDbEtherpad->exec($sql6);
$sql7 = "SELECT p.id INTO $tabla_escritos_a_eliminar
    FROM $tabla_id p LEFT JOIN $tabla_escritos_reales e ON (p.id = e.id_escrito) 
    WHERE e.id_escrito IS NULL";

if (($oDblSt = $oDbEtherpad->Query($sql7)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

$sql71 = "ALTER TABLE $tabla_escritos_a_eliminar ADD COLUMN fet bool DEFAULT 'f'";

if (($oDblSt = $oDbEtherpad->Query($sql71)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

$sql8 = "SELECT * FROM $tabla_escritos_a_eliminar WHERE fet = 'f' ";
if (($oDblSt = $oDbEtherpad->Query($sql8)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}
foreach ($oDblSt as $row) {
    //:  dlb*ent277718
    $id_escrito_eliminada = $row['id'];
    $padId2 = $centro .'*ent' . $id_escrito_eliminada;
    //$sql = "DELETE FROM store WHERE key ~ '$padId'";
    // quizá más rápido:
    $sql = "DELETE FROM store WHERE strpos(key,'$padId2') > 0";
    if ($oDbEtherpad->Query($sql) === FALSE) {
        echo "Error al borrar etherpad: $padId2<br>";
    }
    $sql9 = "UPDATE $tabla_escritos_a_eliminar SET fet = 't' WHERE id = $id_escrito_eliminada";
    if (($oDblSt = $oDbEtherpad->Query($sql9)) === FALSE) {
        echo "999Error de algún tipo..." . "<br>";
    }

}


