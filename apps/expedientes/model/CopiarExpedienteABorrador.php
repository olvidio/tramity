<?php

namespace expedientes\model;

use core\ConfigGlobal;
use Symfony\Component\HttpFoundation\JsonResponse;
use function core\is_true;

class CopiarExpedienteABorrador
{

    public function __invoke(int $Q_id_expediente, int $Q_of_destino, bool $copias): void
    {
        $error_txt = '';
        if (!empty($Q_of_destino)) {
            $of_destino = $Q_of_destino;
        } else {
            if (!empty($copias) && is_true($copias)) {
                $of_destino = 'copias';
            } else {
                $of_destino = ConfigGlobal::role_id_cargo();
            }
        }
        // copiar expediente: poner los escritos como antecedentes.
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->copiar($of_destino) === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
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