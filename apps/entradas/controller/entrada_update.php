<?php
use core\ConfigGlobal;
use function core\is_true;
use entradas\model\Entrada;
use entradas\model\entity\EntradaBypass;
use entradas\model\entity\EntradaDB;
use entradas\model\entity\GestorEntradaBypass;
use lugares\model\entity\GestorGrupo;
use pendientes\model\Pendiente;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Oficina;
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
$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$Qorigen = (integer) \filter_input(INPUT_POST, 'origen');
$Qprot_num_origen = (integer) \filter_input(INPUT_POST, 'prot_num_origen');
$Qprot_any_origen = (integer) \filter_input(INPUT_POST, 'prot_any_origen');
$Qprot_mas_origen = (string) \filter_input(INPUT_POST, 'prot_mas_origen');

$Qasunto_e = (string) \filter_input(INPUT_POST, 'asunto_e');
$Qf_escrito = (string) \filter_input(INPUT_POST, 'f_escrito');
$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qf_entrada = (string) \filter_input(INPUT_POST, 'f_entrada');

$Qdetalle = (string) \filter_input(INPUT_POST, 'detalle');
$Qid_of_ponente = (integer) \filter_input(INPUT_POST, 'of_ponente');
$Qa_oficinas = (array)  \filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Qcategoria = (integer) \filter_input(INPUT_POST, 'categoria');
$Qvisibiliad = (integer) \filter_input(INPUT_POST, 'visibilidad');

$Qplazo = (string) \filter_input(INPUT_POST, 'plazo');
$Qf_plazo = (string) \filter_input(INPUT_POST, 'f_plazo');
$Qbypass = (string) \filter_input(INPUT_POST, 'bypass');
$QAdmitir_hidden = (string) \filter_input(INPUT_POST, 'admitir_hidden');

/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Qa_referencias = (array)  \filter_input(INPUT_POST, 'referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_num_referencias = (array)  \filter_input(INPUT_POST, 'prot_num_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_any_referencias = (array)  \filter_input(INPUT_POST, 'prot_any_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_mas_referencias = (array)  \filter_input(INPUT_POST, 'prot_mas_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$error_txt = '';
$jsondata = [];
switch($Qque) {
    case 'en_asignar':
        $Qid_oficina = ConfigGlobal::role_id_oficina();
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $oEntrada = new EntradaDB($Qid_entrada);
        $oEntrada->DBCarregar();
        // comprobar si es un cambio (ya estaba encargado a alguien)
        $encargado_old = $oEntrada->getEncargado();
        
        $oEntrada->setEncargado($Qid_cargo);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }
        
        // también hay que marcarlo como visto por quien lo encarga
        // Siempre que no sea el mismo:
        if (ConfigGlobal::role_id_cargo() != $Qid_cargo) {
            $flag_encontrado = FALSE;
            $aVisto = $oEntrada->getJson_visto(TRUE);
            foreach ($aVisto as $key => $oVisto) {
                if ( ($oVisto['oficina'] == $Qid_oficina) && ($oVisto['cargo'] == $Qid_cargo) ) {
                    $aVisto[$key]['visto'] = TRUE;
                    $flag_encontrado = TRUE;
                }
            }
            if (!$flag_encontrado) {
                $oVisto = [];
                $oVisto['oficina'] = $Qid_oficina;
                $oVisto['cargo'] = $encargado_old;
                $oVisto['visto'] = TRUE;
                $aVisto[] = $oVisto;
            }
            $oEntrada->setJson_visto($aVisto);
            if ($oEntrada->DBGuardar() === FALSE) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        }
        
        // Si es un cambio: marcar visto al anterior
        if (!empty($encargado_old)) {
            $flag_encontrado = FALSE;
            $aVisto = $oEntrada->getJson_visto(TRUE);
            foreach ($aVisto as $key => $oVisto) {
                if ($oVisto['oficina'] == $Qid_oficina && $oVisto['cargo'] == $Qid_cargo) {
                    $aVisto[$key]['visto'] = TRUE;
                    $flag_encontrado = TRUE;
                }
            }
            if (!$flag_encontrado) {
                $oVisto = [];
                $oVisto['oficina'] = $Qid_oficina;
                $oVisto['cargo'] = $encargado_old;
                $oVisto['visto'] = TRUE;
                $aVisto[] = $oVisto;
            }
            
            $oEntrada->setJson_visto($aVisto);
            if ($oEntrada->DBGuardar() === FALSE) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        }
        // y en cualquier caso: desmarcar al nuevo (podria estar marcado previamente)
        $aVisto = $oEntrada->getJson_visto(TRUE);
        foreach ($aVisto as $key => $oVisto) {
            if ($oVisto['oficina'] == $Qid_oficina && $oVisto['cargo'] == $Qid_cargo) {
                $aVisto[$key]['visto'] = FALSE;
            }
        }
        
        $oEntrada->setJson_visto($aVisto);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
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
    case 'en_visto':
        $Qid_oficina = ConfigGlobal::role_id_oficina();
        $Qid_cargo = ConfigGlobal::role_id_cargo();
        $oEntrada = new EntradaDB($Qid_entrada);
        $oEntrada->DBCarregar();
        
        $aVisto = $oEntrada->getJson_visto(TRUE);
        $oVisto = [];
        $oVisto['oficina'] = $Qid_oficina;
        $oVisto['cargo'] = $Qid_cargo;
        $oVisto['visto'] = TRUE;
        $aVisto[] = $oVisto;
        
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
    case 'eliminar':
        $oEntrada = new EntradaDB($Qid_entrada);
        if ($oEntrada->DBEliminar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'guardar_destinos':
        $gesEntradasBypass = new GestorEntradaBypass();
        $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
        if (!empty($cEntradasBypass)) {
            // solo debería haber una:
            $oEntradaBypass = $cEntradasBypass[0];
            $oEntradaBypass->DBCarregar();
        } else {
            $oEntradaBypass = new EntradaBypass();
            $oEntradaBypass->setId_entrada($Qid_entrada);
        }
        //Qasunto.
        $oEntrada = new EntradaDB($Qid_entrada);
        $oPermisoRegistro = new PermRegistro();
        $perm_asunto = $oPermisoRegistro->permiso_detalle($oEntrada, 'asunto');
        if ( $perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEntrada->DBCarregar();
            $oEntrada->setAsunto($Qasunto);
            if ($oEntrada->DBGuardar() === FALSE) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        }
        // destinos
        $Qgrupo_dst = (string) \filter_input(INPUT_POST, 'grupo_dst');
        $Qf_salida = (string) \filter_input(INPUT_POST, 'f_salida');
        
        // genero un vector con todos los grupos.
        $Qa_grupos = (array)  \filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
        $Qa_destinos = (array)  \filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_num_destinos = (array)  \filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_any_destinos = (array)  \filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_mas_destinos = (array)  \filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        // Si esta marcado como grupo de destinos, o destinos individuales.
        if (core\is_true($Qgrupo_dst)) {
            $descripcion = '';
            $gesGrupo = new GestorGrupo();
            $a_grupos = $gesGrupo->getArrayGrupos();
            foreach ($Qa_grupos as $id_grupo) {
                $descripcion .= empty($descripcion)? '' : ' + ';
                $descripcion .= $a_grupos[$id_grupo];
            }
            $oEntradaBypass->setId_grupos($Qa_grupos);
            $oEntradaBypass->setDescripcion($descripcion);
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
            $oEntradaBypass->setJson_prot_destino($aProtDst);
            $oEntradaBypass->setId_grupos();
            $oEntradaBypass->setDescripcion('x'); // no puede ser null.
        }
        $oEntradaBypass->setF_salida($Qf_salida);
        if ($oEntradaBypass->DBGuardar() === FALSE ) {
            $error_txt .= $oEntradaBypass->getErrorTxt();
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
    case 'f_entrada':
        if ($Qf_entrada == 'hoy') {
            $oHoy = new DateTimeLocal();
            $Qf_entrada = $oHoy->getFromLocal();
        }
        $oEntrada = new Entrada($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setF_entrada($Qf_entrada);
        if (empty($Qf_entrada)) {
            $oEntrada->setEstado(Entrada::ESTADO_INGRESADO);
        } else {
            $oEntrada->setEstado(Entrada::ESTADO_ADMITIDO);
        }
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'detalle':
        $oEntrada = new Entrada($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setDetalle($Qdetalle);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'guardar':
        if (!empty($Qid_entrada)) {
            $oEntrada = new Entrada($Qid_entrada);
            $oEntrada->DBCarregar();
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEntrada, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEntrada, 'detalle');
        } else {
            $oEntrada = new Entrada();
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }
        
        $oEntrada->setModo_entrada(Entrada::MODO_MANUAL);
        
        $oProtOrigen = new Protocolo($Qorigen, $Qprot_num_origen, $Qprot_any_origen, $Qprot_mas_origen);
        $oEntrada->setJson_prot_origen($oProtOrigen->getProt());
        
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
        $oEntrada->setJson_prot_ref($aProtRef);
 
        $oEntrada->setAsunto_entrada($Qasunto_e);
        $oEntrada->setF_documento($Qf_escrito,TRUE);
        $oEntrada->setF_entrada($Qf_entrada);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEntrada->setAsunto($Qasunto);
        }
        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEntrada->setDetalle($Qdetalle);
        }
        $oEntrada->setPonente($Qid_of_ponente);
        $oEntrada->setResto_oficinas($Qa_oficinas);

        $oEntrada->setCategoria($Qcategoria);
        // visibilidad: puede que esté en modo solo lectura, mirar el hiden.
        if (empty($Qvisibiliad)) {
            $Qvisibiliad = (integer) \filter_input(INPUT_POST, 'hidden_visibilidad');
        }
        $oEntrada->setVisibilidad($Qvisibiliad);

        
        // 5º Compruebo si hay que generar un pendiente
        switch ($Qplazo) {
            case 'hoy':
                $oEntrada->setF_contestar('');
                break;
            case 'normal':
                $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
                $periodo = 'P'.$plazo_normal.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'rápido':
                $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
                $periodo = 'P'.$plazo_rapido.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'urgente':
                $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
                $periodo = 'P'.$plazo_urgente.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'fecha':
                $oEntrada->setF_contestar($Qf_plazo);
                break;
            default:
                // Si no hay $Qplazo, No pongo ninguna fecha a contestar
        }
            
        if (is_true($QAdmitir_hidden)) {
            // pasa directamente a asigado. Se supone que el admitido lo ha puesto el vcd.
            // en caso de ponerlo secretaria, al guardar pasa igualmente a asignado.
            $estado = Entrada::ESTADO_ASIGNADO;
        } else {
            $estado = Entrada::ESTADO_INGRESADO;
        }
        // si es el scdl, puede ser que pase a aceptado:
        if ($Qfiltro == 'en_asignado' || $Qfiltro == 'en_buscar') {
            $estado = Entrada::ESTADO_ACEPTADO;
        }
        
        if ($Qfiltro == 'en_buscar') { // NO tocar el estado
            $estado = $oEntrada->getEstado();
            $id_entrada = $Qid_entrada;
        }
        $oEntrada->setEstado($estado);
       
        $oEntrada->setBypass($Qbypass);
        if ($oEntrada->DBGuardar() === FALSE ) {
            $error_txt .= $oEntrada->getErrorTxt();
        }
        
        if (empty($error_txt) && $Qfiltro != 'en_buscar') {
            $id_entrada = $oEntrada->getId_entrada();
            //////// Generar un Pendiente (hay que esperar e tener el id_entrada //////
            // Solo se genera en cuando el scdl lo acepta (filtro=en_asignado). Si no se mira esta condición
            // se van generando pendientes cada vez que se guarda.
            if ($Qfiltro == 'en_asignado') {
                if ($Qplazo != "hoy") {
                    $Qid_pendiente = (integer) \filter_input(INPUT_POST, 'id_pendiente');
                    if (empty($Qid_pendiente)) { // si no se ha generado el pendiente con "modificar pendiente"
                        $f_plazo = $oEntrada->getF_contestar()->getFromLocal();
                        $location = $oProtOrigen->ver_txt_num();
                        $prot_mas = $oProtOrigen->ver_txt_mas();
                        $oOficina = new Oficina($Qid_of_ponente);
                        $oficina_ponente = $oOficina->getSigla();
                        if (empty($oficina_ponente)) {
                            $msg = _("No se puede determinar la ruta del calendario para añadir el pendiente");
                            exit($msg);
                        }
                        $id_reg = 'REN'.$id_entrada; // REN = Regitro Entrada
                        
                        $parent_container = 'oficina_'.$oficina_ponente;
                        $resource = 'registro';
                        $cargo = 'secretaria';
                        $uid = '';
                        $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
                        $oPendiente->setId_reg($id_reg);
                        $oPendiente->setAsunto($Qasunto);
                        $oPendiente->setStatus("NEEDS-ACTION");
                        $oPendiente->setF_inicio($Qf_entrada);
                        $oPendiente->setF_plazo($f_plazo);
                        $oPendiente->setVisibilidad($Qvisibiliad);
                        $oPendiente->setDetalle($Qdetalle);
                        $oPendiente->setPendiente_con($Qorigen);
                        $oPendiente->setLocation($location);
                        $oPendiente->setRef_prot_mas($prot_mas);
                        $oPendiente->setId_oficina($Qid_of_ponente);
                        // las firmas son cargos, buscar las oficinas implicadas:
                        $oPendiente->setOficinasArray($Qa_oficinas);
                        if ($oPendiente->Guardar() === FALSE ) {
                            $error_txt .= _("No se han podido guardar el nuevo pendiente");
                        }
                    } else {
                        // meter el pendienteDB en davical con el id_reg que toque y borrarlo de pendienteDB.
                        $oOficina = new Oficina($Qid_of_ponente);
                        $oficina_ponente = $oOficina->getSigla();
                        if (empty($oficina_ponente)) {
                            $msg = _("No se puede determinar la ruta del calendario para añadir el pendiente");
                            exit($msg);
                        }
                        $id_reg = 'REN'.$id_entrada; // REN = Regitro Entrada
                        $parent_container = 'oficina_'.$oficina_ponente;
                        $resource = 'registro';
                        $cargo = 'secretaria';
                        $uid = '';
                        $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
                        $oPendiente->crear_de_pendienteDB($id_reg,$Qid_pendiente);
                    }
                }
            }
        
            //////// BY PASS //////
            if (is_true($Qbypass) && !empty($Qid_entrada)) {
                $gesEntradasBypass = new GestorEntradaBypass();
                $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
                if (!empty($cEntradasBypass)) {
                    // solo debería haber una:
                    $oEntradaBypass = $cEntradasBypass[0];
                    $oEntradaBypass->DBCarregar();
                } else {
                    $oEntradaBypass = new EntradaBypass();
                    $oEntradaBypass->setId_entrada($Qid_entrada);
                }
                //Qasunto.
                if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
                    $oEntrada = new EntradaDB($Qid_entrada);
                    $oEntrada->DBCarregar();
                    $oEntrada->setAsunto($Qasunto);
                    if ($oEntrada->DBGuardar() === FALSE) {
                        $error_txt .= $oEntrada->getErrorTxt();
                    }
                }
                // destinos
                $Qgrupo_dst = (string) \filter_input(INPUT_POST, 'grupo_dst');
                $Qf_salida = (string) \filter_input(INPUT_POST, 'f_salida');
                
                // genero un vector con todos los grupos.
                $Qa_grupos = (array)  \filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
                $Qa_destinos = (array)  \filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_num_destinos = (array)  \filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_any_destinos = (array)  \filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_mas_destinos = (array)  \filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                
                // Si esta marcado como grupo de destinos, o destinos individuales.
                if (core\is_true($Qgrupo_dst)) {
                    $descripcion = '';
                    $gesGrupo = new GestorGrupo();
                    $a_grupos = $gesGrupo->getArrayGrupos();
                    foreach ($Qa_grupos as $id_grupo) {
                        $descripcion .= empty($descripcion)? '' : ' + ';
                        $descripcion .= $a_grupos[$id_grupo];
                    }
                    $oEntradaBypass->setId_grupos($Qa_grupos);
                    $oEntradaBypass->setDescripcion($descripcion);
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
                    $oEntradaBypass->setJson_prot_destino($aProtDst);
                    $oEntradaBypass->setId_grupos();
                    $oEntradaBypass->setDescripcion('x'); // no puede ser null.
                }
                if ($oEntradaBypass->DBGuardar() === FALSE ) {
                    $error_txt .= $oEntradaBypass->getErrorTxt();
                }
                
                if (!empty($error_txt)) {
                    $jsondata['success'] = FALSE;
                    $jsondata['mensaje'] = $error_txt;
                } else {
                    $jsondata['success'] = TRUE;
                }
            } else {
                // borrar si hubiera habido. ( o no?)
            }
        }
        
        
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_entrada'] = $id_entrada;
            $a_cosas = [ 'id_entrada' => $id_entrada, 'filtro' => $Qfiltro];
            $pagina_mod = web\Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query($a_cosas));
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