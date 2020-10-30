<?php
use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\Oficina;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
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

$aWhere=array();
$aOperador = array();
    
// Segun la ubicación (config de la instalación)
$aWhere['id_ambito'] = Cargo::AMBITO_DL;

//$aWhere['_ordre'] = 'usuario';

$oGesCargos = new GestorCargo();
$cCargos = $oGesCargos->getCargos($aWhere,$aOperador);

//default:
$id_cargo = '';
$cargo = '';
$descripcion = '';
$permiso = 1;

$a_cabeceras=array('cargo','descripcion','director','oficina',array('name'=>'accion','formatter'=>'clickFormatter'));
$a_botones[]=array( 'txt'=> _("borrar"), 'click'=>"fnjs_eliminar()");

$a_valores=array();
$i=0;
foreach ($cCargos as $oCargo) {
	$i++;
	$id_cargo = $oCargo->getId_cargo();
	$cargo = $oCargo->getCargo();
	$descripcion = $oCargo->getDescripcion();
	$id_oficina = $oCargo->getId_oficina();
	$director = $oCargo->getDirector();
	$director_txt = ($director === TRUE)? _("Sí") : _("No");
	
	$oOficina = new Oficina($id_oficina);
	$sigla = $oOficina->getSigla();
	
	$pagina=web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/cargo_form.php?'.http_build_query(array('quien'=>'usuario','id_cargo'=>$id_cargo)));

	$a_valores[$i]['sel']="$id_cargo#";
	$a_valores[$i][1]=$cargo;
	$a_valores[$i][2]=$descripcion;
	$a_valores[$i][3]=$director_txt;
	$a_valores[$i][4]=$sigla;
	$a_valores[$i][5]= array( 'ira'=>$pagina, 'valor'=>'editar');
}
if (isset($Qid_sel) && !empty($Qid_sel)) { $a_valores['select'] = $Qid_sel; }
if (isset($Qscroll_id) && !empty($Qscroll_id)) { $a_valores['scroll_id'] = $Qscroll_id; }

$oTabla = new web\Lista();
$oTabla->setId_tabla('cargo_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('scroll_id');
$oHash->setArraycamposHidden(array('que'=>'eliminar'));

$aQuery = [ 'nuevo' => 1, 'quien' => 'cargo' ];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/cargo_form.php?'.http_build_query($aQuery));
$url_ajax = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/cargo_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/cargo_lista.php');

$a_campos = [
            'oPosicion' => $oPosicion,
			'oHash' => $oHash,
			'oTabla' => $oTabla,
			'permiso' => $permiso,
			'url_nuevo' => $url_nuevo,
			'url_ajax' => $url_ajax,
			'url_actualizar' => $url_actualizar,
 			];
$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('cargo_lista.html.twig',$a_campos);

