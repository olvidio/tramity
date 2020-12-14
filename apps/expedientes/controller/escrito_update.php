<?php
use function core\is_true;
use expedientes\model\Escrito;
use expedientes\model\entity\Accion;
use lugares\model\entity\GestorGrupo;
use web\DateTimeLocal;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');

$Qentradilla = (string) \filter_input(INPUT_POST, 'entradilla');
$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qf_escrito = (string) \filter_input(INPUT_POST, 'f_escrito');

$Qdetalle = (string) \filter_input(INPUT_POST, 'detalle');
$Qid_ponente = (integer) \filter_input(INPUT_POST, 'id_ponente');
$Qa_firmas = (array)  \filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Qcategoria = (integer) \filter_input(INPUT_POST, 'categoria');
$Qvisibiliad = (integer) \filter_input(INPUT_POST, 'visibilidad');

$Qgrupo_dst = (string) \filter_input(INPUT_POST, 'grupo_dst');
// genero un vector con todos los grupos.
$Qa_grupos = (array)  \filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Qa_destinos = (array)  \filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_num_destinos = (array)  \filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_any_destinos = (array)  \filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_mas_destinos = (array)  \filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Qa_referencias = (array)  \filter_input(INPUT_POST, 'referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_num_referencias = (array)  \filter_input(INPUT_POST, 'prot_num_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_any_referencias = (array)  \filter_input(INPUT_POST, 'prot_any_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_mas_referencias = (array)  \filter_input(INPUT_POST, 'prot_mas_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

switch($Qque) {
    case 'escrito_a_secretaria':
        $oEscrito = new Escrito($Qid_escrito);
        $oEscrito->DBCarregar();
        $oEscrito->setOK(Escrito::OK_OFICINA);
        $oEscrito->DBGuardar();
        break;
    case 'tipo_doc':
        $Qtipo_doc = (integer) \filter_input(INPUT_POST, 'tipo_doc');
        $oEscrito = new Escrito($Qid_escrito);
        $oEscrito->DBCarregar();
        $oEscrito->setTipo_doc($Qtipo_doc);
        $oEscrito->DBGuardar();
        
        break;
    case 'f_escrito':
        if ($Qf_escrito == 'hoy') {
            $oHoy = new DateTimeLocal();
            $Qf_escrito = $oHoy->getFromLocal();
        }
        $oEscrito = new Escrito($Qid_escrito);
        $oEscrito->DBCarregar();
        $oEscrito->setF_escrito($Qf_escrito);
        $oEscrito->DBGuardar();
        
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
                
                $nom=$path_parts['filename'];
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
    case 'guardar_asunto':
        $txt_err = '';
        if (!empty($Qid_escrito)) {
            $oEscrito = new Escrito($Qid_escrito);
            $oEscrito->DBCarregar();
            $Qanular = (string) \filter_input(INPUT_POST, 'anular');
        
            if (is_true($Qanular)) {
                if (strpos($Qasunto,_("ANULADO")) === FALSE) {
                    $asunto = _("ANULADO")." $Qasunto";
                } else {
                    $asunto = $Qasunto;
                }
                $oEscrito->setAnulado('t');
            } else {
                $asunto = str_replace(_("ANULADO").' ', '', $Qasunto);
                $oEscrito->setAnulado('f');
            }
            $oEscrito->setAsunto($asunto);
            $oEscrito->setDetalle($Qdetalle);
            if ($oEscrito->DBGuardar() === FALSE ) {
                $txt_err .= _("Hay un error al guardar el escrito");
                $txt_err .= "<br>";
            }
        } else {
            $txt_err = _("No existe el escrito");
        }
        
        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'guardar':
        $nuevo = FALSE;
        if (!empty($Qid_escrito)) {
            $oEscrito = new Escrito($Qid_escrito);
            $oEscrito->DBCarregar();
        } else {
            $oEscrito = new Escrito();
            $oEscrito->setAccion($Qaccion);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $nuevo = TRUE;
        }
        
        if ($Qaccion == Escrito::ACCION_ESCRITO) {
            // Si esta marcado como grupo de destinos, o destinos individuales. 
            if (core\is_true($Qgrupo_dst)) {
                $descripcion = '';
                $gesGrupo = new GestorGrupo();
                $a_grupos = $gesGrupo->getArrayGrupos();
                foreach ($Qa_grupos as $id_grupo) {
                    $descripcion .= empty($descripcion)? '' : ' + ';
                    $descripcion .= $a_grupos[$id_grupo];
                }
                $oEscrito->setId_grupos($Qa_grupos);
            } else {
                $aProtDst = [];
                foreach ($Qa_destinos as $key => $id_lugar) {
                    $prot_num = $Qa_prot_num_destinos[$key];
                    $prot_any = $Qa_prot_any_destinos[$key];
                    $prot_mas = $Qa_prot_mas_destinos[$key];
                    
                    if (!empty($id_lugar)) {
                        $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                        $aProtDst[] = $oProtDst->getProt();
                    }
                }
                $oEscrito->setJson_prot_destino($aProtDst);
                $oEscrito->setId_grupos();
            }
     
            $aProtRef = [];
            foreach ($Qa_referencias as $key => $id_lugar) {
                $prot_num = $Qa_prot_num_referencias[$key];
                $prot_any = $Qa_prot_any_referencias[$key];
                $prot_mas = $Qa_prot_mas_referencias[$key];
                
                if (!empty($id_lugar)) {
                    $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $aProtRef[] = $oProtRef->getProt();
                }
            }
            $oEscrito->setJson_prot_ref($aProtRef);
        }
        
        $oEscrito->setEntradilla($Qentradilla);
        $oEscrito->setAsunto($Qasunto);
        $oEscrito->setF_escrito($Qf_escrito);

        $oEscrito->setDetalle($Qdetalle);
        $oEscrito->setCreador($Qid_ponente);
        $oEscrito->setResto_oficinas($Qa_firmas);

        $oEscrito->setCategoria($Qcategoria);
        $oEscrito->setVisibilidad($Qvisibiliad);

        $oEscrito->DBGuardar();
        
        $id_escrito = $oEscrito->getId_escrito();
            
        if ($nuevo === TRUE) {
            $oAccion = new Accion();
            $oAccion->setId_expediente($Qid_expediente);
            $oAccion->setId_escrito($id_escrito);
            $oAccion->setTipo_accion($Qaccion);
            $oAccion->DBGuardar();
        }
        
        $jsondata['success'] = true;
        $jsondata['id_escrito'] = $id_escrito;
        $a_cosas = [ 'id_escrito' => $id_escrito, 'filtro' => $Qfiltro, 'id_expediente' => $Qid_expediente];
        $pagina_mod = web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
        $jsondata['pagina_mod'] = $pagina_mod;
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
}