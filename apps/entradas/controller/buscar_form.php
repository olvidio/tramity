<?php
use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\Escrito;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorOficina;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (string) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$oficina = ConfigGlobal::mi_oficina();

$gesOficinas = new GestorOficina();
$oDesplOficinas = $gesOficinas->getListaOficinas();
$oDesplOficinas->setNombre('id_oficina');
$oDesplOficinas->setOpcion_sel($oficina);

$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();
/*
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    $txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
}
*/
$oDesplLugares = new Desplegable();
$oDesplLugares->setNombre('id_origen');
$oDesplLugares->setOpciones($a_posibles_lugares);
$oDesplLugares->setBlanco(TRUE);

$a_cosas = [ 'id_expediente' => $Qid_expediente,
            'filtro' => $Qfiltro,
            ];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
$pagina_buscar = web\Hash::link('apps/entradas/controller/entrada_ajax.php?'.http_build_query([]));
$pagina_escrito = web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query(['id_expediente' => $Qid_expediente, 'accion' => Escrito::ACCION_ESCRITO]));
$url_escrito = 'apps/expedientes/controller/escrito_form.php';

$titulo = _("Buscar en entradas:");

$a_campos = [
    'id_expediente' => $Qid_expediente,
    'titulo' => $titulo,
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplLugares' => $oDesplLugares,
    'pagina_cancel' => $pagina_cancel,
    'pagina_buscar' => $pagina_buscar,
    'url_escrito' => $url_escrito,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('buscar_form.html.twig',$a_campos);