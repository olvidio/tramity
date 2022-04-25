<?php
use core\ViewTwig;
use function core\is_true;
use entradas\model\Entrada;
use etherpad\model\Etherpad;
use entradas\model\entity\EntradaCompartida;
use entradas\model\entity\GestorEntradaCompartidaAdjunto;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// porque también se puede abrir en una ventana nueva, y entonces se llama por GET
$Qmethod = (string) \filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if ($Qmethod == 'POST') {
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
}
if ($Qmethod == 'GET') {
    $Qid_entrada = (integer) \filter_input(INPUT_GET, 'id_entrada');
}

$sigla = $_SESSION['oConfig']->getSigla();

$oEntrada = new Entrada($Qid_entrada);

if (!empty($Qid_entrada)) {
    
    // En el caso de distribución cr, si ya está aceptado, el ver es ya para enviar
    // y por tanto las cabeceras van al revés, y el destino se coge del bypass.
    $estado = $oEntrada->getEstado();
    $bypass = $oEntrada->getBypass();
    if (is_true($bypass) && $estado == Entrada::ESTADO_ACEPTADO) {
        $cabeceraIzqd = $oEntrada->cabeceraDistribucion_cr();
        $cabeceraDcha = $oEntrada->cabeceraDerecha();
    } else {
        $cabeceraIzqd = $oEntrada->cabeceraIzquierda();
        $cabeceraDcha = $oEntrada->cabeceraDerecha();
    }
    
	$asunto_e = $oEntrada->getAsunto_entrada();
	// mirar si tienen escrito
	$f_escrito = $oEntrada->getF_documento()->getFromLocal();
	$f_entrada = $oEntrada->getF_entrada()->getFromLocal();
	
    $id_entrada_compartida = $oEntrada->getId_entrada_compartida();
    if (!empty($id_entrada_compartida)) {
    	$bCompartida = TRUE;
    	/*
    	$oEntradaCompartida = new EntradaCompartida($id_entrada_compartida);
    	$descripcion = $oEntradaCompartida->getDescripcion();
    	*/	
    	$gesEntradaAdjuntos = new GestorEntradaCompartidaAdjunto();
    	$a_adjuntos = $gesEntradaAdjuntos->getArrayIdAdjuntos($id_entrada_compartida);
    	
    	
		$oEtherpad = new Etherpad();
    	$oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada_compartida);
    } else {
    	$bCompartida = FALSE;
		$a_adjuntos = $oEntrada->getArrayIdAdjuntos();
		
		$oEtherpad = new Etherpad();
		$oEtherpad->setId (Etherpad::ID_ENTRADA,$Qid_entrada);
    }
    $escrito_html = $oEtherpad->generarHtml();
} else {
	$bCompartida = FALSE;
    $cabeceraIzqd = '';
    $cabeceraDcha = '';
    $a_adjuntos = [];
    $asunto_e = '';
    $f_escrito = '';
    $f_entrada = '';
    $escrito_html = '';
}

if (!empty($f_entrada)) {
    $chk_leido = 'checked';
} else {
    $chk_leido = '';
}

$base_url = core\ConfigGlobal::getWeb();
$url_download = $base_url.'/apps/entradas/controller/download.php';
$url_download_pdf = $base_url.'/apps/entradas/controller/entrada_download.php';

$a_campos = [
    'id_entrada' => $Qid_entrada,
    //'oHash' => $oHash,
    'cabeceraIzqd' => $cabeceraIzqd,
    'cabeceraDcha' => $cabeceraDcha,
    'asunto_e' => $asunto_e,
    'f_escrito' => $f_escrito,
    'a_adjuntos' => $a_adjuntos,
    'url_download' => $url_download,
    'chk_leido' => $chk_leido,
    'f_entrada' => $f_entrada,
    'base_url' => $base_url,
    'sigla' => $sigla,
    'escrito_html' => $escrito_html,
    'url_download_pdf' => $url_download_pdf,
   	'bCompartida' => $bCompartida,
	];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_ver.html.twig',$a_campos);