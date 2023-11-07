<?php

namespace expedientes\model;

use core;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use web\Hash;
use function core\is_true;


class GuardarExpediente
{

    public function __invoke(string $modo_respuesta, int $Q_id_expediente, int $Q_tramite, int $Q_estado, int $Q_prioridad, string $Q_asunto, string $Q_entradilla, string $Q_f_contestar, string $Q_ponente, string $Q_f_reunion, string $Q_f_aprobacion, array $Q_a_firmas_oficina, array $Q_a_firmas, int $Q_vida, int $Q_visibilidad, array $Q_a_preparar, string $Q_filtro)
    {
        $error_txt = '';
        if (!empty($Q_id_expediente)) {
            $oExpediente = new Expediente($Q_id_expediente);
            if ($oExpediente->DBCargar() === FALSE) {
                $err_cargar = sprintf(_("OJO! no existe el expediente en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            // Mantengo al ponente como creador...
        } else {
            // si falla el javascript, puede ser que se hagan varios click a 'Guardar'
            // y se dupliquen los expedientes. Me aseguro de que no exista uno igual:
            $gesExpedientes = new GestorExpediente();
            $aWhere = ['id_tramite' => $Q_tramite,
                'estado' => $Q_estado,
                'prioridad' => $Q_prioridad,
                'asunto' => $Q_asunto,
                'entradilla' => $Q_entradilla,
            ];
            if (!empty($Q_f_contestar)) {
                $oConverter = new core\ConverterDate('date', $Q_f_contestar);
                $f_contestar_iso = $oConverter->toPg();
                $aWhere['f_contestar'] = $f_contestar_iso;
            }
            $cExpedientes = $gesExpedientes->getExpedientes($aWhere);
            if (count($cExpedientes) > 0) {
                exit (_("Creo que ya se ha creado"));
            }
            // nuevo.
            $oExpediente = new Expediente();
            $Q_estado = Expediente::ESTADO_BORRADOR;
            $oExpediente->setPonente($Q_ponente);
        }

        $oExpediente->setId_tramite($Q_tramite);
        $oExpediente->setEstado($Q_estado);
        $oExpediente->setPrioridad($Q_prioridad);
        $oExpediente->setF_reunion($Q_f_reunion);
        $oExpediente->setF_aprobacion($Q_f_aprobacion);
        $oExpediente->setF_contestar($Q_f_contestar);
        $oExpediente->setAsunto($Q_asunto);
        $oExpediente->setEntradilla($Q_entradilla);

        // según el trámite mirar si hay que grabar oficiales y/o varios cargos.
        // para los crt siempre hay oficiales:
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $oficiales = TRUE;
            $varias = FALSE;
        } else {
            $oficiales = FALSE;
            $aWhere = ['id_tramite' => $Q_tramite, 'id_cargo' => Cargo::CARGO_OFICIALES];
            $gesTramiteCargo = new GestorTramiteCargo();
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
            if (count($cTramiteCargos) > 0) {
                $oficiales = TRUE;
            }
            $varias = FALSE;
            $aWhere = ['id_tramite' => $Q_tramite, 'id_cargo' => Cargo::CARGO_VARIAS];
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos($aWhere);
            if (count($cTramiteCargos) > 0) {
                $varias = TRUE;
            }
        }
        // pasar a array para postgresql
        if ($oficiales) {
            $a_filter_firmas_oficina = array_filter($Q_a_firmas_oficina); // Quita los elementos vacíos y nulos.
            $oExpediente->setFirmas_oficina($a_filter_firmas_oficina);
        } else {
            $oExpediente->setFirmas_oficina('');
        }

        // pasar a array para postgresql
        if ($varias) {
            $a_filter_firmas = array_filter($Q_a_firmas); // Quita los elementos vacíos y nulos.
            $oExpediente->setResto_oficinas($a_filter_firmas);
        } else {
            $oExpediente->setResto_oficinas('');
        }

        $oExpediente->setVida($Q_vida);
        $oExpediente->setVisibilidad($Q_visibilidad);

        // oficiales
        $new_preparar = [];
        foreach ($Q_a_preparar as $oficial) {
            $id = strtok($oficial, '#');
            $visto = strtok('#');
            $oJSON = new stdClass;
            $oJSON->id = (int)$id;
            // hay que asegurar que sea bool
            $oJSON->visto = is_true($visto) ? TRUE : FALSE;

            $new_preparar[] = $oJSON;
        }
        $oExpediente->setJson_preparar($new_preparar);

        if ($oExpediente->DBGuardar() === FALSE) {
            $error_txt .= $oExpediente->getErrorTxt();
            $id_expediente = 0;
        } else {
            $id_expediente = $oExpediente->getId_expediente();
            // las etiquetas, después de tener el id_expediente (si es nuevo):
            $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $oExpediente->setEtiquetas($Q_a_etiquetas);
            if ($oExpediente->DBGuardar() === FALSE) {
                $error_txt .= $oExpediente->getErrorTxt();
            }
        }
        ///////// modo respuesta: array //////////////////////////////////
        if ($modo_respuesta === 'array') {
            return array('id_expediente' => $id_expediente, 'error_txt' => $error_txt);
        }

        ///////// modo respuesta: json //////////////////////////////////
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