<?php

use core\ConfigGlobal;
use entradas\model\Entrada;
use escritos\model\Escrito;
use escritos\model\EscritoForm;
use expedientes\model\entity\Accion;
use expedientes\model\Expediente;
use tramites\model\entity\GestorTramite;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
//$Q_filtro = (string) filter_input(INPUT_POST, 'filtro');
// cambio el filtro para ver los borradores:
$filtro = 'borrador_propio';
$modo = 'mod';

$oEntrada = new Entrada($Q_id_entrada);
$f_contestar = $oEntrada->getF_contestar();

// crear el expediente
// valores por defecto:
$ponente = ConfigGlobal::role_id_cargo();
$estado = Expediente::ESTADO_BORRADOR;
$prioridad = Expediente::PRIORIDAD_NORMAL;
$vida = Expediente::VIDA_NORMAL;
$asunto = _("Respuesta a:") . ' ' . $oEntrada->getAsunto_entrada();
$entradilla = '';
$categoria = $oEntrada->getCategoria();
$visibilidad = $oEntrada->getVisibilidad();

// Trámite: Escoger el primero de la lista (por orden) seguramente será el más corto
$gesTrammites = new GestorTramite();
$cTramites = $gesTrammites->getTramites(['_ordre' => 'orden']);
$oTramite = $cTramites[0];
$tramite = $oTramite->getId_tramite();

// nuevo.
$oExpediente = new Expediente();
$oExpediente->setPonente($ponente);

$oExpediente->setId_tramite($tramite);
$oExpediente->setEstado($estado);
$oExpediente->setPrioridad($prioridad);
$oExpediente->setAsunto($asunto);
$oExpediente->setEntradilla($entradilla);
$oExpediente->setVida($vida);
$oExpediente->setVisibilidad($visibilidad);
$oExpediente->setFirmas_oficina('');
$oExpediente->setResto_oficinas('');
$oExpediente->setF_contestar($f_contestar);

// que lo vea todos los oficiales de mi oficina:
$id_oficina = ConfigGlobal::role_id_oficina();
$gesCargos = new GestorCargo();
$a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);

$new_preparar = [];
foreach (array_keys($a_cargos_oficina) as $id_cargo) {
    $oJSON = new stdClass;
    $oJSON->id = (int)$id_cargo;
    // hay que asegurar que sea bool
    $oJSON->visto = FALSE;
    $new_preparar[] = $oJSON;
}
$oExpediente->setJson_preparar($new_preparar);

if ($oExpediente->DBGuardar() === FALSE) {
    $error_txt .= $oExpediente->getErrorTxt();
} else {
    $oExpediente->DBCarregar();
    $id_expediente = $oExpediente->getId_expediente();
}

// adjuntar entrada como antecedente
$a_antecedente = ['tipo' => 'entrada', 'id' => $Q_id_entrada];
$oExpediente->addAntecedente($a_antecedente);
if ($oExpediente->DBGuardar() === FALSE) {
    $error_txt .= $oExpediente->getErrorTxt();
}

// crear el escrito
$accion = Escrito::ACCION_ESCRITO;
$oHoy = new DateTimeLocal();
$f_escrito = $oHoy->getFromLocal();

$oEscrito = new Escrito();
$oEscrito->setAccion($accion);
$oEscrito->setModo_envio(Escrito::MODO_MANUAL);
$nuevo = TRUE;

// Poner el origen como destino.
$json_prot_dst = []; // inicializar variable. Puede tener cosas.
$json_prot_dst[] = $oEntrada->getJson_prot_origen();
$oEscrito->setJson_prot_destino($json_prot_dst);
// borro las posibles personalizaciones:
$oEscrito->setId_grupos();
$oEscrito->setDestinos('');
$oEscrito->setDescripcion('');
$oEscrito->setJson_prot_ref('');
$oEscrito->setF_escrito($f_escrito);
$oEscrito->setAsunto($asunto);
$oEscrito->setCreador($ponente);
$oEscrito->setResto_oficinas('');

$oEscrito->setCategoria($categoria);
$oEscrito->setVisibilidad($visibilidad);
$oEscrito->setF_contestar($f_contestar);

if ($oEscrito->DBGuardar() === FALSE) {
    $error_txt .= $oEscrito->getErrorTxt();
}

$id_escrito = $oEscrito->getId_escrito();

// añadirlo al expediente
if ($nuevo === TRUE) {
    $oAccion = new Accion();
    $oAccion->setId_expediente($id_expediente);
    $oAccion->setId_escrito($id_escrito);
    $oAccion->setTipo_accion($accion);
    if ($oAccion->DBGuardar() === FALSE) {
        $error_txt .= $oAccion->getErrorTxt();
    }
}

// mostrar el form para empezar el etherpad
$oEscritoForm = new EscritoForm($id_expediente, $id_escrito, $accion, $filtro, $modo);
$oEscritoForm->render();
