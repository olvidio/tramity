<?php

namespace expedientes\model;

use core\ConfigGlobal;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use usuarios\model\entity\Cargo;

class PonerFechaReunionEnExpediente
{

    public function __invoke(int $Q_id_expediente, string $Q_f_reunion): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // Si pongo la fecha con datetimepicker, ya esta en ISO (hay que poner FALSE a la conversión).
        $oExpediente->setF_reunion($Q_f_reunion);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido guarda la fecha de reunión");
            $error_txt .= "<br>";
        }
        // firmar el paso de fijar reunion:
        $f_hoy_iso = date(DateTimeInterface::ATOM);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente, 'cargo_tipo' => Cargo::CARGO_REUNION]);
        foreach ($cFirmas as $oFirma) {
            $oFirma->DBCargar();
            if (ConfigGlobal::role_actual() === 'vcd') { // No sé si hace falta??
                $oFirma->setValor(Firma::V_D_OK);
            } else {
                $oFirma->setValor(Firma::V_OK);
            }
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso, FALSE);
            if ($oFirma->DBGuardar() === FALSE) {
                $error_txt .= $oFirma->getErrorTxt();
            }
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