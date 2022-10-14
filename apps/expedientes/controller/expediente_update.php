<?php

use core\ConfigGlobal;
use davical\model\Davical;
use entradas\model\Entrada;
use escritos\model\Escrito;
use expedientes\model\entity\GestorAccion;
use expedientes\model\Expediente;
use expedientes\model\GestorExpediente;
use lugares\model\entity\GestorLugar;
use pendientes\model\Pendiente;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Protocolo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_ponente = (string)filter_input(INPUT_POST, 'ponente');

$Q_tramite = (integer)filter_input(INPUT_POST, 'tramite');
$Q_estado = (integer)filter_input(INPUT_POST, 'estado');
$Q_prioridad = (integer)filter_input(INPUT_POST, 'prioridad');

$Q_f_reunion = (string)filter_input(INPUT_POST, 'f_reunion');
$Q_f_aprobacion = (string)filter_input(INPUT_POST, 'f_aprobacion');
$Q_f_contestar = (string)filter_input(INPUT_POST, 'f_contestar');

$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_entradilla = (string)filter_input(INPUT_POST, 'entradilla');

$Q_a_firmas_oficina = (array)filter_input(INPUT_POST, 'firmas_oficina', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_firmas = (array)filter_input(INPUT_POST, 'firmas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_preparar = (array)filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Q_vida = (integer)filter_input(INPUT_POST, 'vida');
$Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');

$error_txt = '';
$nuevo_creador = '';
switch ($Q_que) {
    case 'en_visto': // Copiado de entradas_update.
        $Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
        $Q_id_oficina = ConfigGlobal::role_id_oficina();
        $Q_id_cargo = ConfigGlobal::role_id_cargo();
        $oEntrada = new Entrada($Q_id_entrada);
        $oEntrada->DBCarregar();

        $aVisto = $oEntrada->getJson_visto(TRUE);
        // Si ya está no hay que añadirlo, sino modificarlo:
        $flag = FALSE;
        foreach ($aVisto as $key => $oVisto) {
            $oficina = $oVisto['oficina'];
            $cargo = $oVisto['cargo'];
            if ($oficina == $Q_id_oficina && $cargo == $Q_id_cargo) {
                $oVisto['visto'] = TRUE;
                $aVisto[$key] = $oVisto;
                $flag = TRUE;
            }
        }
        if ($flag === FALSE) {
            $oVisto = new stdClass;
            $oVisto->oficina = $Q_id_oficina;
            $oVisto->cargo = $Q_id_cargo;
            $oVisto->visto = TRUE;
            $aVisto[] = $oVisto;
        }

        $oEntrada->setJson_visto($aVisto);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        $oEntrada->comprobarVisto();

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
    case 'en_pendiente':
        $Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
        $Q_id_cargo_pendiente = (integer)filter_input(INPUT_POST, 'id_cargo_pendiente');
        $Q_f_plazo = (string)filter_input(INPUT_POST, 'f_plazo');

        $oCargo = new Cargo($Q_id_cargo_pendiente);
        $id_oficina = $oCargo->getId_oficina();

        // nombre normalizado del usuario y oficina:
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $user_davical = $oDavical->getUsernameDavical($Q_id_cargo_pendiente);
        $parent_container = $oDavical->getNombreRecurso($id_oficina);

        $calendario = 'oficina';
        $oHoy = new DateTimeLocal();
        $Q_f_plazo = empty($Q_f_plazo) ? $oHoy->getFromLocal() : $Q_f_plazo;
        // datos de la entrada 
        $id_reg = 'EN' . $Q_id_entrada; // (para calendario='registro': REN = Regitro Entrada, para 'oficina': EN)
        $oEntrada = new Entrada($Q_id_entrada);

        $oPendiente = new Pendiente($parent_container, $calendario, $user_davical);
        $oPendiente->setId_reg($id_reg);
        $oPendiente->setAsunto($oEntrada->getAsunto());
        $oPendiente->setStatus("NEEDS-ACTION");
        $oPendiente->setF_inicio($oHoy->getFromLocal());
        $oPendiente->setF_plazo($Q_f_plazo);
        $oPendiente->setvisibilidad($Q_visibilidad);
        $oPendiente->setDetalle($oEntrada->getDetalle());
        $oPendiente->setEncargado($Q_id_cargo_pendiente);
        $oPendiente->setId_oficina($id_oficina);

        $oProtOrigen = new Protocolo();
        $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
        $location = $oProtOrigen->ver_txt_num();

        $oPendiente->setLocation($location);
        $oPendiente->setRef_prot_mas($oProtOrigen->ver_txt_mas());
        // las oficinas implicadas:
        $oPendiente->setOficinasArray($oEntrada->getResto_oficinas());
        if ($oPendiente->Guardar() === FALSE) {
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
        $Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
        // Hay que crear un nunevo expediente, con un ajunto (entrada).
        $oEntrada = new Entrada($Q_id_entrada);
        $Q_asunto = $oEntrada->getAsunto_entrada();

        $Q_estado = Expediente::ESTADO_BORRADOR;
        $Q_ponente = ConfigGlobal::role_id_cargo();
        $Q_tramite = 2; // Ordinario, no puede ser null.
        $Q_prioridad = Expediente::PRIORIDAD_NORMAL; // no puede ser null.

        $oExpediente = new Expediente();
        $oExpediente->setPonente($Q_ponente);
        $oExpediente->setEstado($Q_estado);
        $oExpediente->setId_tramite($Q_tramite);
        $oExpediente->setPrioridad($Q_prioridad);
        $oExpediente->setAsunto($Q_asunto);
        $oExpediente->setVisibilidad($Q_visibilidad);

        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se han podido crear el nuevo expediente");
            $error_txt .= "\n";
            $error_txt .= $oExpediente->getErrorTxt();
        }

        $a_antecedente = ['tipo' => 'entrada', 'id' => $Q_id_entrada];
        $oExpediente->addAntecedente($a_antecedente);
        if ($oExpediente->DBGuardar() === FALSE) {
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
        $Q_id_oficial = (integer)filter_input(INPUT_POST, 'id_oficial');
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_ENCARGADO);
        $oExpediente->setPonente($Q_id_oficial);
        if ($oExpediente->DBGuardar() === FALSE) {
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
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        // las etiquetas:
        $oExpediente->setEtiquetas($Q_a_etiquetas);
        $oExpediente->setVisibilidad($Q_visibilidad);
        if ($oExpediente->DBGuardar() === FALSE) {
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
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        // Si pongo la fecha con datetimepicker, ya esta en ISO (hay que poner FALSE a la conversión).
        $oExpediente->setF_reunion($Q_f_reunion);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido guarda la fecha de reunión");
            $error_txt .= "<br>";
        }
        // firmar el paso de fijar reunion:
        $f_hoy_iso = date(\DateTimeInterface::ISO8601);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente, 'cargo_tipo' => Cargo::CARGO_REUNION]);
        foreach ($cFirmas as $oFirma) {
            $oFirma->DBCarregar();
            if (ConfigGlobal::role_actual() === 'vcd') { // No sé si hace falta??
                $oFirma->setValor(Firma::V_D_OK);
            } else {
                $oFirma->setValor(Firma::V_OK);
            }
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso, FALSE);
            if ($oFirma->DBGuardar() === FALSE) {
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
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // Se pone cuando se han enviado...
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        // las etiquetas:
        $oExpediente->setEtiquetas($Q_a_etiquetas);
        $oExpediente->setEstado(Expediente::ESTADO_ARCHIVADO);
        if ($oExpediente->DBGuardar() === FALSE) {
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
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        $estado_original = $oExpediente->getEstado();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_SECRETARIA);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // firmar el paso de distribuir:
        $f_hoy_iso = date(\DateTimeInterface::ISO8601);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente, 'cargo_tipo' => Cargo::CARGO_DISTRIBUIR]);
        foreach ($cFirmas as $oFirma) {
            $oFirma->DBCarregar();
            if (ConfigGlobal::role_actual() === 'vcd') { // No sé si hace falta??
                $oFirma->setValor(Firma::V_D_OK);
            } else {
                $oFirma->setValor(Firma::V_OK);
            }
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso, FALSE);
            if ($oFirma->DBGuardar() === FALSE) {
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
        $sigla = 'IESE';
        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        $oLugar = $cLugares[0];
        $id_lugar_iese = $oLugar->getId_lugar();
        // escritos del expediente: acciones tipo escrito
        $aWhereAccion = ['id_expediente' => $Q_id_expediente, '_ordre' => 'tipo_accion'];
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($aWhereAccion);
        $json_prot_local = [];
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $tipo_accion = $oAccion->getTipo_accion();
            // si es propuesta, o plantilla no genero protocolo:
            if ($tipo_accion === Escrito::ACCION_ESCRITO) {
                $proto = TRUE;
                $oEscrito = new Escrito($id_escrito);
                // si es un e12, no hay que numerar.
                if ($oEscrito->getCategoria() === Categoria::CAT_E12) {
                    $proto = FALSE;
                }
                // comprobar que no está anulado:
                if (is_true($oEscrito->getAnulado()) || $estado_original == Expediente::ESTADO_DILATA) {
                    $proto = FALSE;
                }
                if ($proto) {
                    $oEscrito->generarProtocolo($id_lugar, $id_lugar_cr, $id_lugar_iese);
                    // para poder insertar en la plantilla.
                    $json_prot_local = $oEscrito->getJson_prot_local();
                }
            }
            // si proviene de una plantilla, insertar el conforme en el texto:
            // cojo el protocolo del ultimo escrito. No tiene porque ser siempre cierto.
            if ($tipo_accion === Escrito::ACCION_PLANTILLA) {
                $oEscritoP = new Escrito($id_escrito);
                $html = $oEscritoP->addConforme($Q_id_expediente, $json_prot_local);
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
        $Q_of_destino = (integer)filter_input(INPUT_POST, 'of_destino');
    case 'exp_cp_copias':
        $copias = TRUE;
    case 'exp_cp_borrador':
        $error_txt = '';
        if (!empty($Q_of_destino)) {
            $of_destino = $Q_of_destino;
        } else {
            if (!empty($copias) && is_true($copias)) {
                $of_destino = 'copias';
            } else {
                $of_destino = ConfigGlobal::role_id_cargo();
            }
        }
        // copiar expdiente: poner los escritos como antecedentes.
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->copiar($of_destino) === FALSE) {
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
    case 'exp_a_borrador_cmb_creador':
    case 'exp_a_borrador':
        $error_txt = '';
        // Hay que borrar: las firmas.
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente]);
        foreach ($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
            }
        }

        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->setEstado(Expediente::ESTADO_BORRADOR);
        $asunto = $oExpediente->getAsunto();
        $asunto_retirado = _("RETIRADO") . " $asunto";
        $oExpediente->setAsunto($asunto_retirado);
        $oExpediente->setF_contestar('');
        $oExpediente->setF_ini_circulacion('');
        $oExpediente->setF_aprobacion('');
        $oExpediente->setF_reunion('');

        if ($Q_que == 'exp_a_borrador_cmb_creador') {
            $nuevo_creador = ConfigGlobal::role_id_cargo();
            $oExpediente->setPonente($nuevo_creador);
        }
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // Si hay escritos anulados, quitar el 'anulado'
        // cambiar también el creador de todos los escritos:
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Q_id_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $oEscrito->DBCarregar();
            $oEscrito->setAnulado('f');
            if ($Q_que == 'exp_a_borrador_cmb_creador') {
                $oEscrito->setCreador($nuevo_creador);
            }
            if ($oEscrito->DBGuardar() === FALSE) {
                $error_txt .= _("No se ha guardado el escrito");
                $error_txt .= "<br>";
                $error_txt .= $oAccion->getErrorTxt();
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
    case 'exp_eliminar':
        // Si hay escritos enviados, no se borran.
        $error_txt = '';
        // Hay que borrar: el expediente, las firmas, las acciones, los escritos y los adjuntos de los escritos.
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Q_id_expediente]);
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
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente]);
        foreach ($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
            }
        }
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBEliminar() === FALSE) {
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

        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        // oficiales
        $new_preparar = [];
        foreach ($Q_a_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = (int)$id;
            if ($mi_id_cargo == $id) {
                // es un toggle: si esta 1 pongo 0 y al revés.
                $oJSON->visto = is_true($visto) ? FALSE : TRUE;
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
                $visto_db = empty($oficial->visto) ? 0 : $oficial->visto;
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
            $html .= "<div class=\"form-check custom-checkbox form-check-inline\">";
            $html .= "<input type=\"checkbox\" class=\"form-check-input\" name=\"a_preparar[]\" id=\"$id2\" value=\"$id2#$visto\" $chk>";
            if ($visto) {
                $html .= "<label class=\"form-check-label text-success\" for=\"$id2\">$text (" . _("visto") . ")</label>";
            } else {
                $html .= "<label class=\"form-check-label\" for=\"$id2\">$text</label>";
            }
            $html .= "</div>";
        }

        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['id_expediente'] = $Q_id_expediente;
            $jsondata['html'] = $html;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        break;
    case 'circular':
        // primero se guarda, y al final se guarda la fecha de hoy y se crean las firmas para el trámite
    case 'guardar':
        if (!empty($Q_id_expediente)) {
            $oExpediente = new Expediente($Q_id_expediente);
            $oExpediente->DBCarregar();
            // Mantego al ponente como creador...
        } else {
            // si falla el javascript, puede ser que se hagan varios click a 'Guardar' 
            // y se dupliquen los espedientes. Me aseguro de que no exista uno igual:
            $gesExpedientes = new GestorExpediente();
            $aWhere = ['id_tramite' => $Q_tramite,
                'estado' => $Q_estado,
                'prioridad' => $Q_prioridad,
                'asunto' => $Q_asunto,
                'entradilla' => $Q_entradilla,
            ];
            if (!empty($Q_f_contestar)) {
                $oConverter = new core\Converter('date', $Q_f_contestar);
                $f_contestar_iso = $oConverter->toPg();
                $aWhere['f_contestar'] = $f_contestar_iso;
            }
            $cExpedientes = $gesExpedientes->getExpedientes($aWhere);
            if (count($cExpedientes) > 0) {
                exit (_("Creo que ya se ha creado"));
            }
            // nuevo.
            $oExpediente = new Expediente();
            $Q_estado = Expediente::ESTADO_BORRADOR;
            $oExpediente->setPonente($Q_ponente);
        }

        $oExpediente->setId_tramite($Q_tramite);
        $oExpediente->setEstado($Q_estado);
        $oExpediente->setPrioridad($Q_prioridad);
        $oExpediente->setF_reunion($Q_f_reunion);
        $oExpediente->setF_aprobacion($Q_f_aprobacion);
        $oExpediente->setF_contestar($Q_f_contestar);
        $oExpediente->setAsunto($Q_asunto);
        $oExpediente->setEntradilla($Q_entradilla);

        // según el trámite mirar si hay que grabar oficiales y/o varios cargos.
        $oficiales = FALSE;
        $aWhere = ['id_tramite' => $Q_tramite, 'id_cargo' => Cargo::CARGO_OFICIALES];
        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
        if (count($cTramiteCargos) > 0) {
            $oficiales = TRUE;
        }
        $varias = FALSE;
        $aWhere = ['id_tramite' => $Q_tramite, 'id_cargo' => Cargo::CARGO_VARIAS];
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
        if (count($cTramiteCargos) > 0) {
            $varias = TRUE;
        }
        // pasar a array para postgresql
        if ($oficiales) {
            $a_filter_firmas_oficina = array_filter($Q_a_firmas_oficina); // Quita los elementos vacíos y nulos.
            $oExpediente->setFirmas_oficina($a_filter_firmas_oficina);
        } else {
            $oExpediente->setFirmas_oficina('');
        }

        // pasar a array para postgresql
        if ($varias) {
            $a_filter_firmas = array_filter($Q_a_firmas); // Quita los elementos vacíos y nulos.
            $oExpediente->setResto_oficinas($a_filter_firmas);
        } else {
            $oExpediente->setResto_oficinas('');
        }

        $oExpediente->setVida($Q_vida);
        $oExpediente->setVisibilidad($Q_visibilidad);

        // oficiales
        $new_preparar = [];
        foreach ($Q_a_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = (int)$id;
            // hay que asegurar que sea bool
            $oJSON->visto = is_true($visto) ? TRUE : FALSE;

            $new_preparar[] = $oJSON;
        }
        $oExpediente->setJson_preparar($new_preparar);

        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        } else {
            $id_expediente = $oExpediente->getId_expediente();
            // las etiquetas, después de tener el id_expediente (si es nuevo):
            $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $oExpediente->setEtiquetas($Q_a_etiquetas);
            if ($oExpediente->DBGuardar() === FALSE) {
                $error_txt .= $oExpediente->getErrorTxt();
            }
        }

        // CIRCULAR
        if ($Q_que == 'circular') {
            $f_hoy_iso = date(\DateTimeInterface::ISO8601);
            // se pone la fecha del escrito como hoy:
            $oExpediente->setF_escritos($f_hoy_iso, FALSE);
            // Guardar fecha y cambiar estado
            $oExpediente->setF_ini_circulacion($f_hoy_iso, FALSE);
            $oExpediente->setEstado(Expediente::ESTADO_CIRCULANDO);
            if ($oExpediente->DBGuardar() === FALSE) {
                $error_txt .= $oExpediente->getErrorTxt();
            }
            // generar firmas
            $oExpediente->generarFirmas();
            // Si soy el primero, Ya firmo.
            $gesFirmas = new GestorFirma();
            $oFirmaPrimera = $gesFirmas->getPrimeraFirma($id_expediente);
            $id_primer_cargo = $oFirmaPrimera->getId_cargo();
            if ($id_primer_cargo == ConfigGlobal::role_id_cargo()) {
                if (ConfigGlobal::role_actual() === 'vcd') { // No sé si hace falta??
                    $oFirmaPrimera->setValor(Firma::V_D_OK);
                } else {
                    $oFirmaPrimera->setValor(Firma::V_OK);
                }
                $oFirmaPrimera->setId_usuario(ConfigGlobal::mi_id_usuario());
                $oFirmaPrimera->setObserv('');
                $oFirmaPrimera->setF_valor($f_hoy_iso, FALSE);
                if ($oFirmaPrimera->DBGuardar() === FALSE) {
                    $error_txt .= $oFirmaPrimera->getErrorTxt();
                }
                // comprobar que ya han firmado todos, para:
                //  - en caso dl: pasarlo a scdl para distribuir (ok_scdl)
                //  - en caso ctr: marcar como circulando
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
                    $bParaDistribuir = $gesFirmas->isParaDistribuir($Q_id_expediente);
                    if ($bParaDistribuir) {
                        // guardar la firma de Cargo::CARGO_DISTRIBUIR;
                        $oExpediente->DBCarregar();
                        $oExpediente->setEstado(Expediente::ESTADO_ACABADO);
                        $oExpediente->setF_aprobacion($f_hoy_iso, FALSE);
                        $oExpediente->setF_aprobacion_escritos($f_hoy_iso, FALSE);
                        if ($oExpediente->DBGuardar() === FALSE) {
                            $error_txt .= $oExpediente->getErrorTxt();
                        }
                    }
                }
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    // cambio el estado del expediente.
                    $oExpediente->DBCarregar();
                    $estado = Expediente::ESTADO_CIRCULANDO;
                    $oExpediente->setEstado($estado);
                    if ($oExpediente->DBGuardar() === FALSE) {
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
            $a_cosas = ['id_expediente' => $id_expediente];
            $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'cambio_tramite':
        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCarregar();
        $id_tramite_old = $oExpediente->getId_tramite();
        $oExpediente->setId_tramite($Q_tramite);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // generar firmas
        $oExpediente->generarFirmas();
        $gesFirmas = new GestorFirma();
        // copiar las firmas:
        $gesFirmas->copiarFirmas($Q_id_expediente, $Q_tramite, $id_tramite_old);
        // borrar el recorrido del tramite anterior.
        $gesFirmas->borrarFirmas($Q_id_expediente, $id_tramite_old);


        if (!empty($error_txt)) {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = true;
            $jsondata['id_expediente'] = $Q_id_expediente;
            $a_cosas = ['id_expediente' => $Q_id_expediente];
            $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
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