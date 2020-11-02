<?php
use core\ConfigGlobal;
use expedientes\model\Expediente;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use expedientes\model\entity\Accion;
use expedientes\model\entity\GestorAccion;
use expedientes\model\Escrito;
use tramites\model\entity\GestorFirma;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qponente = (string) \filter_input(INPUT_POST, 'ponente');

$Qtramite = (integer) \filter_input(INPUT_POST, 'tramite');
$Qestado = (integer) \filter_input(INPUT_POST, 'estado');
$Qprioridad = (integer) \filter_input(INPUT_POST, 'prioridad');

$Qf_ini_circulacion = (string) \filter_input(INPUT_POST, 'f_ini_circulacion');
$Qf_reunion = (string) \filter_input(INPUT_POST, 'f_reunion');
$Qf_aprobacion = (string) \filter_input(INPUT_POST, 'f_aprobacion');
$Qf_contestar = (string) \filter_input(INPUT_POST, 'f_contestar');

$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qentradilla = (string) \filter_input(INPUT_POST, 'entradilla');

$Qa_firmas_oficina = (array)  \filter_input(INPUT_POST, 'firmas_oficina', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_firmas = (array)  \filter_input(INPUT_POST, 'firmas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_preparar = (array)  \filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Qvida = (integer) \filter_input(INPUT_POST, 'vida');

switch($Qque) {
    case 'exp_eliminar':
        $txt_err = '';
        // Hay que borrar: el expediente, las firmas, las acciones, los escritos y los adjuntos de los escritos.
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Qid_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $rta = $oEscrito->eliminarTodo();
            if (!empty($rta)) { 
                $txt_err .= $rta;
            }
            if ($oAccion->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha elimnado la accion");
                $txt_err .= "<br>";
            }
        }
        // firmas:
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente]);
        foreach($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha elimnado la firma");
                $txt_err .= "<br>";
            }
        }
        $oExpediente = new Expediente($Qid_expediente);
        if ($oExpediente->DBEliminar() === FALSE ) {
            $txt_err .= _("No se ha elimnado el expediente");
            $txt_err .= "<br>";
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
        break;
    case 'visto':
        // yo soy el qur hago el click:
        $mi_id_cargo = ConfigGlobal::mi_id_cargo();
        $oCargo = new Cargo($mi_id_cargo);
        $mi_id_oficina = $oCargo->getId_oficina();
        
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // oficiales
        $new_preparar = [];
        foreach ($Qa_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = $id;
            if ($mi_id_cargo == $id){
                $oJSON->visto = empty($visto)? 1 : 0;
            } else {
                $oJSON->visto = $visto;
            }
            
            $new_preparar[] = $oJSON;
        }
        $oExpediente->setJson_preparar($new_preparar);
        $oExpediente->DBGuardar();
        
        //para regenerar la linea de oficiales
        $gesCargos = new GestorCargo();
        $a_cargos_oficina = $gesCargos->getArrayCargosOficina($mi_id_oficina);
        $a_preparar = [];
        foreach ($a_cargos_oficina as $id_cargo => $cargo) {
            $a_preparar[] = ['id' => $id_cargo, 'text' => $cargo, 'chk' => '', 'visto' => 0];
        }
        $json_preparar = $oExpediente->getJson_preparar();
        $html = '';
        foreach ($a_preparar as $key => $oficial2) {
            $id2 = $oficial2['id'];
            $text = $oficial2['text'];
            foreach ($json_preparar as $oficial) {
                $id = $oficial->id;
                //$chk = $oficial->chk; 
                $visto_db = empty($oficial->visto)? 0 : $oficial->visto;
                // marcar las que estan.
                if ($id == $id2) {
                    $chk = 'checked';
                    $visto = $visto_db;
                    // rompo el bucle
                    break;
                } else {
                    $chk = '';
                    $visto = '';
                }
            }
            $html .= "<div class=\"custom-control custom-checkbox custom-control-inline\">";
            $html .= "<input type=\"checkbox\" class=\"custom-control-input\" name=\"a_preparar[]\" id=\"$id2\" value=\"$id2#$visto\" $chk>";
            if ($visto) {
                $html .= "<label class=\"custom-control-label text-success\" for=\"$id2\">$text ("._("visto").")</label>";
            } else {
                $html .= "<label class=\"custom-control-label\" for=\"$id2\">$text</label>";
            }
            $html .= "</div>";
        }
        
        $jsondata['success'] = true;
        $jsondata['id_expediente'] = $Qid_expediente;
        $jsondata['html'] = $html;
        //$a_cosas = [ 'id_expediente' => $Qid_expediente];
        //$pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
        //$jsondata['pagina_mod'] = $pagina_mod;
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        //exit();

        break;
    case 'upload_antecedente':
        
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
    case 'circular':
        // primero se guarda, y al final se guarda la fecha de hoy y se crean las firmas para el trámite
    case 'guardar':
        if (!empty($Qid_expediente)) {
            $oExpediente = new Expediente($Qid_expediente);
            $oExpediente->DBCarregar();
            // Mantego al ponente como creador...
        } else {
            // nuevo.
            $oExpediente = new Expediente();
            $Qestado = Expediente::ESTADO_BORRADOR;
            $oExpediente->setPonente($Qponente);
        }
        

        $oExpediente->setId_tramite($Qtramite);
        $oExpediente->setEstado($Qestado);
        $oExpediente->setPrioridad($Qprioridad);
        
        $oExpediente->setF_reunion($Qf_reunion);
        $oExpediente->setF_aprobacion($Qf_aprobacion);
        $oExpediente->setF_contestar($Qf_contestar);
        
        $oExpediente->setAsunto($Qasunto);

        $oExpediente->setEntradilla($Qentradilla);
        
        // según el trámite mirar si hay que grabar oficiales y/o varios cargos.
        $oficiales = FALSE;
        $aWhere = ['id_tramite' => $Qtramite, 'id_cargo' => Cargo::CARGO_OFICIALES];
        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
        if (count($cTramiteCargos) > 0) {
            $oficiales = TRUE;
        }
        $varias = FALSE;
        $aWhere = ['id_tramite' => $Qtramite, 'id_cargo' => Cargo::CARGO_VARIAS];
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
        if (count($cTramiteCargos) > 0) {
            $varias = TRUE;
        }
        // pasar a array para postgresql
        if ($oficiales) {
            $a_filter_firmas_oficina = array_filter($Qa_firmas_oficina); // Quita los elementos vacíos y nulos.
            $oExpediente->setFirmas_oficina($a_filter_firmas_oficina);
        } else {
            $oExpediente->setFirmas_oficina('');
        }

        // pasar a array para postgresql
        if ($varias) {
            $a_filter_firmas = array_filter($Qa_firmas); // Quita los elementos vacíos y nulos.
            $oExpediente->setResto_oficinas($a_filter_firmas);
        } else {
            $oExpediente->setResto_oficinas('');
        }

        $oExpediente->setVida($Qvida);

        // oficiales
        $new_preparar = [];
        foreach ($Qa_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = $id;
            $oJSON->visto = $visto;
            
            $new_preparar[] = $oJSON;
        }
        $oExpediente->setJson_preparar($new_preparar);
        
        $oExpediente->DBGuardar();
        
        $id_expediente = $oExpediente->getId_expediente();
            
        // CIRCULAR
        if ($Qque == 'circular') {
            $f_hoy_iso = date('Y-m-d');
            // se pone la fecha del escrito como hoy:
            $oExpediente->setF_escritos($f_hoy_iso,FALSE);
            // Guardar fecha y cambiar estado
            $oExpediente->setF_ini_circulacion($f_hoy_iso,FALSE);
            $oExpediente->setEstado(Expediente::ESTADO_CIRCULANDO);
            $oExpediente->DBGuardar();
            // generar firmas
            $oExpediente->generarFirmas();
        }
        // FIN CIRCULAR
        
        $jsondata['success'] = true;
        $jsondata['id_expediente'] = $id_expediente;
        $a_cosas = [ 'id_expediente' => $id_expediente];
        $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
        $jsondata['pagina_mod'] = $pagina_mod;
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
}