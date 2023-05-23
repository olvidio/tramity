<?php

use lugares\model\entity\Lugar;
use migration_ctr\model\MigrationCtr;
use web\StringLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_lista_id_ctr = (string)filter_input(INPUT_POST, 'lista_id_ctr');

// pasar id ctr a nombre schemas.
$a_schemas = [];
$a_id_ctr = explode(",", $Q_lista_id_ctr);
foreach ($a_id_ctr as $id_ctr) {
    $oLugar = new Lugar($id_ctr);
    if (!empty($oLugar)) {
        $nombre_ctr = $oLugar->getNombre();
        $a_schema[$id_ctr] = StringLocal::toRFC952($nombre_ctr);
    }
}

$mensaje = '';
switch ($Q_que) {
    case 'entradas_compartidas':
        // por lo mennos un schema para pillar la configuración de conexión a la DB
        $schema = current($a_schema);
        $oMigration = new MigrationCtr($schema);
        $oMigration->entradas_compartidas();
        $mensaje .= "OK entradas compartidas \n";
        break;
    case 'entradas':
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->entradas($id_ctr);
            $mensaje .= "OK entradas para $schema \n";
        }
        break;
    case 'salidas': //salidas individuales de la dl al ctr,
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->salidas($id_ctr);
            $mensaje .= "OK aprobaciones individuales para $schema  \n";
        }
        break;
    case 'leer_escritos':
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->crear_etherpad_como_escrito();
            $mensaje .= "OK entradas de la dl pasadas a escritos etherpad para $schema  \n";
        }
        break;
    case 'leer_entradas':
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->crear_etherpad_como_entrada();
            $mensaje .= "OK escritos de la dl pasados a entradas etherpad para $schema  \n";
        }
        break;
    case 'leer_entradas_compartidas':
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->crear_etherpad_como_entrada_compartida();
            $mensaje .= "OK escritos de la dl pasados a entradas etherpad para $schema  \n";
        }
        break;
    case 'crear_entradas_individuales':
        foreach ($a_schema as $id_ctr => $schema) {
            $oMigration = new MigrationCtr($schema);
            $oMigration->crear_entradas_individuales($id_ctr);
            $mensaje .= "OK entradas individuales de compartidas para $schema  \n";
        }
        break;
}

if (!empty($mensaje)) {
    $jsondata['success'] = FALSE;
    $jsondata['mensaje'] = $mensaje;
} else {
    $jsondata['success'] = TRUE;
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();