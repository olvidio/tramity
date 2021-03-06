<?php
use core\ViewTwig;
use documentos\model\DocumentoLista;
use etiquetas\model\entity\GestorEtiqueta;
use documentos\model\entity\GestorEtiquetaDocumento;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$oTabla = new DocumentoLista();
$oTabla->setFiltro($Qfiltro);

$msg = '';
// añadir dialogo de búsquedas
$QandOr = (string) \filter_input(INPUT_POST, 'andOr');
$Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$a_etiquetas_filtered = array_filter($Qa_etiquetas);

$aWhere = [];
$aOperador = [];

$chk_or = ($QandOr == 'OR')? 'checked' : '';
// por defecto 'AND':
$chk_and = (($QandOr == 'AND') OR empty($QandOr))? 'checked' : '';

if (!empty($a_etiquetas_filtered)) {
    $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
    $cDocumentos = $gesEtiquetasDocumento->getArrayDocumentos($a_etiquetas_filtered,$QandOr);
    if (!empty($cDocumentos)) {
        $aWhere['id_doc'] = implode(',',$cDocumentos);
        $aOperador['id_doc'] = 'IN';
    } else {
        // No hay ninguno. No importa el resto de condiciones
        $msg = _("No hay ningún documento con estas etiquetas");
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

$oArrayDesplEtiquetas = new web\DesplegableArray($a_etiquetas_filtered,$a_posibles_etiquetas,'etiquetas');
$oArrayDesplEtiquetas ->setBlanco('t');
$oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');

$a_campos = [
    'filtro' => $Qfiltro,
    'chk_and' => $chk_and,
    'chk_or' => $chk_or,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    
];

$oView = new ViewTwig('documentos/controller');
echo $oView->renderizar('documentos_buscar.html.twig',$a_campos);

$oTabla->setAndOr($QandOr);
$oTabla->setEtiquetas($a_etiquetas_filtered);
$oTabla->setAWhere($aWhere);
$oTabla->setAOperador($aOperador);

if (empty($msg)) {
    echo $oTabla->mostrarTabla();
} else {
    echo $msg;
}