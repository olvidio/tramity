<?php
use core\ViewTwig;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();
	

$Qid_sel = (string) \filter_input(INPUT_POST, 'id_sel');
$Qscroll_id = (string) \filter_input(INPUT_POST, 'scroll_id');	

//Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
	$stack = \filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
	if ($stack != '') {
		$oPosicion2 = new web\Posicion();
		if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
			$Qid_sel=$oPosicion2->getParametro('id_sel');
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
}


//$oPosicion->setParametros(array('username'=>$Qusername),1);

$aWhere['_ordre'] = 'sigla';
$aOperador = [];

$oGesLugares = new GestorLugar();
$cLugares = $oGesLugares->getLugares($aWhere,$aOperador);

//default:
$id_lugar='';
$sigla='';
$dl='';
$region='';
$nombre='';
$tipo_ctr = '';
$e_mail = '';
$anulado = '';

$a_cabeceras = [ 'sigla','dl','region','nombre','tipo_ctr','e_mail','modo envio' ];
$a_botones = [ ['txt'=> _("borrar"), 'click'=>"fnjs_eliminar()"],
               ['txt'=> _("modificar"), 'click'=>"fnjs_editar()"],
            ];

$a_valores=array();
$i=0;
$oLugar = new Lugar();
$a_modos_envio = $oLugar->getArrayModoEnvio();
foreach ($cLugares as $oLugar) {
	$i++;
	$id_lugar = $oLugar->getId_lugar();
	$sigla = $oLugar->getSigla();
	$dl = $oLugar->getDl();
	$region = $oLugar->getRegion();
	$nombre = $oLugar->getNombre();
	$tipo_ctr = $oLugar->getTipo_ctr();
	$e_mail = $oLugar->getE_mail ();
	$modo_envio = $oLugar->getModo_envio();

	$a_valores[$i]['sel'] = "$id_lugar#";
	$a_valores[$i][1] = $sigla;
	$a_valores[$i][2] = $dl;
	$a_valores[$i][3] = $region;
	$a_valores[$i][4] = $nombre;
	$a_valores[$i][5] = $tipo_ctr;
	$a_valores[$i][6] = $e_mail;
	$a_valores[$i][7] = $a_modos_envio[$modo_envio];
}
if (isset($Qid_sel) && !empty($Qid_sel)) { $a_valores['select'] = $Qid_sel; }
if (isset($Qscroll_id) && !empty($Qscroll_id)) { $a_valores['scroll_id'] = $Qscroll_id; }

$oTabla = new web\Lista();
$oTabla->setId_tabla('lugar_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que'=>''));

$aQuery = [ 'nuevo' => 1, 'quien' => 'lugar' ];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/lugares/controller/lugar_form.php?'.http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/lugares/controller/lugar_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/lugares/controller/lugar_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/lugares/controller/lugar_lista.php');

$a_campos = [
            'oPosicion' => $oPosicion,
			'oHash' => $oHash,
			'oTabla' => $oTabla,
			'url_nuevo' => $url_nuevo,
			'url_form' => $url_form,
			'url_eliminar' => $url_eliminar,
			'url_actualizar' => $url_actualizar,
 			];

$oView = new ViewTwig('lugares/controller');
echo $oView->renderizar('lugar_lista.html.twig',$a_campos);

