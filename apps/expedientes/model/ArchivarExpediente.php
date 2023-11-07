<?php

namespace expedientes\model;

use Symfony\Component\HttpFoundation\JsonResponse;

class ArchivarExpediente
{

    public function __invoke(int $Q_id_expediente, array $Q_a_etiquetas): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // las etiquetas:
        $oExpediente->setEtiquetas($Q_a_etiquetas);
        $oExpediente->setEstado(Expediente::ESTADO_ARCHIVADO);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
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