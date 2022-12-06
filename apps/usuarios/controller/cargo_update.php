<?php

use davical\model\Davical;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');

$txt_err = '';
switch ($Q_que) {
    case "suplente":
        $Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');
        $Q_id_suplente = (integer)filter_input(INPUT_POST, 'id_suplente');
        $CargoRepository = new CargoRepository();
        $oCargo = $CargoRepository->findById($Q_id_cargo);
        $oCargo->setId_suplente($Q_id_suplente);
        if ($CargoRepository->Guardar($oCargo) === FALSE) {
            $txt_err .= _("Hay un error al guardar");
            $txt_err .= "<br>";
        }
        break;
    case "eliminar":
        $a_sel = (array)filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if (!empty($a_sel)) { //vengo de un checkbox
            $Q_id_cargo = (integer)strtok($a_sel[0], "#");
            if ($Q_id_cargo > Cargo::CARGO_REUNION) { // A dia de hoy, es el número mayor (7)
                $CargoRepository = new CargoRepository();
                $oCargo = $CargoRepository->findById($Q_id_cargo);
                // hay que coger la información antes de borrar:
                $id_oficina = $oCargo->getId_oficina();
                $cargo = $oCargo->getCargo();
                if ($CargoRepository->Eliminar($oCargo) === false) {
                    $txt_err .= _("hay un error, no se ha eliminado");
                    $txt_err .= "\n" . $CargoRepository->getErrorTxt();
                } else {
                    // Eliminar el usuario en davical.
                    $aDatosCargo = ['cargo' => $cargo,
                        'oficina' => $id_oficina,
                    ];
                    $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                    $txt_err .= $oDavical->eliminarUser($aDatosCargo);
                }
            } else {
                $txt_err .= _("No se puede eliminar un cargo tipo.");
            }
        }
        break;
    case "nuevo":
    case "guardar":
        $Q_cargo = (string)filter_input(INPUT_POST, 'cargo');

        if (empty($Q_cargo)) {
            $txt_err .= _("debe poner un nombre");
        }
        $Q_id_cargo = (integer)filter_input(INPUT_POST, 'id_cargo');
        $Q_descripcion = (string)filter_input(INPUT_POST, 'descripcion');
        $Q_id_ambito = (integer)filter_input(INPUT_POST, 'id_ambito');
        $Q_id_oficina = (integer)filter_input(INPUT_POST, 'id_oficina');
        $Q_director = (bool)filter_input(INPUT_POST, 'director');
        $Q_sacd = (bool)filter_input(INPUT_POST, 'sacd');
        $Q_id_usuario = (integer)filter_input(INPUT_POST, 'id_usuario');
        $Q_id_suplente = (integer)filter_input(INPUT_POST, 'id_suplente');

        if ($_SESSION['oConfig']->getAmbito() !== Cargo::AMBITO_DL) {
            $Q_id_oficina = Cargo::OFICINA_ESQUEMA;
        }

        $CargoRepository = new CargoRepository();
        $oCargo = $CargoRepository->findById($Q_id_cargo);
        if ($oCargo === null) {
            $Q_id_cargo = $CargoRepository->getNewId_cargo();
            $oCargo = new Cargo();
            $oCargo->setId_cargo($Q_id_cargo);
        }
        $oCargo->setCargo($Q_cargo);
        $oCargo->setDescripcion($Q_descripcion);
        $oCargo->setId_ambito($Q_id_ambito);
        $oCargo->setId_oficina($Q_id_oficina);
        $oCargo->setDirector($Q_director);
        $oCargo->setSacd($Q_sacd);
        $oCargo->setId_usuario($Q_id_usuario);
        $oCargo->setId_suplente($Q_id_suplente);
        if ($CargoRepository->Guardar($oCargo) === false) {
            $txt_err .= _("hay un error, no se ha guardado");
            $txt_err .= "\n" . $CargoRepository->getErrorTxt();
        }
        // Crear el usuario en davical.
        $aDatosCargo = ['cargo' => $Q_cargo,
            'descripcion' => $Q_descripcion,
            'oficina' => $Q_id_oficina,
        ];
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $txt_err .= $oDavical->crearUser($aDatosCargo);
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
if (empty($txt_err)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();