<?php

use core\ConfigGlobal;
use documentos\model\Documento;
use documentos\model\entity\DocumentoDB;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$Q_nom = (string)filter_input(INPUT_POST, 'nom');
$Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
$Q_tipo_doc = (integer)filter_input(INPUT_POST, 'tipo_doc');
$Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$error_txt = '';
$jsondata = [];
switch ($Q_que) {
    case 'tipo_doc':
        if (!empty($Q_id_doc)) {
            $oHoy = new DateTimeLocal();
            $hoy_iso = $oHoy->getIso();

            $oDocumento = new Documento($Q_id_doc);
            $oDocumento->DBCarregar();
            $oDocumento->setTipo_doc($Q_tipo_doc);
            $oDocumento->setF_upload($hoy_iso, FALSE);

            if ($oDocumento->DBGuardar() === FALSE) {
                $error_txt .= $oDocumento->getErrorTxt();
            }
        }
        break;
    case 'eliminar':
        $oDocumento = new DocumentoDB($Q_id_doc);
        if ($oDocumento->DBEliminar() === FALSE) {
            $error_txt .= $oDocumento->getErrorTxt();
        }
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'guardar':
        if (!empty($Q_id_doc)) {
            $oDocumento = new Documento($Q_id_doc);
            $oDocumento->DBCarregar();
        } else {
            $oDocumento = new Documento();
            $id_creador = ConfigGlobal::role_id_cargo();
            $oDocumento->setCreador($id_creador);
        }

        $oDocumento->setNom($Q_nom);
        $oDocumento->setVisibilidad($Q_visibilidad);
        $oDocumento->setTipo_doc($Q_tipo_doc);


        if ($oDocumento->DBGuardar() === FALSE) {
            $error_txt .= $oDocumento->getErrorTxt();
        }
        $id_doc = $oDocumento->getId_doc();
        $tipo_doc = $oDocumento->getTipo_doc();

        // las etiquetas despues de guardar el documento:
        if (!empty($Q_a_etiquetas)) { // No puede haber un docuemento sin etiquetas
            $oDocumento->setEtiquetas($Q_a_etiquetas);
        }

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_doc'] = $id_doc;
            $jsondata['tipo_doc'] = $tipo_doc;
            $a_cosas = ['id_doc' => $id_doc, 'filtro' => $Q_filtro];
            $pagina_mod = web\Hash::link('apps/documentos/controller/documento_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}