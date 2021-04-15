<?php
use core\ConfigGlobal;
use function core\is_true;
use entradas\model\Entrada;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use expedientes\model\GestorExpediente;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorLugar;
use pendientes\model\Pendiente;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\DateTimeLocal;
use web\Protocolo;

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

$error_txt = '';
switch($Qque) {
    case 'en_pendiente':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $oCargo = new Cargo($Qid_cargo);
        $cargo = $oCargo->getCargo();
        $id_oficina = ConfigGlobal::role_id_oficina();
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
        $sigla = $a_posibles_oficinas[$id_oficina];
        $parent_container = "oficina_$sigla";
        $resource = 'oficina';
        $oHoy = new DateTimeLocal();
        // datos de la entrada 
        $id_reg = 'EN'.$Qid_entrada; // (para resource='registro': REN = Regitro Entrada, para 'oficina': OFEN)
        $oEntrada = new Entrada($Qid_entrada);
        
        $oPendiente = new Pendiente($parent_container, $resource, $cargo);
        $oPendiente->setId_reg($id_reg);
        $oPendiente->setAsunto($oEntrada->getAsunto());
        $oPendiente->setStatus("NEEDS-ACTION");
        $oPendiente->setF_inicio($oHoy->getFromLocal());
        $oPendiente->setF_plazo($oHoy->getFromLocal());
        $oPendiente->setvisibilidad($Qvisibilidad);
        $oPendiente->setDetalle($oEntrada->getDetalle());
        $oPendiente->setEncargado($Qid_cargo);
        $oPendiente->setId_oficina($id_oficina);

        $oProtOrigen = new Protocolo();
        $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
        $location = $oProtOrigen->ver_txt_num();
        
        $oPendiente->setLocation($location);
        $oPendiente->setRef_prot_mas($oProtOrigen->ver_txt_mas());
        // las oficinas implicadas:
        $oPendiente->setOficinasArray($oEntrada->getResto_oficinas());
        if ($oPendiente->Guardar() === FALSE ) {
            $error_txt .= _("No se han podido guardar el nuevo pendiente");
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
        break;
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
            $error_txt .= _("No se han podido crear el nuevo expediente");
            $error_txt .= "\n";
            $error_txt .= $oExpediente->getErrorTxt();
        }
        
        $antecedente = [ 'tipo'=> 'entrada', 'id' => $Qid_entrada ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente->addAntecedente($json_antecedente);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= _("No se han podido adjuntar la entrada");
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
        break;
    case 'encargar_a':
        $Qid_oficial = (integer)  \filter_input(INPUT_POST, 'id_oficial');
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_ENCARGADO);
        $oExpediente->setPonente($Qid_oficial);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= _("No se han podido asignar el nuevo encargado");
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
        break;
    case 'guardar_etiquetas':
        $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // las etiquetas:
        $oExpediente->setEtiquetas($Qa_etiquetas);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= _("No se han podido guardar las etiquetas");
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
        break;
    case 'reunion':
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        // Si pongo la fecha con datetimepicker, ya esta en ISO (hay que poner FALSE a la conversión).
        $oExpediente->setF_reunion($Qf_reunion);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= _("No se ha podido guarda la fecha de reunión");
            $error_txt .= "<br>";
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
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
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
        break;
    case 'distribuir':
        $html = '';
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_SECRETARIA);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
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
        $aWhereAccion = ['id_expediente' => $Qid_expediente, '_ordre' => 'tipo_accion' ];
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($aWhereAccion);
        $json_prot_local = [];
        foreach($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $tipo_accion = $oAccion->getTipo_accion();
            // si es propuesta, o plantilla no genero protocolo:
            if ($tipo_accion === Escrito::ACCION_ESCRITO) {
                $proto = TRUE;
                $oEscrito = new Escrito($id_escrito);
                // si es un e12, no hay que numerar.
                if ($oEscrito->getCategoria() === Escrito::CAT_E12) {
                    $proto = FALSE;
                }
                // comprobar que no está anulado:
                if (is_true($oEscrito->getAnulado())) {
                    $proto = FALSE;
                }
                if ($proto) {
                    $oEscrito->generarProtocolo($id_lugar,$id_lugar_cr);
                    // para poder insertar en la plantilla.
                    $json_prot_local = $oEscrito->getJson_prot_local();
                }
            }
            // si proviene de una plantilla, insertar el conforme en el texto:
            // cojo el protocolo del ultimo escrito. No tiene porque ser siempre cierto.
            if ($tipo_accion === Escrito::ACCION_PLANTILLA) {
                $oEscritoP = new Escrito($id_escrito);
                $html = $oEscritoP->addConforme($Qid_expediente,$json_prot_local);
            }
        }

        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
            $jsondata['rta'] = $html;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
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
        $error_txt = '';
        if (!empty($Qof_destino)) {
            $of_destino = $Qof_destino;
        } else {
            if (!empty($copias) && is_true($copias)) {
                $of_destino = 'copias';
            } else {
                $of_destino = ConfigGlobal::role_id_cargo();
            }
        }
        // copiar expdiente: poner los escritos como antecedentes.
        $oExpediente = new Expediente($Qid_expediente);
        if ($oExpediente->copiar($of_destino) === FALSE ) {
            $error_txt .= $oExpediente->getErrorTxt();
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
        break;
    case 'exp_a_borrador':
        $error_txt = '';
        // Hay que borrar: las firmas.
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente]);
        foreach($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
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
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
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
        breaK;
    case 'exp_eliminar':
        // Si hay escritos enviados, no se borran.
        $error_txt = '';
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
                    $error_txt .= $rta;
                }
                if ($oAccion->DBEliminar() === FALSE) {
                    $error_txt .= _("No se ha eliminado la accion");
                    $error_txt .= "<br>";
                }
            }
        }
        // firmas:
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Qid_expediente]);
        foreach($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
            }
        }
        $oExpediente = new Expediente($Qid_expediente);
        if ($oExpediente->DBEliminar() === FALSE ) {
            $error_txt .= _("No se ha eliminado el expediente");
            $error_txt .= "<br>";
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
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        
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
        
        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['id_expediente'] = $Qid_expediente;
            $jsondata['html'] = $html;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }
        
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
        
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        
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
            if ($oExpediente->DBGuardar() === FALSE ) {
                $error_txt .= $oExpediente->getErrorTxt();
            }
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
    case 'cambio_tramite':
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $id_tramite_old = $oExpediente->getId_tramite();
        $oExpediente->setId_tramite($Qtramite);
        if ($oExpediente->DBGuardar() === FALSE ) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // generar firmas
        $oExpediente->generarFirmas();
        $gesFirmas = new GestorFirma();
        // copiar las firmas:
        $gesFirmas->copiarFirmas($Qid_expediente, $Qtramite, $id_tramite_old);
        // borrar el recorrido del tramite anterior.
        $gesFirmas->borrarFirmas($Qid_expediente, $id_tramite_old);
        
        
        if (!empty($error_txt)) {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = true;
            $jsondata['id_expediente'] = $Qid_expediente;
            $a_cosas = [ 'id_expediente' => $Qid_expediente];
            $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
}