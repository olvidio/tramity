<?php

use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use expedientes\model\Expediente;
use expedientes\model\ExpedienteLista;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$oTabla = new ExpedienteLista();
$oTabla->setFiltro($Q_filtro);

$msg = '';
// añadir dialogo de búsquedas
if ($Q_filtro === 'archivados') {
    $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
    $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
    $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
    $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $a_etiquetas_filtered = array_filter($Q_a_etiquetas);

    $a_condiciones = [
        'asunto' => $Q_asunto,
        'andOr' => $Q_andOr,
        'etiquetas' => $Q_a_etiquetas,
        'periodo' => $Q_periodo,
    ];
    $oTabla->setACondiciones($a_condiciones);

    $gesEtiquetas = new GestorEtiqueta();
    $a_posibles_etiquetas = $gesEtiquetas->getArrayMisEtiquetas();
    $oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

    $aWhereADD = [];
    $aOperadorADD = [];

    $chk_or = ($Q_andOr === 'OR') ? 'checked' : '';
    // por defecto 'AND':
    $chk_and = (($Q_andOr === 'AND') || empty($Q_andOr)) ? 'checked' : '';

    if (!empty($a_etiquetas_filtered)) {
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($a_etiquetas_filtered, $Q_andOr);
        if (!empty($cExpedientes)) {
            $aWhereADD['id_expediente'] = implode(',', $cExpedientes);
            $aOperadorADD['id_expediente'] = 'IN';
        } else {
            // No hay ninguno. No importa el resto de condiciones
            $msg = _("No hay ningún expediente con estas etiquetas");
        }
    }

    if (!empty($Q_asunto)) {
        $aWhereADD['asunto'] = $Q_asunto;
        $aOperadorADD['asunto'] = 'sin_acentos';
    }
    $sel_mes = '';
    $sel_mes_6 = '';
    $sel_any_1 = '';
    $sel_any_2 = '';
    $sel_siempre = '';
    $periodo = '';
    switch ($Q_periodo) {
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
            break;
        default:
            // no hace falta, ya se borran todas los $sel_ antes del switch
    }
    if (!empty($Q_periodo) && !empty($periodo)) {
        $oFecha = new DateTimeLocal();
        $oFecha->sub(new DateInterval($periodo));
        $aWhereADD['f_aprobacion'] = $oFecha->getIso();
        $aOperadorADD['f_aprobacion'] = '>';
    }

    $a_campos = [
        'filtro' => $Q_filtro,
        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
        'chk_and' => $chk_and,
        'chk_or' => $chk_or,
        'asunto' => $Q_asunto,
        'sel_mes' => $sel_mes,
        'sel_mes_6' => $sel_mes_6,
        'sel_any_1' => $sel_any_1,
        'sel_any_2' => $sel_any_2,
        'sel_siempre' => $sel_siempre,
    ];

    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('archivados_buscar.html.twig', $a_campos);

    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

// añadir dialogo de búsquedas
if ($Q_filtro === 'borrador_oficina' || $Q_filtro === 'borrador_propio') {
    $Q_prioridad_sel = (integer)filter_input(INPUT_POST, 'prioridad_sel');
    $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
    $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $a_etiquetas_filtered = array_filter($Q_a_etiquetas);

    $oTabla->setPrioridad_sel($Q_prioridad_sel);

    $aWhereADD = [];
    $aOperadorADD = [];
    $aWhereADD['prioridad'] = Expediente::PRIORIDAD_ESPERA;
    if ($Q_prioridad_sel === Expediente::PRIORIDAD_ESPERA) {
        $aOperadorADD['prioridad'] = '=';
        $chk_espera = 'checked';
        $chk_resto = '';
    } else {
        $aOperadorADD['prioridad'] = '!=';
        $chk_resto = 'checked';
        $chk_espera = '';
    }

    $chk_or = ($Q_andOr === 'OR') ? 'checked' : '';
    // por defecto 'AND':
    $chk_and = (($Q_andOr === 'AND') || empty($Q_andOr)) ? 'checked' : '';

    if (!empty($a_etiquetas_filtered)) {
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($a_etiquetas_filtered, $Q_andOr);
        if (!empty($cExpedientes)) {
            $aWhereADD['id_expediente'] = implode(',', $cExpedientes);
            $aOperadorADD['id_expediente'] = 'IN';
        } else {
            // No hay ninguno. No importa el resto de condiciones
            $msg = _("No hay ningún expediente con estas etiquetas");
        }
    }


    $gesEtiquetas = new GestorEtiqueta();
    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    }

    $oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

    $a_campos = [
        'filtro' => $Q_filtro,
        'chk_resto' => $chk_resto,
        'chk_espera' => $chk_espera,
        'chk_and' => $chk_and,
        'chk_or' => $chk_or,
        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,

    ];

    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('expedientes_espera_buscar.html.twig', $a_campos);

    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if (empty($msg)) {
    echo $oTabla->mostrarTabla();
} else {
    echo $msg;
}