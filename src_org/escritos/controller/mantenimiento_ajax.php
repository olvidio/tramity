<?php


// INICIO Cabecera global de URL de controlador *********************************
use config\domain\repositories\ConfigSchemaRepository;

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Q_que) {
    case 'update_plataforma':
        $Q_plataforma = (string)filter_input(INPUT_POST, 'plataforma');

        $configSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $configSchemaRepository->findById('plataforma_mantenimiento');
        $oConfigSchema->setValor($Q_plataforma);

        $error_txt = '';
        if ($configSchemaRepository->Guardar($oConfigSchema) === FALSE) {
            $error_txt = $configSchemaRepository->getErrorTxt();
        }

        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
