<?php

namespace expedientes\model;

use Symfony\Component\HttpFoundation\JsonResponse;

class EncargarExpediente
{

    public function __invoke(int $Q_id_expediente, int $Q_id_oficial): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_ENCARGADO);
        $oExpediente->setPonente($Q_id_oficial);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se han podido asignar el nuevo encargado");
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