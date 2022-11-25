<?php

use core\ConfigGlobal;
use davical\model\Davical;
use lugares\model\entity\Lugar;
use pendientes\model\entity\PendienteDB;
use pendientes\model\Pendiente;
use pendientes\model\Rrule;
use web\DateTimeLocal;

/**
 * Esta página actualiza la base de datos del registro.
 *
 * Se le puede pasar la varaible $nueva.
 *    Si es 1 >> inserta una nueva entrada.
 *    Si es 2 >> modifica un pendiente.
 *    Si es 3 >> elimina un pendiente.
 *    Si es 4 >> marca como contestado un pendiente.
 *
 *
 * @package    delegacion
 * @subpackage    registro
 * @author    Daniel Serrabou
 * @since        20/5/03.
 *
 */
// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

// ----------------------------------------------------------------------------------------------
/* Resetear valores iniciales */

$Q_go = (string)filter_input(INPUT_POST, 'go');
$Q_calendario = (string)filter_input(INPUT_POST, 'calendario');
$Q_nuevo = (string)filter_input(INPUT_POST, 'nuevo');
$Q_uid = (string)filter_input(INPUT_POST, 'uid');
$Q_id_reg = (string)filter_input(INPUT_POST, 'id_reg');
$Q_status = (string)filter_input(INPUT_POST, 'status');
$Q_f_inicio = (string)filter_input(INPUT_POST, 'f_inicio');
$Q_f_acabado = (string)filter_input(INPUT_POST, 'f_acabado');
$Q_f_plazo = (string)filter_input(INPUT_POST, 'f_plazo');
$Q_cal_oficina = (string)filter_input(INPUT_POST, 'cal_oficina');
$Q_id_oficina = (string)filter_input(INPUT_POST, 'id_oficina');
if (empty($Q_cal_oficina) && !empty($Q_id_oficina)) { // si soy secretaria puede ser que haya definido la oficina posteriormente
    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
    $Q_cal_oficina = $oDavical->getNombreRecurso($Q_id_oficina);
}

$Q_ref_id_lugar = (string)filter_input(INPUT_POST, 'ref_id_lugar');
$Q_ref_prot_num = (string)filter_input(INPUT_POST, 'ref_prot_num');
$Q_ref_prot_any = (string)filter_input(INPUT_POST, 'ref_prot_any');
$Q_ref_prot_mas = (string)filter_input(INPUT_POST, 'ref_prot_mas');

$Q_observ = (string)filter_input(INPUT_POST, 'observ');
$Q_visibilidad = (string)filter_input(INPUT_POST, 'visibilidad');
$Q_detalle = (string)filter_input(INPUT_POST, 'detalle');
$Q_encargado = (string)filter_input(INPUT_POST, 'encargado');
$Q_pendiente_con = (string)filter_input(INPUT_POST, 'pendiente_con');
$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_simple_per = (string)filter_input(INPUT_POST, 'simple_per');
$rrule = '';

$Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_oficinas = (array)filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_a_exdates = (array)filter_input(INPUT_POST, 'exdates', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);


/* Para mantener las comillas en el asunto */
$trans = get_html_translation_table(HTML_ENTITIES);
$trans = array_flip($trans);
$asunto = strtr($Q_asunto, $trans);

// meter el protocolo en el campo LOCATION (text)
if (!empty($Q_ref_id_lugar)) {
    $oLugar = new Lugar($Q_ref_id_lugar);
    $location = $oLugar->getSigla();
    $location .= empty($Q_ref_prot_num) ? '' : ' ' . $Q_ref_prot_num;
    $location .= empty($Q_ref_prot_any) ? '' : '/' . $Q_ref_prot_any;
} else {
    $location = '';
}

// nombre normalizado del usuario y oficina:
$id_cargo_role = ConfigGlobal::role_id_cargo();
$oDavical = new Davical($_SESSION['oConfig']->getAmbito());
$user_davical = $oDavical->getUsernameDavical($id_cargo_role);

$txt_err = '';
if (!empty($Q_simple_per)) { // sólo para los periodicos.
    $Q_until = (string)filter_input(INPUT_POST, 'until');
    if (!empty($Q_until)) {
        $request['until'] = $Q_until;
    }
    $Q_periodico_tipo = (string)filter_input(INPUT_POST, 'periodico_tipo');
    $Q_tipo_dia = (string)filter_input(INPUT_POST, 'tipo_dia');
    switch ($Q_periodico_tipo) {
        case "periodico_d_a":
            $request['tipo'] = "d_a";
            $request['tipo_dia'] = (string)filter_input(INPUT_POST, 'tipo_dia');
            $request['interval'] = (integer)filter_input(INPUT_POST, 'a_interval');
            switch ($Q_tipo_dia) {
                case "num":
                    $request['dias'] = (string)filter_input(INPUT_POST, 'a_dia_num');
                    $request['meses'] = (string)filter_input(INPUT_POST, 'mes_num');
                    break;
                case "ref":
                    $request['ordinal'] = (string)filter_input(INPUT_POST, 'ordinal_a');
                    $request['dia_semana'] = (string)filter_input(INPUT_POST, 'dia_semana_a');
                    $request['meses'] = (string)filter_input(INPUT_POST, 'mes_num_ref');
                    break;
                case "num_dm":
                    $request['dias'] = (array)filter_input(INPUT_POST, 'dias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    $request['meses'] = (array)filter_input(INPUT_POST, 'meses', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            $rrule = Rrule::montar_rrule($request);
            break;
        case "periodico_d_m":
            $request['tipo'] = "d_m";
            $request['tipo_dia'] = (string)filter_input(INPUT_POST, 'tipo_dia');
            switch ($Q_tipo_dia) {
                case "num_ini":
                    // cojo el dia de la fecha inicio
                    $oF_ini = DateTimeLocal::createFromLocal($Q_f_inicio);
                    $dia = $oF_ini->format('j');
                    $request['dias'] = $dia;
                    break;
                case "num":
                    $request['dias'] = (string)filter_input(INPUT_POST, 'dia_num');
                    break;
                case "ref":
                    $request['ordinal'] = (string)filter_input(INPUT_POST, 'ordinal');
                    $request['dia_semana'] = (string)filter_input(INPUT_POST, 'dia_semana');
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            $rrule = Rrule::montar_rrule($request);
            break;
        case "periodico_d_s":
            $request['tipo'] = "d_s";
            $request['tipo_dia'] = (string)filter_input(INPUT_POST, 'tipo_dia');
            $request['dias'] = (array)filter_input(INPUT_POST, 'dias_w', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $rrule = Rrule::montar_rrule($request);
            break;
        case "periodico_d_d":
            $request['tipo'] = "d_d";
            $rrule = Rrule::montar_rrule($request);
            break;
        case "":
            // No está definido como periódico
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }
}

switch ($Q_nuevo) {
    case "1": //nuevo pendiente
        // si vengo de entradas, primero lo guardo en una tabla temporal hasta que sepa el id_reg
        if ($Q_go === "entradas") {
            if (empty($Q_f_plazo)) {
                $Q_f_plazo = $Q_f_inicio;
            } // En el caso de periodico, no tengo fecha plazo.

            $oPendienteDB = new PendienteDB();
            $oPendienteDB->setAsunto($asunto);
            $oPendienteDB->setStatus($Q_status);
            $oPendienteDB->setF_inicio($Q_f_inicio);
            $oPendienteDB->setF_acabado($Q_f_acabado);
            $oPendienteDB->setF_plazo($Q_f_plazo);
            $oPendienteDB->setRef_mas($Q_ref_prot_mas);
            $oPendienteDB->setObserv($Q_observ);
            $oPendienteDB->setvisibilidad($Q_visibilidad);
            $oPendienteDB->setDetalle($Q_detalle);
            $oPendienteDB->setEncargado($Q_encargado);
            $oPendienteDB->setPendiente_con($Q_pendiente_con);
            $oPendienteDB->setId_oficina($Q_id_oficina);
            $oPendienteDB->setRrule($rrule);
            // las oficinas implicadas:
            $oPendienteDB->setOficinasArray($Q_a_oficinas);
            // las etiquetas:
            $oPendienteDB->setEtiquetasArray($Q_a_etiquetas);
            if ($oPendienteDB->DBGuardar() === FALSE) {
                $txt_err .= $oPendienteDB->getErrorTxt();
            }

            $id_pendiente = $oPendienteDB->getId_pendiente();

            if (strlen($id_pendiente) > 10) {
                $txt_err .= "ERROR: $id_pendiente \n";
            } else {
                $jsondata['id_pendiente'] = $id_pendiente;
                $jsondata['f_plazo'] = $Q_f_plazo;
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
        } else {
            $oPendiente = new Pendiente($Q_cal_oficina, $Q_calendario, $user_davical, $Q_uid);
            $oPendiente->setId_reg($Q_id_reg);
            $oPendiente->setAsunto($asunto);
            $oPendiente->setStatus($Q_status);
            $oPendiente->setF_inicio($Q_f_inicio);
            $oPendiente->setF_acabado($Q_f_acabado);
            $oPendiente->setF_plazo($Q_f_plazo);
            $oPendiente->setRef_prot_mas($Q_ref_prot_mas);
            $oPendiente->setObserv($Q_observ);
            $oPendiente->setvisibilidad($Q_visibilidad);
            $oPendiente->setDetalle($Q_detalle);
            $oPendiente->setEncargado($Q_encargado);
            $oPendiente->setPendiente_con($Q_pendiente_con);
            $oPendiente->setRrule($rrule);
            $oPendiente->setExdatesArray($Q_a_exdates);
            $oPendiente->setLocation($location);
            $oPendiente->setId_oficina($Q_id_oficina);
            // las oficinas implicadas:
            $oPendiente->setOficinasArray($Q_a_oficinas);
            // las etiquetas:
            $oPendiente->setEtiquetasArray($Q_a_etiquetas);
            $aRespuesta = $oPendiente->Guardar();
            if ($aRespuesta['success'] === FALSE) {
                $txt_err .= _("No se han podido guardar el nuevo pendiente");
                $txt_err .= "\n";
                $txt_err .= $aRespuesta['mensaje'];
            } else {
                $jsondata['f_plazo'] = $Q_f_plazo;
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
        }
    case "2": //modificar pendiente
        // 1º actualizo el escrito
        $oPendiente = new Pendiente($Q_cal_oficina, $Q_calendario, $user_davical, $Q_uid);
        if (!empty($Q_id_reg)) {
            $oPendiente->setId_reg($Q_id_reg);
        }
        $oPendiente->Carregar();
        $oPendiente->setAsunto($asunto);
        $oPendiente->setStatus($Q_status);
        $oPendiente->setF_inicio($Q_f_inicio);
        $oPendiente->setF_acabado($Q_f_acabado);
        $oPendiente->setF_plazo($Q_f_plazo);
        $oPendiente->setRef_prot_mas($Q_ref_prot_mas);
        $oPendiente->setObserv($Q_observ);
        $oPendiente->setvisibilidad($Q_visibilidad);
        $oPendiente->setDetalle($Q_detalle);
        $oPendiente->setEncargado($Q_encargado);
        $oPendiente->setPendiente_con($Q_pendiente_con);
        $oPendiente->setRrule($rrule);
        $oPendiente->setExdatesArray($Q_a_exdates);
        $oPendiente->setLocation($location);
        // las oficinas implicadas:
        $oPendiente->setOficinasArray($Q_a_oficinas);
        // las etiquetas:
        $oPendiente->setEtiquetasArray($Q_a_etiquetas);
        $aRespuesta = $oPendiente->Guardar();
        if ($aRespuesta['success'] === FALSE) {
            $txt_err .= _("No se han podido modificar el pendiente");
            $txt_err .= "\n";
            $txt_err .= $aRespuesta['mensaje'];
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
    case "3": //eliminar pendiente.
        //vengo de un checkbox
        $Q_a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($Q_a_sel)) { // puedo seleccionar más de uno.
            foreach ($Q_a_sel as $id) {
                $uid = strtok($id, '#');
                $cal_oficina = strtok('#');
                // miro si es una recursiva de un pendiente:
                $f_recur = strtok('#');
                $oPendiente = new Pendiente($cal_oficina, $Q_calendario, $user_davical, $uid);
                $oPendiente->Carregar();
                $oPendiente->marcar_contestado("eliminar");
            }
        } else {
            $txt_err .= _("No se cual tengo que eliminar.");
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
    case "4": //marcar com contestado
        //vengo de un checkbox
        $Q_a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($Q_a_sel)) { // puedo seleccionar más de uno.
            foreach ($Q_a_sel as $id) {
                $uid = strtok($id, '#');
                $cal_oficina = strtok('#');
                // miro si es una recursiva de un pendiente:
                $f_recur = strtok('#');
                $oPendiente = new Pendiente($cal_oficina, $Q_calendario, $user_davical, $uid);
                $oPendiente->Carregar();
                if (!empty($f_recur)) {
                    $aRespuesta = $oPendiente->marcar_excepcion($f_recur);
                    if ($aRespuesta['success'] === FALSE) {
                        $txt_err .= _("No se han podido marcar como contestado");
                        $txt_err .= "\n";
                        $txt_err .= $aRespuesta['mensaje'];
                    }
                } else {
                    $aRespuesta = $oPendiente->marcar_contestado("contestado");
                    if ($aRespuesta['success'] === FALSE) {
                        $txt_err .= _("No se han podido marcar como contestado");
                        $txt_err .= "\n";
                        $txt_err .= $aRespuesta['mensaje'];
                    }
                }
            }
        } else {
            $txt_err .= _("No se cual tengo que eliminar.");
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
    case "5": // modificar etiquetas y encargados
        $oPendiente = new Pendiente($Q_cal_oficina, $Q_calendario, $user_davical, $Q_uid);
        if (!empty($Q_id_reg)) {
            $oPendiente->setId_reg($Q_id_reg);
        }
        $oPendiente->Carregar();
        // Encargados
        $oPendiente->setEncargado($Q_encargado);
        // las etiquetas:
        $oPendiente->setEtiquetasArray($Q_a_etiquetas);
        $aRespuesta = $oPendiente->Guardar();
        if ($aRespuesta['success'] === FALSE) {
            $txt_err .= _("No se han podido modificar el pendiente");
            $txt_err .= "\n";
            $txt_err .= $aRespuesta['mensaje'];
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
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
} // fin del switch de nuevo.	
