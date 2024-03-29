<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use davical\model\Davical;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use expedientes\model\entity\Accion;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorGrupo;
use pendientes\model\GestorPendienteEntrada;
use pendientes\model\Pendiente;
use pendientes\model\Rrule;
use usuarios\model\PermRegistro;
use web\DateTimeLocal;
use web\Lista;
use web\Protocolo;
use function core\is_true;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
$Q_accion = (integer)filter_input(INPUT_POST, 'accion');

$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_f_escrito = (string)filter_input(INPUT_POST, 'f_escrito');

$Q_detalle = (string)filter_input(INPUT_POST, 'detalle');
$Q_id_ponente = (integer)filter_input(INPUT_POST, 'id_ponente');
$Q_a_firmas = (array)filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Q_categoria = (integer)filter_input(INPUT_POST, 'categoria');
$Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
$Q_visibilidad_dst = (integer)filter_input(INPUT_POST, 'visibilidad_dst');
$Q_plazo = (string)filter_input(INPUT_POST, 'plazo');
$Q_f_plazo = (string)filter_input(INPUT_POST, 'f_plazo');
$Q_ok = (string)filter_input(INPUT_POST, 'ok');

$Q_grupo_dst = (string)filter_input(INPUT_POST, 'grupo_dst');
// genero un vector con todos los grupos.
$Q_a_grupos = (array)filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Q_a_destinos = (array)filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_num_destinos = (array)filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_any_destinos = (array)filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_mas_destinos = (array)filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Q_a_referencias = (array)filter_input(INPUT_POST, 'referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_num_referencias = (array)filter_input(INPUT_POST, 'prot_num_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_any_referencias = (array)filter_input(INPUT_POST, 'prot_any_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_prot_mas_referencias = (array)filter_input(INPUT_POST, 'prot_mas_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

switch ($Q_que) {
    case 'conmutar':
        $error_txt = '';
        $oEscrito = new Escrito($Q_id_escrito);
        $accion = ($oEscrito->getAccion() === 1)? 2 : 1;
        $oEscrito->setAccion($accion);
        if ($oEscrito->DBGuardar() === FALSE) {
            $error_txt .= $oEscrito->getErrorTxt();
        }
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $Q_id_expediente, 'id_escrito' => $Q_id_escrito]);
        // solamente debería haber uno
        $oAccion = $cAcciones[0];
        if ($oAccion->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe la acción en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oAccion->setTipo_accion($accion);
        if ($oAccion->DBGuardar() === FALSE) {
            $error_txt .= $oAccion->getErrorTxt();
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
    case 'modificar_detalle':
        $error_txt = '';
        $oEscrito = new Escrito($Q_id_escrito);
        $Q_detalle = (string)filter_input(INPUT_POST, 'text');
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setDetalle($Q_detalle);
        if ($oEscrito->DBGuardar() === FALSE) {
            $error_txt = $oEscrito->getErrorTxt();
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
    case 'get_detalle':
        $oEscrito = new Escrito($Q_id_escrito);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEscrito, 'detalle');
        if ($perm < PermRegistro::PERM_MODIFICAR) {
            $mensaje = _("No tiene permiso para modificar el detalle");
        } else {
            $detalle = $oEscrito->getDetalle();
            $mensaje = '';
        }

        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['detalle'] = $detalle;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'perm_ver':
        $oEscrito = new Escrito($Q_id_escrito);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEscrito, 'escrito');
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
    case 'lista_pendientes':
        $txt_err = '';
        $Q_pendientes_uid = (string)filter_input(INPUT_POST, 'pendientes_uid');
        $Q_id_contestados = (string)filter_input(INPUT_POST, 'id_contestados');
        $txt_err = '';
        $a_pendientes_uid = explode(',', $Q_pendientes_uid);
        $a_id_contestados = explode(',', $Q_id_contestados);

        $a_pendientes_uid_filtered = array_filter($a_pendientes_uid);
        $a_id_contestados_filtered = array_filter($a_id_contestados);
        $a_valores = [];
        $p = 0;
        foreach ($a_pendientes_uid_filtered as $uid_container) {
            if (in_array($uid_container, $a_id_contestados_filtered)) {
                continue;
            }
            $f_iso = '';
            $uid = strtok($uid_container, '#');
            $parent_container = strtok('#');
            $oficina = str_replace('oficina_', '', $parent_container);
            $calendario = 'registro';
            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $user_davical = $oDavical->getUsernameDavicalSecretaria();
            $oPendiente = new Pendiente($parent_container, $calendario, $user_davical, $uid);
            $asunto = $oPendiente->getAsunto();
            $protocolo = $oPendiente->getProtocolo();
            $f_plazo = $oPendiente->getF_plazo()->getFromLocal();

            $rrule = $oPendiente->getRrule();
            if (!empty($rrule)) {
                // calcular las recurrencias que tocan.
                $oF_plazo = new DateTimeLocal(); // + 6 meses
                $interval = new DateInterval('P6M');
                $oF_plazo->add($interval);
                $f_plazo = $oF_plazo->getIsoTime();
                $dtstart = $oPendiente->getF_inicio()->getIso();
                $dtend = $oPendiente->getF_end()->getIso();
                $a_exdates = $oPendiente->getExdates();
                $f_recurrentes = Rrule::recurrencias($rrule, $dtstart, $dtend, $f_plazo);
                $recur = 0;
                foreach ($f_recurrentes as $key => $f_iso) {
                    $oF_recurrente = new DateTimeLocal($f_iso);
                    $recur++;
                    // Quito las excepciones.
                    if (is_array($a_exdates)) {
                        foreach ($a_exdates as $icalprop) {
                            // si hay más de uno separados por coma
                            $a_fechas = explode(',', $icalprop->content);
                            foreach ($a_fechas as $f_ex) {
                                $oF_exception = new DateTimeLocal($f_ex);
                                if ($oF_recurrente == $oF_exception) {
                                    continue(3);
                                }
                            }
                        }
                    }
                    $p++;
                    $f_plazo = $oF_recurrente->getFromLocal();
                    $periodico = 'p';

                    $a_valores[$p]['sel'] = "$uid#$parent_container#$f_iso";
                    $a_valores[$p][1] = $protocolo;
                    $a_valores[$p][2] = $periodico;
                    $a_valores[$p][3] = $asunto;
                    $a_valores[$p][4] = $f_plazo;
                    $a_valores[$p][5] = $oficina;
                }
            } else {
                $p++;
                $periodico = '';

                $a_valores[$p]['sel'] = "$uid#$parent_container#$f_iso";
                $a_valores[$p][1] = $protocolo;
                $a_valores[$p][2] = $periodico;
                $a_valores[$p][3] = $asunto;
                $a_valores[$p][4] = $f_plazo;
                $a_valores[$p][5] = $oficina;
            }
        }
        $a_cabeceras = [_("protocolo"),
            _("p"),
            _("asunto"),
            _("fecha plazo"),
            _("oficina"),
        ];
        //$a_botones[]=array( 'txt' => _('marcar como contestado'), 'click' => "fnjs_marcar(\"#seleccionados\");" ) ;
        $a_botones = 'ninguno'; // para que si ponga los checkboxs

        $oTabla = new Lista();
        $oTabla->setId_tabla('pen_tabla');
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($a_botones);
        $oTabla->setDatos($a_valores);

        $base_url = core\ConfigGlobal::getWeb();

        $a_campos = [
            'base_url' => $base_url,
            'oTabla' => $oTabla,
            'id_escrito' => $Q_id_escrito,
            'pendientes_uid' => $Q_pendientes_uid,
        ];

        $oView = new ViewTwig('pendientes/controller');
        $oView->renderizar('pendiente_lista_enviar.html.twig', $a_campos);


        break;
    case 'contestar_pendientes':
        $Q_pendientes_uid = (string)filter_input(INPUT_POST, 'pendientes_uid');
        $txt_err = '';
        $a_pendientes_uid = explode(',', $Q_pendientes_uid);
        $a_pendientes_uid = array_filter($a_pendientes_uid); // evitar valores nulos

        foreach ($a_pendientes_uid as $uid_container) {
            $uid = strtok($uid_container, '#');
            $parent_container = strtok('#');
            $calendario = 'registro';
            $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
            $user_davical = $oDavical->getUsernameDavicalSecretaria();
            $oPendiente = new Pendiente($parent_container, $calendario, $user_davical, $uid);
            $rrule = $oPendiente->getRrule();
            if (empty($rrule)) {
                $aRespuesta = $oPendiente->marcar_contestado("contestado");
                if ($aRespuesta['success'] === FALSE) {
                    $txt_err .= _("No se han podido marcar como contestado");
                    $txt_err .= "\n";
                    $txt_err .= $aRespuesta['mensaje'];
                }
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
    case 'comprobar_pendientes':
        $txt_err = '';
        $mensaje = '';
        $oEscrito = new Escrito($Q_id_escrito);
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
        $pendientes_uid = $a_params_dst['pendientes_uid'];
        $pendientes_uid .= empty($pendientes_uid) ? '' : ',';
        $pendientes_uid .= $a_params_ref['pendientes_uid'];
        $lista_pendientes = '';
        if (!empty($a_lista_pendientes)) {
            $mensaje = _("Es posible que esté relacionado con alguno de estos pendientes:");
            $lista_pendientes = '<ol><li>';
            $lista_pendientes .= implode('</li><li>', $a_lista_pendientes);
            $lista_pendientes .= '</li></ol>';
        }
        if ($num_pendientes == 1) {
            $mensaje = sprintf(_("Tiene %s pendiente asociado"), $num_pendientes);
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
    case 'eliminar':
        $txt_err = '';
        if (!empty($Q_id_escrito)) {
            $oEscrito = new Escrito($Q_id_escrito);
            // Sólo se puede eliminar si no se ha enviado, o si es secretaría 
            // Si se ha enviado se puede quitar del expediente:
            $f_salida = $oEscrito->getF_salida()->getIso();
            if (empty($f_salida) || ConfigGlobal::role_actual() === 'secretaria') {
                $tipo_doc = $oEscrito->getTipo_doc();
                // borrar el Etherpad
                if ($tipo_doc == Escrito::TIPO_ETHERPAD) {
                    $oNewEtherpad = new Etherpad();
                    $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $Q_id_escrito);
                    $oNewEtherpad->eliminarPad();
                }

                if ($oEscrito->DBEliminar() === FALSE) {
                    $txt_err .= _("Hay un error al eliminar el escrito");
                    $txt_err .= "<br>";
                }
            } else {
                // Si está dentro de un expediente, lo quito:
                if (!empty($Q_id_expediente)) {
                    $gesAcciones = new GestorAccion();
                    $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $Q_id_expediente, 'id_escrito' => $Q_id_escrito]);
                    // debería existir sólo uno
                    $oAccion = $cAcciones[0];
                    if ($oAccion->DBEliminar() === FALSE) {
                        $txt_err .= _("Hay un error al quitar el escrito de expediente");
                        $txt_err .= "<br>";
                        $txt_err .= $oAccion->getErrorTxt();
                    }
                }
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
    case 'escrito_a_secretaria':
        $oEscrito = new Escrito($Q_id_escrito);
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setComentarios('');
        $oEscrito->setOK(Escrito::OK_OFICINA);
        if ($oEscrito->DBGuardar() === FALSE) {
            exit($oEscrito->getErrorTxt());
        }
        break;
    case 'escrito_a_oficina':
        $Q_comentario = (string)filter_input(INPUT_POST, 'comentario');
        $oEscrito = new Escrito($Q_id_escrito);
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setComentarios($Q_comentario);
        $oEscrito->setOK(Escrito::OK_NO);
        if ($oEscrito->DBGuardar() === FALSE) {
            exit($oEscrito->getErrorTxt());
        }
        break;
    case 'tipo_doc':
        $Q_tipo_doc = (integer)filter_input(INPUT_POST, 'tipo_doc');
        $oEscrito = new Escrito($Q_id_escrito);
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setTipo_doc($Q_tipo_doc);
        if ($oEscrito->DBGuardar() === FALSE) {
            exit($oEscrito->getErrorTxt());
        }

        break;
    case 'f_escrito':
        if ($Q_f_escrito === 'hoy') {
            $oHoy = new DateTimeLocal();
            $Q_f_escrito = $oHoy->getFromLocal();
        }
        $oEscrito = new Escrito($Q_id_escrito);
        if ($oEscrito->DBCargar() === FALSE ){
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setF_escrito($Q_f_escrito);
        if ($oEscrito->DBGuardar() === FALSE) {
            exit($oEscrito->getErrorTxt());
        }
        break;
    case 'guardar_asunto':
        $txt_err = '';
        if (!empty($Q_id_escrito)) {
            $oEscrito = new Escrito($Q_id_escrito);
            if ($oEscrito->DBCargar() === FALSE ){
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $Q_anular = (string)filter_input(INPUT_POST, 'anular');

            if (is_true($Q_anular)) {
                $oEscrito->setAnulado('t');
            } else {
                $oEscrito->setAnulado('f');
            }
            $oEscrito->setAsunto($Q_asunto);
            $oEscrito->setDetalle($Q_detalle);
            if ($oEscrito->DBGuardar() === FALSE) {
                $txt_err .= _("Hay un error al guardar el escrito");
                $txt_err .= "<br>";
                $txt_err .= $oEscrito->getErrorTxt();
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
        $error_txt = '';
        $nuevo = FALSE;
        if (!empty($Q_id_escrito)) {
            $oEscrito = new Escrito($Q_id_escrito);
            if ($oEscrito->DBCargar() === FALSE ){
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEscrito, 'detalle');
        } else {
            $oEscrito = new Escrito();
            $oEscrito->setAccion($Q_accion);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $nuevo = TRUE;
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }

        if ($Q_accion === Escrito::ACCION_ESCRITO) {
            // Si esta marcado como grupo de destinos, o destinos individuales. 
            if (core\is_true($Q_grupo_dst)) {
                $descripcion = '';
                $saltar = FALSE;
                $gesGrupo = new GestorGrupo();
                $a_grupos = $gesGrupo->getArrayGrupos();
                foreach ($Q_a_grupos as $id_grupo) {
                    // si es personalizado, no cambio nada porque ya se ha guardado al personalizar
                    if ($id_grupo === 'custom') {
                        $saltar = TRUE;
                        break;
                    }
                    $descripcion .= empty($descripcion) ? '' : ' + ';
                    $descripcion .= $a_grupos[$id_grupo];
                }
                if ($saltar === FALSE) {
                    $oEscrito->setId_grupos($Q_a_grupos);
                    // borro las posibles personalizaciones:
                    $oEscrito->setDestinos('');
                    $oEscrito->setDescripcion('');
                }
                // borro los individuales
                $oEscrito->setJson_prot_destino([]);
            } else {
                $aProtDst = [];
                foreach ($Q_a_destinos as $key => $id_lugar) {
                    $prot_num = $Q_a_prot_num_destinos[$key];
                    $prot_any = $Q_a_prot_any_destinos[$key];
                    $prot_mas = $Q_a_prot_mas_destinos[$key];

                    if (!empty($id_lugar)) {
                        $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                        $aProtDst[] = $oProtDst->getProt();
                    }
                }
                $oEscrito->setJson_prot_destino($aProtDst);
                $oEscrito->setId_grupos();
                // borro las posibles personalizaciones:
                $oEscrito->setDestinos('');
                $oEscrito->setDescripcion('');
            }

            $aProtRef = [];
            foreach ($Q_a_referencias as $key => $id_lugar) {
                $prot_num = $Q_a_prot_num_referencias[$key];
                $prot_any = $Q_a_prot_any_referencias[$key];
                $prot_mas = $Q_a_prot_mas_referencias[$key];

                if (!empty($id_lugar)) {
                    $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $aProtRef[] = $oProtRef->getProt();
                }
            }
            $oEscrito->setJson_prot_ref($aProtRef);
        }

        $oEscrito->setF_escrito($Q_f_escrito);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setAsunto($Q_asunto);
        }

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setDetalle($Q_detalle);
        }
        $oEscrito->setCreador($Q_id_ponente);
        $oEscrito->setResto_oficinas($Q_a_firmas);

        $oEscrito->setCategoria($Q_categoria);
        // visibilidad: puede que esté en modo solo lectura, mirar el hiden.
        if (empty($Q_visibilidad)) {
            $Q_visibilidad = (integer)filter_input(INPUT_POST, 'hidden_visibilidad');
        }
        $oEscrito->setVisibilidad($Q_visibilidad);
        $oEscrito->setVisibilidad_dst($Q_visibilidad_dst);

        switch ($Q_plazo) {
            case 'hoy':
                $oEscrito->setF_contestar('');
                break;
            case 'normal':
                $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
                $periodo = 'P' . $plazo_normal . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEscrito->setF_contestar($oF);
                break;
            case 'rápido':
                $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
                $periodo = 'P' . $plazo_rapido . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEscrito->setF_contestar($oF);
                break;
            case 'urgente':
                $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
                $periodo = 'P' . $plazo_urgente . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEscrito->setF_contestar($oF);
                break;
            case 'fecha':
                $oEscrito->setF_contestar($Q_f_plazo);
                break;
            default:
                // Si no hay $Q_plazo, No pongo ninguna fecha a contestar
        }

        if (is_true($Q_ok)) {
            $oEscrito->setComentarios('');
            $oEscrito->setOK(Escrito::OK_OFICINA);
        } else {
            $oEscrito->setOK(Escrito::OK_NO);
        }

        if ($oEscrito->DBGuardar() === FALSE) {
            $error_txt .= $oEscrito->getErrorTxt();
        }

        $id_escrito = $oEscrito->getId_escrito();

        // ctr_correo: Se crea directamente el escrito sin expediente.
        if ($nuevo === TRUE && !empty($Q_id_expediente)) {
            $oAccion = new Accion();
            $oAccion->setId_expediente($Q_id_expediente);
            $oAccion->setId_escrito($id_escrito);
            $oAccion->setTipo_accion($Q_accion);
            if ($oAccion->DBGuardar() === FALSE) {
                $error_txt .= $oAccion->getErrorTxt();
            }
        }

        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['id_escrito'] = $id_escrito;
            $a_cosas = ['id_escrito' => $id_escrito, 'filtro' => $Q_filtro, 'id_expediente' => $Q_id_expediente];
            $pagina_mod = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'explotar':
        $txt_err = '';
        if (!empty($Q_id_escrito)) {
            $oEscrito = new Escrito($Q_id_escrito);
            if ($oEscrito->DBCargar() === FALSE ){
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
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
    case 'guardar_manual':
        $nuevo = FALSE;
        $Q_f_aprobacion = (string)filter_input(INPUT_POST, 'f_aprobacion');
        if (!empty($Q_id_escrito)) {
            $oEscrito = new Escrito($Q_id_escrito);
            if ($oEscrito->DBCargar() === FALSE ){
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
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
        if (core\is_true($Q_grupo_dst)) {
            $descripcion = '';
            $saltar = FALSE;
            $gesGrupo = new GestorGrupo();
            $a_grupos = $gesGrupo->getArrayGrupos();
            foreach ($Q_a_grupos as $id_grupo) {
                // si es personalizado, no cambio nada porque ya se ha guardado al personalizar
                if ($id_grupo === 'custom') {
                    $saltar = TRUE;
                    break;
                }
                $descripcion .= empty($descripcion) ? '' : ' + ';
                $descripcion .= $a_grupos[$id_grupo];
            }
            if ($saltar === FALSE) {
                $oEscrito->setId_grupos($Q_a_grupos);
                // borro las posibles personalizaciones:
                $oEscrito->setDestinos('');
                $oEscrito->setDescripcion('');
            }
        } else {
            $aProtDst = [];
            foreach ($Q_a_destinos as $key => $id_lugar) {
                $prot_num = $Q_a_prot_num_destinos[$key];
                $prot_any = $Q_a_prot_any_destinos[$key];
                $prot_mas = $Q_a_prot_mas_destinos[$key];

                if (!empty($id_lugar)) {
                    $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $aProtDst[] = $oProtDst->getProt();
                }
            }
            $oEscrito->setJson_prot_destino($aProtDst);
            $oEscrito->setId_grupos();
        }

        $aProtRef = [];
        foreach ($Q_a_referencias as $key => $id_lugar) {
            $prot_num = $Q_a_prot_num_referencias[$key];
            $prot_any = $Q_a_prot_any_referencias[$key];
            $prot_mas = $Q_a_prot_mas_referencias[$key];

            if (!empty($id_lugar)) {
                $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                $aProtRef[] = $oProtRef->getProt();
            }
        }
        $oEscrito->setJson_prot_ref($aProtRef);

        $oEscrito->setF_escrito($Q_f_escrito);
        $oEscrito->setF_aprobacion($Q_f_aprobacion);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setAsunto($Q_asunto);
        }

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEscrito->setDetalle($Q_detalle);
        }
        $oEscrito->setCreador($Q_id_ponente);
        $oEscrito->setResto_oficinas($Q_a_firmas);

        $oEscrito->setCategoria($Q_categoria);
        $oEscrito->setVisibilidad($Q_visibilidad);

        if ($nuevo === TRUE) {
            $oEscrito->setOK(Escrito::OK_NO);
        } else {
            $oEscrito->setOK(Escrito::OK_OFICINA);
        }

        // OJO hay que guardar antes de generar el protocolo
        if ($oEscrito->DBGuardar() === FALSE) {
            exit($oEscrito->getErrorTxt());
        }
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
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}