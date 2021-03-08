<?php
use core\ViewTwig;
use expedientes\model\Escrito;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');


$oEscrito = new Escrito($Qid_escrito);

if (!empty($Qid_escrito)) {
    $destino_txt = $oEscrito->getDestinosEscrito();
    
    $a_adjuntos = $oEscrito->getArrayIdAdjuntos();
    $preview = [];
    $config = [];
    foreach ($a_adjuntos as $id_item => $nom) {
        $preview[] = "'$nom'";
        $config[] = [
            'key' => $id_item,
            'caption' => $nom,
            'url' => 'apps/expedientes/controller/adjunto_delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',',$preview);
    $json_config = json_encode($config);
    
    $titulo = _("modificar ajuntos escrito");
    $titulo .= " ".$destino_txt;
}

$a_cosas = [ 'id_expediente' => $Qid_expediente,
    'filtro' => $Qfiltro,
];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_ver.php?'.http_build_query($a_cosas));
    
    
$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Qid_expediente,
    'id_escrito' => $Qid_escrito,
    'filtro' => $Qfiltro,
    'pagina_cancel' => $pagina_cancel,
    //'oHash' => $oHash,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('adjunto_revisar.html.twig',$a_campos);