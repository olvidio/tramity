<?php


use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\EntradaLista;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;
use web\Desplegable;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_slide_mode = (string)filter_input(INPUT_POST, 'slide_mode');

$oTabla = new EntradaLista();
$oTabla->setFiltro($Q_filtro);
$oTabla->setSlide_mode($Q_slide_mode);

$msg = '';
// añadir dialogo de búsquedas
if ($Q_filtro === 'en_aceptado') {
    $Q_oficina = (string)filter_input(INPUT_POST, 'oficina');
    // por defecto:
    if (empty($Q_oficina)) {
        $Q_oficina = 'propia';
    }

    // para los ctr no hace falta
    if ($_SESSION['oConfig']->getAmbito() !== Cargo::AMBITO_CTR) {
        $chk_of_propia = ($Q_oficina === 'propia') ? 'checked' : '';
        $chk_of_resto = ($Q_oficina === 'resto') ? 'checked' : '';

        $a_campos = [
            'filtro' => $Q_filtro,
            'chk_of_propia' => $chk_of_propia,
            'chk_of_resto' => $chk_of_resto,
        ];

        $oView = new ViewTwig('entradas/controller');
        $oView->renderizar('oficinas_buscar.html.twig', $a_campos);

    }
    $aWhereADD = [];
    $aOperadorADD = [];
    $aWhereADD['ponente'] = $Q_oficina;
    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if ($Q_filtro === 'en_encargado') {
    $Q_encargado = (string)filter_input(INPUT_POST, 'encargado');
    // por defecto:
    if (empty($Q_encargado)) {
        $Q_encargado = ConfigGlobal::role_id_cargo();
    }

    $id_oficina = ConfigGlobal::role_id_oficina();

    // sólo el director puede ver al resto de oficiales
    $id_cargo = ConfigGlobal::role_id_cargo();
    $CargoRepository = new CargoRepository();
    $oCargo = $CargoRepository->findById($id_cargo);
    if ($oCargo->isDirector()) {
        $a_usuarios_oficina = $CargoRepository->getArrayUsuariosOficina($id_oficina);
    } else {
        $nom_cargo = $oCargo->getCargo();
        $a_usuarios_oficina = [$id_cargo => $nom_cargo];
    }
    // para el dialogo de búsquedas:
    $oDesplEncargados = new Desplegable('encargado', $a_usuarios_oficina, $Q_encargado, FALSE);
    $oDesplEncargados->setAction("fnjs_buscar('#que');");


    $aWhereADD = [];
    $aOperadorADD = [];
    $aWhereADD['encargado'] = $Q_encargado;

    $a_campos = [
        'filtro' => $Q_filtro,
        'oDesplEncargados' => $oDesplEncargados,
    ];

    $oView = new ViewTwig('entradas/controller');
    $oView->renderizar('encargados_buscar.html.twig', $a_campos);

    $oTabla->setAWhereADD($aWhereADD);
    $oTabla->setAOperadorADD($aOperadorADD);
}

if (empty($msg)) {
    $oTabla->mostrarTabla();
} else {
    echo $msg;
}