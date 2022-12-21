<?php

use core\ViewTwig;
use escritos\model\Escrito;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_id_escrito = (integer)filter_input(INPUT_POST, 'id_escrito');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();

$oEscrito = new Escrito($Q_id_escrito);

if (!empty($Q_id_escrito)) {
    $destino_txt = $oEscrito->getDestinosEscrito();

    $a_adjuntos = $oEscrito->getArrayIdAdjuntos();
    $preview = [];
    $config = [];
    foreach ($a_adjuntos as $id_item => $nom) {
        $preview[] = "'$nom'";
        $config[] = [
            'key' => $id_item,
            'caption' => $nom,
            'url' => 'src/escritos/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',', $preview);
    $json_config = json_encode($config);

    $titulo = _("modificar ajuntos escrito");
    $titulo .= " " . $destino_txt;
}

$a_cosas = ['id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
    'modo' => $Q_modo,
];

if ($Q_filtro === 'distribuir') {
    $pagina_cancel = web\Hash::link('src/expedientes/controller/expediente_distribuir.php?' . http_build_query($a_cosas));
} else {
    if ($Q_modo === 'mod') {
        $pagina_cancel = web\Hash::link('src/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
    } else {
        $pagina_cancel = web\Hash::link('src/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));
    }
}


$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Q_id_expediente,
    'id_escrito' => $Q_id_escrito,
    'filtro' => $Q_filtro,
    'pagina_cancel' => $pagina_cancel,
    //'oHash' => $oHash,
    'initialPreview' => $initialPreview,
    'post_max_size' => $post_max_size,
    'json_config' => $json_config,
];

$oView = new ViewTwig('expedientes/controller');
$oView->renderizar('adjunto_revisar.html.twig', $a_campos);