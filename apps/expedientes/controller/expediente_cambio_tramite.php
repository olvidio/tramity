<?php
use core\ViewTwig;
use expedientes\model\Expediente;
use tramites\model\entity\GestorTramite;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (string) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$gesTramites = new GestorTramite();
$oDesplTramites = $gesTramites->getListaTramites();
$oDesplTramites->setNombre('tramite');

$a_cosas = [ 'id_expediente' => $Qid_expediente,
            'filtro' => $Qfiltro,
            ];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($a_cosas));
$pagina_update= web\Hash::link('apps/expedientes/controller/expediente_update.php?'.http_build_query([]));

$oExpediente = new Expediente($Qid_expediente);
$id_tramite = $oExpediente->getId_tramite();
$oDesplTramites->setOpcion_sel($id_tramite);


$titulo = _("Cambiar el trámite del expediente");
    
$a_campos = [
    'id_expediente' => $Qid_expediente,
    'titulo' => $titulo,
    'pagina_cancel' => $pagina_cancel,
    'pagina_update' => $pagina_update,
    'oDesplTramites' => $oDesplTramites,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_cambio_tramite.html.twig',$a_campos);