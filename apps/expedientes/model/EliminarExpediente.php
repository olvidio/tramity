<?php

namespace expedientes\model;

use escritos\model\Escrito;
use expedientes\model\entity\GestorAccion;
use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\GestorFirma;

class EliminarExpediente
{

    public function __invoke(int $Q_id_expediente): void
    {
        // Si hay escritos enviados, no se borran.
        $error_txt = '';
        // Hay que borrar: el expediente, las firmas, las acciones, los escritos y los adjuntos de los escritos.
        $gesAccion = new GestorAccion();
        $cAcciones = $gesAccion->getAcciones(['id_expediente' => $Q_id_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            // Si hay escritos enviados, no se borran.
            $f_salida = $oEscrito->getF_salida()->getFromLocal();
            if (empty($f_salida)) {
                $rta = $oEscrito->eliminarTodo();
                if (!empty($rta)) {
                    $error_txt .= $rta;
                }
                if ($oAccion->DBEliminar() === FALSE) {
                    $error_txt .= _("No se ha eliminado la acción");
                    $error_txt .= "<br>";
                }
            }
        }
        // firmas:
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente]);
        foreach ($cFirmas as $oFirma) {
            if ($oFirma->DBEliminar() === FALSE) {
                $error_txt .= _("No se ha eliminado la firma");
                $error_txt .= "<br>";
            }
        }
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBEliminar() === FALSE) {
            $error_txt .= _("No se ha eliminado el expediente");
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