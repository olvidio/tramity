<?php
use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\ExpedienteLista;
use web\DateTimeLocal;
use etiquetas\model\entity\GestorEtiquetaExpediente;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$oTabla = new ExpedienteLista();
$oTabla->setFiltro($Qfiltro);

$msg = '';
// añadir dialogo de búsquedas
if ($Qfiltro == 'archivados') {
    $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
    $QandOr = (string) \filter_input(INPUT_POST, 'andOr');
    $Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $Qperiodo =  (string) \filter_input(INPUT_POST, 'periodo');

    $a_etiquetas_filtered = array_filter($Qa_etiquetas);
    
    $gesEtiquetas = new GestorEtiqueta();
    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    }
    
    $oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered,$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');
    
    $aWhereADD = [];
    $aOperadorADD = [];

    $chk_or = ($QandOr == 'OR')? 'checked' : '';
    // por defecto 'AND':
    $chk_and = (($QandOr == 'AND') OR empty($QandOr))? 'checked' : '';
    
    if (!empty($a_etiquetas_filtered)) {
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($a_etiquetas_filtered,$QandOr);
        if (!empty($cExpedientes)) {
            $aWhereADD['id_expediente'] = implode(',',$cExpedientes);
            $aOperadorADD['id_expediente'] = 'IN';
        } else {
            // No hay ninguno. No importa el resto de condiciones
            $msg = _("No hay ningún expediente con estas etiquetas");
        }
    }
    
    if (!empty($Qasunto )) {
        $aWhereADD['asunto'] = $Qasunto;
        $aOperadorADD['asunto'] = 'sin_acentos';
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
            break;
    }
    if (!empty($Qperiodo)) {
        $oFecha = new DateTimeLocal();
        $oFecha->sub(new DateInterval($periodo));
        $aWhereADD['f_aprobacion'] = $oFecha->getIso();
        $aOperadorADD['f_aprobacion'] = '>';
    }

    $a_campos = [
        'filtro' => $Qfiltro,
        'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
        'chk_and' => $chk_and,
        'chk_or' => $chk_or,
        'asunto' => $Qasunto,
        'sel_mes' => $sel_mes,
        'sel_mes_6' => $sel_mes_6,
        'sel_any_1' => $sel_any_1,
        'sel_any_2' => $sel_any_2,
        'sel_siempre' => $sel_siempre,
    ];
    
    $oView = new ViewTwig('expedientes/controller');
    echo $oView->renderizar('archivados_buscar.html.twig',$a_campos);
    
    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if (empty($msg)) {
    echo $oTabla->mostrarTabla();
} else {
    echo $msg;
}