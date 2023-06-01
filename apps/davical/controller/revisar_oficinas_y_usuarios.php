<?php

use davical\model\Davical;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************


$txt_err = '';

if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    $sigla = $_SESSION['oConfig']->getSigla();
    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
    $txt_err .= $oDavical->crearOficina($sigla);
    $txt_err .= empty($txt_err) ? '' : '<br>';
} else {
    $gesOficinas = new GestorOficina();
    $a_oficinas = $gesOficinas->getArrayOficinas();
    foreach ($a_oficinas as $id_oficina => $sigla) {
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $txt_err_i = $oDavical->crearOficina($sigla);
        $txt_err .= empty($txt_err_i) ? '' : '<br>';
    }
}

$gesCargos = new GestorCargo();
$a_cargos = $gesCargos->getArrayCargos();
foreach ($a_cargos as $id_cargo => $cargo) {

    $oCargo = new Cargo ($id_cargo);
    $oCargo->DBCargar();
    $id_oficina = $oCargo->getId_oficina();
    $descripcion = $oCargo->getDescripcion();


    // Crear el usuario en davical.
    $aDatosCargo = ['cargo' => $cargo,
        'descripcion' => $descripcion,
        'id_oficina' => $id_oficina,
    ];
    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
    $txt_err_i = $oDavical->crearUser($aDatosCargo);
    $txt_err .= empty($txt_err_i) ? '' : '<br>';
}

if (!empty($txt_err)) {
    exit ($txt_err);
}
echo _("ok. creados los calendarios.");
