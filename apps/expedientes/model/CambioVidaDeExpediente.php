<?php

namespace expedientes\model;

use Symfony\Component\HttpFoundation\JsonResponse;
use web;

class CambioVidaDeExpediente
{

    public function __invoke(int $Q_id_expediente, int $Q_vida): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oExpediente->setVida($Q_vida);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_expediente'] = $Q_id_expediente;
            $a_cosas = ['id_expediente' => $Q_id_expediente];
            $pagina_mod = web\Hash::link('apps/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        $response = new JsonResponse($jsondata);
        $response->send();
    }
}