<?php
use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use entradas\model\EntradaLista;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\Desplegable;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qslide_mode = (string) \filter_input(INPUT_POST, 'slide_mode');

$oTabla = new EntradaLista();
$oTabla->setFiltro($Qfiltro);
$oTabla->setSlide_mode($Qslide_mode);

$msg = '';
// añadir dialogo de búsquedas
if ($Qfiltro == 'en_aceptado') {
    $Qoficina = (string) \filter_input(INPUT_POST, 'oficina');
    // por defecto:
    if (empty($Qoficina)) {
        $Qoficina = 'propia';
    }
    
    $chk_of_propia = ($Qoficina == 'propia')? 'checked' : '';
    $chk_of_resto = ($Qoficina == 'resto')? 'checked' : '';
    
    $aWhereADD = [];
    $aOperadorADD = [];
    $aWhereADD['ponente'] = $Qoficina;
    
    $a_campos = [
        'filtro' => $Qfiltro,
        'chk_of_propia' => $chk_of_propia,
        'chk_of_resto' => $chk_of_resto,
    ];

    $oView = new ViewTwig('entradas/controller');
    echo $oView->renderizar('oficinas_buscar.html.twig',$a_campos);

    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if ($Qfiltro == 'en_encargado') {
    $Qencargado = (string) \filter_input(INPUT_POST, 'encargado');
    // por defecto:
    if (empty($Qencargado)) {
        $Qencargado = ConfigGlobal::role_id_cargo();
    }
    
    $id_oficina = ConfigGlobal::role_id_oficina();
    
    // sólo el director puede ver al resto de oficiales
    $id_cargo = ConfigGlobal::role_id_cargo();
    $oCargo = new Cargo($id_cargo);
    $dtor = $oCargo->getDirector();
    if (is_true($dtor)) {
        $gesCargos = new GestorCargo();
        //$a_usuarios_oficina = $gesCargos->getArrayUsuariosOficina($id_oficina);
        $a_usuarios_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
    } else {
        $nom_cargo = $oCargo->getCargo();
        $a_usuarios_oficina = [$id_cargo => $nom_cargo];
    }
    // para el dialogo de búsquedas:
    $oDesplEncargados = new Desplegable('encargado',$a_usuarios_oficina,$Qencargado,TRUE);
    $oDesplEncargados->setAction("fnjs_buscar('#que');");
    
    
    $aWhereADD = [];
    $aOperadorADD = [];
    $aWhereADD['encargado'] = $Qencargado;
    
    $a_campos = [
        'filtro' => $Qfiltro,
        'oDesplEncargados' => $oDesplEncargados,
    ];

    $oView = new ViewTwig('entradas/controller');
    echo $oView->renderizar('encargados_buscar.html.twig',$a_campos);
    
    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if (empty($msg)) {
    echo $oTabla->mostrarTabla();
} else {
    echo $msg;
}