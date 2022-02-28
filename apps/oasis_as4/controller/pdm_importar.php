<?php
use core\ViewTwig;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$a_campos = [
];

$oView = new ViewTwig('oasis_as4/controller');
echo $oView->renderizar('pdm_importar.html.twig',$a_campos);