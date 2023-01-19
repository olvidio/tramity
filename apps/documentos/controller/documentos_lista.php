<?php

use core\ViewTwig;
use documentos\model\DocumentoLista;
use documentos\model\entity\GestorEtiquetaDocumento;
use etiquetas\model\entity\GestorEtiqueta;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_que = (string)filter_input(INPUT_POST, 'que');

$oTabla = new DocumentoLista();
$oTabla->setFiltro($Q_filtro);

// añadir dialogo de búsquedas
$Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
$Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$a_etiquetas_filtered = array_filter($Q_a_etiquetas);

$aWhere = [];
$aOperador = [];

$cDocumentos = [];
if ($Q_que == 'todos') {
    $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
    $cDocumentos = $gesEtiquetasDocumento->getArrayDocumentosTodos();
    // borro las etiquetas seleccionadas
    $a_etiquetas_filtered = [];
    $Q_andOr = 'AND';
} elseif (!empty($a_etiquetas_filtered)) {
    $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
    $cDocumentos = $gesEtiquetasDocumento->getArrayDocumentos($a_etiquetas_filtered, $Q_andOr);
}

$chk_or = ($Q_andOr == 'OR') ? 'checked' : '';
// por defecto 'AND':
$chk_and = (($Q_andOr == 'AND') || empty($Q_andOr)) ? 'checked' : '';

if (!empty($cDocumentos)) {
    $aWhere['id_doc'] = implode(',', $cDocumentos);
    $aOperador['id_doc'] = 'IN';
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
    'chk_and' => $chk_and,
    'chk_or' => $chk_or,
    'que' => $Q_que,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,

];

$oView = new ViewTwig('documentos/controller');
$oView->renderizar('documentos_buscar.html.twig', $a_campos);

$oTabla->setQue($Q_que);
$oTabla->setAndOr($Q_andOr);
$oTabla->setEtiquetas($a_etiquetas_filtered);
$oTabla->setAWhere($aWhere);
$oTabla->setAOperador($aOperador);

echo $oTabla->mostrarTabla();