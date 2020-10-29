<?php
use entradas\model\entity\EntradaDocDB;
use etherpad\model\Etherpad;
use ethercalc\model\Ethercalc;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El delete es via POST!!!";

$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
$Qf_escrito = (string) \filter_input(INPUT_POST, 'f_escrito');
$Qtipo_doc = (integer) \filter_input(INPUT_POST, 'tipo_doc');

//$Qtipo = EntradaDocDB::TIPO_ETHERPAD;

if (!empty($Qid_entrada)) {
    $oEntradaDocBD = new EntradaDocDB($Qid_entrada);
    $oEntradaDocBD->setF_doc($Qf_escrito);
    $oEntradaDocBD->setTipo_doc($Qtipo_doc);

    $error = FALSE;
    if ($oEntradaDocBD->DBGuardar() === FALSE) {
        $error = TRUE;
    }
} else {
    $error = TRUE;
}

$jsondata = [];
if ($error === TRUE) {   
    $jsondata['error'] = true;
} else {
    switch($Qtipo_doc) {
        case EntradaDocDB::TIPO_ETHERCALC : 
            $oEthercalc = new Ethercalc();
            $oEthercalc->setId(Ethercalc::ID_ENTRADA, $Qid_entrada);
            $padID = $oEthercalc->getPadId();
            $url = $oEthercalc->getUrl();

            $fullUrl = "$url/$padID";

            $jsondata['error'] = false;
            $jsondata['url'] = $fullUrl;
            break;
        case EntradaDocDB::TIPO_ETHERPAD : 
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_ENTRADA, $Qid_entrada);
            $padID = $oEtherpad->getPadId();
            // add user access to pad (Session)
            //$oEtherpad->addUserPerm($id_entrada);
            $url = $oEtherpad->getUrl();

            $fullUrl = "$url/p/$padID?showChat=false&showLineNumbers=false";
            
            $jsondata['error'] = false;
            $jsondata['url'] = $fullUrl;
            break;
    }
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);