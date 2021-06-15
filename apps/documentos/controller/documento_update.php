<?php
use core\ConfigGlobal;
use documentos\model\Documento;
use documentos\model\entity\DocumentoDB;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qid_doc = (integer) \filter_input(INPUT_POST, 'id_doc');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$Qnom = (string) \filter_input(INPUT_POST, 'nom');
$Qvisibiliad = (integer) \filter_input(INPUT_POST, 'visibilidad');
$Qtipo_doc = (integer) \filter_input(INPUT_POST, 'tipo_doc');
$Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$error_txt = '';
$jsondata = [];
switch($Qque) {
    case 'eliminar':
        $oDocumento = new DocumentoDB($Qid_doc);
        if ($oDocumento->DBEliminar() === FALSE) {
            $error_txt .= $oDocumento->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'upload_adjunto':
        
        if (empty($_FILES['adjuntos'])) {
            // Devolvemos un array asociativo con la clave error en formato JSON como respuesta
            echo json_encode(['error'=>'No hay ficheros para realizar upload.']);
            // Cancelamos el resto del script
            return;
        }
        $respuestas = [];
        $ficheros = $_FILES['adjuntos'];
        
        $a_error = $ficheros['error'];
        $a_names = $ficheros['name'];
        $a_tmp = $ficheros['tmp_name'];
        foreach ($a_names as $key => $name) {
            if ($a_error[$key] > 0) {
                $respuestas = [ "error" => $a_error[$key] ];
            } else {
                $path_parts = pathinfo($name);
                
                $nombre_fichero=$path_parts['filename'];
                // puede no existir la extension
                $extension=empty($path_parts['extension'])? '' : $path_parts['extension'];

                $userfile= $a_tmp[$key];
                
                $fichero=file_get_contents($userfile);
                
            }
            $respuestas = ["ok" => "Ja está"];
            
            // Devolvemos el array asociativo en formato JSON como respuesta
        }
        echo json_encode($respuestas);
        
        break;
    case 'guardar':
        if (!empty($Qid_doc)) {
            $oDocumento = new Documento($Qid_doc);
            $oDocumento->DBCarregar();
        } else {
            $oDocumento = new Documento();
            $id_creador = ConfigGlobal::role_id_cargo();
            $oDocumento->setCreador($id_creador);
        }
        
        $oDocumento->setNom($Qnom);
        $oDocumento->setVisibilidad($Qvisibiliad);
        $oDocumento->setTipo_doc($Qtipo_doc);

        
        if ($oDocumento->DBGuardar() === FALSE ) {
            $error_txt .= $oDocumento->getErrorTxt();
        }
        $id_doc = $oDocumento->getId_doc();
        
        // las etiquetas despues de guardar el documento:
        $oDocumento->setEtiquetas($Qa_etiquetas);
        
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_doc'] = $id_doc;
            $a_cosas = [ 'id_doc' => $id_doc, 'filtro' => $Qfiltro];
            $pagina_mod = web\Hash::link('apps/documentos/controller/documento_form.php?'.http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
    break;
}