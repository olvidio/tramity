<?php

namespace expedientes\model;

use core\ConfigGlobal;
use DateTimeInterface;
use escritos\model\Escrito;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorLugar;
use Symfony\Component\HttpFoundation\JsonResponse;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use web\Protocolo;
use function core\is_true;

class DistribuirExpediente
{

    public function __invoke(int $Q_id_expediente): void
    {
        $error_txt = '';
        $html = '';
        $oExpediente = new Expediente($Q_id_expediente);
        $estado_original = $oExpediente->getEstado();
        $oExpediente->setEstado(Expediente::ESTADO_ACABADO_SECRETARIA);
        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= _("No se ha podido cambiar el estado del expediente");
            $error_txt .= "<br>";
            $error_txt .= $oExpediente->getErrorTxt();
        }
        // firmar el paso de distribuir:
        $f_hoy_iso = date(DateTimeInterface::ATOM);
        $gesFirmas = new  GestorFirma();
        $cFirmas = $gesFirmas->getFirmas(['id_expediente' => $Q_id_expediente, 'cargo_tipo' => Cargo::CARGO_DISTRIBUIR]);
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
        // crear los números de protocolo local de los escritos.
        // busco aquí el id_lugar para no tener que hacerlo dentro del bucle.
        $sigla = $_SESSION['oConfig']->getSigla();
        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        $oLugar = $cLugares[0];
        $id_lugar = $oLugar->getId_lugar();
        // escritos del expediente: acciones tipo escrito
        $aWhereAccion = ['id_expediente' => $Q_id_expediente, '_ordre' => 'tipo_accion'];
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($aWhereAccion);
        $json_prot_local = [];
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $tipo_accion = $oAccion->getTipo_accion();
            // si es propuesta, o plantilla no genero protocolo:
            if ($tipo_accion === Escrito::ACCION_ESCRITO) {
                $proto = TRUE;
                $oEscrito = new Escrito($id_escrito);
                // si es un e12, no hay que numerar. [Si pongo el id_lugar origen, para poder hacer búsquedas]
                if ($oEscrito->getCategoria() === Categoria::CAT_E12) {
                    $proto = FALSE;
                    $id_lugar_local = $gesLugares->getId_sigla_local();
                    $oProtLocal = new Protocolo($id_lugar_local, 0, '', '');
                    $prot_local = $oProtLocal->getProt();
                    $oEscrito->DBCargar();
                    $oEscrito->setJson_prot_local($prot_local);
                    $oEscrito->DBGuardar();
                }
                // comprobar que no está anulado:
                if (is_true($oEscrito->getAnulado()) || $estado_original === Expediente::ESTADO_DILATA) {
                    $proto = FALSE;
                }
                if ($proto) {
                    $oEscrito->generarProtocolo($id_lugar);
                    // para poder insertar en la plantilla.
                    $json_prot_local = $oEscrito->getJson_prot_local();
                }
            }
            // si proviene de una plantilla, insertar el conforme en el texto:
            // cojo el protocolo del ultimo escrito. No tiene porque ser siempre cierto.
            if ($tipo_accion === Escrito::ACCION_PLANTILLA) {
                $oEscritoP = new Escrito($id_escrito);
                $html = $oEscritoP->addConforme($Q_id_expediente, $json_prot_local);
            }
        }

        if (empty($error_txt)) {
            $jsondata['success'] = TRUE;
            $jsondata['mensaje'] = 'ok';
            $jsondata['rta'] = $html;
        } else {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        }
        $response = new JsonResponse($jsondata);
        $response->send();
    }
}