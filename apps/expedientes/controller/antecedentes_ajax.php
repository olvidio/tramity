<?php

use core\ConfigGlobal;
use core\ViewTwig;
use documentos\model\Documento;
use documentos\model\entity\GestorEtiquetaDocumento;
use documentos\model\GestorDocumento;
use entradas\model\GestorEntrada;
use escritos\model\Escrito;
use escritos\model\GestorEscrito;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use expedientes\model\Expediente;
use expedientes\model\GestorExpediente;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use usuarios\model\PermRegistro;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;
use web\Protocolo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Q_que = (string)filter_input(INPUT_POST, 'que');

$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_oficina_buscar = (integer)filter_input(INPUT_POST, 'oficina_buscar');

$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
//n = 1 -> Entradas
//n = 2 -> Expedientes
//n = 3 -> Escritos-propuestas
switch ($Q_que) {
    case 'quitar':
        $Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
        $Q_tipo_antecedente = (string)filter_input(INPUT_POST, 'tipo_doc');

        $a_antecedente = ['tipo' => $Q_tipo_antecedente, 'id' => $Q_id_escrito];
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oExpediente->delAntecedente($a_antecedente);
        if ($oExpediente->DBGuardar() === FALSE) {
            exit($oExpediente->getErrorTxt());
        }
        echo $oExpediente->getHtmlAntecedentes();
        break;
    case 'adjuntar':
        $Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
        $Q_tipo_antecedente = (string)filter_input(INPUT_POST, 'tipo_doc');

        $a_antecedente = ['tipo' => $Q_tipo_antecedente, 'id' => $Q_id_escrito];
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oExpediente->addAntecedente($a_antecedente);
        if ($oExpediente->DBGuardar() === FALSE) {
            exit($oExpediente->getErrorTxt());
        }
        echo $oExpediente->getHtmlAntecedentes();
        break;
    case 'buscar_entrada':
    case 'buscar_1':
        //n = 1 -> Entradas
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
        $Q_origen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');
        $Q_origen_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
        $Q_origen_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
        $Q_chk_anulados = (bool)filter_input(INPUT_POST, 'chk_anulados');

        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
        $gesEntradas = new GestorEntrada();
        $aWhere = [];
        $aOperador = [];
        if (!empty($Q_oficina_buscar)) {
            $aWhere['ponente'] = $Q_oficina_buscar;
        }
        if (!empty($Q_asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $Q_asunto;
        }

        switch ($Q_periodo) {
            case "mes":
                $periodo = 'P1M';
                break;
            case "mes_6":
                $periodo = 'P6M';
                break;
            case "any_1":
                $periodo = 'P1Y';
                break;
            case "any_2":
                $periodo = 'P2Y';
                break;
            case "siempre":
                $periodo = '';
                break;
            default:
                $periodo = 'P1M';
        }
        if (!empty($periodo)) {
            $oFecha = new DateTimeLocal();
            $oFecha->sub(new DateInterval($periodo));
            $aWhere['f_entrada'] = $oFecha->getIso();
            $aOperador['f_entrada'] = '>';
        }

        // por defecto, buscar sólo 50.
        if (empty($Q_asunto && empty($Q_oficina_buscar))) {
            $aWhere['_limit'] = 50;
        }
        $aWhere['_ordre'] = 'f_entrada DESC';

        if (!empty($Q_origen_id_lugar)) {
            $gesEntradas = new GestorEntrada();
            $id_lugar = $Q_origen_id_lugar;
            if (!empty($Q_origen_prot_num) && !empty($Q_origen_prot_any)) {
                // No tengo en cuenta las otras condiciones de la búsqueda
                $aProt_origen = ['id_lugar' => $Q_origen_id_lugar,
                    'num' => $Q_origen_prot_num,
                    'any' => $Q_origen_prot_any,
                ];
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
            } else {
                $cEntradas = $gesEntradas->getEntradasByLugarDB($id_lugar, $aWhere, $aOperador);
            }
        } else {
            $cEntradas = $gesEntradas->getEntradas($aWhere, $aOperador);
        }

        $a_cabeceras = ['', ['width' => 200, 'name' => _("protocolo")],
            ['width' => 100, 'name' => _("fecha")],
            ['width' => 600, 'name' => _("asunto")],
            ['width' => 50, 'name' => _("ponente")],
            ''];
        $a_valores = [];
        $a = 0;
        $oProtOrigen = new Protocolo();
        $oPermRegistro = new PermRegistro();
        foreach ($cEntradas as $oEntrada) {
            $perm_ver_entrada = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
            if ($perm_ver_entrada < PermRegistro::PERM_VER) {
                continue;
            }
            $a++;
            $id_entrada = $oEntrada->getId_entrada();
            $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
            $id_of_ponente = $oEntrada->getPonente();
            $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
            $proto_txt = $oProtOrigen->ver_txt();

            $ponente_txt = empty($a_posibles_oficinas[$id_of_ponente]) ? '?' : $a_posibles_oficinas[$id_of_ponente];

            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$id_entrada','$Q_id_expediente');\" >adjuntar</span>";

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
        $oDesplOficinas = new web\Desplegable('oficina_buscar', $a_posibles_oficinas, $Q_oficina_buscar, TRUE);

        $gesLugares = new GestorLugar();
        $a_lugares = $gesLugares->getArrayBusquedas($Q_chk_anulados);

        $oDesplOrigen = new Desplegable();
        $oDesplOrigen->setNombre('origen_id_lugar');
        $oDesplOrigen->setBlanco(TRUE);
        $oDesplOrigen->setOpciones($a_lugares);
        $oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");
        $oDesplOrigen->setOpcion_sel($Q_origen_id_lugar);

        if (is_true($Q_chk_anulados)) {
            $chk_ctr_anulados = 'checked';
        } else {
            $chk_ctr_anulados = '';
        }

        // para que no ponga '0'
        $Q_origen_prot_num = empty($Q_origen_prot_num) ? '' : $Q_origen_prot_num;
        $a_campos = [
            'id_expediente' => $Q_id_expediente,
            'oDesplOrigen' => $oDesplOrigen,
            'oDesplOficinas' => $oDesplOficinas,
            'oLista' => $oLista,
            'asunto' => $Q_asunto,
            'prot_num' => $Q_origen_prot_num,
            'prot_any' => $Q_origen_prot_any,
            'chk_ctr_anulados' => $chk_ctr_anulados,
        ];
        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('modal_buscar_entradas.html.twig', $a_campos);
        break;
    case 'buscar_expediente':
    case 'buscar_2':
        //n = 2 -> Expediente
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');

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
            $aWhere['ponente'] = implode(',', $a_cargos);
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

        $oArrayDesplEtiquetas = new web\DesplegableArray($Q_a_etiquetas, $a_posibles_etiquetas, 'etiquetas');
        $oArrayDesplEtiquetas->setBlanco('t');
        $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

        $chk_or = ($Q_andOr === 'OR') ? 'checked' : '';
        // por defecto 'AND':
        $chk_and = (($Q_andOr === 'AND') || empty($Q_andOr)) ? 'checked' : '';

        if (!empty($Q_a_etiquetas)) {
            $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
            $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($Q_a_etiquetas, $Q_andOr);
            if (!empty($cExpedientes)) {
                $aWhere['id_expediente'] = implode(',', $cExpedientes);
                $aOperador['id_expediente'] = 'IN';
            } else {
                // No hay ninguno. No importa el resto de condiciones
                exit(_("No hay ningún expediente con estas etiquetas"));
            }
        }

        if (!empty($Q_asunto)) {
            $aWhere['asunto'] = $Q_asunto;
            $aOperador['asunto'] = 'sin_acentos';
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
            $aWhere['f_aprobacion'] = $oFecha->getIso();
            $aOperador['f_aprobacion'] = '>';
        }

        // por defecto, buscar sólo 50.
        if (empty($Q_asunto && empty($Q_oficina_buscar))) {
            $aWhere['_limit'] = 50;
        }
        $aWhere['_ordre'] = 'f_aprobacion DESC';

        $cExpedientes = $gesExpediente->getExpedientes($aWhere, $aOperador);

        $a_cabeceras = ['', ['width' => 70, 'name' => _("fecha")],
            ['width' => 500, 'name' => _("asunto")],
            ['width' => 50, 'name' => _("ponente")],
            ['width' => 50, 'name' => _("etiquetas")],
            ''];
        $a_valores = [];
        $a = 0;
        $oPermiso = new PermRegistro();
        foreach ($cExpedientes as $oExpediente) {
            $a++;
            // mirar permisos...
            $visibilidad = $oExpediente->getVisibilidad();
            if (!$oPermiso->isVisibleDtor($visibilidad)) {
                continue;
            }
            $id_expediente = $oExpediente->getId_expediente();
            $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
            $ponente = $oExpediente->getPonente();

            $ponente_txt = $a_posibles_cargos[$ponente];

            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >ver</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('expediente','$id_expediente','$Q_id_expediente');\" >adjuntar</span>";

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

        $a_campos = [
            'id_expediente' => $Q_id_expediente,
            'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
            'chk_and' => $chk_and,
            'chk_or' => $chk_or,
            'asunto' => $Q_asunto,
            'sel_mes' => $sel_mes,
            'sel_mes_6' => $sel_mes_6,
            'sel_any_1' => $sel_any_1,
            'sel_any_2' => $sel_any_2,
            'sel_siempre' => $sel_siempre,
            'oLista' => $oLista,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('modal_buscar_expedientes.html.twig', $a_campos);
        break;
    case 'buscar_escrito':
    case 'buscar_3':
        //n = 3 -> Escrito
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
        $Q_dest_id_lugar = (integer)filter_input(INPUT_POST, 'dest_id_lugar');
        $Q_local_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
        $Q_local_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
        $Q_chk_anulados = (bool)filter_input(INPUT_POST, 'chk_anulados');

        $gesEscrito = new GestorEscrito();
        $aWhere = [];
        $aOperador = [];
        // Sólo los escritos que ya se han enviado
        $aWhere['f_salida'] = 'x';
        $aOperador['f_salida'] = 'IS NOT NULL';
        if (!empty($Q_oficina_buscar)) {
            $aWhere['creador'] = $Q_oficina_buscar;
        }
        if (!empty($Q_asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $Q_asunto;
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
            $aWhere['f_aprobacion'] = $oFecha->getIso();
            $aOperador['f_aprobacion'] = '>';
        }

        // por defecto, buscar sólo 50.
        if (empty($Q_asunto && empty($Q_oficina_buscar))) {
            $aWhere['_limit'] = 50;
        }
        $aWhere['_ordre'] = 'f_aprobacion DESC';

        if (!empty($Q_dest_id_lugar)) {
            $gesEscritos = new GestorEscrito();
            $id_lugar = $Q_dest_id_lugar;
            $cEscritos = $gesEscritos->getEscritosByLugarDB($id_lugar, $aWhere, $aOperador);
        } else {
            $cEscritos1 = $gesEscrito->getEscritos($aWhere, $aOperador);
            // añadir los modelos jurídicos (tipo_doc=3) y sin f_salida
            $aWhereModeloJ = $aWhere;
            $aOperadorModeloJ = $aOperador;
            unset($aWhereModeloJ['f_salida']);
            unset($aOperadorModeloJ['f_salida']);
            $aWhereModeloJ['accion'] = Escrito::ACCION_PLANTILLA;
            $cEscritosJ = $gesEscrito->getEscritos($aWhereModeloJ, $aOperadorModeloJ);
            $cEscritos = array_merge($cEscritos1, $cEscritosJ);
        }

        // No tengo en cuenta las otras condiciones de la búsqueda
        if (!empty($Q_local_prot_num) && !empty($Q_local_prot_any)) {
            $gesLugares = new GestorLugar();
            $id_sigla_local = $gesLugares->getId_sigla_local();
            $aProt_local = ['id_lugar' => $id_sigla_local,
                'num' => $Q_local_prot_num,
                'any' => $Q_local_prot_any,
            ];
            $aWhere = ['anulado' => 'f'];
            $gesEscritos = new GestorEscrito();
            $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_local, $aWhere);
        }

        $a_cabeceras = ['', ['width' => 150, 'name' => _("protocolo")],
            ['width' => 100, 'name' => _("fecha")],
            ['width' => 650, 'name' => _("asunto")],
            ['width' => 50, 'name' => _("ponente")],
            ['width' => 50, 'name' => _("destinos")],
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

            $ponente_txt = empty($a_posibles_cargos[$ponente]) ? '?' : $a_posibles_cargos[$ponente];

            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >ver</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('escrito','$id_escrito','$Q_id_expediente');\" >adjuntar</span>";

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

        $oDesplCargos = new web\Desplegable('oficina_buscar', $a_posibles_cargos, $Q_oficina_buscar, TRUE);

        $gesLugares = new GestorLugar();
        $a_lugares = $gesLugares->getArrayBusquedas($Q_chk_anulados);
        $oDesplDestino = new Desplegable();
        $oDesplDestino->setNombre('dest_id_lugar');
        $oDesplDestino->setBlanco(TRUE);
        $oDesplDestino->setOpciones($a_lugares);
        $oDesplDestino->setOpcion_sel($Q_dest_id_lugar);

        if (is_true($Q_chk_anulados)) {
            $chk_ctr_anulados = 'checked';
        } else {
            $chk_ctr_anulados = '';
        }

        $sigla = $_SESSION['oConfig']->getSigla();

        // para que no ponga '0'
        $Q_local_prot_num = empty($Q_local_prot_num) ? '' : $Q_local_prot_num;
        $a_campos = [
            'id_expediente' => $Q_id_expediente,
            'oDesplCargos' => $oDesplCargos,
            'oDesplDestino' => $oDesplDestino,
            'oLista' => $oLista,
            'asunto' => $Q_asunto,
            'sel_mes' => $sel_mes,
            'sel_mes_6' => $sel_mes_6,
            'sel_any_1' => $sel_any_1,
            'sel_any_2' => $sel_any_2,
            'sel_siempre' => $sel_siempre,
            'sigla' => $sigla,
            'prot_num' => $Q_local_prot_num,
            'prot_any' => $Q_local_prot_any,
            'chk_ctr_anulados' => $chk_ctr_anulados,
        ];
        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('modal_buscar_escritos.html.twig', $a_campos);
        break;
    case 'buscar_documento':
    case 'buscar_4':
        //n = 4 -> Documento
        //n = 5 -> Documento Etherpad (insertar)
        $Q_tipo_n = (integer)filter_input(INPUT_POST, 'tipo_n');
        $Q_nom = (string)filter_input(INPUT_POST, 'nom');
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');

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
            $aWhere['creador'] = implode(',', $a_cargos);
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

        $oArrayDesplEtiquetas = new web\DesplegableArray($Q_a_etiquetas, $a_posibles_etiquetas, 'etiquetas');
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

        $cDocumentos = $gesDocumento->getDocumentos($aWhere, $aOperador);

        $a_cabeceras = [['width' => 70, 'name' => _("fecha")],
            ['width' => 500, 'name' => _("nombre")],
            ['width' => 50, 'name' => _("creador")],
            ['width' => 50, 'name' => _("etiquetas")],
            ''];
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

            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('documento','$id_doc','$Q_id_expediente');\" >adjuntar</span>";

            $a_valores[$a][1] = $fecha_txt;
            $a_valores[$a][2] = $oDocumento->getNom();
            $a_valores[$a][3] = $creador;
            $a_valores[$a][4] = $oDocumento->getEtiquetasVisiblesTxt();
            $a_valores[$a][5] = $add;
        }

        $oLista = new Lista();
        $oLista->setCabeceras($a_cabeceras);
        $oLista->setDatos($a_valores);

        $a_campos = [
            'id_expediente' => $Q_id_expediente,
            'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
            'chk_and' => $chk_and,
            'chk_or' => $chk_or,
            'tipo_n' => $Q_tipo_n,
            'nom' => $Q_nom,
            'sel_mes' => $sel_mes,
            'sel_mes_6' => $sel_mes_6,
            'sel_any_1' => $sel_any_1,
            'sel_any_2' => $sel_any_2,
            'sel_siempre' => $sel_siempre,
            'oLista' => $oLista,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('modal_buscar_documentos.html.twig', $a_campos);
        break;
    case 'buscar_expediente_borrador':
        $Q_asunto_buscar = (string)filter_input(INPUT_POST, 'asunto_buscar');
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        //$Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
        $Qid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
        $a_entrada = explode('#', $Qid_entrada);
        $Q_id_entrada = (int)$a_entrada[0];
        $compartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);
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
            $aWhere['ponente'] = implode(',', $a_cargos);
            $aOperador['ponente'] = 'IN';
        }
        // por defecto, buscar sólo 15.
        if (empty($Q_asunto_buscar)) {
            $aWhere['_limit'] = 15;
        }
        $aWhere['_ordre'] = 'f_aprobacion DESC';

        $cExpedientes = $gesExpediente->getExpedientes($aWhere, $aOperador);

        $a_cabeceras = ['', ['width' => 70, 'name' => _("fecha")],
            ['width' => 500, 'name' => _("asunto")],
            ['width' => 50, 'name' => _("ponente")]
            , ''];
        $a_valores = [];
        $a = 0;
        foreach ($cExpedientes as $oExpediente) {
            $a++;
            $id_expediente = $oExpediente->getId_expediente();
            $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
            $ponente = $oExpediente->getPonente();

            $ponente_txt = $a_posibles_cargos[$ponente];

            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >" . _("ver") . "</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$Q_id_entrada','$id_expediente');\" >" . _("adjuntar") . "</span>";

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
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
