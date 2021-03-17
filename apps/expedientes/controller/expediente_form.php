<?php
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use expedientes\model\Escrito;
use expedientes\model\Expediente;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorGrupo;
use tramites\model\entity\GestorTramite;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Desplegable;
use web\Protocolo;
use web\ProtocoloArray;
use usuarios\model\entity\GestorOficina;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

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

$txt_option_cargos = '';
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$txt_option_cargos_oficina = '';
$cCargos_oficina = $gesCargos->getCargos(['id_oficina' => $id_oficina]);
$a_posibles_cargos_oficina = [];
foreach ($cCargos_oficina as $oCargo) {
    $id_cargo = $oCargo->getId_cargo();
    $cargo = $oCargo->getCargo();
    $a_posibles_cargos_oficina[$id_cargo] = $cargo;
    $txt_option_cargos_oficina .= "<option value=$id_cargo >$cargo</option>";
}

$gesOficinas = new GestorOficina();
$a_posibles_oficinas = $gesOficinas->getArrayOficinas();

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
    
    $gesGrupos = new GestorGrupo();
    $a_grupos = $gesGrupos->getArrayGrupos();
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
        ];
        $pag_escrito =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
        $pag_rev =  web\Hash::link('apps/expedientes/controller/escrito_rev.php?'.http_build_query($a_cosas));
            
        $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >"._("mod.datos")."</span>";
        $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_rev');\" >"._("rev.texto")."</span>";
        
        $aGrupos = $oEscrito->getId_grupos();
        if (!empty($aGrupos)) {
            $dst_txt = '';
            foreach ($aGrupos as $id_grupo) {
                $dst_txt .= empty($dst_txt)? '' : ' + ';
                $dst_txt .= $a_grupos[$id_grupo];
            }
        } else {
            $json_prot_destino = $oEscrito->getJson_prot_destino();
            $oArrayProtDestino = new ProtocoloArray($json_prot_destino,'','destinos');
            $dst_txt = $oArrayProtDestino->ListaTxtBr();
        }
        
        $json_ref = $oEscrito->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($json_ref,'','');
        $oArrayProtRef->setRef(TRUE);
        
        // Tiene adjuntos?
        $adjuntos = '';
        $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
        if (!empty($a_id_adjuntos)) {
            $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto(event,'$id_escrito');\"  ></i>";
        }
        
        $a_accion['protocolo'] = $dst_txt;
        $a_accion['link_ver'] = 'v';
        $a_accion['referencias'] = $oArrayProtRef->ListaTxtBr();
        $a_accion['tipo'] = $txt_tipo;
        $a_accion['asunto'] = $oEscrito->getAsuntoDetalle();
        $a_accion['adjuntos'] = $adjuntos;
            
        $a_acciones[] = $a_accion;
    }
    
    $oficiales = $oExpediente->getFirmas_oficina();

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales,$a_posibles_cargos_oficina,'firmas_oficina');
    $oArrayDesplFirmasOficina ->setBlanco('t');
    $oArrayDesplFirmasOficina ->setAccionConjunto('fnjs_mas_firmas_oficina(event)');

    $oficinas = $oExpediente->getResto_oficinas();

    $oArrayDesplFirmas = new web\DesplegableArray($oficinas,$a_posibles_cargos,'firmas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_firmas(event)');

    $oDesplOficinas = new web\Desplegable('oficina_buscar_1',$a_posibles_oficinas,'',TRUE);
    $oDesplCargos = new web\Desplegable('oficina_buscar_2',$a_posibles_cargos,'',TRUE);
    
    // $a_preparar[] = ['id' => $id_cargo, 'text' => $cargo, 'chk' => '', 'visto' => 0];
    $json_preparar = $oExpediente->getJson_preparar();
    foreach ($json_preparar as $oficial) {
        $id = $oficial->id;
        //$chk = $oficial->chk;
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

    $oArrayDesplFirmasOficina = new web\DesplegableArray($oficiales,$a_posibles_cargos_oficina,'firmas_oficina');
    $oArrayDesplFirmasOficina ->setBlanco('t');
    $oArrayDesplFirmasOficina ->setAccionConjunto('fnjs_mas_firmas_oficina(event)');
    
    $oArrayDesplFirmas = new web\DesplegableArray($oficinas,$a_posibles_cargos,'firmas');
    $oArrayDesplFirmas ->setBlanco('t');
    $oArrayDesplFirmas ->setAccionConjunto('fnjs_mas_firmas(event)');
        
    $oDesplOficinas = new web\Desplegable('oficina_buscar_1',$a_posibles_oficinas,'',TRUE);
    $oDesplCargos = new web\Desplegable('oficina_buscar_2',$a_posibles_cargos,'',TRUE);
}

$lista_antecedentes = $oExpediente->getHtmlAntecedentes();

$url_update = 'apps/expedientes/controller/expediente_update.php';
$url_ajax = 'apps/tramites/controller/tramitecargo_ajax.php';
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query(['filtro' => $Qfiltro]));
$pagina_nueva = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query([]));

$pag_escrito =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_ESCRITO]));
$pag_propuesta =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_PROPUESTA]));
$pag_plantilla =  web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_PLANTILLA]));
$pag_respuesta =  web\Hash::link('apps/entradas/controller/buscar_form.php?'.http_build_query(['id_expediente' => $Qid_expediente,'filtro' => $Qfiltro]));
$server = ConfigGlobal::getWeb(); //http://tramity.local

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
    //búsquedas
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplCargos' => $oDesplCargos,
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