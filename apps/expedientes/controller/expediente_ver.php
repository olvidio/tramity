<?php

use core\ConfigGlobal;
use core\ViewTwig;
use escritos\model\Escrito;
use escritos\model\EscritoLista;
use expedientes\model\Expediente;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\Tramite;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\Visibilidad;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_prioridad_sel = (integer)filter_input(INPUT_POST, 'prioridad_sel');

// para reducir la vista en el caso de los ctr
$vista_dl = TRUE;
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $vista_dl = FALSE;
}

$gesCargos = new GestorCargo();
$aCargos = $gesCargos->getArrayCargos();

$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$oExpediente = new Expediente($Q_id_expediente);
if ($oExpediente->DBCargar() === FALSE) {
    $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
    exit ($err_cargar);
}

$ponente_txt = '?';
$id_ponente = $oExpediente->getPonente();
$ponente_txt = $aCargos[$id_ponente];

$id_tramite = $oExpediente->getId_tramite();
$oTramite = new Tramite($id_tramite);
$tramite_txt = $oTramite->getTramite();

$estado = $oExpediente->getEstado();
$a_estado = $oExpediente->getArrayEstado();
$estado_txt = $a_estado[$estado];

// Valores posibles para la firma
$gesFirmas = new GestorFirma();
$oFirma = new Firma();
$a_posibles_valores_firma = [];
$rango = 'voto';
if (ConfigGlobal::role_actual() === 'vcd') {
    // Ver cual toca
    $aWhere = ['id_expediente' => $Q_id_expediente,
        'id_cargo' => ConfigGlobal::role_id_cargo(),
        '_ordre' => 'orden_tramite',
    ];
    $cFirmasVcd = $gesFirmas->getFirmas($aWhere);
    foreach ($cFirmasVcd as $oFirma) {
        $valor = $oFirma->getValor();
        $cargo_tipo = $oFirma->getCargo_tipo();
        if (empty($valor) ||
            ($valor !== Firma::V_D_NO && $valor !== Firma::V_D_OK && $valor !== Firma::V_D_VISTO_BUENO)) {
            if ($cargo_tipo === Cargo::CARGO_VB_VCD) {
                $rango = 'vb_vcd';
            } else {
                $rango = 'vcd';
            }
            break; // Me paro en el primero. 
        }
    }
}

foreach ($oFirma->getArrayValor($rango) as $key => $valor) {
    $a_voto['id'] = $key;
    $a_voto['valor'] = $valor;
    $a_posibles_valores_firma[] = $a_voto;
}

$prioridad = $oExpediente->getPrioridad();
$a_prioridad = $oExpediente->getArrayPrioridad();
$prioridad_txt = $a_prioridad[$prioridad];

$visibilidad = $oExpediente->getVisibilidad();
$visibilidad = $visibilidad ?? Visibilidad::V_TODOS;
$oVisibilidad = new Visibilidad();
if ($vista_dl) {
    $a_visibilidad = $oVisibilidad->getArrayVisibilidadDl();
} else {
    $a_visibilidad = $oVisibilidad->getArrayVisibilidadCtr();
}
$visibilidad_txt = $a_visibilidad[$visibilidad]?? _("No tiene visibilidad definida");

$a_vida = $oExpediente->getArrayVida();
$vida = $oExpediente->getVida();
if (!empty($vida)) {
    $vida_txt = $a_vida[$vida];
} else {
    $vida_txt = '';
}
$oDesplVida = new Desplegable('vida', $a_vida, '', FALSE);
$oDesplVida->setOpcion_sel($vida);
$oDesplVida->setAction("fnjs_cambio_vida();");

$f_contestar = $oExpediente->getF_contestar()->getFromLocal();
$f_ini_circulacion = $oExpediente->getF_ini_circulacion()->getFromLocal();
$f_reunion = $oExpediente->getF_reunion()->getFromLocal();
$f_aprobacion = $oExpediente->getF_aprobacion()->getFromLocal();

$asunto = $oExpediente->getAsunto();
$entradilla = $oExpediente->getEntradilla();

$oEscritoLista = new EscritoLista();
$oEscritoLista->setId_expediente($Q_id_expediente);
$oEscritoLista->setFiltro($Q_filtro);
$oEscritoLista->setModo('mod');

// Comentarios y Aclaraciones
$aRecorrido = $gesFirmas->getRecorrido($Q_id_expediente);
$a_recorrido = $aRecorrido['recorrido'];
$comentarios = $aRecorrido['comentarios'];
$responder = $aRecorrido['responder'];

// firmar o responder
$firma_txt = _("Firmar");
$aclaracion = '';
$aclaracion_event = '';
$bool_aclaracion = FALSE;
$a_cargos_oficina = $gesCargos->getArrayCargosOficina(ConfigGlobal::role_id_oficina());
if ($responder) {
    if (!ConfigGlobal::soy_dtor()) {
        $a_cargos_oficina = [];
    }

    if (array_key_exists($id_ponente, $a_cargos_oficina) || ($id_ponente === ConfigGlobal::role_id_cargo())) {
        $aclaracion = _("Responder aclaración");
        $aclaracion_event = 'respuesta';
        $bool_aclaracion = TRUE;
        $firma_txt = _("Responder");
    }
} else {
    $aclaracion = _("Pedir aclaración");
    $aclaracion_event = 'nueva';
}

// Etiquetas
$ver_etiquetas = FALSE;
$oArrayDesplEtiquetas = '';
if ($estado === Expediente::ESTADO_ACABADO) {
    $cEtiquetas = $oExpediente->getEtiquetasVisibles();
    $a_etiquetas = [];
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
        $a_etiquetas[] = $id_etiqueta;
    }
    $oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas, $a_posibles_etiquetas, 'etiquetas');
    $ver_etiquetas = TRUE;
}


$oficinas = $oExpediente->getResto_oficinas();

$oArrayDesplFirmas = new web\DesplegableArray($oficinas, $a_posibles_cargos, 'oficinas');
$oArrayDesplFirmas->setBlanco('t');
$oArrayDesplFirmas->setAccionConjunto('fnjs_mas_oficinas()');

$lista_antecedentes = $oExpediente->getHtmlAntecedentes(FALSE);
$ver_todos_antecedentes = 'ver todo';

$url_update = 'apps/expedientes/controller/expediente_update.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query(['filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));
$base_url = core\ConfigGlobal::getWeb();
$pagina_cambio = web\Hash::link('apps/expedientes/controller/expediente_cambio_tramite.php?' . http_build_query(['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));

$add_del_txt = '';
if ($Q_filtro === 'seg_reunion') {
    $add_del = 'del';
    // solo secretaria tiene permiso
    if (ConfigGlobal::role_actual() === 'secretaria') {
        $add_del_txt = _("Quitar Firmas");
    }
} else {
    $add_del = 'add';
    $add_del_txt = _("Añadir Firmas");
}
$reset_txt = _("Re-circular");

// solamente el scdl tiene permiso. Ahora 11-3-22 también el sd.
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL && (ConfigGlobal::role_actual() === 'scdl' || ConfigGlobal::role_actual() === 'sd')) {
    $cmb_tramite = TRUE;
} else {
    $cmb_tramite = FALSE;
}
// Sólo puede cambiar los antecedentes el ponente
if (array_key_exists($id_ponente, $a_cargos_oficina) || ($id_ponente === ConfigGlobal::role_id_cargo())) {
    $antecedentes_txt = _("modificar");
} else {
    $antecedentes_txt = '';
}

$a_campos = [
    'vista_dl' => $vista_dl,
    'id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
    'prioridad_sel' => $Q_prioridad_sel,
    //'oHash' => $oHash,
    'ponente_txt' => $ponente_txt,
    'id_ponente' => $id_ponente,
    'tramite_txt' => $tramite_txt,
    'estado_txt' => $estado_txt,
    'prioridad_txt' => $prioridad_txt,
    'visibilidad_txt' => $visibilidad_txt,
    'vida_txt' => $vida_txt,
    'oDesplVida' => $oDesplVida,

    'f_contestar' => $f_contestar,
    'f_ini_circulacion' => $f_ini_circulacion,
    'f_reunion' => $f_reunion,
    'f_aprobacion' => $f_aprobacion,

    'asunto' => $asunto,
    'entradilla' => $entradilla,
    'comentarios' => $comentarios,
    'a_recorrido' => $a_recorrido,

    'oficinas' => $oficinas,
    'oArrayDesplFirmas' => $oArrayDesplFirmas,
    'txt_option_cargos' => $txt_option_cargos,
    'lista_antecedentes' => $lista_antecedentes,
    'ver_todos_antecedentes' => $ver_todos_antecedentes,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    'ver_etiquetas' => $ver_etiquetas,

    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    //acciones
    'oEscritoLista' => $oEscritoLista,
    //'a_acciones' => $a_acciones,
    'firma_txt' => $firma_txt,
    'a_posibles_valores_firma' => $a_posibles_valores_firma,
    'base_url' => $base_url,
    'aclaracion' => $aclaracion,
    'aclaracion_event' => $aclaracion_event,
    'bool_aclaracion' => $bool_aclaracion,
    'add_del' => $add_del,
    'add_del_txt' => $add_del_txt,
    'reset_txt' => $reset_txt,
    'cmb_tramite' => $cmb_tramite,
    'pagina_cambio' => $pagina_cambio,
    // cambiar antecedentes
    'antecedentes_txt' => $antecedentes_txt,
];

$oView = new ViewTwig('expedientes/controller');
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $a_cosas = ['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'volver_a' => 'expediente_ver'];
    $a_cosas['accion'] = Escrito::ACCION_ESCRITO;
    $pag_nuevo_escrito = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
    $a_cosas['accion'] = Escrito::ACCION_PROPUESTA;
    $pag_nueva_propuesta = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
    $a_cosas = ['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'modo' => 'ver'];
    $pag_actualizar = web\Hash::link('apps/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));

    $a_campos['pag_nuevo_escrito'] = $pag_nuevo_escrito;
    $a_campos['pag_nueva_propuesta'] = $pag_nueva_propuesta;
    $a_campos['pag_actualizar'] = $pag_actualizar;
    $oView->renderizar('expediente_ctr_ver.html.twig', $a_campos);
} else {
    $oView->renderizar('expediente_ver.html.twig', $a_campos);
}