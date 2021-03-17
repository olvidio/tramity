<?php

// INICIO Cabecera global de URL de controlador *********************************
	use core\ViewTwig;
use function core\is_true;
use expedientes\model\Escrito;
use expedientes\model\GestorEscrito;
use expedientes\model\entity\Accion;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use pendientes\model\GestorPendienteEntrada;
use pendientes\model\Pendiente;
use pendientes\model\Rrule;
use usuarios\model\PermRegistro;
use web\DateTimeLocal;
use web\Lista;
use web\Protocolo;
use entradas\model\GestorEntrada;

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
$Qok = (string) \filter_input(INPUT_POST, 'ok');

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
    case 'perm_ver':
        $oEscrito = new Escrito($Qid_escrito);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEscrito,'asunto');
        if ($perm < PermRegistro::PERM_VER) {
            $mensaje = _("No tiene permiso ver el escrito");
        } else {
            $mensaje = '';
        }
        
        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'lista_pendientes':
        $txt_err = '';
        $Qpendientes_uid = (string) \filter_input(INPUT_POST, 'pendientes_uid');
        $Qid_contestados = (string) \filter_input(INPUT_POST, 'id_contestados');
        $txt_err = '';
        $a_pendientes_uid = explode(',', $Qpendientes_uid);
        $a_id_contestados = explode(',', $Qid_contestados);
        
        $a_pendientes_uid_filtered = array_filter($a_pendientes_uid);
        $a_id_contestados_filtered = array_filter($a_id_contestados);
        $a_valores = [];
        $p = 0;
        foreach ($a_pendientes_uid_filtered as $uid_container) {
            if (in_array($uid_container, $a_id_contestados_filtered)) { continue; }
            $f_iso = '';
            $uid = strtok($uid_container, '#');
            $parent_container = strtok('#');
            $oficina = str_replace('oficina_', '' , $parent_container);
            $resource = 'registro';
            $cargo = 'secretaria';
            $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
            $asunto = $oPendiente->getAsunto();
            $protocolo = $oPendiente->getLocation();
            $f_plazo = $oPendiente->getF_plazo()->getFromLocal();

            $rrule = $oPendiente->getRrule();
            if (!empty($rrule)) {
                // calcular las recurrencias que tocan.
                $oF_plazo = new DateTimeLocal(); // + 6 meses
                $interval = new DateInterval('P6M');
                $oF_plazo->add($interval);
                $f_plazo = $oF_plazo->getIsoTime();
                $dtstart=$oPendiente->getF_inicio()->getIso();
                $dtend=$oPendiente->getF_end()->getIso();
                $a_exdates = $oPendiente->getExdates();
                $f_recurrentes = Rrule::recurrencias($rrule, $dtstart, $dtend, $f_plazo);
                //print_r($f_recurrentes);
                $recur = 0;
                foreach ($f_recurrentes as $key => $f_iso) {
                    $oF_recurrente = new DateTimeLocal($f_iso);
                    $recur++;
                    // Quito las excepciones.
                    if (is_array($a_exdates) ){
                        foreach ($a_exdates as $icalprop) {
                            // si hay más de uno separados por coma
                            $a_fechas=preg_split('/,/',$icalprop->content);
                            foreach ($a_fechas as $f_ex) {
                                $oF_exception = new DateTimeLocal($f_ex);
                                if ($oF_recurrente == $oF_exception)  continue(3);
                            }
                        }
                    }
                    $p++;
                    $f_plazo = $oF_recurrente->getFromLocal();
                    $periodico = 'p';

                    $a_valores[$p]['sel']="$uid#$parent_container#$f_iso";
                    $a_valores[$p][1]=$protocolo;
                    $a_valores[$p][2]=$periodico;
                    $a_valores[$p][3]=$asunto;
                    $a_valores[$p][4]=$f_plazo;
                    $a_valores[$p][5]=$oficina;
                }
            } else {
                $p++;
                $periodico = '';
                
                $a_valores[$p]['sel']="$uid#$parent_container#$f_iso";
                $a_valores[$p][1]=$protocolo;
                $a_valores[$p][2]=$periodico;
                $a_valores[$p][3]=$asunto;
                $a_valores[$p][4]=$f_plazo;
                $a_valores[$p][5]=$oficina;
            }
        }
        $a_cabeceras = [ _("protocolo"),
                        _("p"),
                        _("asunto"),
                        _("fecha plazo"),
                        _("oficina"),
                    ];
        $a_botones[]=array( 'txt' => _('marcar como contestado'), 'click' => "fnjs_marcar(\"#seleccionados\");" ) ;
        
        $oTabla = new Lista();
        $oTabla->setId_tabla('pen_tabla');
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($a_botones);
        $oTabla->setDatos($a_valores);
        
        $base_url = core\ConfigGlobal::getWeb();
        
        $a_campos = [
            'base_url' => $base_url,
            'oTabla' => $oTabla,
            'id_escrito' => $Qid_escrito,
            'pendientes_uid' => $Qpendientes_uid,
        ];
        
        $oView = new ViewTwig('pendientes/controller');
        echo $oView->renderizar('pendiente_lista_enviar.html.twig',$a_campos);
        
        
        break;
    case 'contestar_pendientes':
        $Qpendientes_uid = (string) \filter_input(INPUT_POST, 'pendientes_uid');
        $txt_err = '';
        $a_pendientes_uid = explode(',', $Qpendientes_uid);
        $a_pendientes_uid = array_filter($a_pendientes_uid); // evitar valores nulos
        
        foreach ($a_pendientes_uid as $uid_container) {
            $uid = strtok($uid_container, '#');
            $parent_container = strtok('#');
            $resource = 'registro';
            $cargo = 'secretaria';
            $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
            $rrule = $oPendiente->getRrule();
            if (empty($rrule)) {
                $oPendiente->marcar_contestado('contestado');
            } else {
                // los periodicos
                $txt_err .= _("falta definir fecha para periodico");
            }
        }
        
        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = $txt_err;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'comprobar_pendientes':
        $txt_err = '';
        $mensaje = '';
        $oEscrito = new Escrito($Qid_escrito);
        // buscar en los destinos.
        $a_prot_dst = $oEscrito->getJson_prot_destino(TRUE);
        $gesPendientesEntrada = new GestorPendienteEntrada();
        $a_params_dst = $gesPendientesEntrada->getPedientesByProtOrigen($a_prot_dst);
        
        // buscar en las ref.
        $a_prot_ref = $oEscrito->getJson_prot_ref(TRUE);
        $a_params_ref = $gesPendientesEntrada->getPedientesByProtOrigen($a_prot_ref);
        
        // Sumar las dos
        $num_periodicos = $a_params_dst['num_periodicos'] + $a_params_ref['num_periodicos'];
        $num_pendientes = $a_params_dst['num_pendientes'] + $a_params_ref['num_pendientes'];
        $a_lista_pendientes = array_merge($a_params_dst['a_lista_pendientes'], $a_params_ref['a_lista_pendientes']);
        $pendientes_uid = $a_params_dst['pendientes_uid'] .','. $a_params_ref['pendientes_uid'];
        $lista_pendientes = ''; 
        if (!empty($a_lista_pendientes)) {
            $mensaje = _("Es posible que esté relacionado con alguno de estos pendientes:");
            $lista_pendientes = '<ol><li>';
            $lista_pendientes .= implode('</li><li>', $a_lista_pendientes);
            $lista_pendientes .= '</li></ol>';
        }
        if ($num_pendientes == 1) {
            $mensaje = sprintf(_("Tiene %s pendiente asociado"),$num_pendientes);
        }
        
        if (empty($txt_err)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = $mensaje;
            $jsondata['num_periodicos'] = $num_periodicos;
            $jsondata['num_pendientes'] = $num_pendientes;
            $jsondata['lista_pendientes'] = $lista_pendientes;
            $jsondata['pendientes_uid'] = $pendientes_uid;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $txt_err;
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'eliminar':
        $txt_err = '';
        if (!empty($Qid_escrito)) {
            $oEscrito = new Escrito($Qid_escrito);
            if ($oEscrito->DBEliminar() === FALSE ) {
                $txt_err .= _("Hay un error al eliminar el escrito");
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
        break;
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
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEscrito, 'detalle');
        } else {
            $oEscrito = new Escrito();
            $oEscrito->setAccion($Qaccion);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $nuevo = TRUE;
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
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
        $oEscrito->setF_escrito($Qf_escrito);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setAsunto($Qasunto);
        }

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setDetalle($Qdetalle);
        }
        $oEscrito->setCreador($Qid_ponente);
        $oEscrito->setResto_oficinas($Qa_firmas);

        $oEscrito->setCategoria($Qcategoria);
        $oEscrito->setVisibilidad($Qvisibiliad);
        if (is_true($Qok)) {
            $oEscrito->setOK(Escrito::OK_OFICINA);
        } else {
            $oEscrito->setOK(Escrito::OK_NO);
        }

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
    case 'explotar':
        $txt_err = '';
        if (!empty($Qid_escrito)) {
            $oEscrito = new Escrito($Qid_escrito);
            $oEscrito->DBCarregar();
        } else {
            $txt_err .= _("No puede ser");
        }
        
        // por cada destino
        if ($oEscrito->explotar() !== TRUE) {
            $txt_err .= _("Algún error al explotar");
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
    case 'guardar_manual':
        $nuevo = FALSE;
        $Qf_aprobacion = (string) \filter_input(INPUT_POST, 'f_aprobacion');
        if (!empty($Qid_escrito)) {
            $oEscrito = new Escrito($Qid_escrito);
            $oEscrito->DBCarregar();
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEscrito, 'detalle');
        } else {
            $nuevo = TRUE;
            $oEscrito = new Escrito();
            $oEscrito->setAccion(Escrito::ACCION_ESCRITO);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }
        
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
        
        $oEscrito->setEntradilla($Qentradilla);
        $oEscrito->setF_escrito($Qf_escrito);
        $oEscrito->setF_aprobacion($Qf_aprobacion);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setAsunto($Qasunto);
        }

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setDetalle($Qdetalle);
        }
        $oEscrito->setCreador($Qid_ponente);
        $oEscrito->setResto_oficinas($Qa_firmas);

        $oEscrito->setCategoria($Qcategoria);
        $oEscrito->setVisibilidad($Qvisibiliad);
        
        if ($nuevo === TRUE) {
            $oEscrito->setOK(Escrito::OK_NO);
        } else {
            $oEscrito->setOK(Escrito::OK_OFICINA);
        }

        $oEscrito->DBGuardar(); // OJO hay que guardar antes de generar el protocolo
        if ($nuevo === TRUE) {
            $oEscrito->generarProtocolo();
        }
        
        $id_escrito = $oEscrito->getId_escrito();
        $json_prot_local = $oEscrito->getJson_prot_local();
        $oProtocolo = new Protocolo();
        $oProtocolo->setJson($json_prot_local);
        $protocolo_txt = $oProtocolo->ver_txt();
        
        $jsondata['success'] = true;
        $jsondata['id_escrito'] = $id_escrito;
        $jsondata['protocolo'] = $protocolo_txt;
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
    case 'buscar_entrada_correspondiente':
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
        
        $Qprot_any = core\any_2($Qprot_any);
        
        $aProt_origen = [ 'lugar' => $Qid_lugar,
            'num' => $Qprot_num,
            'any' => $Qprot_any,
        ];
        
        $id_entrada = '';
        $gesEntradas = new GestorEntrada();
        $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
        foreach ($cEntradas as $oEntrada) {
            $bypass = $oEntrada->getBypass();
            if ($bypass) continue;
            $id_entrada = $oEntrada->getId_entrada();
        }
                
        if (!empty($id_entrada)) {
            $jsondata['success'] = true;
            $jsondata['id_entrada'] = $id_entrada;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = _("No se...");
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;

}