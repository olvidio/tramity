<?php

use core\ConfigGlobal;
use core\ViewTwig;
use documentos\model\Documento;
use documentos\model\GestorDocumento;
use documentos\model\entity\GestorEtiquetaDocumento;
use entradas\model\Entrada;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\Expediente;
use expedientes\model\entity\EscritoAdjunto;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Lista;
use etherpad\model\GestorEtherpad;
use expedientes\model\Escrito;
use etherpad\model\Etherpad;
use expedientes\model\GestorExpediente;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
$Qoficina_buscar = (integer) \filter_input(INPUT_POST, 'oficina_buscar');

$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
$error_txt = '';
switch ($Qque) {
    case 'insertar':
        $Qid_doc = (integer) \filter_input(INPUT_POST, 'id_doc');
        
        $gesEtherpad = new GestorEtherpad();
        $error_txt .= $gesEtherpad->moveDocToEscrito($Qid_doc, $Qid_escrito, TRUE);
        
        if (empty($error_txt)) {
            // borrar el documento:
            $oDocumento = new Documento($Qid_doc);
            // Avisar si está como antecedente en algun sitio:
            $error_txt .= $oDocumento->comprobarEliminar($Qid_doc);
            if (empty($error_txt)) {
                $oDocumento = new Documento($Qid_doc);
                if ($oDocumento->DBEliminar() === FALSE) {
                    $error_txt .= ($oDocumento->getErrorTxt());
                }
                $oEscrito = new Escrito($Qid_escrito);
                $oEscrito->DBCarregar();
                $oEscrito->setTipo_doc(Documento::DOC_ETHERPAD);
                if ($oEscrito->DBGuardar() === FALSE) {
                    $error_txt .= ($oEscrito->getErrorTxt());
                }
            }
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
        break;
    case 'quitar':
        $Qid_adjunto = (integer) \filter_input(INPUT_POST, 'id_adjunto');
        
        $oEscritoAdjunto = new EscritoAdjunto($Qid_adjunto);
        // borrar de adjuntos
        if ($oEscritoAdjunto->DBEliminar() === FALSE) {
            $error_txt .= $oEscritoAdjunto->getErrorTxt();
        }
        // eliminar el pad:
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ADJUNTO,$Qid_adjunto);
        $sourceID = $oEtherpad->getId_pad();
        
        $rta = $oEtherpad->deletePad($sourceID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            echo $oEtherpad->mostrar_error($rta);
        }
        
        $oEscrito = new Escrito($Qid_escrito);
        echo $oEscrito->getHtmlAdjuntos();
        break;
    case 'adjuntar':
        $Qid_doc = (integer) \filter_input(INPUT_POST, 'id_doc');
        // recuperar el documento
        $oDocumento = new Documento($Qid_doc);
        $tipo_doc = $oDocumento->getTipo_doc();
        
        // Avisar si está como antecedente en algun sitio:
        $error_txt .= $oDocumento->comprobarEliminar($Qid_doc);
        if (empty($error_txt)) {
            switch ($tipo_doc) {
                case Documento::DOC_ETHERPAD:
                    $fileName = $oDocumento->getNom();
                    // gravar en adjuntos escrito
                    // new
                    $oEscritoAdjunto = new EscritoAdjunto();
                    $oEscritoAdjunto->setId_escrito($Qid_escrito);
                    $oEscritoAdjunto->setNom($fileName);
                    $oEscritoAdjunto->setTipo_doc(Documento::DOC_ETHERPAD);
                    
                    if ($oEscritoAdjunto->DBGuardar() === FALSE) {
                        $error_txt .= $oEscritoAdjunto->getErrorTxt();
                    }
                    $id_item = $oEscritoAdjunto->getId_item();
                    
                    $gesEtherpad = new GestorEtherpad();
                    $error_txt .= $gesEtherpad->moveDocToAdjunto($Qid_doc, $id_item, TRUE);
                    break;
                case Documento::DOC_UPLOAD:
                    $contenido_encoded = $oDocumento->getDocumentoTxt();
                    $contenido_doc = base64_decode($contenido_encoded);
                    $fileName = $oDocumento->getNombre_fichero();
                    
                    // gravar en adjuntos escrito
                    // new
                    $oEscritoAdjunto = new EscritoAdjunto();
                    $oEscritoAdjunto->setId_escrito($Qid_escrito);
                    $oEscritoAdjunto->setNom($fileName);
                    $oEscritoAdjunto->setAdjunto($contenido_doc);
                    $oEscritoAdjunto->setTipo_doc(Documento::DOC_UPLOAD);
                    
                    if ($oEscritoAdjunto->DBGuardar() === FALSE) {
                        $error_txt .= $oEscritoAdjunto->getErrorTxt();
                    }
                    // NO sirve el metodo ¡refresh' del fileinput parar cambiar la lista de docuemntos.
                    // habrá que refrescar toda la página
                    
                    // borrar de documentos
                    if ($oDocumento->DBEliminar() === FALSE) {
                        $error_txt .= $oDocumento->getErrorTxt();    
                    }
                    break;
            }
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
        break;
	case 'buscar_documento':
	case 'buscar_4':
	case 'buscar_5':
        //n = 4 -> Documento Upload y Etherpad (adjuntar)
        //n = 5 -> Documento Etherpad (insertar)
	    $Qtipo_n = (string) \filter_input(INPUT_POST, 'tipo_n');
	    $Qnom = (string) \filter_input(INPUT_POST, 'nom');
	    $QandOr = (string) \filter_input(INPUT_POST, 'andOr');
	    $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    $Qperiodo =  (string) \filter_input(INPUT_POST, 'periodo');
	    
	    $gesDocumento = new GestorDocumento();
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
	        $aWhere['creador'] = implode(',',$a_cargos);
	        $aOperador['creador'] = 'IN';
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
	    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');
	    
	    $chk_or = ($QandOr == 'OR')? 'checked' : '';
	    // por defecto 'AND':
        $chk_and = (($QandOr == 'AND') OR empty($QandOr))? 'checked' : '';
	    
	    if (!empty($Qa_etiquetas)) {
	        $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
	        $cDocumentos = $gesEtiquetasDocumento->getArrayDocumentos($Qa_etiquetas,$QandOr);
	        if (!empty($cDocumentos)) {
	            $aWhere['id_doc'] = implode(',',$cDocumentos);
	            $aOperador['id_doc'] = 'IN';
	        } else {
	            // No hay ninguno. No importa el resto de condiciones
	            exit(_("No hay ningún documento con estas etiquetas"));
	        }
	    }
	    
	    if (!empty($Qnom )) {
	        $aWhere['nom'] = $Qnom;
	        $aOperador['nom'] = 'sin_acentos';
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
	        $aWhere['f_upload'] = $oFecha->getIso();
	        $aOperador['f_upload'] = '>';
	    }
	    
	    // por defecto, buscar sólo 50.
	    if (empty($Qnom && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 50;
	    }
	    $aWhere['_ordre'] = 'f_upload DESC';
	    
	    $cDocumentos = $gesDocumento->getDocumentos($aWhere,$aOperador);
	    
	    $a_cabeceras = [ [ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("nombre")],
	                       [ 'width' => 50, 'name' => _("ponente")],
	                       [ 'width' => 50, 'name' => _("etiquetas")],
	                   ''];
	    
	    $txt_ajuntar = ($Qtipo_n == 5)? _("abrir") : _("adjuntar");
	    $a_valores = [];
	    $a = 0;
	    foreach ($cDocumentos as $oDocumento) {
            // Si sólo quiero los etherpad, quitar el resto:
	        if ($Qtipo_n == 5 && $oDocumento->getTipo_doc() != Documento::DOC_ETHERPAD) {
	            continue;
	        }
	        // mirar permisos...
	        $visibilidad = $oDocumento->getVisibilidad();
	        if ( ($visibilidad == Entrada::V_DIRECTORES OR $visibilidad == Entrada::V_RESERVADO OR $visibilidad == Entrada::V_RESERVADO_VCD)
	            && ConfigGlobal::soy_dtor() === FALSE) {
	                continue;
	        }
	        $a++;
	        $id_doc = $oDocumento->getId_doc();
	        $fecha_txt = $oDocumento->getF_upload()->getFromLocal();
	        $ponente = $oDocumento->getCreador();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        if ($Qtipo_n == 5) {
                $add = "<span class=\"btn btn-link\" onclick=\"fnjs_insertar_documento('documento','$id_doc','$Qid_escrito');\" >$txt_ajuntar</span>";
	        } else {
                $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_documento('documento','$id_doc','$Qid_escrito');\" >$txt_ajuntar</span>";
	        }
	        
	        $a_valores[$a][1] = $fecha_txt;
	        $a_valores[$a][2] = $oDocumento->getNom();
	        $a_valores[$a][3] = $ponente_txt;
	        $a_valores[$a][4] = $oDocumento->getEtiquetasVisiblesTxt();
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    //echo $oLista->mostrar_tabla_html();

	    $a_campos = [
            'id_escrito' => $Qid_escrito,
	        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
	        'chk_and' => $chk_and,
	        'chk_or' => $chk_or,
	        'nom' => $Qnom,
	        'sel_mes' => $sel_mes,
	        'sel_mes_6' => $sel_mes_6,
	        'sel_any_1' => $sel_any_1,
	        'sel_any_2' => $sel_any_2,
	        'sel_siempre' => $sel_siempre,
            'oLista' => $oLista,  
	    ];
	    
	    $oView = new ViewTwig('expedientes/controller');
	    echo $oView->renderizar('modal_buscar_documentos.html.twig',$a_campos);
	    break;
}
