<?php


use core\ViewTwig;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
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

$a_campos = [
    'filtro' => $Q_filtro,
    'initialPreview' => $initialPreview,
    'post_max_size' => $post_max_size,
    'json_config' => $json_config,
];

$oView = new ViewTwig('entradas/controller');
$oView->renderizar('entrada_directorio.html.twig', $a_campos);