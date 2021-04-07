<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use entradas\model\entity\GestorEntradaDB;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use expedientes\model\Expediente;
use expedientes\model\GestorEscrito;
use expedientes\model\GestorExpediente;
use lugares\model\entity\GestorLugar;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Qque = (string) \filter_input(INPUT_POST, 'que');

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qoficina_buscar = (integer) \filter_input(INPUT_POST, 'oficina_buscar');

$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
//n = 1 -> Entradas
//n = 2 -> Expedientes
//n = 3 -> Escritos-propuestas
switch ($Qque) {
    case 'quitar':
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qtipo_antecedente = (string) \filter_input(INPUT_POST, 'tipo_doc');
        
        $antecedente = [ 'tipo'=> $Qtipo_antecedente, 'id' => $Qid_escrito ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->delAntecedente($json_antecedente);
        $oExpediente->DBGuardar();
        
        echo $oExpediente->getHtmlAntecedentes();
        break;
    case 'adjuntar':
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qtipo_antecedente = (string) \filter_input(INPUT_POST, 'tipo_doc');
        
        $antecedente = [ 'tipo'=> $Qtipo_antecedente, 'id' => $Qid_escrito ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->addAntecedente($json_antecedente);
        $oExpediente->DBGuardar();
        
        echo $oExpediente->getHtmlAntecedentes();
        break;
	case 'buscar_entrada':
	case 'buscar_1':
        //n = 1 -> Entradas
	    $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
	    $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    $Qperiodo =  (string) \filter_input(INPUT_POST, 'periodo');
	    $Qorigen_id_lugar =  (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
	    
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
	    $gesEntradas = new GestorEntrada();
	    $aWhere = [];
	    $aOperador = [];
	    if (!empty($Qoficina_buscar)) {
            $aWhere['ponente'] = $Qoficina_buscar;
	    }
	    if (!empty($Qasunto)) {
            // en este caso el operador es 'sin_acentos'
	        $aWhere['asunto_detalle'] = $Qasunto;
	    }

	    $sel_mes = '';
	    $sel_mes_6 = '';
	    $sel_any_1 = '';
	    $sel_any_2 = '';
	    $sel_siempre = '';
	    switch ($Qperiodo) {
	        case "mes":
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	            break;
	        case "mes_6":
	            $sel_mes_6 = 'selected';
	            $periodo = 'P6M';
	            break;
	        case "any_1":
	            $sel_any_1 = 'selected';
	            $periodo = 'P1Y';
	            break;
	        case "any_2":
	            $sel_any_2 = 'selected';
	            $periodo = 'P2Y';
	            break;
	        case "siempre":
	            $sel_siempre = 'selected';
	            $periodo = '';
	            break;
	        default:
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	    }
	    if (!empty($periodo)) {
	        $oFecha = new DateTimeLocal();
	        $oFecha->sub(new DateInterval($periodo));
	        $aWhere['f_entrada'] = $oFecha->getIso();
	        $aOperador['f_entrada'] = '>';
	    }
	    
	    // por defecto, buscar sólo 50.
	    if (empty($Qasunto && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 50;
	    }
	    $aWhere['_ordre'] = 'f_entrada DESC';
	    
	    if (!empty($Qorigen_id_lugar)) {
	            $gesEntradas = new GestorEntradaDB();
	            $id_lugar = $Qorigen_id_lugar;
	            $cEntradas = $gesEntradas->getEntradasByLugarDB($id_lugar,$aWhere, $aOperador);
        } else {
            $cEntradas = $gesEntradas->getEntradas($aWhere,$aOperador);
        }
	    
	    $a_cabeceras = [ '',[ 'width' => 200, 'name' => _("protocolo")],
	                       [ 'width' => 100, 'name' => _("fecha")],
	                       [ 'width' => 600, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")],
	                   ''];
	    $a_valores = [];
	    $a = 0;
	    $oProtOrigen = new Protocolo();
	    $oPermRegistro = new PermRegistro();
	    foreach ($cEntradas as $oEntrada) {
	        $perm_ver_escrito = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
	        if ($perm_ver_escrito < PermRegistro::PERM_VER) {
	            continue;
	        }
	        $a++;
	        $id_entrada = $oEntrada->getId_entrada();
	        $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
	        $id_of_ponente = $oEntrada->getPonente();
	        $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
	        $proto_txt = $oProtOrigen->ver_txt();
	        
	        $ponente_txt = empty($a_posibles_oficinas[$id_of_ponente])? '?' : $a_posibles_oficinas[$id_of_ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$id_entrada','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $proto_txt;
	        $a_valores[$a][3] = $fecha_txt;
	        $a_valores[$a][4] = $oEntrada->getAsuntoDetalle();
	        $a_valores[$a][5] = $ponente_txt;
	        $a_valores[$a][6] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    
	    $gesOficinas = new GestorOficina();
	    $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
	    $oDesplOficinas = new web\Desplegable('oficina_buscar',$a_posibles_oficinas,$Qoficina_buscar,TRUE);

	    $gesLugares = new GestorLugar();
	    $a_lugares = $gesLugares->getArrayBusquedas();
	    $oDesplOrigen = new Desplegable();
	    $oDesplOrigen->setNombre('origen_id_lugar');
	    $oDesplOrigen->setBlanco(TRUE);
	    $oDesplOrigen->setOpciones($a_lugares);
	    $oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");
	    $oDesplOrigen->setOpcion_sel($Qorigen_id_lugar);
	    $a_campos = [
                'oDesplOrigen' => $oDesplOrigen,
                'oDesplOficinas' => $oDesplOficinas,
                'oLista' => $oLista,  
                'asunto' => $Qasunto,
                'sel_mes' => $sel_mes,
                'sel_mes_6' => $sel_mes_6,
                'sel_any_1' => $sel_any_1,
                'sel_any_2' => $sel_any_2,
                'sel_siempre' => $sel_siempre,
             ];
	    $oView = new ViewTwig('expedientes/controller');
	    echo $oView->renderizar('modal_buscar_entradas.html.twig',$a_campos);
	    break;
	case 'buscar_expediente':
	case 'buscar_2':
        //n = 2 -> Expediente
	    $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
	    $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    $Qperiodo =  (string) \filter_input(INPUT_POST, 'periodo');
	    
	    $gesExpediente = new GestorExpediente();
	    $aWhere = [];
	    $aOperador = [];
	    // sólo los de mi oficina:
	    $id_oficina = ConfigGlobal::role_id_oficina();
	    $gesCargos = new GestorCargo();
	    $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
	    $a_cargos = [];
	    foreach (array_keys($a_cargos_oficina) as $id_cargo) {
	        $a_cargos[] = $id_cargo;
	    }
	    if (!empty($a_cargos)) {
	        $aWhere['ponente'] = implode(',',$a_cargos);
	        $aOperador['ponente'] = 'IN';
	    }
	    
	    $gesEtiquetas = new GestorEtiqueta();
	    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
	    $a_posibles_etiquetas = [];
	    foreach ($cEtiquetas as $oEtiqueta) {
	        $id_etiqueta = $oEtiqueta->getId_etiqueta();
	        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
	        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
	    }
	    
	    $oArrayDesplEtiquetas = new web\DesplegableArray($Qa_etiquetas,$a_posibles_etiquetas,'etiquetas');
	    $oArrayDesplEtiquetas ->setBlanco('t');
	    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas(event)');
	    
	    if (!empty($Qa_etiquetas)) {
	        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
	        $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($Qa_etiquetas);
	        if (!empty($cExpedientes)) {
	            $aWhere['id_expediente'] = implode(',',$cExpedientes);
	            $aOperador['id_expediente'] = 'IN';
	        } else {
	            // No hay ninguno. No importa el resto de condiciones
	            exit(_("No hay ningún expediente con estas etiquetas"));
	        }
	    }
	    
	    if (!empty($Qasunto )) {
	        $aWhere['asunto'] = $Qasunto;
	        $aOperador['asunto'] = 'sin_acentos';
	    }
	    $sel_mes = '';
	    $sel_mes_6 = '';
	    $sel_any_1 = '';
	    $sel_any_2 = '';
	    $sel_siempre = '';
	    switch ($Qperiodo) {
	        case "mes":
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	            break;
	        case "mes_6":
	            $sel_mes_6 = 'selected';
	            $periodo = 'P6M';
	            break;
	        case "any_1":
	            $sel_any_1 = 'selected';
	            $periodo = 'P1Y';
	            break;
	        case "any_2":
	            $sel_any_2 = 'selected';
	            $periodo = 'P2Y';
	            break;
	        case "siempre":
	            $sel_siempre = 'selected';
	            $periodo = '';
	            break;
	        default:
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	    }
	    if (!empty($periodo)) {
	        $oFecha = new DateTimeLocal();
	        $oFecha->sub(new DateInterval($periodo));
	        $aWhere['f_aprobacion'] = $oFecha->getIso();
	        $aOperador['f_aprobacion'] = '>';
	    }
	    
	    // por defecto, buscar sólo 50.
	    if (empty($Qasunto && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 50;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    $cExpedientes = $gesExpediente->getExpedientes($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")],
	                       [ 'width' => 50, 'name' => _("etiquetas")],
	                   ''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cExpedientes as $oExpediente) {
	        $a++;
	        // mirar permisos...
	        $visibilidad = $oExpediente->getVisibilidad();
	        if ( ($visibilidad == Entrada::V_RESERVADO OR $visibilidad == Entrada::V_RESERVADO_VCD)
	            && ConfigGlobal::soy_dtor() === FALSE) {
	                continue;
	        }
	        $id_expediente = $oExpediente->getId_expediente();
	        $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
	        $ponente = $oExpediente->getPonente();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('expediente','$id_expediente','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oExpediente->getAsunto();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $oExpediente->getEtiquetasVisiblesTxt();
	        $a_valores[$a][6] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    //echo $oLista->mostrar_tabla_html();

	    $a_campos = [
	        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
	        'asunto' => $Qasunto,
	        'sel_mes' => $sel_mes,
	        'sel_mes_6' => $sel_mes_6,
	        'sel_any_1' => $sel_any_1,
	        'sel_any_2' => $sel_any_2,
	        'sel_siempre' => $sel_siempre,
            'oLista' => $oLista,  
	    ];
	    
	    $oView = new ViewTwig('expedientes/controller');
	    echo $oView->renderizar('modal_buscar_expedientes.html.twig',$a_campos);
	    break;
	case 'buscar_escrito':
	case 'buscar_3':
        //n = 3 -> Escrito
	    $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
	    $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    $Qperiodo =  (string) \filter_input(INPUT_POST, 'periodo');
	    $Qdest_id_lugar =  (integer) \filter_input(INPUT_POST, 'dest_id_lugar');
	    
	    $gesEscrito = new GestorEscrito();
	    $aWhere = [];
	    $aOperador = [];
	    // Sólo los escritos que ya se han enviado
        $aWhere['f_salida'] = 'x';
	    $aOperador['f_salida'] = 'IS NOT NULL'; 
	    if (!empty($Qoficina_buscar)) {
            $aWhere['creador'] = $Qoficina_buscar;
	    }
	    if (!empty($Qasunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $Qasunto;
	    }

	    $sel_mes = '';
	    $sel_mes_6 = '';
	    $sel_any_1 = '';
	    $sel_any_2 = '';
	    $sel_siempre = '';
	    switch ($Qperiodo) {
	        case "mes":
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	            break;
	        case "mes_6":
	            $sel_mes_6 = 'selected';
	            $periodo = 'P6M';
	            break;
	        case "any_1":
	            $sel_any_1 = 'selected';
	            $periodo = 'P1Y';
	            break;
	        case "any_2":
	            $sel_any_2 = 'selected';
	            $periodo = 'P2Y';
	            break;
	        case "siempre":
	            $sel_siempre = 'selected';
	            $periodo = '';
	            break;
	        default:
	            $sel_mes = 'selected';
	            $periodo = 'P1M';
	    }
	    if (!empty($periodo)) {
	        $oFecha = new DateTimeLocal();
	        $oFecha->sub(new DateInterval($periodo));
	        $aWhere['f_aprobacion'] = $oFecha->getIso();
	        $aOperador['f_aprobacion'] = '>';
	    }
	    
	    // por defecto, buscar sólo 50.
	    if (empty($Qasunto && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 50;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    if (!empty($Qdest_id_lugar)) {
	            $gesEscritos = new GestorEscrito();
	            $id_lugar = $Qdest_id_lugar;
	            $cEscritos = $gesEscritos->getEscritosByLugarDB($id_lugar,$aWhere,$aOperador);
        } else {
            $cEscritos = $gesEscrito->getEscritos($aWhere,$aOperador);
        }
	    
	    $a_cabeceras = [ '',[ 'width' => 150, 'name' => _("protocolo")],
	        [ 'width' => 100, 'name' => _("fecha")],
	        [ 'width' => 650, 'name' => _("asunto")],
	        [ 'width' => 50, 'name' => _("ponente")],
	        [ 'width' => 50, 'name' => _("destinos")],
	        ''];
	    $a_valores = [];
	    $a = 0;
	    $oProtLocal = new Protocolo();
	    $oPermRegistro = new PermRegistro();
	    foreach ($cEscritos as $oEscrito) {
	        $perm_ver_escrito = $oPermRegistro->permiso_detalle($oEscrito, 'escrito');
	        if ($perm_ver_escrito < PermRegistro::PERM_VER) {
	            continue;
	        }
	        $a++;
	        $id_escrito = $oEscrito->getId_escrito();
	        $fecha_txt = $oEscrito->getF_aprobacion()->getFromLocal();
	        $ponente = $oEscrito->getCreador();
	        $oProtLocal->setJson($oEscrito->getJson_prot_local());
	        $proto_txt = $oProtLocal->ver_txt();
	        
	        $ponente_txt = empty($a_posibles_cargos[$ponente])? '?' : $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('escrito','$id_escrito','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $proto_txt;
	        $a_valores[$a][3] = $fecha_txt;
	        $a_valores[$a][4] = $oEscrito->getAsuntoDetalle();
	        $a_valores[$a][5] = $ponente_txt;
	        $a_valores[$a][6] = $oEscrito->getDestinosEscrito();
	        $a_valores[$a][7] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    
	    $oDesplCargos = new web\Desplegable('oficina_buscar',$a_posibles_cargos,$Qoficina_buscar,TRUE);
	    
	    $gesLugares = new GestorLugar();
	    $a_lugares = $gesLugares->getArrayBusquedas();
	    $oDesplDestino = new Desplegable();
	    $oDesplDestino->setNombre('dest_id_lugar');
	    $oDesplDestino->setBlanco(TRUE);
	    $oDesplDestino->setOpciones($a_lugares);
	    $oDesplDestino->setOpcion_sel($Qdest_id_lugar);
	    
	    $a_campos = [
                'oDesplCargos' => $oDesplCargos,
                'oDesplDestino' => $oDesplDestino,
                'oLista' => $oLista,  
                'asunto' => $Qasunto,
                'sel_mes' => $sel_mes,
                'sel_mes_6' => $sel_mes_6,
                'sel_any_1' => $sel_any_1,
                'sel_any_2' => $sel_any_2,
                'sel_siempre' => $sel_siempre,
                ];
	    $oView = new ViewTwig('expedientes/controller');
	    echo $oView->renderizar('modal_buscar_escritos.html.twig',$a_campos);
	    break;
	case 'buscar_expediente_borrador':
        $Qasunto_buscar = (string) \filter_input(INPUT_POST, 'asunto_buscar');
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        // Expediente de mi oficina en borrador
	    $gesExpediente = new GestorExpediente();
	    $aWhere = [];
	    $aOperador = [];
        $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
        // posibles oficiales de la oficina:
        $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
        $id_oficina = $oCargo->getId_oficina();
        $gesCargos = new GestorCargo();
        $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
        $a_cargos = [];
        foreach (array_keys($a_cargos_oficina) as $id_cargo) {
            $a_cargos[] = $id_cargo;
        }
        if (!empty($a_cargos)) {
            $aWhere['ponente'] = implode(',',$a_cargos);
            $aOperador['ponente'] = 'IN';
        }
	    // por defecto, buscar sólo 15.
	    if (empty($Qasunto_buscar)) {
	        $aWhere['_limit'] = 15;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    $cExpedientes = $gesExpediente->getExpedientes($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")]
	                   ,''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cExpedientes as $oExpediente) {
	        $a++;
	        $id_expediente = $oExpediente->getId_expediente();
	        $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
	        $ponente = $oExpediente->getPonente();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >"._("ver")."</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$Qid_entrada','$id_expediente');\" >"._("adjuntar")."</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oExpediente->getAsunto();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    echo $oLista->mostrar_tabla_html();
	    break;
}
