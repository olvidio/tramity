<?php
//namespace usuarios\controller;
use usuarios\domain\repositories\PreferenciaRepository;
use usuarios\domain\repositories\UsuarioRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$Q_que = (string)filter_input(INPUT_POST, 'que');

$PreferenciaRepository = new PreferenciaRepository();
switch ($Q_que) {
    case "slickGrid":
        $Q_tabla = (string)filter_input(INPUT_POST, 'tabla');
        $Q_sPrefs = (string)filter_input(INPUT_POST, 'sPrefs');
        $idioma = core\ConfigGlobal::mi_Idioma();
        $tipo = 'slickGrid_' . $Q_tabla . '_' . $idioma;
        $oPref = $PreferenciaRepository->getMiPreferencia($tipo);
        // si no se han cambiado las columnas visibles, pongo las actuales (sino las borra).
        $aPrefs = json_decode($Q_sPrefs, true);
        if ($aPrefs['colVisible'] === 'noCambia') {
            $sPrefs_old = $oPref->getMiPreferencia();
            $aPrefs_old = json_decode($sPrefs_old, true);
            $aPrefs['colVisible'] = empty($aPrefs_old['colVisible']) ? '' : $aPrefs_old['colVisible'];
            $Q_sPrefs = json_encode($aPrefs, true);
        }

        $oPref->setPreferencia($Q_sPrefs);
        if ($PreferenciaRepository->Guardar($oPref) === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n" . $PreferenciaRepository->getErrorTxt();
        }
        break;
    default:
        // Guardar idioma:
        $Q_idioma_nou = (string)filter_input(INPUT_POST, 'idioma_nou');
        $oPref = $PreferenciaRepository->getMiPreferencia('idioma');
        $oPref->setPreferencia($Q_idioma_nou);
        if ($PreferenciaRepository->Guardar($oPref) === FALSE) {
            echo _("hay un error, no se ha guardado idioma");
            echo "\n" . $PreferenciaRepository->getErrorTxt();
        }

        // Guardar Nombre a Mostrar, mail, cargo preferido
        $id_usuario = core\ConfigGlobal::mi_id_usuario();
        $Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
        $Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $Q_id_cargo_preferido = (integer)filter_input(INPUT_POST, 'id_cargo_preferido');

        $UsuarioRepository = new UsuarioRepository();
        $oUsuario = $UsuarioRepository->findById($id_usuario);
        $oUsuario->setId_cargo_preferido($Q_id_cargo_preferido);
        $oUsuario->setEmail($Q_email);
        $oUsuario->setNom_usuario($Q_nom_usuario);
        if ($UsuarioRepository->Guardar($oUsuario) === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n" . $UsuarioRepository->getErrorTxt();
        }
}
