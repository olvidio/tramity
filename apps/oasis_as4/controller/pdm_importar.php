<?php

use core\ViewTwig;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();

$a_campos = [
    'post_max_size' => $post_max_size,
];

$oView = new ViewTwig('oasis_as4/controller');
$oView->renderizar('pdm_importar.html.twig', $a_campos);