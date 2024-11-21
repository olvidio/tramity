<?php

use core\ViewTwig;
use documentos\model\Documento;
use escritos\model\TextoDelEscrito;
use etiquetas\model\entity\GestorEtiqueta;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_doc = (integer)filter_input(INPUT_POST, 'id_doc');
$Q_accion = (integer)filter_input(INPUT_POST, 'accion');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
$Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Q_que = (string)filter_input(INPUT_POST, 'que');

$visibilidad = 0;

$oDocumento = new Documento($Q_id_doc);
$post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();

// visibilidad (usar las mismas opciones que en entradas)
$aOpciones = $oDocumento->getArrayVisibilidad();
$oDesplVisibilidad = new Desplegable();
$oDesplVisibilidad->setNombre('visibilidad');
$oDesplVisibilidad->setOpciones($aOpciones);
$oDesplVisibilidad->setOpcion_sel($visibilidad);

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

$preview = [];
$config = [];
$tipo_doc = '';
if (!empty($Q_id_doc)) {
    // destinos individuales
    $nom = $oDocumento->getNom();
    $nombre_fichero = $oDocumento->getNombre_fichero();
    $documento_txt = $oDocumento->getDocumento();

    if (!empty($oDocumento->getVisibilidad())) {
        $visibilidad = $oDocumento->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
    }

    $f_mod = $oDocumento->getF_upload()->getFromLocal();
    $tipo_doc = $oDocumento->getTipo_doc();

    $etiquetas = $oDocumento->getEtiquetasVisiblesArray();
    $oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas, $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

    $titulo = _("modificar");

    if (!empty($documento_txt)) {
        $preview[] = "'$nombre_fichero'";
        $config[] = [
            'key' => $Q_id_doc,
            'caption' => $nombre_fichero,
            'url' => 'apps/documentos/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
        $tipo_doc = TextoDelEscrito::TIPO_UPLOAD;
    }

} else {
    // Valores por defecto:
    $nom = '';
    $visibilidad = '';
    $oDesplVisibilidad->setOpcion_sel($visibilidad);

    $f_mod = '';
    $titulo = _("nuevo documento");

    $oArrayDesplEtiquetas = new web\DesplegableArray([], $a_posibles_etiquetas, 'etiquetas');
    $oArrayDesplEtiquetas->setBlanco('t');
    $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

}
$initialPreview = implode(',', $preview);
$json_config = json_encode($config);

// poner '' en vez de 0
$tipo_doc = empty($tipo_doc) ? '' : $tipo_doc;

$url_update = 'apps/documentos/controller/documento_update.php';
$a_cosas = [
    'filtro' => $Q_filtro,
    'andOr' => $Q_andOr,
    'etiquetas' => $Q_a_etiquetas,
    'que' => $Q_que,
];

$pagina_cancel = web\Hash::link('apps/documentos/controller/documentos_lista.php?' . http_build_query($a_cosas));

$a_campos = [
    //'oHash' => $oHash,
    'titulo' => $titulo,
    'id_doc' => $Q_id_doc,
    'accion' => $Q_accion,
    'filtro' => $Q_filtro,
    'f_mod' => $f_mod,
    'tipo_doc' => $tipo_doc,
    'nom' => $nom,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    'initialPreview' => $initialPreview,
    'post_max_size' => $post_max_size,
    'json_config' => $json_config,
    // para js
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'etiquetas' => $Q_a_etiquetas,
    'andOr' => $Q_andOr,

];

$oView = new ViewTwig('documentos/controller');
$oView->renderizar('documento_form.html.twig', $a_campos);