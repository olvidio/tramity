<?php
use core\ViewTwig;
use documentos\model\Documento;
use etiquetas\model\entity\GestorEtiqueta;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_doc = (integer) \filter_input(INPUT_POST, 'id_doc');
$Qaccion = (integer) \filter_input(INPUT_POST, 'accion');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$QandOr = (string) \filter_input(INPUT_POST, 'andOr');
$Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
/*
if (empty($Qid_doc)) {
    $Qa_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    // sólo debería seleccionar uno.
    $Qid_doc = $Qa_sel[0];
}
*/

$visibilidad = 0;

$oDocumento = new Documento($Qid_doc);

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
if (!empty($Qid_doc)) {
    // destinos individuales
    $nom = $oDocumento->getNom();
    $nombre_fichero = $oDocumento->getNombre_fichero();
    $documento = $oDocumento->getDocumento();
    
    //Ponente;
    //$id_ponente = $oDocumento->getCreador();
    if (!empty($oDocumento->getVisibilidad())) {
        $visibilidad = $oDocumento->getVisibilidad();
        $oDesplVisibilidad->setOpcion_sel($visibilidad);
    }
    
    $f_mod = $oDocumento->getF_upload()->getFromLocal();
    $tipo_doc = $oDocumento->getTipo_doc();
    
    $etiquetas = $oDocumento->getEtiquetasVisiblesArray();
    $oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas,$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');
    
    $titulo = _("modificar");

    if (!empty($documento)) {
        $preview[] = "'$nombre_fichero'";
        $config[] = [
            'key' => $Qid_doc,
            'caption' => $nombre_fichero,
            'url' => 'apps/documentos/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
        $tipo_doc = Documento::DOC_UPLOAD;
    }
    
} else {
    // Valors por defecto:
    $nom = '';
    $visibilidad = '';
    $oDesplVisibilidad->setOpcion_sel($visibilidad);

    $f_mod = '';
    $titulo = _("nuevo documento");
    //$id_ponente = ConfigGlobal::role_id_cargo();
    
    
    $oArrayDesplEtiquetas = new web\DesplegableArray([],$a_posibles_etiquetas,'etiquetas');
    $oArrayDesplEtiquetas ->setBlanco('t');
    $oArrayDesplEtiquetas ->setAccionConjunto('fnjs_mas_etiquetas()');
    
}
$initialPreview = implode(',',$preview);
$json_config = json_encode($config);

// poner '' en vez de 0
$tipo_doc = empty($tipo_doc)? '' : $tipo_doc;

$url_update = 'apps/documentos/controller/documento_update.php';
$a_cosas = [
            'filtro' => $Qfiltro,
            'andOr' => $QandOr,
            'etiquetas' => $Qa_etiquetas,
        ];

$pagina_cancel = web\Hash::link('apps/documentos/controller/documentos_lista.php?'.http_build_query($a_cosas));

$a_campos = [
    //'oHash' => $oHash,
    'titulo' => $titulo,
    'id_doc' => $Qid_doc,
    'accion' => $Qaccion,
    'filtro' => $Qfiltro,
    'f_mod' => $f_mod,
    'tipo_doc' => $tipo_doc,
    'nom' => $nom,
    'oDesplVisibilidad' => $oDesplVisibilidad,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    // para js
    'url_update' => $url_update,
    'pagina_cancel' => $pagina_cancel,
    'etiquetas' => $Qa_etiquetas,
    'andOr' => $QandOr,

];

$oView = new ViewTwig('documentos/controller');
echo $oView->renderizar('documento_form.html.twig',$a_campos);