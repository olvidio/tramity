<?php

use expedientes\model\ArchivarExpediente;
use expedientes\model\CambioAsuntoDeExpediente;
use expedientes\model\CambioEntradillaDeExpediente;
use expedientes\model\CambioTramiteDeExpediente;
use expedientes\model\CambioVidaDeExpediente;
use expedientes\model\CircularExpediente;
use expedientes\model\CopiarExpedienteABorrador;
use expedientes\model\CrearNuevoExpedienteDeEntrada;
use expedientes\model\CrearPendienteDeEntrada;
use expedientes\model\DistribuirExpediente;
use expedientes\model\EliminarExpediente;
use expedientes\model\EncargarExpediente;
use expedientes\model\GuardarExpediente;
use expedientes\model\MoverExpedienteABorrador;
use expedientes\model\CambioEtiquetasYVisisbilidadAExpediente;
use expedientes\model\PonerFechaReunionEnExpediente;
use expedientes\model\PonerVistoEnEntrada;
use expedientes\model\PonerVistoEnExpediente;
use expedientes\model\RecircularExpediente;
use usuarios\model\Visibilidad;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

/*
$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_ponente = (string)filter_input(INPUT_POST, 'ponente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

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
$Q_visibilidad = $Q_visibilidad ?? Visibilidad::V_TODOS;

$error_txt = '';
*/

$Q_que = (string)filter_input(INPUT_POST, 'que');
switch ($Q_que) {
    case 'en_visto': // Copiado de entradas_update.
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        $Qid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
        PonerVistoEnEntrada::en_visto($Qid_entrada);
        break;
    case 'en_pendiente':
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        $Qid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
        $Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
        $Q_visibilidad = empty($Q_visibilidad)? Visibilidad::V_TODOS : $Q_visibilidad;
        (new CrearPendienteDeEntrada) ($Qid_entrada, $Q_visibilidad);
        break;
    case 'en_add_expediente':
        // nada
        break;
    case 'en_expediente':
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        $Qid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
        $Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
        $Q_visibilidad = empty($Q_visibilidad)? Visibilidad::V_TODOS : $Q_visibilidad;
        (new CrearNuevoExpedienteDeEntrada)($Qid_entrada, $Q_visibilidad);
        break;
    case 'encargar_a':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_id_oficial = (integer)filter_input(INPUT_POST, 'id_oficial');
        // Se pone cuando se han enviado...
        (new EncargarExpediente)($Q_id_expediente, $Q_id_oficial);
        break;
    case 'guardar_etiquetas_y_visibilidad':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
        $Q_visibilidad = empty($Q_visibilidad)? Visibilidad::V_TODOS : $Q_visibilidad;
        // Se pone cuando se han enviado...
        (new CambioEtiquetasYVisisbilidadAExpediente)($Q_id_expediente, $Q_a_etiquetas, $Q_visibilidad);
        break;
    case 'recircular':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        // borrar todas la firmas
        (new RecircularExpediente)($Q_id_expediente);
        exit();
    case 'reunion':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_f_reunion = (string)filter_input(INPUT_POST, 'f_reunion');
        (new PonerFechaReunionEnExpediente)($Q_id_expediente, $Q_f_reunion);
        break;
    case 'archivar':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        (new ArchivarExpediente)($Q_id_expediente, $Q_a_etiquetas);
        break;
    case 'distribuir':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        (new DistribuirExpediente)($Q_id_expediente);
        break;
    case 'exp_cp_oficina':
        $copias = TRUE;
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_of_destino = (integer)filter_input(INPUT_POST, 'of_destino');
        (new CopiarExpedienteABorrador)($Q_id_expediente, $Q_of_destino, $copias);
        break;
    case 'exp_cp_copias':
        $copias = TRUE;
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_of_destino = 0;
        (new CopiarExpedienteABorrador)($Q_id_expediente, $Q_of_destino, $copias);
        break;
    case 'exp_cp_borrador':
        $copias = FALSE;
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_of_destino = 0;
        (new CopiarExpedienteABorrador)($Q_id_expediente, $Q_of_destino, $copias);
        break;
    case 'exp_a_borrador_cmb_creador':
    case 'exp_a_borrador':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_que = (string)filter_input(INPUT_POST, 'que');
        (new MoverExpedienteABorrador)($Q_id_expediente, $Q_que);
        break;
    case 'exp_eliminar':
        // Si hay escritos enviados, no se borran.
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        (new EliminarExpediente)($Q_id_expediente);
        break;
    case 'visto':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_a_preparar = (array)filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // yo soy el que hago el click:
        (new PonerVistoEnExpediente)($Q_id_expediente, $Q_a_preparar);
        break;
    case 'circular':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_tramite = (integer)filter_input(INPUT_POST, 'tramite');
        $Q_estado = (integer)filter_input(INPUT_POST, 'estado');
        $Q_prioridad = (integer)filter_input(INPUT_POST, 'prioridad');
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_entradilla = (string)filter_input(INPUT_POST, 'entradilla');
        $Q_f_contestar = (string)filter_input(INPUT_POST, 'f_contestar');
        $Q_ponente = (string)filter_input(INPUT_POST, 'ponente');
        $Q_f_reunion = (string)filter_input(INPUT_POST, 'f_reunion');
        $Q_f_aprobacion = (string)filter_input(INPUT_POST, 'f_aprobacion');
        $Q_a_firmas_oficina = (array)filter_input(INPUT_POST, 'firmas_oficina', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_a_firmas = (array)filter_input(INPUT_POST, 'firmas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_a_preparar = (array)filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_vida = (integer)filter_input(INPUT_POST, 'vida');
        $Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
        $Q_visibilidad = empty($Q_visibilidad)? Visibilidad::V_TODOS : $Q_visibilidad;
        $Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

        // primero se guarda, y al final se guarda la fecha de hoy y se crean las firmas para el trámite
        $RespuestaGuardar = (new GuardarExpediente)('array', $Q_id_expediente, $Q_tramite, $Q_estado, $Q_prioridad, $Q_asunto, $Q_entradilla, $Q_f_contestar, $Q_ponente, $Q_f_reunion, $Q_f_aprobacion, $Q_a_firmas_oficina, $Q_a_firmas, $Q_vida, $Q_visibilidad, $Q_a_preparar, $Q_filtro);
        $error_txt = $RespuestaGuardar['error_txt'];
        $id_expediente = $RespuestaGuardar['id_expediente'];
        if (empty($error_txt)) {
            (new CircularExpediente)($id_expediente, $Q_filtro);
        }
        break;
    case 'guardar':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_tramite = (integer)filter_input(INPUT_POST, 'tramite');
        $Q_estado = (integer)filter_input(INPUT_POST, 'estado');
        $Q_prioridad = (integer)filter_input(INPUT_POST, 'prioridad');
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_entradilla = (string)filter_input(INPUT_POST, 'entradilla');
        $Q_f_contestar = (string)filter_input(INPUT_POST, 'f_contestar');
        $Q_ponente = (string)filter_input(INPUT_POST, 'ponente');
        $Q_f_reunion = (string)filter_input(INPUT_POST, 'f_reunion');
        $Q_f_aprobacion = (string)filter_input(INPUT_POST, 'f_aprobacion');
        $Q_a_firmas_oficina = (array)filter_input(INPUT_POST, 'firmas_oficina', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_a_firmas = (array)filter_input(INPUT_POST, 'firmas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_a_preparar = (array)filter_input(INPUT_POST, 'a_preparar', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_vida = (integer)filter_input(INPUT_POST, 'vida');
        $Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');
        $Q_visibilidad = empty($Q_visibilidad)? Visibilidad::V_TODOS : $Q_visibilidad;
        $Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
        (new GuardarExpediente)('json', $Q_id_expediente, $Q_tramite, $Q_estado, $Q_prioridad, $Q_asunto, $Q_entradilla, $Q_f_contestar, $Q_ponente, $Q_f_reunion, $Q_f_aprobacion, $Q_a_firmas_oficina, $Q_a_firmas, $Q_vida, $Q_visibilidad, $Q_a_preparar, $Q_filtro);
        break;
    case 'cambio_tramite':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_tramite = (integer)filter_input(INPUT_POST, 'tramite');
        (new CambioTramiteDeExpediente) ($Q_id_expediente, $Q_tramite);
        break;
    case 'cambio_vida':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_vida = (integer)filter_input(INPUT_POST, 'vida');
        (new CambioVidaDeExpediente)($Q_id_expediente, $Q_vida);
        break;
    case 'cambio_asunto':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        (new CambioAsuntoDeExpediente)($Q_id_expediente, $Q_asunto);
        break;
    case 'cambio_entradilla':
        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
        $Q_entradilla = (string)filter_input(INPUT_POST, 'entradilla');
        (new CambioEntradillaDeExpediente)($Q_id_expediente, $Q_entradilla);
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}