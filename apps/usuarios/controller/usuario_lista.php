<?php

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorUsuario;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();


$Qid_sel = (string)\filter_input(INPUT_POST, 'id_sel');
$Qscroll_id = (string)\filter_input(INPUT_POST, 'scroll_id');

//Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = \filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack != '') {
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $Qid_sel = $oPosicion2->getParametro('id_sel');
            $Qscroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
}

$aWhere['_ordre'] = 'usuario';
$aOperador = [];

$oGesUsuarios = new GestorUsuario();
$oUsuarioColeccion = $oGesUsuarios->getUsuarios($aWhere, $aOperador);

//default:
$id_usuario = '';
$usuario = '';
$nom_usuario = '';
$email = '';
$cargo = '';
$permiso = 1;

$a_cabeceras = ['usuario',
    'nombre a mostrar',
    'cargo preferido',
    'email',
    //array('name'=>'accion','formatter'=>'clickFormatter'),
];
$a_botones = [['txt' => _("borrar"), 'click' => "fnjs_eliminar()"],
    ['txt' => _("cambiar password"), 'click' => "fnjs_cmb_passwd()"],
    ['txt' => _("modificar"), 'click' => "fnjs_editar()"],
];

$a_valores = array();
$i = 0;
$oCargo = new Cargo();
foreach ($oUsuarioColeccion as $oUsuario) {
    $i++;
    $id_usuario = $oUsuario->getId_usuario();
    $usuario = $oUsuario->getUsuario();
    $nom_usuario = $oUsuario->getNom_usuario();
    $email = $oUsuario->getEmail();
    $id_cargo_preferido = $oUsuario->getId_cargo_preferido();

    if (!empty($id_cargo_preferido) && ConfigGlobal::getEsquema() != 'admin') {
        $oCargo->setId_cargo($id_cargo_preferido);
        $oCargo->DBCarregar();
        $cargo = $oCargo->getCargo();
    } else {
        $cargo = '?';
    }

    //$pagina=web\Hash::link(core\ConfigGlobal::getWeb().'/apps/usuarios/controller/usuario_form.php?'.http_build_query(array('quien'=>'usuario','id_usuario'=>$id_usuario)));

    $a_valores[$i]['sel'] = "$id_usuario#";
    $a_valores[$i][1] = $usuario;
    $a_valores[$i][2] = $nom_usuario;
    $a_valores[$i][3] = $cargo;
    $a_valores[$i][5] = $email;
    //$a_valores[$i][6]= array( 'ira'=>$pagina, 'valor'=>'editar');
}
if (isset($Qid_sel) && !empty($Qid_sel)) {
    $a_valores['select'] = $Qid_sel;
}
if (isset($Qscroll_id) && !empty($Qscroll_id)) {
    $a_valores['scroll_id'] = $Qscroll_id;
}

$oTabla = new web\Lista();
$oTabla->setId_tabla('usuario_lista');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);

$oHash = new web\Hash();
$oHash->setcamposForm('sel');
$oHash->setcamposNo('que!scroll_id');
$oHash->setArraycamposHidden(array('que' => ''));

$aQuery = ['nuevo' => 1, 'quien' => 'usuario'];
$url_nuevo = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/usuario_form.php?' . http_build_query($aQuery));

$url_form = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/usuario_form.php');
$url_form_pwd = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/usuario_form_pwd.php');
$url_eliminar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/usuario_update.php');
$url_actualizar = web\Hash::link(core\ConfigGlobal::getWeb() . '/apps/usuarios/controller/usuario_lista.php');

$a_campos = [
    'oPosicion' => $oPosicion,
    'oHash' => $oHash,
    'oTabla' => $oTabla,
    'permiso' => $permiso,
    'url_nuevo' => $url_nuevo,
    'url_form' => $url_form,
    'url_form_pwd' => $url_form_pwd,
    'url_eliminar' => $url_eliminar,
    'url_actualizar' => $url_actualizar,
];

$oView = new ViewTwig('usuarios/controller');
echo $oView->renderizar('usuario_lista.html.twig', $a_campos);

