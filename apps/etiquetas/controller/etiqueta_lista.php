<?php
use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use etiquetas\model\entity\GestorEtiqueta;
use usuarios\model\entity\Cargo;

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


$gesEtiquetas = new GestorEtiqueta();
// Etiquetas personales + Etiquetas de la oficina
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();

//default:
$id_etiqueta = '';
$nom_etiqueta = '';

$a_cabeceras = [ ['name'=>_("etiqueta"),'width'=>30], _("entorno"), _("id_cargo") ];

$a_botones = [ ['txt'=> _("borrar"), 'click'=>"fnjs_eliminar()"],
               ['txt'=> _("modificar"), 'click'=>"fnjs_editar()"],
            ];

$a_valores=array();
$i=0;
foreach ($cEtiquetas as $oEtiqueta) {
	$i++;
	$id_etiqueta = $oEtiqueta->getId_etiqueta();
	$nom_etiqueta = $oEtiqueta->getNom_etiqueta();
	$id_oficina = $oEtiqueta->getId_cargo();
	$oficina = $oEtiqueta->getOficina();
	if (is_true($oficina)) {
	    $oficina_txt = _("de la oficina");
	} else {
	    $oficina_txt = _("personal");
	}

	$a_valores[$i]['sel'] = "$id_etiqueta#";
	$a_valores[$i][1] = $nom_etiqueta;
	$a_valores[$i][2] = $oficina_txt;
	$a_valores[$i][3] = $id_oficina;
}
if (isset($Qid_sel) && !empty($Qid_sel)) { $a_valores['select'] = $Qid_sel; }
if (isset($Qscroll_id) && !empty($Qscroll_id)) { $a_valores['scroll_id'] = $Qscroll_id; }

$oTabla = new web\Lista();
$oTabla->setId_tabla('etiqueta_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que'=>''));

$aQuery = [ 'nuevo' => 1, 'quien' => 'etiqueta' ];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/etiquetas/controller/etiqueta_form.php?'.http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/etiquetas/controller/etiqueta_form.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/etiquetas/controller/etiqueta_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/etiquetas/controller/etiqueta_lista.php');

$a_campos = [
            'oPosicion' => $oPosicion,
			'oHash' => $oHash,
			'oTabla' => $oTabla,
			'url_nuevo' => $url_nuevo,
			'url_form' => $url_form,
			'url_eliminar' => $url_eliminar,
			'url_actualizar' => $url_actualizar,
 			];

$oView = new ViewTwig('etiquetas/controller');
echo $oView->renderizar('etiqueta_lista.html.twig',$a_campos);

