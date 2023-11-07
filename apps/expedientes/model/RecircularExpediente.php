<?php

namespace expedientes\model;

use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\GestorFirma;

class RecircularExpediente
{

    public function __invoke(int $Q_id_expediente): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente]);
        foreach ($cFirmas as $oFirma) {
            $oFirma->DBCargar();
            $oFirma->setValor(NULL);
            $oFirma->setF_valor(NULL);
            if ($oFirma->DBGuardar() === FALSE) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        // Es posible que esté como acabad, hay que cambiar el estado:
        $oExpediente->setEstado(Expediente::ESTADO_CIRCULANDO);
        if ($oExpediente->DBGuardar() === FALSE) {
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