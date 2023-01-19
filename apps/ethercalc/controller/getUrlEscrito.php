<?php

use ethercalc\model\Ethercalc;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id = (string)filter_input(INPUT_POST, 'id');
$Q_tipo_id = (string)filter_input(INPUT_POST, 'tipo_id');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

if (empty($Q_tipo_id)) {
    exit (_("Falta definir el tipo de documento etherpad"));
}

$oEthercalc = new Ethercalc();
$oEthercalc->setId($Q_tipo_id, $Q_id);
$padID = $oEthercalc->getPadId();
$url = $oEthercalc->getUrl();

switch ($Q_modo) {
    case 'html':
        // Hay que evitar el CORS ( no puedo acceder al tramity.local:9001) 
        //$url = 'http://tramity.local:8080';
        //echo "$url/$padID/export/html";
        break;
    case 'iframe':
        //echo "$url/p/$padID?showChat=false&showLineNumbers=false";
        // echo "<iframe src='$url/p/$padID?showChat=false&showLineNumbers=false' width=1020 height=500></iframe>";
        break;
    default:
        $rta = "$url/$padID";
        echo $rta;
}