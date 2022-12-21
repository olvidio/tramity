<?php

use core\ConfigGlobal;
use entradas\domain\entity\EntradaRepository;
use escritos\domain\entity\Escrito;
use escritos\domain\repositories\EscritoRepository;
use escritos\model\EscritoForm;
use expedientes\domain\entity\Accion;
use expedientes\domain\repositories\AccionRepository;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepository;
use tramites\domain\repositories\TramiteRepository;
use usuarios\domain\repositories\CargoRepository;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
//$Q_filtro = (string) filter_input(INPUT_POST, 'filtro');
// cambio el filtro para ver los borradores:
$filtro = 'borrador_propio';
$modo = 'mod';

$EntradaRepository = new EntradaRepository();
$oEntrada = $EntradaRepository->findById($Q_id_entrada);
$oF_contestar = $oEntrada->getF_contestar();

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
$TramiteRepository = new TramiteRepository();
$cTramites = $TramiteRepository->getTramites(['_ordre' => 'orden']);
$oTramite = $cTramites[0];
$tramite = $oTramite->getId_tramite();

// nuevo.
$ExpedienteRepository = new ExpedienteRepository();
$id_expediente = $ExpedienteRepository->getNewId_expediente();
$oExpediente = new Expediente();
$oExpediente->setId_expediente($id_expediente);
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
$oExpediente->setF_contestar($oF_contestar);

// que lo vea todos los oficiales de mi oficina:
$id_oficina = ConfigGlobal::role_id_oficina();
$CargoRepository = new CargoRepository();
$a_cargos_oficina = $CargoRepository->getArrayCargosOficina($id_oficina);

$new_preparar = [];
foreach (array_keys($a_cargos_oficina) as $id_cargo) {
    $oJSON = new stdClass;
    $oJSON->id = (int)$id_cargo;
    // hay que asegurar que sea bool
    $oJSON->visto = FALSE;
    $new_preparar[] = $oJSON;
}
$oExpediente->setJson_preparar($new_preparar);

if ($ExpedienteRepository->Guardar($oExpediente) === FALSE) {
    $error_txt = $ExpedienteRepository->getErrorTxt();
}

// adjuntar entrada como antecedente
$Antecedente = new stdClass();
$Antecedente->tipo = 'entrada';
$Antecedente->id = $Q_id_entrada;
$oExpediente->addAntecedente($Antecedente);
if ($ExpedienteRepository->Guardar($oExpediente) === FALSE) {
    $error_txt .= $ExpedienteRepository->getErrorTxt();
}

// crear el escrito
$accion = Escrito::ACCION_ESCRITO;
$oHoy = new DateTimeLocal();

$escritoRepository = new EscritoRepository();
$id_escrito = $escritoRepository->getNewId_escrito();
$oEscrito = new Escrito();
$oEscrito->setId_escrito($id_escrito);
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
$oEscrito->setF_escrito($oHoy);
$oEscrito->setAsunto($asunto);
$oEscrito->setCreador($ponente);
$oEscrito->setResto_oficinas('');

$oEscrito->setCategoria($categoria);
$oEscrito->setVisibilidad($visibilidad);
$oEscrito->setF_contestar($oF_contestar);

if ($escritoRepository->Guardar($oEscrito) === FALSE) {
    $error_txt .= $escritoRepository->getErrorTxt();
}

// añadirlo al expediente
if ($nuevo === TRUE) {
    $AccionRepository = new AccionRepository();
    $id_item = $AccionRepository->getNewId_item();
    $oAccion = new Accion();
    $oAccion->setId_item($id_item);
    $oAccion->setId_expediente($id_expediente);
    $oAccion->setId_escrito($id_escrito);
    $oAccion->setTipo_accion($accion);
    if ($AccionRepository->Guardar($oAccion) === FALSE) {
        $error_txt .= $AccionRepository->getErrorTxt();
    }
}

// mostrar el form para empezar el etherpad
$oEscritoForm = new EscritoForm($id_expediente, $id_escrito, $accion, $filtro, $modo);
$oEscritoForm->render();
