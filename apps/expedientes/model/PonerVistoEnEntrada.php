<?php

namespace expedientes\model;

use core\ConfigGlobal;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use function core\is_true;


class PonerVistoEnEntrada
{

    public static function en_visto($Qid_entrada): void
    {
        $error_txt = '';
        // nuevo formato: id_entrada#comparida (compartida = boolean)
        $a_entrada = explode('#', $Qid_entrada);
        $Q_id_entrada = (int)$a_entrada[0];
        $compartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);

        $Q_id_oficina = ConfigGlobal::role_id_oficina();
        $Q_id_cargo = ConfigGlobal::role_id_cargo();

        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }
        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe el entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }

        $aVisto = $oEntrada->getJson_visto(TRUE);
        // Si ya está no hay que añadirlo, sino modificarlo:
        $flag = FALSE;
        foreach ($aVisto as $key => $oVisto) {
            $oficina = $oVisto['oficina'];
            $cargo = $oVisto['cargo'];
            if ($oficina == $Q_id_oficina && $cargo == $Q_id_cargo) {
                $oVisto['visto'] = TRUE;
                $aVisto[$key] = $oVisto;
                $flag = TRUE;
            }
        }
        if ($flag === FALSE) {
            $oVisto = new stdClass;
            $oVisto->oficina = $Q_id_oficina;
            $oVisto->cargo = $Q_id_cargo;
            $oVisto->visto = TRUE;
            $aVisto[] = $oVisto;
        }

        $oEntrada->setJson_visto($aVisto);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        $oEntrada->comprobarVisto();

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        $response = new JsonResponse($jsondata);
        $response->send();
    }
}