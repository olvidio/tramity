<?php
use core\ViewTwig;
use lugares\model\entity\Grupo;
use web\Desplegable;
use lugares\model\entity\GestorLugar;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qrefresh = (integer)  \filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Qrefresh);

$Qid_grupo = (integer) \filter_input(INPUT_POST, 'id_grupo');
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
                $Qid_grupo = (integer) strtok($a_sel[0],"#");
			} else {
                $Qid_grupo = $oPosicion2->getParametro('id_usuario');
			    $Qquien = $oPosicion2->getParametro('quien');
			}
			$Qscroll_id = $oPosicion2->getParametro('scroll_id');
			$oPosicion2->olvidar($stack);
		}
	}
} elseif (!empty($a_sel)) { //vengo de un checkbox
	$Qque = (string) \filter_input(INPUT_POST, 'que');
	if ($Qque != 'del_grupmenu') { //En el caso de venir de borrar un grupmenu, no hago nada
	    $Qid_grupo = (integer) strtok($a_sel[0],"#");
		// el scroll id es de la página anterior, hay que guardarlo allí
		$oPosicion->addParametro('id_sel',$a_sel,1);
		$Qscroll_id = (integer) \filter_input(INPUT_POST, 'scroll_id');
		$oPosicion->addParametro('scroll_id',$Qscroll_id,1);
	}
}
$oPosicion->setParametros(array('id_grupo'=>$Qid_grupo),1);

$sigla = $_SESSION['oConfig']->getSigla();
$gesLugares = new GestorLugar();
$a_posibles_lugares_ctr= $gesLugares->getArrayLugares('',$sigla,'');
$a_posibles_lugares_dl= $gesLugares->getArrayLugares('dl','','');
$a_posibles_lugares_cr= $gesLugares->getArrayLugares('cr','','');



if (!empty($Qid_grupo)) {
    $que_user='guardar';
    $oGrupo = new Grupo(array('id_grupo'=>$Qid_grupo));

    $descripcion = $oGrupo->getDescripcion();
    $a_miembros = $oGrupo->getMiembros();
} else {
    $que_user = 'nuevo';
    $descripcion = '';
    $a_miembros = [];
}

$oDesplLugaresCtr = new Desplegable('lugares',$a_posibles_lugares_ctr,$a_miembros);
$oDesplLugaresDl = new Desplegable('lugares',$a_posibles_lugares_dl,$a_miembros);
$oDesplLugaresCr = new Desplegable('lugares',$a_posibles_lugares_cr,$a_miembros);

$camposForm = 'que!sigla!dl!region!nombre!tipo_ctr!tipo_labor';
$oHash = new web\Hash();
$oHash->setcamposForm($camposForm);
$oHash->setCamposChk('anulado');
$a_camposHidden = array(
        'id_grupo' => $Qid_grupo,
        'quien' => $Qquien,
        'que' => 'guardar',
        );
$oHash->setArraycamposHidden($a_camposHidden);

$txt_guardar=_("guardar datos grupo");
$txt_eliminar = _("¿Está seguro que desea quitar este permiso?");

$a_campos = [
            'oPosicion' => $oPosicion,
            'id_grupo' => $Qid_grupo,
            'que_user' => $que_user,
            'quien' => $Qquien,
            'oHash' => $oHash,
            'descripcion' => $descripcion,
            'txt_guardar' => $txt_guardar,
            'txt_eliminar' => $txt_eliminar,
            'oDesplLugaresCtr' => $oDesplLugaresCtr,           
            'oDesplLugaresDl' => $oDesplLugaresDl,           
            'oDesplLugaresCr' => $oDesplLugaresCr,           
            ];

$oView = new ViewTwig('lugares/controller');
echo $oView->renderizar('grupo_form.html.twig',$a_campos);
