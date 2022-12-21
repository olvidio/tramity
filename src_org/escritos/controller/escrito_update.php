<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use davical\model\Davical;
use escritos\domain\entity\Escrito;
use escritos\domain\entity\EscritoDB;
use escritos\domain\repositories\EscritoRepository;
use etherpad\model\Etherpad;
use expedientes\domain\entity\Accion;
use expedientes\domain\repositories\AccionRepository;
use lugares\domain\repositories\GrupoRepository;
use pendientes\model\GestorPendienteEntrada;
use pendientes\model\Pendiente;
use pendientes\model\Rrule;
use usuarios\domain\PermRegistro;
use web\DateTimeLocal;
use web\Hash;
use web\Lista;
use web\Protocolo;
use function core\is_true;

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
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

/* convertir las fechas a DateTimeLocal */
$oF_escrito = DateTimeLocal::createFromLocal($Q_f_escrito);
$oF_plazo = DateTimeLocal::createFromLocal($Q_f_plazo);

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
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        $accion = ($oEscrito->getAccion() === 1) ? 2 : 1;
        $oEscrito->setAccion($accion);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            $error_txt .= $oEscrito->getErrorTxt();
        }
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $Q_id_expediente, 'id_escrito' => $Q_id_escrito]);
        // solamente debería haber uno
        $oAccion = $cAcciones[0];
        if ($oAccion === null) {
            $err_cargar = sprintf(_("OJO! no existe la acción en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oAccion->setTipo_accion($accion);
        if ($AccionRepository->Guardar($oAccion) === FALSE) {
            $error_txt .= $AccionRepository->getErrorTxt();
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
        $Q_detalle = (string)filter_input(INPUT_POST, 'text');

        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        if ($oEscrito === null) {
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setDetalle($Q_detalle);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            $error_txt = $escritoRepository->getErrorTxt();
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
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEscrito, 'detalle');
        if ($perm < PermRegistro::PERM_MODIFICAR) {
            $mensaje = _("No tiene permiso para modificar el detalle");
            $detalle = '';
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
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
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

        $base_url = ConfigGlobal::getWeb();

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
                // los periódicos
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
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
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
        if ($num_pendientes === 1) {
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
            $escritoRepository = new EscritoRepository();
            $oEscrito = $escritoRepository->findById($Q_id_escrito);
            // Sólo se puede eliminar si no se ha enviado, o si es secretaría
            // Si se ha enviado se puede quitar del expediente:
            $f_salida = $oEscrito->getF_salida()->getIso();
            if (empty($f_salida) || ConfigGlobal::role_actual() === 'secretaria') {
                $tipo_doc = $oEscrito->getTipo_doc();
                // borrar el Etherpad
                if ($tipo_doc === EscritoDB::TIPO_ETHERPAD) {
                    $oNewEtherpad = new Etherpad();
                    $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $Q_id_escrito);
                    $oNewEtherpad->eliminarPad();
                }

                if ($escritoRepository->Eliminar($oEscrito) === FALSE) {
                    $txt_err .= _("Hay un error al eliminar el escrito");
                    $txt_err .= "<br>";
                }
            } else {
                // Si está dentro de un expediente, lo quito:
                if (!empty($Q_id_expediente)) {
                    $AccionRepository = new AccionRepository();
                    $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $Q_id_expediente, 'id_escrito' => $Q_id_escrito]);
                    // debería existir sólo uno
                    $oAccion = $cAcciones[0];
                    if ($AccionRepository->Eliminar($oAccion) === FALSE) {
                        $txt_err .= _("Hay un error al quitar el escrito de expediente");
                        $txt_err .= "<br>";
                        $txt_err .= $AccionRepository->getErrorTxt();
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
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        if ($oEscrito === null) {
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setComentarios('');
        $oEscrito->setOK(EscritoDB::OK_OFICINA);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            exit($oEscrito->getErrorTxt());
        }
        break;
    case 'escrito_a_oficina':
        $Q_comentario = (string)filter_input(INPUT_POST, 'comentario');
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        if ($oEscrito === null) {
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setComentarios($Q_comentario);
        $oEscrito->setOK(EscritoDB::OK_NO);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            exit($escritoRepository->getErrorTxt());
        }
        break;
    case 'tipo_doc':
        $Q_tipo_doc = (integer)filter_input(INPUT_POST, 'tipo_doc');
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        if ($oEscrito === null) {
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setTipo_doc($Q_tipo_doc);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            exit($escritoRepository->getErrorTxt());
        }

        break;
    case 'f_escrito':
        if ($Q_f_escrito === 'hoy') {
            $oF_escrito = new DateTimeLocal();
        }
        $escritoRepository = new EscritoRepository();
        $oEscrito = $escritoRepository->findById($Q_id_escrito);
        if ($oEscrito === null) {
            $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEscrito->setF_escrito($oF_escrito);
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            exit($escritoRepository->getErrorTxt());
        }
        break;
    case 'guardar_asunto':
        $txt_err = '';
        if (!empty($Q_id_escrito)) {
            $escritoRepository = new EscritoRepository();
            $oEscrito = $escritoRepository->findById($Q_id_escrito);
            if ($oEscrito === null) {
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
            if ($escritoRepository->Guardar($oEscrito) === FALSE) {
                $txt_err .= _("Hay un error al guardar el escrito");
                $txt_err .= "<br>";
                $txt_err .= $escritoRepository->getErrorTxt();
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
            $escritoRepository = new EscritoRepository();
            $oEscrito = $escritoRepository->findById($Q_id_escrito);
            if ($oEscrito === null) {
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEscrito, 'detalle');
        } else {
            $escritoRepository = new EscritoRepository();
            $id_escrito = $escritoRepository->getNewId_escrito();
            $oEscrito = new Escrito();
            $oEscrito->setId_escrito($id_escrito);
            $oEscrito->setAccion($Q_accion);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $nuevo = TRUE;
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }

        if ($Q_accion === Escrito::ACCION_ESCRITO) {
            // Si esta marcado como grupo de destinos, o destinos individuales. 
            if (is_true($Q_grupo_dst)) {
                $descripcion = '';
                $saltar = FALSE;
                $GrupoRepository = new GrupoRepository();
                $a_grupos = $GrupoRepository->getArrayGrupos();
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
                    $oEscrito->setDestinos();
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
                $oEscrito->setDestinos();
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

        $oEscrito->setF_escrito($oF_escrito);
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
                $oEscrito->setF_contestar(null);
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
                $oEscrito->setF_contestar($oF_plazo);
                break;
            default:
                // Si no hay $Q_plazo, No pongo ninguna fecha a contestar
        }

        if (is_true($Q_ok)) {
            $oEscrito->setComentarios('');
            $oEscrito->setOK(EscritoDB::OK_OFICINA);
        } else {
            $oEscrito->setOK(EscritoDB::OK_NO);
        }

        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            $error_txt .= $escritoRepository->getErrorTxt();
        }

        $id_escrito = $oEscrito->getId_escrito();

        if ($nuevo === TRUE) {
            $AccionRepository = new AccionRepository();
            $id_item = $AccionRepository->getNewId_item();
            $oAccion = new Accion();
            $oAccion->setId_item($id_item);
            $oAccion->setId_expediente($Q_id_expediente);
            $oAccion->setId_escrito($id_escrito);
            $oAccion->setTipo_accion($Q_accion);
            if ($AccionRepository->Guardar($oAccion) === FALSE) {
                $error_txt .= $AccionRepository->getErrorTxt();
            }
        }

        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['id_escrito'] = $id_escrito;
            $a_cosas = ['id_escrito' => $id_escrito, 'filtro' => $Q_filtro, 'id_expediente' => $Q_id_expediente];
            $pagina_mod = Hash::link('src/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
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
            $escritoRepository = new EscritoRepository();
            $oEscrito = $escritoRepository->findById($Q_id_escrito);
            if ($oEscrito === null) {
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            // por cada destino
            if ($oEscrito->explotar() !== TRUE) {
                $txt_err .= _("Algún error al explotar");
            }
        } else {
            $txt_err .= _("No puede ser");
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
        $oF_aprobacion = DateTimeLocal::createFromLocal($Q_f_aprobacion);
        $escritoRepository = new EscritoRepository();
        if (!empty($Q_id_escrito)) {
            $oEscrito = $escritoRepository->findById($Q_id_escrito);
            if ($oEscrito === null) {
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEscrito, 'detalle');
        } else {
            $nuevo = TRUE;
            $id_escrito = $escritoRepository->getNewId_escrito();
            $oEscrito = new Escrito();
            $oEscrito->setId_escrito($id_escrito);
            $oEscrito->setAccion(Escrito::ACCION_ESCRITO);
            $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }

        // Si esta marcado como grupo de destinos, o destinos individuales. 
        if (is_true($Q_grupo_dst)) {
            $descripcion = '';
            $saltar = FALSE;
            $GrupoRepository = new GrupoRepository();
            $a_grupos = $GrupoRepository->getArrayGrupos();
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
                $oEscrito->setDestinos();
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

        $oEscrito->setF_escrito($oF_escrito);
        $oEscrito->setF_aprobacion($oF_aprobacion);
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
            $oEscrito->setOK(EscritoDB::OK_NO);
        } else {
            $oEscrito->setOK(EscritoDB::OK_OFICINA);
        }

        if ($nuevo === TRUE) {
            $oEscrito->generarProtocolo();
        }
        if ($escritoRepository->Guardar($oEscrito) === FALSE) {
            exit($escritoRepository->getErrorTxt());
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