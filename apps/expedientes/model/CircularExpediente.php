<?php

namespace expedientes\model;

use core\ConfigGlobal;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use usuarios\model\entity\Cargo;
use web\Hash;

class CircularExpediente
{

    public function __invoke(int $id_expediente, string $Q_filtro)
    {
        $error_txt = '';
        $oExpediente = new Expediente($id_expediente);
        $oExpediente->DBCargar();
        $f_hoy_iso = date(DateTimeInterface::ATOM);
        // se pone la fecha del escrito como hoy:
        $oExpediente->setF_escritos($f_hoy_iso, FALSE);
        // Guardar fecha y cambiar estado
        $oExpediente->setF_ini_circulacion($f_hoy_iso, FALSE);
        $oExpediente->setEstado(Expediente::ESTADO_CIRCULANDO);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // generar firmas
        $role_id_cargo = ConfigGlobal::role_id_cargo();
        $oExpediente->generarFirmas();
        $gesFirmas = new GestorFirma();
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            // Para los centros, firmo sea quien sea
            $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $id_expediente, 'id_cargo' => $role_id_cargo, 'tipo' => Firma::TIPO_VOTO]);
            $oFirmaPrimera = $cFirmas[0];
            $oFirmaPrimera->DBCargar();
            $oFirmaPrimera->setValor(Firma::V_OK);
        } else {
            // Si soy el primero, Ya firmo.
            $oFirmaPrimera = $gesFirmas->getPrimeraFirma($id_expediente);
            $id_primer_cargo = $oFirmaPrimera->getId_cargo();
            if ($id_primer_cargo === $role_id_cargo) {
                if (ConfigGlobal::role_actual() === 'vcd') { // No sé si hace falta??
                    $oFirmaPrimera->setValor(Firma::V_D_OK);
                } else {
                    $oFirmaPrimera->setValor(Firma::V_OK);
                }
            }
        }
        $oFirmaPrimera->setId_usuario(ConfigGlobal::mi_id_usuario());
        $oFirmaPrimera->setObserv('');
        $oFirmaPrimera->setF_valor($f_hoy_iso, FALSE);
        if ($oFirmaPrimera->DBGuardar() === FALSE) {
            $error_txt .= $oFirmaPrimera->getErrorTxt();
        }
        // comprobar que ya han firmado todos, para:
        //  - en caso dl: pasarlo a scdl para distribuir (ok_scdl)
        //  - en caso ctr: marcar como circulando
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $bParaDistribuir = $gesFirmas->isParaDistribuir($id_expediente);
            if ($bParaDistribuir) {
                // guardar la firma de Cargo::CARGO_DISTRIBUIR;
                if ($oExpediente->DBCargar() === FALSE) {
                    $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_cargar);
                }
                $oExpediente->setEstado(Expediente::ESTADO_ACABADO);
                $oExpediente->setF_aprobacion($f_hoy_iso, FALSE);
                $oExpediente->setF_aprobacion_escritos($f_hoy_iso, FALSE);
                if ($oExpediente->DBGuardar() === FALSE) {
                    $error_txt .= $oExpediente->getErrorTxt();
                }
            }
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            // cambio el estado del expediente.
            if ($oExpediente->DBCargar() === FALSE) {
                $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $estado = Expediente::ESTADO_CIRCULANDO;
            $oExpediente->setEstado($estado);
            if ($oExpediente->DBGuardar() === FALSE) {
                $error_txt .= $oExpediente->getErrorTxt();
            }
        }

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
            $statusCode = Response::HTTP_NOT_MODIFIED;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['mensaje'] = 'hola';
            $jsondata['id_expediente'] = $id_expediente;
            $a_cosas = ['id_expediente' => $id_expediente, 'filtro' => $Q_filtro];
            $pagina_mod = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
            $statusCode = Response::HTTP_CREATED;
        }
        $response = new JsonResponse($jsondata, status: $statusCode);
        $response->send();
    }
}