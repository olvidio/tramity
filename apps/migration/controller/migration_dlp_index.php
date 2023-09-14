<?php

use core\ViewTwig;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$a_campos = [
];

$oView = new ViewTwig('migration/controller');
$oView->renderizar('migration_dlp_index.html.twig', $a_campos);