<?php

use escritos\application\DestinosTxt;
use escritos\domain\repositories\EscritoRepository;
use lugares\domain\repositories\LugarRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');
switch ($Q_que) {
    case 'get_destinos':
        $Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
        $Destinos = new DestinosTxt($Q_id_escrito);
        $jsondata = $Destinos->destinosTxt($Q_id_escrito);


        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata, JSON_THROW_ON_ERROR);
        exit();
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}