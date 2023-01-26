<?php


use core\ViewTwig;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_importar = (bool)filter_input(INPUT_POST, 'importar');
$Q_slide_mode = (string)filter_input(INPUT_POST, 'slide_mode');

$post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();

$a_adjuntos = [];
$preview = [];
$config = [];
foreach ($a_adjuntos as $id_item => $nom) {
    $preview[] = "'$nom'";
    $config[] = [
        'key' => $id_item,
        'caption' => $nom,
        'url' => 'apps/entradas/controller/delete.php', // server api to delete the file based on key
    ];
}
$initialPreview = implode(',', $preview);
$json_config = json_encode($config);

$txt_btn_revisar = _("revisar las entradas");
$pagina_revisar = Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query(['filtro' => $Q_filtro,'importar' => $Q_importar]));
$titulo = _("Seleccionar las entradas");

$a_campos = [
    'titulo' => $titulo,
    'filtro' => $Q_filtro,
    'initialPreview' => $initialPreview,
    'post_max_size' => $post_max_size,
    'json_config' => $json_config,
    'txt_btn_revisar' => $txt_btn_revisar,
    'pagina_revisar' => $pagina_revisar,
];

$oView = new ViewTwig('entradas/controller');
$oView->renderizar('entrada_importar.html.twig', $a_campos);