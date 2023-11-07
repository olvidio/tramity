<?php

namespace expedientes\model;

use davical\model\Davical;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use pendientes\model\Pendiente;
use Symfony\Component\HttpFoundation\JsonResponse;
use usuarios\model\entity\Cargo;
use web\DateTimeLocal;
use web\Protocolo;
use function core\is_true;

class CrearPendienteDeEntrada
{

    public function __invoke(string $Qid_entrada, int $Q_visibilidad): void
    {
        $error_txt = '';
        $a_entrada = explode('#', $Qid_entrada);
        $Q_id_entrada = (int)$a_entrada[0];
        $compartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);

        $Q_id_cargo_pendiente = (integer)filter_input(INPUT_POST, 'id_cargo_pendiente');
        $Q_f_plazo = (string)filter_input(INPUT_POST, 'f_plazo');

        $oCargo = new Cargo($Q_id_cargo_pendiente);
        $id_oficina = $oCargo->getId_oficina();

        // nombre normalizado del usuario y oficina:
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $user_davical = $oDavical->getUsernameDavical($Q_id_cargo_pendiente);
        $parent_container = $oDavical->getNombreRecursoPorIdOficina($id_oficina);

        $calendario = 'oficina';
        $oHoy = new DateTimeLocal();
        $Q_f_plazo = empty($Q_f_plazo) ? $oHoy->getFromLocal() : $Q_f_plazo;
        // datos de la entrada
        $id_reg = 'EN' . $Q_id_entrada; // (para calendario='registro': REN = Registro Entrada, para 'oficina': EN)
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        $oPendiente = new Pendiente($parent_container, $calendario, $user_davical);
        $oPendiente->setId_reg($id_reg);
        $oPendiente->setAsunto($oEntrada->getAsunto());
        $oPendiente->setStatus("NEEDS-ACTION");
        $oPendiente->setF_inicio($oHoy->getFromLocal());
        $oPendiente->setF_plazo($Q_f_plazo);
        $oPendiente->setvisibilidad($Q_visibilidad);
        $oPendiente->setDetalle($oEntrada->getDetalle());
        $oPendiente->setEncargado($Q_id_cargo_pendiente);
        $oPendiente->setId_oficina($id_oficina);

        $oProtOrigen = new Protocolo();
        $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
        $location = $oProtOrigen->ver_txt_num();

        $oPendiente->setLocation($location);
        $oPendiente->setRef_prot_mas($oProtOrigen->ver_txt_mas());
        // las oficinas implicadas:
        $oPendiente->setOficinasArray($oEntrada->getResto_oficinas());
        if ($oPendiente->Guardar() === FALSE) {
            $error_txt .= _("No se han podido guardar el nuevo pendiente");
        }
        if (empty($error_txt)) {
            $jsondata['success'] = TRUE;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        }
        $response = new JsonResponse($jsondata);
        $response->send();
    }
}