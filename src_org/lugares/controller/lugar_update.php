<?php

use lugares\domain\entity\Lugar;
use lugares\domain\repositories\LugarRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$error_txt = '';
switch ($Q_que) {
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_lugar = (integer)strtok($a_sel[0], "#");
            $LugarRepository = new LugarRepository();
            $oLugar = $LugarRepository->findById($Q_id_lugar);
            if ($LugarRepository->Eliminar($oLugar) === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $LugarRepository->getErrorTxt();
            }
        }

        break;
    case "guardar":
        $Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
        $Q_sigla = (string)filter_input(INPUT_POST, 'sigla');

        if (empty($Q_sigla)) {
            echo _("debe poner un nombre");
        }

        $Q_dl = (string)filter_input(INPUT_POST, 'dl');
        $Q_region = (string)filter_input(INPUT_POST, 'region');
        $Q_nombre = (string)filter_input(INPUT_POST, 'nombre');
        $Q_tipo_ctr = (string)filter_input(INPUT_POST, 'tipo_ctr');
        $Q_plataforma = (string)filter_input(INPUT_POST, 'plataforma');
        $Q_e_mail = (string)filter_input(INPUT_POST, 'e_mail');
        $Q_modo_envio = (integer)filter_input(INPUT_POST, 'modo_envio');
        $Q_anulado = (bool)filter_input(INPUT_POST, 'anulado');

        $LugarRepository = new LugarRepository();
        $oLugar = $LugarRepository->findById($Q_id_lugar);
        $oLugar->setSigla($Q_sigla);
        $oLugar->setDl($Q_dl);
        $oLugar->setRegion($Q_region);
        $oLugar->setNombre($Q_nombre);
        $oLugar->setTipo_ctr($Q_tipo_ctr);
        $oLugar->setPlataforma($Q_plataforma);
        $oLugar->setE_mail($Q_e_mail);
        $oLugar->setModo_envio($Q_modo_envio);
        $oLugar->setAnulado($Q_anulado);
        if ($LugarRepository->Guardar($oLugar) === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $LugarRepository->getErrorTxt();
        }
        break;
    case "nuevo":
        $Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
        $Q_sigla = (string)filter_input(INPUT_POST, 'sigla');
        if (empty($Q_sigla)) {
            echo _("debe poner un nombre");
        }
        $Q_dl = (string)filter_input(INPUT_POST, 'dl');
        $Q_region = (string)filter_input(INPUT_POST, 'region');
        $Q_nombre = (string)filter_input(INPUT_POST, 'nombre');
        $Q_tipo_ctr = (string)filter_input(INPUT_POST, 'tipo_ctr');
        $Q_plataforma = (string)filter_input(INPUT_POST, 'plataforma');
        $Q_e_mail = (string)filter_input(INPUT_POST, 'e_mail');
        $Q_modo_envio = (integer)filter_input(INPUT_POST, 'modo_envio');

        $LugarRepository = new LugarRepository();
        $id_lugar = $LugarRepository->getNewId_lugar();
        $oLugar = new Lugar();
        $oLugar->setId_lugar($id_lugar);
        $oLugar->setSigla($Q_sigla);
        $oLugar->setDl($Q_dl);
        $oLugar->setRegion($Q_region);
        $oLugar->setNombre($Q_nombre);
        $oLugar->setTipo_ctr($Q_tipo_ctr);
        $oLugar->setPlataforma($Q_plataforma);
        $oLugar->setE_mail($Q_e_mail);
        $oLugar->setModo_envio($Q_modo_envio);
        if ($LugarRepository->Guardar($oLugar) === FALSE) {
            $error_txt .= _("hay un error, no se ha guardado");
            $error_txt .= "\n" . $LugarRepository->getErrorTxt();
        }
        break;
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