<?php
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use expedientes\model\entity\GestorAccion;
use tramites\model\entity\GestorTramite;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Desplegable;
use web\Protocolo;
use web\ProtocoloArray;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qprioridad_sel = (integer) \filter_input(INPUT_POST, 'prioridad_sel');
$Qmodo = (string) \filter_input(INPUT_POST, 'modo');

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
$oCargo =new Cargo($id_ponente);
$ponente_txt = '';
if (!empty($oCargo)) {
    $id_oficina = $oCargo->getId_oficina();
    $ponente_txt = $oCargo->getCargo();
}

// para reducir la vista en el caso de los ctr
$vista_dl = TRUE;
if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
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

$oExpediente = new Expediente();

$a_prioridad = $oExpediente->getArrayPrioridad();
$oDesplPrioridad = new Desplegable('prioridad',$a_prioridad,Expediente::PRIORIDAD_UNKNOW,FALSE);
$oDesplPrioridad->setAction('fnjs_comprobar_plazo()');

$a_vida = $oExpediente->getArrayVida();
$oDesplVida = new Desplegable('vida',$a_vida,'',FALSE);

// visibilidad (usar las mismas opciones que en entradas)
$oEntrada = new Entrada();
$aOpciones = $oEntrada->getArrayVisibilidad();
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
$cCargos_oficina = $gesCargos->getCargos(['id_oficina' => $id_oficina]);
$a_posibles_cargos_oficina = [];
foreach ($cCargos_oficina as $oCargo) {
    if ($oCargo->getDirector()) { continue; } // No pongo al director, ya está con el resto de firmas.
    $id_cargo = $oCargo->getId_cargo();
    $cargo = $oCargo->getCargo();
    $a_posibles_cargos_oficina[$id_cargo] = $cargo;
    $txt_option_cargos_oficina .= "<option value=$id_cargo >$cargo</option>";
}

if ($Qid_expediente) {
    $titulo=_("expediente");
    $oExpediente->setId_expediente($Qid_expediente);
    $oExpediente->DBCarregar();
    
    $id_tramite = $oExpediente->getId_tramite();
    $oDesplTramites->setOpcion_sel($id_tramite);
    $estado = $oExpediente->getEstado();
    $prioridad = $oExpediente->getPrioridad();
    $oDesplPrioridad->setOpcion_sel($prioridad);
    
    $vida = $oExpediente->getVida();
    $oDesplVida->setOpcion_sel($vida);
    $visibilidad = $oExpediente->getVisibilidad();
    $oDesplVisibilidad->setOpcion_sel($visibilidad);

    $etiquetas = $oExpediente->getEtiquetasVisiblesArray();
    $oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas,$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');

    $f_contestar = $oExpediente->getF_contestar()->getFromLocal();
    $f_ini_circulacion = $oExpediente->getF_ini_circulacion()->getFromLocal();
    $f_reunion = $oExpediente->getF_reunion()->getFromLocal();
    $f_aprobacion = $oExpediente->getF_aprobacion()->getFromLocal();
    
    $asunto = $oExpediente->getAsunto();
    $entradilla = $oExpediente->getEntradilla();
    
    $gesAcciones = new GestorAccion();
    $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $Qid_expediente, '_ordre' => 'tipo_accion']);
    $a_acciones = [];
    
    $oEscrito = new Escrito();
    $aAcciones = $oEscrito->getArrayAccion();
    $aCategorias = $oEscrito->getArrayCategoria();
    
    $oProtDestino = new Protocolo();
    $oProtDestino->setNombre('destino');
    foreach ($cAcciones as $oAccion) {
        $id_escrito = $oAccion->getId_escrito();
        $tipo_accion = $oAccion->getTipo_accion();
        $txt_tipo = $aAcciones[$tipo_accion];
        
        $oEscrito = new Escrito($id_escrito);
        
        $a_cosas =  ['id_expediente' => $Qid_expediente,
                    'id_escrito' => $id_escrito,
                    'accion' => $tipo_accion,
                    'filtro' => $Qfiltro,
                    'prioridad_sel' => $Qprioridad_sel,
        ];
        $pag_escrito =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
        
        $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >"._("mod.datos")."</span>";
        $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >"._("rev.texto")."</span>";
            
        // Sólo se puede eliminar si no se ha enviado. Si se ha enviado se puede quitar del expediente:
        $f_salida = $oEscrito->getF_salida()->getIso();
        if (empty($f_salida)) {
            $a_accion['link_del'] = "<span class=\"btn btn-link\" onclick=\"fnjs_eliminar_accion($id_escrito);\" >"._("eliminar")."</span>";
        } else {
            $a_accion['link_del'] = "<span class=\"btn btn-link\" onclick=\"fnjs_eliminar_accion($id_escrito);\" >"._("quitar")."</span>";
        }
        // para los centros, se puede enviar desde aquí
        if (!$vista_dl) {
            $link_enviar = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito($id_escrito);\" >"._("enviar")."</span>";
            $a_accion['link_del'] .= $link_enviar;
        }
        
        
        $dst_txt = $oEscrito->getDestinosEscrito();
        
        $json_ref = $oEscrito->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($json_ref,'','');
        $oArrayProtRef->setRef(TRUE);
        
        // Tiene adjuntos?
        $adjuntos = '';
        $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
        if (!empty($a_id_adjuntos)) {
            $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto('$id_escrito');\"  ></i>";
        }
        // Categoria
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

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales,$a_posibles_cargos_oficina,'firmas_oficina');
    $oArrayDesplFirmasOficina ->setBlanco('t');
    $oArrayDesplFirmasOficina ->setAccionConjunto('fnjs_mas_firmas_oficina()');

    $oficinas = $oExpediente->getResto_oficinas();

    $oArrayDesplFirmas = new web\DesplegableArray($oficinas,$a_posibles_cargos,'firmas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_firmas()');

    $json_preparar = $oExpediente->getJson_preparar();
    foreach ($json_preparar as $oficial) {
        $id = $oficial->id;
        $visto = empty($oficial->visto)? 0 : $oficial->visto;
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
    $titulo=_("nuevo expediente");
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

    $oArrayDesplEtiquetas = new web\DesplegableArray([],$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales,$a_posibles_cargos_oficina,'firmas_oficina');
    $oArrayDesplFirmasOficina ->setBlanco('t');
    $oArrayDesplFirmasOficina ->setAccionConjunto('fnjs_mas_firmas_oficina()');
    
    $oArrayDesplFirmas = new web\DesplegableArray($oficinas,$a_posibles_cargos,'firmas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_firmas()');
        
}

$lista_antecedentes = $oExpediente->getHtmlAntecedentes();

$url_update = 'apps/expedientes/controller/expediente_update.php';
$url_ajax = 'apps/tramites/controller/tramitecargo_ajax.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query(['filtro' => $Qfiltro, 'prioridad_sel' => $Qprioridad_sel]));
$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query([]));

$pag_escrito =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_ESCRITO]));
$pag_propuesta =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_PROPUESTA]));
$pag_plantilla =  web\Hash::link('apps/plantillas/controller/plantilla_lista_expediente.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'filtro' => $Qfiltro, 'modo' => $Qmodo, 'prioridad_sel' => $Qprioridad_sel]));
$pag_respuesta =  web\Hash::link('apps/entradas/controller/buscar_form.php?'.http_build_query(['id_expediente' => $Qid_expediente,'filtro' => $Qfiltro, 'prioridad_sel' => $Qprioridad_sel]));
$server = ConfigGlobal::getWeb(); //http://tramity.local

$pag_actualizar = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query(['id_expediente' => $Qid_expediente,'filtro' => $Qfiltro, 'prioridad_sel' => $Qprioridad_sel]));

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;
$error_fecha = $_SESSION['oConfig']->getPlazoError();
$error_fecha_txt = 'P'.$error_fecha.'D';
$oHoy = new DateTimeLocal();
$oHoy->sub(new DateInterval($error_fecha_txt));
$minIso = $oHoy->format('Y-m-d');

$a_campos = [
    'vista_dl' => $vista_dl,
    'titulo' => $titulo,
    'id_expediente' => $Qid_expediente,
    //'oHash' => $oHash,
    'ponente_txt' => $ponente_txt,
    'id_ponente' => $id_ponente,
    'oDesplTramites' => $oDesplTramites,
    //'oDesplEstado' => $oDesplEstado,
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
    'pag_escrito' => $pag_escrito,
    'pag_propuesta' => $pag_propuesta,
    'pag_plantilla' => $pag_plantilla,
    'pag_respuesta' => $pag_respuesta,
    'pag_actualizar' => $pag_actualizar,
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

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_form.html.twig',$a_campos);