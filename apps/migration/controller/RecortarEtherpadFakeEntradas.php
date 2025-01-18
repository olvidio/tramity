<?php

// INICIO Cabecera global de URL de controlador *********************************

use migration\model\Connect2Etherpad;
use migration\model\FakeDocs;

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

// documentos fake:
$docs = FakeDocs::docs;

// revision en entradas
// los acabados en 1 pongo $doc[1].... en 8->$doc[8].
foreach ($docs as $key => $doc_id) {
    $padIdDoc = 'pad:.*\$' . $centro . '\*doc' . $doc_id . '$';
    $padId = 'pad:.*\$' . $centro . '\*ent\d+' . $key . '$';

    $sql = "UPDATE store SET value = (SELECT value FROM store WHERE key ~ '$padIdDoc')  WHERE key ~ '$padId'";

    if ($oDbEtherpad->Query($sql) === FALSE) {
        echo "Error al fake entradas etherpad: $padId<br>";
    }
}
