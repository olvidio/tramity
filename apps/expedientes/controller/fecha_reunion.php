<?php
use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\Escrito;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorOficina;
use web\Desplegable;
use expedientes\model\Expediente;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (string) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$id_oficina = ConfigGlobal::role_id_oficina();

$gesOficinas = new GestorOficina();
$oDesplOficinas = $gesOficinas->getListaOficinas();
$oDesplOficinas->setNombre('id_oficina');
$oDesplOficinas->setOpcion_sel($id_oficina);

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
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($a_cosas));
$pagina_reunion= web\Hash::link('apps/expedientes/controller/expediente_update.php?'.http_build_query([]));

$oExpediente = new Expediente($Qid_expediente);
$f_reunion = $oExpediente->getF_reunion()->getFromLocalHora();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;
$hoyIso = date('Y-m-d');
    
$titulo = _("Fijar fecha reunión:");
    
$a_campos = [
    'id_expediente' => $Qid_expediente,
    'titulo' => $titulo,
    'pagina_cancel' => $pagina_cancel,
    'pagina_reunion' => $pagina_reunion,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    'hoyIso' => $hoyIso,
    'f_reunion' => $f_reunion,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('fecha_reunion.html.twig',$a_campos);