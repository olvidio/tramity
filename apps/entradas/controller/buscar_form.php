<?php
use core\ViewTwig;
use usuarios\model\entity\GestorOficina;
use core\ConfigGlobal;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$ponente = ConfigGlobal::mi_oficina();

$gesOficinas = new GestorOficina();
$oDesplPonente = $gesOficinas->getListaOficinas();
$oDesplPonente->setNombre('ponente');
$oDesplPonente->setTabIndex(60);
$oDesplPonente->setOpcion_sel($ponente);


$titulo = _("Buscar por:");

$a_campos = [
    'titulo' => $titulo,
    'oDesplPonente' => $oDesplPonente,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('buscar_form.html.twig',$a_campos);