<?php

use core\ConfigGlobal;
use Symfony\Component\HttpFoundation\JsonResponse;
use usuarios\model\entity\GestorPreferencia;
use usuarios\model\entity\Usuario;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$error_txt = '';
$gesPreferencias = new GestorPreferencia();
// Guardar idioma:
$Q_idioma_nou = (string)filter_input(INPUT_POST, 'idioma_nou');
$oPref = $gesPreferencias->getMiPreferencia('idioma');
$oPref->setPreferencia($Q_idioma_nou);
if ($oPref->DBGuardar() === FALSE) {
    $error_txt .= _("hay un error, no se ha guardado idioma");
    $error_txt .= "\n" . $oPref->getErrorTxt();
}

// Guardar Nombre a Mostrar, mail, cargo preferido
$id_usuario = ConfigGlobal::mi_id_usuario();
$Q_nom_usuario = (string)filter_input(INPUT_POST, 'nom_usuario');
$Q_email = (string)filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$Q_id_cargo_preferido = (integer)filter_input(INPUT_POST, 'id_cargo_preferido');

$oUsuario = new Usuario($id_usuario);
if ($oUsuario->DBCargar() === FALSE) {
    $error_txt .= sprintf(_("OJO! no existe el usuario en %s, linea %s"), __FILE__, __LINE__);
}

$oUsuario->setId_cargo_preferido($Q_id_cargo_preferido);
$oUsuario->setEmail($Q_email);
$oUsuario->setNom_usuario($Q_nom_usuario);
if ($oUsuario->DBGuardar() === FALSE) {
    $error_txt .= _("hay un error, no se ha guardado");
    $error_txt .= "\n" . $oUsuario->getErrorTxt();
}

if (empty($error_txt)) {
    $jsondata['success'] = TRUE;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = FALSE;
    $jsondata['mensaje'] = $error_txt;
}
$response = new JsonResponse($jsondata);
$response->send();