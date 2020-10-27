<?php
use etherpad\model\Etherpad;
use core\ConfigGlobal;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$id_entrada = 'hola3';

$oEtherpad = new Etherpad();
$padID = $oEtherpad->getPadId($id_entrada);
// add user access to pad (Session)
//$oEtherpad->addUserPerm($id_entrada);
$url = $oEtherpad->getUrl();

echo "$url/p/$padID?showChat=false&showLineNumbers=false";

echo "<iframe src='$url/p/$padID?showChat=false&showLineNumbers=false' width=920 height=500></iframe>";

/*
echo "pad para 1: $padID<br>";

$sessionID = "s.85dddce166d40485bc0f5b5ce7800393";

echo "info session: $sessionID<br>";
$info = $oEtherpad->getSessionInfo($sessionID);
echo "<pre>";
echo print_r($info->getData());
echo "</pre>";

$rta = $oEtherpad->listAllPads();
echo "<br>";
echo "lista de all pads";
echo "<pre>";
echo print_r($rta->getData());
echo "a</pre>";
echo "aa<br>";

$rta = $oEtherpad->listAuthorsOfPad($padID);
echo "Autores de pad: $padID";
echo "<pre>";
echo print_r($rta->getData());
echo "b</pre>";
echo "bb<br>";
*/