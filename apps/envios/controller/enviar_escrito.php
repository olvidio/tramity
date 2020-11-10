<?php
use envios\model\Enviar;


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');

echo "<h1>KKKKKKKKKKK $Qid_entrada KKKKKKKKK</h1>";


$oEnviar = new Enviar($Qid_entrada,'entrada');
$oEnviar->enviar();
