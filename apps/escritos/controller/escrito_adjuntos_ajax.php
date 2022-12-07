<?php

use core\ConfigGlobal;
use core\ViewTwig;
use documentos\model\Documento;
use documentos\model\entity\GestorEtiquetaDocumento;
use documentos\model\GestorDocumento;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use etherpad\model\GestorEtherpad;
use etiquetas\model\entity\GestorEtiqueta;
use usuarios\domain\repositories\CargoRepository;
use web\DateTimeLocal;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
$Q_oficina_buscar = (integer)filter_input(INPUT_POST, 'oficina_buscar');

$CargoRepository = new CargoRepository();
$a_posibles_cargos = $CargoRepository->getArrayCargos();
$error_txt = '';
switch ($Q_que) {
    case 'insertar_copia':
    case 'insertar':
        $Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');
        $Q_force = (string)filter_input(INPUT_POST, 'force');

        $oDocumento = new Documento($Q_id_doc);
        if ($Q_force === 'false') {
            // Avisar si está como antecedente en algún sitio:
            $error_txt .= $oDocumento->comprobarEliminar($Q_id_doc);
            if (!empty($error_txt)) {
                $jsondata['err_tipo'] = 'antecedente';
            }
        }

        if (empty($error_txt)) {
            $gesEtherpad = new GestorEtherpad();
            if ($Q_que === 'insertar_copia') {
                $error_txt .= $gesEtherpad->copyDocToEscrito($Q_id_doc, $Q_id_escrito, 'true');
            } elseif ($Q_que === 'insertar') {
                $error_txt .= $gesEtherpad->moveDocToEscrito($Q_id_doc, $Q_id_escrito, 'true');
                // borrar el documento:
                $oDocumento = new Documento($Q_id_doc);
                if ($oDocumento->DBEliminar() === FALSE) {
                    $error_txt .= ($oDocumento->getErrorTxt());
                }
            }
            $oEscrito = new Escrito($Q_id_escrito);
            if ($oEscrito->DBCargar() === FALSE) {
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oEscrito->setTipo_doc(Documento::DOC_ETHERPAD);
            if ($oEscrito->DBGuardar() === FALSE) {
                $error_txt .= ($oEscrito->getErrorTxt());
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
        $Q_id_adjunto = (integer)filter_input(INPUT_POST, 'id_adjunto');

        $oEscritoAdjunto = new EscritoAdjunto($Q_id_adjunto);
        // borrar de adjuntos
        if ($oEscritoAdjunto->DBEliminar() === FALSE) {
            $error_txt .= $oEscritoAdjunto->getErrorTxt();
        }
        // eliminar el pad:
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ADJUNTO, $Q_id_adjunto);
        $sourceID = $oEtherpad->getId_pad();

        $rta = $oEtherpad->deletePad($sourceID);
        if ($rta->getCode() === 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            echo $oEtherpad->mostrar_error($rta);
        }

        $oEscrito = new Escrito($Q_id_escrito);
        echo $oEscrito->getHtmlAdjuntos();
        break;
    case 'adjuntar_copia':
    case 'adjuntar':
        $Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');
        $Q_force = (string)filter_input(INPUT_POST, 'force');
        // recuperar el documento
        $oDocumento = new Documento($Q_id_doc);
        $tipo_doc = $oDocumento->getTipo_doc();

        if ($Q_force === 'false') {
            // Avisar si está como antecedente en algun sitio:
            $error_txt .= $oDocumento->comprobarEliminar($Q_id_doc);
            if (!empty($error_txt)) {
                $jsondata['err_tipo'] = 'antecedente';
            }
        }
        if (empty($error_txt)) {
            switch ($tipo_doc) {
                case Documento::DOC_ETHERPAD:
                    $fileName = $oDocumento->getNom();
                    // gravar en adjuntos escrito
                    // new
                    $oEscritoAdjunto = new EscritoAdjunto();
                    $oEscritoAdjunto->setId_escrito($Q_id_escrito);
                    $oEscritoAdjunto->setNom($fileName);
                    $oEscritoAdjunto->setTipo_doc(Documento::DOC_ETHERPAD);

                    if ($oEscritoAdjunto->DBGuardar() === FALSE) {
                        $error_txt .= $oEscritoAdjunto->getErrorTxt();
                    }
                    $id_item = $oEscritoAdjunto->getId_item();

                    $gesEtherpad = new GestorEtherpad();
                    if ($Q_que === 'adjuntar_copia') {
                        $error_txt .= $gesEtherpad->copyDocToAdjunto($Q_id_doc, $id_item, 'true');
                    } elseif ($Q_que === 'adjuntar') {
                        $error_txt .= $gesEtherpad->moveDocToAdjunto($Q_id_doc, $id_item, 'true');
                        // borra de la lista de documentos:
                        $oDocumento->DBEliminar();
                    }
                    break;
                case Documento::DOC_UPLOAD:
                    $nombre_fichero = $oDocumento->getNombre_fichero();
                    $contenido_doc = $oDocumento->getDocumento();

                    // gravar en adjuntos escrito
                    // new
                    $oEscritoAdjunto = new EscritoAdjunto();
                    $oEscritoAdjunto->setId_escrito($Q_id_escrito);
                    $oEscritoAdjunto->setNom($nombre_fichero);
                    $oEscritoAdjunto->setAdjunto($contenido_doc);
                    $oEscritoAdjunto->setTipo_doc(Documento::DOC_UPLOAD);

                    if ($oEscritoAdjunto->DBGuardar() === FALSE) {
                        $error_txt .= $oEscritoAdjunto->getErrorTxt();
                    }
                    // NO sirve el metodo 'refresh' del fileinput parar cambiar la lista de docuemntos.
                    // habrá que refrescar toda la página

                    // borrar de documentos
                    if ($Q_que === 'adjuntar' && $oDocumento->DBEliminar() === FALSE) {
                        $error_txt .= $oDocumento->getErrorTxt();
                    }
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
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
        $Q_tipo_n = (integer)filter_input(INPUT_POST, 'tipo_n');
        $Q_nom = (string)filter_input(INPUT_POST, 'nom');
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $a_etiquetas_filtered = array_filter($Q_a_etiquetas);

        $Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');

        $aWhere = [];
        $aOperador = [];
        // sólo los de mi oficina:
        $id_oficina = ConfigGlobal::role_id_oficina();
        $a_cargos_oficina = $CargoRepository->getArrayCargosOficina($id_oficina);
        $a_cargos = [];
        foreach (array_keys($a_cargos_oficina) as $id_cargo) {
            $a_cargos[] = $id_cargo;
        }
        if (!empty($a_cargos)) {
            $aWhere['creador'] = implode(',', $a_cargos);
            $aOperador['creador'] = 'IN';
        }

        $gesEtiquetas = new GestorEtiqueta();
        $a_posibles_etiquetas = $gesEtiquetas->getArrayMisEtiquetas();
        $oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
        $oArrayDesplEtiquetas->setBlanco('t');
        $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

        $chk_or = ($Q_andOr === 'OR') ? 'checked' : '';
        // por defecto 'AND':
        $chk_and = (($Q_andOr === 'AND') || empty($Q_andOr)) ? 'checked' : '';

        if (!empty($Q_a_etiquetas)) {
            $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
            $cDocumentos = $gesEtiquetasDocumento->getArrayDocumentos($Q_a_etiquetas, $Q_andOr);
            if (!empty($cDocumentos)) {
                $aWhere['id_doc'] = implode(',', $cDocumentos);
                $aOperador['id_doc'] = 'IN';
            } else {
                // No hay ninguno. No importa el resto de condiciones
                exit(_("No hay ningún documento con estas etiquetas"));
            }
        }

        if (!empty($Q_nom)) {
            $aWhere['nom'] = $Q_nom;
            $aOperador['nom'] = 'sin_acentos';
        }
        $sel_mes = '';
        $sel_mes_6 = '';
        $sel_any_1 = '';
        $sel_any_2 = '';
        $sel_siempre = '';
        switch ($Q_periodo) {
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
            case "mes":
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
        if (empty($Q_nom && empty($Q_oficina_buscar))) {
            $aWhere['_limit'] = 50;
        }
        $aWhere['_ordre'] = 'f_upload DESC';

        $gesDocumento = new GestorDocumento();
        $cDocumentos = $gesDocumento->getDocumentos($aWhere, $aOperador);

        $a_cabeceras = [['width' => 70, 'name' => _("fecha")],
            ['width' => 500, 'name' => _("nombre")],
            ['width' => 50, 'name' => _("ponente")],
            ['width' => 50, 'name' => _("etiquetas")],
            ''];

        $txt_ajuntar = ($Q_tipo_n === 5) ? _("abrir") : _("adjuntar");
        $a_valores = [];
        $a = 0;
        foreach ($cDocumentos as $oDocumento) {
            // Si sólo quiero los etherpad, quitar el resto:
            if ($Q_tipo_n === 5 && $oDocumento->getTipo_doc() != Documento::DOC_ETHERPAD) {
                continue;
            }
            // mirar permisos...
            $visibilidad = $oDocumento->getVisibilidad();

            if (ConfigGlobal::soy_dtor() === FALSE
                && $visibilidad == Documento::V_PERSONAL
                && $oDocumento->getCreador() != ConfigGlobal::role_id_cargo()
            ) {
                continue;
            }
            $a++;
            $id_doc = $oDocumento->getId_doc();
            $fecha_txt = $oDocumento->getF_upload()->getFromLocal();
            $id_creador = $oDocumento->getCreador();

            $creador = $a_posibles_cargos[$id_creador];

            if ($Q_tipo_n === 5) {
                $add = "<span class=\"btn btn-link\" onclick=\"fnjs_confirm_insertar_documento('documento','$id_doc','$Q_id_escrito');\" >$txt_ajuntar</span>";
            } else {
                $add = "<span class=\"btn btn-link\" onclick=\"fnjs_confirm_adjuntar_documento('documento','$id_doc','$Q_id_escrito');\" >$txt_ajuntar</span>";
            }

            $a_valores[$a][1] = $fecha_txt;
            $a_valores[$a][2] = $oDocumento->getNom();
            $a_valores[$a][3] = $creador;
            $a_valores[$a][4] = $oDocumento->getEtiquetasVisiblesTxt();
            $a_valores[$a][5] = $add;
        }

        $oLista = new Lista();
        $oLista->setCabeceras($a_cabeceras);
        $oLista->setDatos($a_valores);

        // Alerta!!
        $alerta = '';
        if ($Q_tipo_n === 5) {
            $alerta = _("ATENCIÓN: Sólo se pueden insertar los de tipo Etherpad");
        }
        $a_campos = [
            'para' => 'adjunto',
            'id_expediente' => $Q_id_expediente,
            'id_escrito' => $Q_id_escrito,
            'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
            'chk_and' => $chk_and,
            'chk_or' => $chk_or,
            'nom' => $Q_nom,
            'sel_mes' => $sel_mes,
            'sel_mes_6' => $sel_mes_6,
            'sel_any_1' => $sel_any_1,
            'sel_any_2' => $sel_any_2,
            'sel_siempre' => $sel_siempre,
            'oLista' => $oLista,
            'tipo_n' => $Q_tipo_n,
            'alerta' => $alerta
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('modal_buscar_documentos.html.twig', $a_campos);
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
