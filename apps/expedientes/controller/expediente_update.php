<?php
use core\ConfigGlobal;
use function core\is_true;
use entradas\model\Entrada;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use expedientes\model\GestorExpediente;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorLugar;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;

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

$Qf_reunion = (string) \filter_input(INPUT_POST, 'f_reunion');
$Qf_aprobacion = (string) \filter_input(INPUT_POST, 'f_aprobacion');
$Qf_contestar = (string) \filter_input(INPUT_POST, 'f_contestar');

$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qentradilla = (string) \filter_input(INPUT_POST, 'entradilla');

$Qa_firmas_oficina = (array)  \filter_input(INPUT_POST, 'firmas_oficina', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_firmas = (array)  \filter_input(INPUT_POST, 'firmas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_preparar = (array)  \filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Qvida = (integer) \filter_input(INPUT_POST, 'vida');
$Qvisibilidad = (integer) \filter_input(INPUT_POST, 'visibilidad');

$txt_err = '';
switch($Qque) {
    case 'en_add_expediente':
        // nada
        break;
    case 'en_expediente':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        // Hay que crear un nunevo expediente, con un ajunto (entrada).
        $oEntrada = new Entrada($Qid_entrada);
        $Qasunto = $oEntrada->getAsunto_entrada();
        
        $Qestado = Expediente::ESTADO_BORRADOR;
        $Qponente = ConfigGlobal::role_id_cargo();
        $Qtramite = 1; // Qualquiera, no puede ser null.
        $Qprioridad = 1; // Qualquiera, no puede ser null.

        $oExpediente = new Expediente();
        $oExpediente->setPonente($Qponente);
        $oExpediente->setEstado($Qestado);
        $oExpediente->setId_tramite($Qtramite);
        $oExpediente->setPrioridad($Qprioridad);
        //$oExpediente->setF_reunion($Qf_reunion);
        //$oExpediente->setF_contestar($Qf_contestar);
        $oExpediente->setAsunto($Qasunto);
        //$oExpediente->setEntradilla($Qentradilla);
        //$oExpediente->setVida($Qvida);
        $oExpediente->setVisibilidad($Qvisibilidad);

        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se han podido crear el nuevo expediente");
            $txt_err .= "\n";
            $txt_err .= $oExpediente->getErrorTxt();
        }
        
        $antecedente = [ 'tipo'=> 'entrada', 'id' => $Qid_entrada ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente->addAntecedente($json_antecedente);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se han podido adjuntar la entrada");
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
    case 'encargar_a':
        $Qid_oficial = (integer)  \filter_input(INPUT_POST, 'id_oficial');
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_ENCARGADO);
        $oExpediente->setPonente($Qid_oficial);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se han podido asignar el nuevo encargado");
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
    case 'guardar_etiquetas':
        $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // las etiquetas:
        $oExpediente->setEtiquetas($Qa_etiquetas);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se han podido guardar las etiquetas");
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
    case 'reunion':
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // Si pongo la fecha con datetimepicker, ya esta en ISO (hay que poner FALSE a la conversión).
        $oExpediente->setF_reunion($Qf_reunion);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se ha podido guarda la fecha de reunión");
            $txt_err .= "<br>";
        }
        // firmar el paso de fijar reunion:
        $f_hoy_iso = date(\DateTimeInterface::ISO8601);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente, 'cargo_tipo' => Cargo::CARGO_REUNION]);
        foreach($cFirmas as $oFirma) {
            $oFirma->DBCarregar();
            if (ConfigGlobal::mi_usuario_cargo() === 'vcd') { // No sé si hace falta??
                $oFirma->setValor(Firma::V_D_OK);
            } else {
                $oFirma->setValor(Firma::V_OK);
            }
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso,FALSE);
            if ($oFirma->DBGuardar() === FALSE ) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        break;
    case 'archivar':
        $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // las etiquetas:
        $oExpediente->setEtiquetas($Qa_etiquetas);
        $oExpediente->setEstado(Expediente::ESTADO_ARCHIVADO);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se ha podido cambiar el estado del expediente");
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
    case 'distribuir':
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_SECRETARIA);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se ha podido cambiar el estado del expediente");
            $txt_err .= "<br>";
        }
        // firmar el paso de distribuir:
        $f_hoy_iso = date(\DateTimeInterface::ISO8601);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente, 'cargo_tipo' => Cargo::CARGO_DISTRIBUIR]);
        foreach($cFirmas as $oFirma) {
            $oFirma->DBCarregar();
            if (ConfigGlobal::mi_usuario_cargo() === 'vcd') { // No sé si hace falta??
                $oFirma->setValor(Firma::V_D_OK);
            } else {
                $oFirma->setValor(Firma::V_OK);
            }
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso,FALSE);
            if ($oFirma->DBGuardar() === FALSE ) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        // crear los números de protocolo local de los escritos.
        // busco aquí el id_lugar para no tener que hacerlo dentro del bucle.
        $sigla = $_SESSION['oConfig']->getSigla();
        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        $oLugar = $cLugares[0];
        $id_lugar = $oLugar->getId_lugar();
        $sigla = 'cr';
        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        $oLugar = $cLugares[0];
        $id_lugar_cr = $oLugar->getId_lugar();
        // escritos del expediente: acciones tipo escrito
        $aWhereAccion = ['id_expediente' => $Qid_expediente, 'tipo_accion' => Escrito::ACCION_ESCRITO];
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($aWhereAccion);
        foreach($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $oEscrito->generarProtocolo($id_lugar,$id_lugar_cr);
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
    case 'exp_cp_oficina':
        $Qof_destino = (integer) \filter_input(INPUT_POST, 'of_destino');
    case 'exp_cp_copias':
        $copias = TRUE;
    case 'exp_cp_borrador':
        $txt_err = '';
        if (!empty($copias) && is_true($copias)) {
            $of_destino = 'copias';
        } else {
            $of_destino = empty($Qof_destino)? ConfigGlobal::role_id_cargo() : $Qof_destino;
        }
        // copiar expdiente: poner los escritos como antecedentes.
        $oExpediente = new Expediente($Qid_expediente);
        if ($oExpediente->copiar($of_destino) === FALSE ) {
            $error_txt .= $oExpediente->getErrorTxt();
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
    case 'exp_a_borrador':
        $txt_err = '';
        // Hay que borrar: las firmas.
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente]);
        foreach($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha eliminado la firma");
                $txt_err .= "<br>";
            }
        }
        
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_BORRADOR);
        $asunto = $oExpediente->getAsunto();
        $asunto_retirado = _("RETIRADO")." $asunto";
        $oExpediente->setAsunto($asunto_retirado);
        $oExpediente->setF_contestar('');
        $oExpediente->setF_ini_circulacion('');
        $oExpediente->setF_aprobacion('');
        $oExpediente->setF_reunion('');
        if ($oExpediente->DBGuardar() === FALSE ) {
            $txt_err .= _("No se ha podido cambiar el estado del expediente");
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
        breaK;
    case 'exp_eliminar':
        // Si hay escritos enviados, no se borran.
        $txt_err = '';
        // Hay que borrar: el expediente, las firmas, las acciones, los escritos y los adjuntos de los escritos.
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Qid_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            // Si hay escritos enviados, no se borran.
            $f_salida = $oEscrito->getF_salida();
            if (empty($f_salida)) {
                $rta = $oEscrito->eliminarTodo();
                if (!empty($rta)) { 
                    $txt_err .= $rta;
                }
                if ($oAccion->DBEliminar() === FALSE) {
                    $txt_err .= _("No se ha eliminado la accion");
                    $txt_err .= "<br>";
                }
            }
        }
        // firmas:
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente]);
        foreach($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha eliminado la firma");
                $txt_err .= "<br>";
            }
        }
        $oExpediente = new Expediente($Qid_expediente);
        if ($oExpediente->DBEliminar() === FALSE ) {
            $txt_err .= _("No se ha eliminado el expediente");
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
        // yo soy el que hago el click:
        $mi_id_cargo = ConfigGlobal::role_id_cargo();
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
            // si falla el javascript, puede ser que se hagan varios click a 'Guardar' 
            // y se dupliquen los espedientes. Me aseguro de que no exista uno igual:
            $gesExpedientes = new GestorExpediente();
            $aWhere = [ 'id_tramite' => $Qtramite,
                        'estado' => $Qestado,
                        'prioridad' => $Qprioridad,
                        'asunto' => $Qasunto,
                        'entradilla' => $Qentradilla,
                        ];
            if (!empty($Qf_contestar)) {
                $oConverter = new core\Converter('date', $Qf_contestar);
                $f_contestar_iso = $oConverter->toPg();
                $aWhere['f_contestar'] = $f_contestar_iso;
            }
            $cExpedientes = $gesExpedientes->getExpedientes($aWhere);
            if (count($cExpedientes) > 0) {
                exit (_("Creo que ya se ha creado"));
            }
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
        $oExpediente->setVisibilidad($Qvisibilidad);

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
            //$f_hoy_iso = date('Y-m-d');
            $f_hoy_iso = date(\DateTimeInterface::ISO8601);
            // se pone la fecha del escrito como hoy:
            $oExpediente->setF_escritos($f_hoy_iso,FALSE);
            // Guardar fecha y cambiar estado
            $oExpediente->setF_ini_circulacion($f_hoy_iso,FALSE);
            $oExpediente->setEstado(Expediente::ESTADO_CIRCULANDO);
            $oExpediente->DBGuardar();
            // generar firmas
            $oExpediente->generarFirmas();
            // Si soy el primero, Ya firmo.
            $gesFirmas = new GestorFirma();
            $oFirmaPrimera = $gesFirmas->getPrimeraFirma($id_expediente);
            $id_primer_cargo = $oFirmaPrimera->getId_cargo();
            if ($id_primer_cargo == ConfigGlobal::role_id_cargo()) {
                if (ConfigGlobal::mi_usuario_cargo() === 'vcd') { // No sé si hace falta??
                    $oFirmaPrimera->setValor(Firma::V_D_OK);
                } else {
                    $oFirmaPrimera->setValor(Firma::V_OK);
                }
                $oFirmaPrimera->setId_usuario(ConfigGlobal::mi_id_usuario());
                $oFirmaPrimera->setObserv('');
                $oFirmaPrimera->setF_valor($f_hoy_iso,FALSE);
                if ($oFirmaPrimera->DBGuardar() === FALSE ) {
                    $error_txt .= $oFirmaPrimera->getErrorTxt();
                }
                // comprobar que ya ha firmado todo el mundo, para
                // pasarlo a scdl para distribuir (ok_scdl)
                $bParaDistribuir = $gesFirmas->paraDistribuir($Qid_expediente);
                if ($bParaDistribuir) {
                    $oExpediente->DBCarregar();
                    $oExpediente->setEstado(Expediente::ESTADO_ACABADO);
                    $oExpediente->setF_aprobacion($f_hoy_iso,FALSE);
                    $oExpediente->setF_aprobacion_escritos($f_hoy_iso,FALSE);
                    if ($oExpediente->DBGuardar() === FALSE ) {
                        $error_txt .= $oExpediente->getErrorTxt();
                    }
                }
            }
            
        }
        // FIN CIRCULAR
        
        if (!empty($error_txt)) {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = true;
            $jsondata['id_expediente'] = $id_expediente;
            $a_cosas = [ 'id_expediente' => $id_expediente];
            $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
}