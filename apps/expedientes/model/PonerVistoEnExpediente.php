<?php

namespace expedientes\model;

use core\ConfigGlobal;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use function core\is_true;

class PonerVistoEnExpediente
{

    public function __invoke(int $Q_id_expediente, array $Q_a_preparar): void
    {
        $error_txt = '';
        $mi_id_cargo = ConfigGlobal::role_id_cargo();
        $oCargo = new Cargo($mi_id_cargo);
        $mi_id_oficina = $oCargo->getId_oficina();

        $oExpediente = new Expediente($Q_id_expediente);
        if ($oExpediente->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // oficiales
        $new_preparar = [];
        foreach ($Q_a_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = (int)$id;
            if ($mi_id_cargo == $id) {
                // es un toggle: si esta 1 pongo 0 y al revés.
                $oJSON->visto = is_true($visto) ? FALSE : TRUE;
            } else {
                $oJSON->visto = $visto;
            }

            $new_preparar[] = $oJSON;
        }
        $oExpediente->setJson_preparar($new_preparar);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }

        //para regenerar la linea de oficiales
        $gesCargos = new GestorCargo();
        $a_cargos_oficina = $gesCargos->getArrayCargosOficina($mi_id_oficina);
        $a_preparar = [];
        foreach ($a_cargos_oficina as $id_cargo => $cargo) {
            $a_preparar[] = ['id' => $id_cargo, 'text' => $cargo, 'chk' => '', 'visto' => 0];
        }
        $json_preparar = $oExpediente->getJson_preparar();
        $html = '';
        foreach ($a_preparar as $key => $oficial2) {
            $id2 = $oficial2['id'];
            $text = $oficial2['text'];
            foreach ($json_preparar as $oficial) {
                $id = $oficial->id;
                $visto_db = empty($oficial->visto) ? 0 : $oficial->visto;
                // marcar las que están.
                if ($id === $id2) {
                    $chk = 'checked';
                    $visto = $visto_db;
                    // rompo el bucle
                    break;
                }
                $chk = '';
                $visto = '';
            }
            $html .= "<div class=\"form-check custom-checkbox form-check-inline\">";
            $html .= "<input type=\"checkbox\" class=\"form-check-input\" name=\"a_preparar[]\" id=\"$id2\" value=\"$id2#$visto\" $chk>";
            if ($visto) {
                $html .= "<label class=\"form-check-label text-success\" for=\"$id2\">$text (" . _("visto") . ")</label>";
            } else {
                $html .= "<label class=\"form-check-label\" for=\"$id2\">$text</label>";
            }
            $html .= "</div>";
        }

        if (empty($error_txt)) {
            $jsondata['success'] = TRUE;
            $jsondata['id_expediente'] = $Q_id_expediente;
            $jsondata['html'] = $html;
        } else {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        }
        $response = new JsonResponse($jsondata);
        $response->send();
    }
}