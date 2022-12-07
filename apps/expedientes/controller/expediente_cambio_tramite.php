<?php

use core\ViewTwig;
use expedientes\model\Expediente;
use tramites\domain\repositories\TramiteRepository;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_id_expediente = (string)filter_input(INPUT_POST, 'id_expediente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$TramiteRepository = new TramiteRepository();
$oDesplTramites = $TramiteRepository->getListaTramites();
$oDesplTramites->setNombre('tramite');

$a_cosas = ['id_expediente' => $Q_id_expediente,
    'filtro' => $Q_filtro,
];
$pagina_cancel = Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($a_cosas));
$pagina_update = Hash::link('apps/expedientes/controller/expediente_update.php?' . http_build_query([]));

$oExpediente = new Expediente($Q_id_expediente);
$id_tramite = $oExpediente->getId_tramite();
$oDesplTramites->setOpcion_sel($id_tramite);


$titulo = _("Cambiar el trámite del expediente");

$a_campos = [
    'id_expediente' => $Q_id_expediente,
    'titulo' => $titulo,
    'pagina_cancel' => $pagina_cancel,
    'pagina_update' => $pagina_update,
    'oDesplTramites' => $oDesplTramites,
];

$oView = new ViewTwig('expedientes/controller');
$oView->renderizar('expediente_cambio_tramite.html.twig', $a_campos);