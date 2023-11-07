<?php

namespace expedientes\model;

use core\ConfigGlobal;
use escritos\model\Escrito;
use expedientes\model\entity\GestorAccion;
use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\GestorFirma;

class MoverExpedienteABorrador
{

    public function __invoke(int $Q_id_expediente, string $Q_que): void
    {
        $error_txt = '';
        $nuevo_creador = ConfigGlobal::role_id_cargo();

        // Hay que borrar: las firmas.
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente]);
        foreach ($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
            }
        }

        $oExpediente = new Expediente($Q_id_expediente);
        $oExpediente->DBCargar();
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oExpediente->setEstado(Expediente::ESTADO_BORRADOR);
        $asunto = $oExpediente->getAsunto();
        $asunto_retirado = _("RETIRADO") . " $asunto";
        $oExpediente->setAsunto($asunto_retirado);
        $oExpediente->setF_contestar('');
        $oExpediente->setF_ini_circulacion('');
        $oExpediente->setF_aprobacion('');
        $oExpediente->setF_reunion('');

        if ($Q_que === 'exp_a_borrador_cmb_creador') {
            $oExpediente->setPonente($nuevo_creador);
        }
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // Si hay escritos anulados, quitar el 'anulado'
        // cambiar también el creador de todos los escritos:
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Q_id_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            if ($oEscrito->DBCargar() === FALSE) {
                $err_cargar = sprintf(_("OJO! no existe el escrito en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oEscrito->setAnulado('f');
            if ($Q_que === 'exp_a_borrador_cmb_creador') {
                $oEscrito->setCreador($nuevo_creador);
            }
            if ($oEscrito->DBGuardar() === FALSE) {
                $error_txt .= _("No se ha guardado el escrito");
                $error_txt .= "<br>";
                $error_txt .= $oAccion->getErrorTxt();
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