<?php

use core\ConfigGlobal;
use core\ViewTwig;
use escritos\model\Escrito;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\entity\GestorAccion;
use expedientes\model\Expediente;
use tramites\model\entity\GestorTramite;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\DateTimeLocal;
use web\Desplegable;
use web\Protocolo;
use web\ProtocoloArray;

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
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$prioridad_fecha = Expediente::PRIORIDAD_FECHA;
$prioridad_desconocido = Expediente::PRIORIDAD_UNKNOW;
$prioridad_urgente = Expediente::PRIORIDAD_URGENTE;
$plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
$prioridad_rapido = Expediente::PRIORIDAD_RAPIDO;
$plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
$prioridad_normal = Expediente::PRIORIDAD_NORMAL;
$plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
$error_fecha = $_SESSION['oConfig']->getPlazoError();

$id_ponente = ConfigGlobal::role_id_cargo();
$oCargo = new Cargo($id_ponente);
$ponente_txt = '';
if (!empty($oCargo)) {
    $id_oficina = $oCargo->getId_oficina();
    $ponente_txt = $oCargo->getCargo();
}

// para reducir la vista en el caso de los ctr
$vista_dl = TRUE;
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $vista_dl = FALSE;
}

// preparar
$gesCargos = new GestorCargo();
$a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
$a_preparar = [];
foreach ($a_cargos_oficina as $id_cargo => $cargo) {
    $a_preparar[] = ['id' => $id_cargo, 'text' => $cargo, 'chk' => '', 'visto' => 0];
}

$gesTramites = new GestorTramite();
$oDesplTramites = $gesTramites->getListaTramites();
$oDesplTramites->setNombre('tramite');
$oDesplTramites->setAction('fnjs_tramite()');
// para los centros selecciono el primer trámite (quito la opción blanco)
if (!$vista_dl) {
    $oDesplTramites->setBlanco(FALSE);
}

$oExpediente = new Expediente();

$a_prioridad = $oExpediente->getArrayPrioridad();
$oDesplPrioridad = new Desplegable('prioridad', $a_prioridad, Expediente::PRIORIDAD_UNKNOW, FALSE);
$oDesplPrioridad->setAction('fnjs_comprobar_plazo()');

$a_vida = $oExpediente->getArrayVida();
$oDesplVida = new Desplegable('vida', $a_vida, '', FALSE);

// visibilidad
$oVisibilidad = new Visibilidad();
$aOpciones = $oVisibilidad->getArrayVisibilidad(TRUE);
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);

// Etiquetas
$etiquetas = []; // No hay ninguna porque en archivar es cuando se añaden.
$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}

$txt_option_cargos = '';
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$txt_option_cargos_oficina = '';
$cCargos_oficina = $gesCargos->getCargos(['id_oficina' => $id_oficina, '_ordre' => 'director DESC, cargo DESC']);
$a_posibles_cargos_oficina = [];
foreach ($cCargos_oficina as $oCargo) {
    // No pongo al director, ya está con el resto de firmas.
    if ($oCargo->getDirector()) {
        continue;
    }
    $id_cargo = $oCargo->getId_cargo();
    $cargo = $oCargo->getCargo();
    $a_posibles_cargos_oficina[$id_cargo] = $cargo;
    $txt_option_cargos_oficina .= "<option value=$id_cargo >$cargo</option>";
}

if ($Q_id_expediente) {
    $titulo = _("expediente");
    $oExpediente->setId_expediente($Q_id_expediente);
    $oExpediente->DBCargar();

    $id_tramite = $oExpediente->getId_tramite();
    $oDesplTramites->setOpcion_sel($id_tramite);
    $estado = $oExpediente->getEstado();
    $prioridad = $oExpediente->getPrioridad();
    $oDesplPrioridad->setOpcion_sel($prioridad);

    $vida = $oExpediente->getVida();
    $oDesplVida->setOpcion_sel($vida);
    $visibilidad = $oExpediente->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);
    $oPermisoregistro = new PermRegistro();

    $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oExpediente, 'cambio');
    if ($perm_cambio_visibilidad < PermRegistro::PERM_MODIFICAR) {
        $oDesplVisibilidad->setDisabled(TRUE);
    }

    $etiquetas = $oExpediente->getEtiquetasVisiblesArray();
    $oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas, $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

    $f_contestar = $oExpediente->getF_contestar()->getFromLocal();
    $f_ini_circulacion = $oExpediente->getF_ini_circulacion()->getFromLocal();
    $f_reunion = $oExpediente->getF_reunion()->getFromLocal();
    $f_aprobacion = $oExpediente->getF_aprobacion()->getFromLocal();

    $asunto = $oExpediente->getAsunto();
    $entradilla = $oExpediente->getEntradilla();

    $gesAcciones = new GestorAccion();
    $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $Q_id_expediente, '_ordre' => 'tipo_accion']);
    $a_acciones = [];

    $oEscrito = new Escrito();
    $aAcciones = $oEscrito->getArrayAccion();
    $oCategoria = new Categoria();
    $aCategorias = $oCategoria->getArrayCategoria();

    $oProtDestino = new Protocolo();
    $oProtDestino->setNombre('destino');
    // mostrar archivar si todas las acciones están envidas
    $mostrar_archivar = TRUE;
    foreach ($cAcciones as $oAccion) {
        $id_escrito = $oAccion->getId_escrito();
        $tipo_accion = $oAccion->getTipo_accion();
        $txt_tipo = $aAcciones[$tipo_accion];

        $oEscrito = new Escrito($id_escrito);

        $a_cosas = ['id_expediente' => $Q_id_expediente,
            'id_escrito' => $id_escrito,
            'accion' => $tipo_accion,
            'filtro' => $Q_filtro,
            'prioridad_sel' => $Q_prioridad_sel,
        ];
        $pag_escrito = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));

        $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >" . _("mod.datos") . "</span>";
        $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >" . _("rev.texto") . "</span>";

        // Sólo se puede eliminar si no se ha enviado. Si se ha enviado se puede quitar del expediente:
        $f_salida = $oEscrito->getF_salida()->getIso();
        if (empty($f_salida)) {
            $a_accion['link_del'] = "<span class=\"btn btn-link\" onclick=\"fnjs_eliminar_accion($id_escrito);\" >" . _("eliminar") . "</span>";
            $mostrar_archivar = FALSE;
        } else {
            $a_accion['link_del'] = "<span class=\"btn btn-link\" onclick=\"fnjs_eliminar_accion($id_escrito);\" >" . _("quitar") . "</span>";

            $protocolo_txt = $oEscrito->getProt_local_txt();
            $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >$protocolo_txt</span>";
            $a_accion['link_mod'] = _("enviado");
        }


        $dst_txt = $oEscrito->getDestinosEscrito();

        $json_ref = $oEscrito->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
        $oArrayProtRef->setRef(TRUE);

        // Tiene adjuntos?
        $adjuntos = '';
        $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
        if (!empty($a_id_adjuntos)) {
            $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto_en_expediente('$id_escrito','mod');\"  ></i>";
        }
        // Categoría
        $categoria = $oEscrito->getCategoria();
        $categoria_txt = $aCategorias[$categoria];

        $a_accion['destino'] = $dst_txt;
        $a_accion['link_ver'] = 'v';
        $a_accion['referencias'] = $oArrayProtRef->ListaTxtBr();
        $a_accion['tipo'] = $txt_tipo;
        $a_accion['categoria'] = $categoria_txt;
        $a_accion['asunto'] = $oEscrito->getAsuntoDetalle();
        $a_accion['adjuntos'] = $adjuntos;

        $a_acciones[] = $a_accion;
    }

    $oficiales = $oExpediente->getFirmas_oficina();

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales, $a_posibles_cargos_oficina, 'firmas_oficina');
    $oArrayDesplFirmasOficina->setBlanco('t');
    $oArrayDesplFirmasOficina->setAccionConjunto('fnjs_mas_firmas_oficina()');

    $oficinas = $oExpediente->getResto_oficinas();

    $oArrayDesplFirmas = new web\DesplegableArray($oficinas, $a_posibles_cargos, 'firmas');
    $oArrayDesplFirmas->setBlanco('t');
    $oArrayDesplFirmas->setAccionConjunto('fnjs_mas_firmas()');

    $json_preparar = $oExpediente->getJson_preparar();
    foreach ($json_preparar as $oficial) {
        $id = $oficial->id;
        $visto = empty($oficial->visto) ? 0 : $oficial->visto;
        // marcar las que estan.
        foreach ($a_preparar as $key => $oficial2) {
            $id2 = $oficial2['id'];
            if ($id == $id2) {
                $a_preparar[$key]['chk'] = 'checked';
                $a_preparar[$key]['visto'] = $visto;
            }
        }
    }
} else {
    $mostrar_archivar = FALSE;
    $titulo = _("nuevo expediente");
    $estado = Expediente::ESTADO_BORRADOR;
    $f_contestar = '';
    $f_ini_circulacion = '';
    $f_reunion = '';
    $f_aprobacion = '';
    $asunto = '';
    $entradilla = '';
    $a_acciones = [];
    $oficinas = '';
    $oficiales = '';
    // para los centros selecciono todos los oficiales por defecto
    if (!$vista_dl) {
        $oficiales = array_keys($a_posibles_cargos_oficina);
    }

    if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
        $oDesplVisibilidad->setOpcion_sel(Visibilidad::V_CTR_TODOS);
    } else {
        $oDesplVisibilidad->setOpcion_sel(Visibilidad::V_PERSONAL);
    }


    $oArrayDesplEtiquetas = new web\DesplegableArray([], $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales, $a_posibles_cargos_oficina, 'firmas_oficina');
    $oArrayDesplFirmasOficina->setBlanco('t');
    $oArrayDesplFirmasOficina->setAccionConjunto('fnjs_mas_firmas_oficina()');

    $oArrayDesplFirmas = new web\DesplegableArray($oficinas, $a_posibles_cargos, 'firmas');
    $oArrayDesplFirmas->setBlanco('t');
    $oArrayDesplFirmas->setAccionConjunto('fnjs_mas_firmas()');

}

$lista_antecedentes = $oExpediente->getHtmlAntecedentes();

$url_update = 'apps/expedientes/controller/expediente_update.php';
$url_ajax = 'apps/tramites/controller/tramitecargo_ajax.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query(['filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));
$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query(['filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));

$pag_nuevo_escrito = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query(['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'accion' => Escrito::ACCION_ESCRITO]));
$pag_propuesta = web\Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query(['id_expediente' => $Q_id_expediente,'filtro' => $Q_filtro, 'accion' => Escrito::ACCION_PROPUESTA]));
$pag_plantilla = web\Hash::link('apps/plantillas/controller/plantilla_lista_expediente.php?' . http_build_query(['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'modo' => $Q_modo, 'prioridad_sel' => $Q_prioridad_sel]));
$pag_respuesta = web\Hash::link('apps/entradas/controller/buscar_form.php?' . http_build_query(['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));
$server = ConfigGlobal::getWeb(); //http://tramity.local

$pag_actualizar = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query(['id_expediente' => $Q_id_expediente, 'filtro' => $Q_filtro, 'prioridad_sel' => $Q_prioridad_sel]));

// datepicker
$oHoy = new DateTimeLocal();
$format = $oHoy::getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;
$error_fecha = $_SESSION['oConfig']->getPlazoError();
$error_fecha_txt = 'P' . $error_fecha . 'D';
$oHoy->sub(new DateInterval($error_fecha_txt));
$minIso = $oHoy->format('Y-m-d');

$a_campos = [
    'vista_dl' => $vista_dl,
    'titulo' => $titulo,
    'id_expediente' => $Q_id_expediente,
    'ponente_txt' => $ponente_txt,
    'id_ponente' => $id_ponente,
    'filtro' => $Q_filtro,
    'oDesplTramites' => $oDesplTramites,
    'estado' => $estado,
    'oDesplPrioridad' => $oDesplPrioridad,
    'oDesplVida' => $oDesplVida,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,

    'f_contestar' => $f_contestar,
    'f_ini_circulacion' => $f_ini_circulacion,
    'f_reunion' => $f_reunion,
    'f_aprobacion' => $f_aprobacion,

    'asunto' => $asunto,
    'entradilla' => $entradilla,
    'oficinas' => $oficinas,
    'oArrayDesplFirmasOficina' => $oArrayDesplFirmasOficina,
    'txt_option_cargos_oficina' => $txt_option_cargos_oficina,
    'oArrayDesplFirmas' => $oArrayDesplFirmas,
    'txt_option_cargos' => $txt_option_cargos,
    'lista_antecedentes' => $lista_antecedentes,

    'url_update' => $url_update,
    'url_ajax' => $url_ajax,
    'pagina_cancel' => $pagina_cancel,
    'pagina_nueva' => $pagina_nueva,
    //acciones
    'a_acciones' => $a_acciones,
    'pag_nuevo_escrito' => $pag_nuevo_escrito,
    'pag_propuesta' => $pag_propuesta,
    'pag_plantilla' => $pag_plantilla,
    'pag_respuesta' => $pag_respuesta,
    'pag_actualizar' => $pag_actualizar,
    'mostrar_archivar' => $mostrar_archivar,
    // preparar
    'a_preparar' => $a_preparar,
    // para la pagina js (prioridades)
    'prioridad_fecha' => $prioridad_fecha,
    'prioridad_desconocido' => $prioridad_desconocido,
    'prioridad_urgente' => $prioridad_urgente,
    'plazo_urgente' => $plazo_urgente,
    'prioridad_rapido' => $prioridad_rapido,
    'plazo_rapido' => $plazo_rapido,
    'prioridad_normal' => $prioridad_normal,
    'plazo_normal' => $plazo_normal,
    'error_fecha' => $error_fecha,
    // parar _antecedentes_js
    'server' => $server,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    'minIso' => $minIso,
];

// dentro del _expediente_form_js.html.twig hay un include a  escritos/view/_escrito_enviar_js.html.twig
$additionalPaths = ['escritos' => 'escritos/view'];
$oView = new ViewTwig('expedientes/controller', $additionalPaths);
$oView->renderizar('expediente_form.html.twig', $a_campos);