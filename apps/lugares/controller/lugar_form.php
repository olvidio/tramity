<?php
use core\ViewTwig;
use lugares\model\entity\Lugar;
use web\Desplegable;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
$Qquien = (string) \filter_input(INPUT_POST, 'quien');

$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
	$stack = \filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
	if ($stack != '') {
		// No me sirve el de global_object, sino el de la session
		$oPosicion2 = new web\Posicion();
		if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
			$a_sel=$oPosicion2->getParametro('id_sel');
			if (!empty($a_sel)) {
                $Qid_lugar = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_lugar = $oPosicion2->getParametro('id_usuario');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_lugar = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_lugar'=>$Qid_lugar),1);


$oLugar = new Lugar();
$aOpciones = $oLugar->getArrayModoEnvio();
$oDesplModoEnvio = new Desplegable();
$oDesplModoEnvio->setNombre('modo_envio');
$oDesplModoEnvio->setOpciones($aOpciones);

if (!empty($Qid_lugar)) {
    $que_user='guardar';

    $oLugar = new Lugar($Qid_lugar);
    $sigla = $oLugar->getSigla();
    $dl = $oLugar->getDl();
    $region = $oLugar->getRegion();
    $nombre = $oLugar->getNombre();
    $tipo_ctr = $oLugar->getTipo_ctr();
    $e_mail = $oLugar->getE_mail();
    $anulado = $oLugar->getAnulado();
    // modo envio
    $modo_envio = $oLugar->getModo_envio();
    $oDesplModoEnvio->setOpcion_sel($modo_envio);
    
} else {
    $que_user='nuevo';
    $sigla='';
    $dl='';
    $region='';
    $nombre='';
    $tipo_ctr = '';
    $e_mail = '';
    $anulado = '';
    
}
$chk_anulado = is_true($anulado)? 'checked' : '' ;

$camposForm = 'que!sigla!dl!region!nombre!tipo_ctr!e_mail';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('anulado');
$a_camposHidden = array(
        'id_lugar' => $Qid_lugar,
        'quien' => $Qquien,
        'que' => 'guardar',
        );
$oHash->setArraycamposHidden($a_camposHidden);

$txt_guardar=_("guardar datos lugar");
$txt_eliminar = _("¿Está seguro que desea quitar este permiso?");

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_lugar' => $Qid_lugar,
            'que_user' => $que_user,
            'quien' => $Qquien,
            'oHash' => $oHash,
            'sigla' => $sigla,
            'dl' => $dl,
            'region' => $region,
            'nombre' => $nombre,
            'tipo_ctr' => $tipo_ctr,
            'e_mail' => $e_mail,
            'oDesplModoEnvio' => $oDesplModoEnvio,
            'chk_anulado' => $chk_anulado,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            ];

$oView = new ViewTwig('lugares/controller');
echo $oView->renderizar('lugar_form.html.twig',$a_campos);
