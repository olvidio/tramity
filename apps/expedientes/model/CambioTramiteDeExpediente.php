<?php
namespace expedientes\model;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\GestorFirma;
use web\Hash;

class CambioTramiteDeExpediente
{

    #[NoReturn] public function __invoke(int $Q_id_expediente, int $Q_tramite): void
    {
        $error_txt = '';
        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $id_tramite_old = $oExpediente->getId_tramite();
        $oExpediente->setId_tramite($Q_tramite);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // generar firmas
        $oExpediente->generarFirmas();
        $gesFirmas = new GestorFirma();
        // copiar las firmas:
        $gesFirmas->copiarFirmas($Q_id_expediente, $Q_tramite, $id_tramite_old);
        // borrar el recorrido del tramite anterior.
        $gesFirmas->borrarFirmas($Q_id_expediente, $id_tramite_old);


        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_expediente'] = $Q_id_expediente;
            $a_cosas = ['id_expediente' => $Q_id_expediente];
            $pagina_mod = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        $response = new JsonResponse( $jsondata);
        $response->send();
    }
}