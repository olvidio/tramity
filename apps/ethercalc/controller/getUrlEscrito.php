<?php
use ethercalc\model\Ethercalc;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid = (string) \filter_input(INPUT_POST, 'id');
$Qtipo_id = (string) \filter_input(INPUT_POST, 'tipo_id');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

if (empty($Qtipo_id)) {
    exit (_("Falta definir el tipo de documento etherpad"));
}

$oEthercalc = new Ethercalc();
$oEthercalc->setId ($Qtipo_id,$Qid); 
$padID = $oEthercalc->getPadId();
$url = $oEthercalc->getUrl();

switch ($Qmodo) {
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