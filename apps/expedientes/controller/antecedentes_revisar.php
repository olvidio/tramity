<?php

// INICIO Cabecera global de URL de controlador *********************************
use core\ViewTwig;
use expedientes\model\Expediente;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();


$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_modo = (string)filter_input(INPUT_POST, 'modo');

$oExpediente = new Expediente($Q_id_expediente);

$lista_antecedentes = $oExpediente->getHtmlAntecedentes();


$a_cosas = ['id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
    'modo' => $Q_modo,
];

if ($Q_modo === 'mod') {
    $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
} else {
    $pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));
}


$titulo = "añadir o quitar antecedentes";

$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
    'pagina_cancel' => $pagina_cancel,
    'lista_antecedentes' => $lista_antecedentes,
];

$oView = new ViewTwig('expedientes/controller');
$oView->renderizar('antecedentes_revisar.html.twig', $a_campos);