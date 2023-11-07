<?php

namespace expedientes\model;

use core\ConfigGlobal;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use function core\is_true;

class CrearNuevoExpedienteDeEntrada
{

    public function __invoke(string $Qid_entrada, int $Q_visibilidad): void
    {
        $error_txt = '';
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        $a_entrada = explode('#', $Qid_entrada);
        $Q_id_entrada = (int)$a_entrada[0];
        $compartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);

        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }
        // Hay que crear un nuevo expediente, con un adjunto (entrada).
        $Q_asunto = $oEntrada->getAsunto_entrada();

        $Q_estado = Expediente::ESTADO_BORRADOR;
        $Q_ponente = ConfigGlobal::role_id_cargo();
        $Q_tramite = 2; // Ordinario, no puede ser null.
        $Q_prioridad = Expediente::PRIORIDAD_NORMAL; // no puede ser null.

        $oExpediente = new Expediente();
        $oExpediente->setPonente($Q_ponente);
        $oExpediente->setEstado($Q_estado);
        $oExpediente->setId_tramite($Q_tramite);
        $oExpediente->setPrioridad($Q_prioridad);
        $oExpediente->setAsunto($Q_asunto);
        $oExpediente->setVisibilidad($Q_visibilidad);

        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se han podido crear el nuevo expediente");
            $error_txt .= "\n";
            $error_txt .= $oExpediente->getErrorTxt();
        }

        // adjuntar entrada como antecedente
        if ($compartida) {
            $a_antecedente = ['tipo' => 'entrada_compartida', 'id' => $Q_id_entrada];
        } else {
            $a_antecedente = ['tipo' => 'entrada', 'id' => $Q_id_entrada];
        }
        $oExpediente->addAntecedente($a_antecedente);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se han podido adjuntar la entrada");
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