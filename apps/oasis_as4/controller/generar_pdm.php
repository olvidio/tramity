<?php

use oasis_as4\model\Pmode;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$destino = 'dlb';
$accion = 'nuevo';
$server = 'http://localhost:9090/holodeckb2b/as4';


$oPdm = new Pmode();

$oPdm->setDestino($destino);
$oPdm->setAccion($accion);
$oPdm->setHolo_server_dst($server);

/* por defecto el nombre es:
$filename = 'pm-' . $this->getDestino() . '-' . $this->getAccion() . '-init';
*/

$oPdm->saveInit();
$oPdm->saveResp();