<?php

use core\ViewTwig;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\repositories\OficinaRepository;
use usuarios\domain\repositories\UsuarioRepository;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_refresh = (integer)filter_input(INPUT_POST, 'refresh');
$oPosicion->recordar($Q_refresh);

$Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');

$Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
$a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
// Hay que usar isset y empty porque puede tener el valor =0.
// Si vengo por medio de Posicion, borro la última
if (isset($_POST['stack'])) {
    $stack = filter_input(INPUT_POST, 'stack', FILTER_SANITIZE_NUMBER_INT);
    if ($stack !== '') {
        // No me sirve el de global_object, sino el de la session
        $oPosicion2 = new web\Posicion();
        if ($oPosicion2->goStack($stack)) { // devuelve false si no puede ir
            $a_sel = $oPosicion2->getParametro('id_sel');
            if (!empty($a_sel)) {
                $Q_id_cargo = (integer)strtok($a_sel[0], "#");
            } else {
                $Q_id_cargo = $oPosicion2->getParametro('id_cargo');
                $Q_quien = $oPosicion2->getParametro('quien');
            }
            $Q_scroll_id = $oPosicion2->getParametro('scroll_id');
            $oPosicion2->olvidar($stack);
        }
    }
} elseif (!empty($a_sel)) { //vengo de un checkbox
    $Q_que = (string)filter_input(INPUT_POST, 'que');
    $Q_id_cargo = (integer)strtok($a_sel[0], "#");
    // el scroll id es de la página anterior, hay que guardarlo allí
    $oPosicion->addParametro('id_sel', $a_sel, 1);
    $Q_scroll_id = (integer)filter_input(INPUT_POST, 'scroll_id');
    $oPosicion->addParametro('scroll_id', $Q_scroll_id, 1);
}
$oPosicion->setParametros(array('id_cargo' => $Q_id_cargo), 1);


$txt_guardar = _("guardar datos cargo");
$CargoRepository = new CargoRepository();
$oCargo = $CargoRepository->findById($Q_id_cargo);
if ($oCargo !== null) {
    $que = 'guardar';
    $cargo = $oCargo->getCargo();
    $descripcion = $oCargo->getDescripcion();
    $id_ambito = $oCargo->getId_ambito();
    $id_oficina = $oCargo->getId_oficina();
    $director = $oCargo->isDirector();
    $chk_director = ($director === TRUE) ? 'checked' : '';
    $sacd = $oCargo->isSacd();
    $chk_sacd = ($sacd === TRUE) ? 'checked' : '';
    $id_usuario = $oCargo->getId_usuario();
    $id_suplente = $oCargo->getId_suplente();
} else {
    $que = 'nuevo';
    $Q_id_cargo = '';
    $cargo = '';
    $descripcion = '';
    $id_ambito = $_SESSION['oConfig']->getAmbito(); // según configuración de la aplicación;
    $id_oficina = '';
    $chk_director = '';
    $chk_sacd = '';
    $id_usuario = '';
    $id_suplente = '';
}

if ($id_ambito === Cargo::AMBITO_DL) {
    $hay_oficina = TRUE;
    $OficinaRepository = new OficinaRepository();
    $oDesplOficinas = $OficinaRepository->getListaOficinas();
    $oDesplOficinas->setOpcion_sel($id_oficina);
    $oDesplOficinas->setNombre('id_oficina');
} else {
    $hay_oficina = FALSE;
}

$UsuarioRepository = new UsuarioRepository();
$oDesplUsuarios = $UsuarioRepository->getDesplUsuarios();
$oDesplUsuarios->setNombre('id_usuario');
$oDesplUsuarios->setOpcion_sel($id_usuario);

$oDesplSuplentes = $UsuarioRepository->getDesplUsuarios();
$oDesplSuplentes->setNombre('id_suplente');
$oDesplSuplentes->setOpcion_sel($id_suplente);


$camposForm = 'que!cargo!descripcion!id_oficina';
$oHash = new Hash();
$oHash->setcamposForm($camposForm);
$oHash->setcamposNo('');
$a_camposHidden = array(
    'id_cargo' => $Q_id_cargo,
    'que' => $que,
    'id_ambito' => $id_ambito,
);
$oHash->setArraycamposHidden($a_camposHidden);

$url_update = 'src/usuarios/controller/cargo_update.php';
$txt_eliminar = _("¿Está seguro que desea quitar este cargo?");

$a_campos = [
    'oPosicion' => $oPosicion,
    'id_cargo' => $Q_id_cargo,
    'oHash' => $oHash,
    'cargo' => $cargo,
    'descripcion' => $descripcion,
    'oDesplUsuarios' => $oDesplUsuarios,
    'oDesplSuplentes' => $oDesplSuplentes,
    'chk_director' => $chk_director,
    'chk_sacd' => $chk_sacd,
    'url_update' => $url_update,
    'txt_guardar' => $txt_guardar,
    'txt_eliminar' => $txt_eliminar,
    'hay_oficina' => $hay_oficina,
];

if ($hay_oficina) {
    $a_campos['oDesplOficinas'] = $oDesplOficinas;
}

$oView = new ViewTwig('usuarios/controller');
$oView->renderizar('cargo_form.html.twig', $a_campos);
