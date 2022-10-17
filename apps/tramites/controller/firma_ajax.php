<?php

use core\ConfigGlobal;
use expedientes\model\Expediente;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Q_que = (string)filter_input(INPUT_POST, 'que');

$Q_id_expediente = (integer)filter_input(INPUT_POST, 'id_expediente');
$Q_voto = (integer)filter_input(INPUT_POST, 'voto');
$Q_comentario = (string)filter_input(INPUT_POST, 'comentario');

$id_cargo = ConfigGlobal::role_id_cargo();
$oExpediente = new Expediente($Q_id_expediente);
$id_tramite = $oExpediente->getId_tramite();
$id_ponente = $oExpediente->getPonente();

$error_txt = '';
$jsondata = [];
switch ($Q_que) {
    case 'recorrido':
        $gesFirmas = new GestorFirma();
        $aRecorrido = $gesFirmas->getRecorrido($Q_id_expediente);
        $a_recorrido = $aRecorrido['recorrido'];

        $jsondata['recorrido'] = json_encode($a_recorrido);
        break;

    case 'add':
        $Q_a_cargos = (array)filter_input(INPUT_POST, 'a_cargos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // buscar el orden del último:
        $gesTramiteCargo = new GestorTramiteCargo();
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $id_tramite, 'id_cargo' => Cargo::CARGO_VARIAS]);
        } else {
            // Para los centros
            $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $id_tramite, '_ordre' => 'orden_tramite DESC']);
        }
        $oTramiteCargo = $cTramiteCargos[0];
        $orden_tramite = $oTramiteCargo->getOrden_tramite();
        // buscar el orden dentro de las firmas
        $aWhere = ['id_expediente' => $Q_id_expediente,
            'orden_tramite' => $orden_tramite,
            '_ordre' => 'orden_oficina DESC',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        if (empty($cFirmas)) {
            $orden_oficina = 0;
        } else {
            $oFirmaOrden = $cFirmas[0];
            $orden_oficina = $oFirmaOrden->getOrden_oficina();
        }
        foreach ($Q_a_cargos as $id_cargo) {
            $orden_oficina++;
            $oFirma = new Firma();
            $oFirma->setId_expediente($Q_id_expediente);
            $oFirma->setId_tramite($id_tramite);
            $oFirma->setId_cargo_creador($id_ponente);
            $oFirma->setCargo_tipo(Cargo::CARGO_VARIAS);
            $oFirma->setId_cargo($id_cargo);
            $oFirma->setOrden_tramite($orden_tramite);
            $oFirma->setOrden_oficina($orden_oficina);
            // Al inicializar, sólo pongo los votos.
            $oFirma->setTipo(Firma::TIPO_VOTO);
            if ($oFirma->DBGuardar() === FALSE) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        break;
    case 'del':
        $Q_a_cargos = (array)filter_input(INPUT_POST, 'a_cargos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($Q_a_cargos as $id_cargo) {
            $aWhere = ['id_expediente' => $Q_id_expediente,
                'cargo_tipo' => Cargo::CARGO_TODOS_DIR,
                'id_cargo' => $id_cargo,
            ];
            $gesFirmas = new GestorFirma();
            $cFirmas = $gesFirmas->getFirmas($aWhere);
            foreach ($cFirmas as $oFirma) {
                if ($oFirma->DBEliminar() === FALSE) {
                    $error_txt .= $oFirma->getErrorTxt();
                }
            }
        }
        break;
    case 'lst_falta_firma':
        // todos los cargos
        $gesCargos = new GestorCargo();
        $a_cargos = $gesCargos->getArrayCargos();

        $gesFirmas = new GestorFirma();
        $aCargosFaltan = $gesFirmas->faltaFirmarReunionExpediente($Q_id_expediente);
        $a_posibles_cargos = [];
        foreach ($aCargosFaltan as $id_cargo) {
            // Sólo los cargos de personas, no los genéricos (sin oficina):
            if (empty($a_cargos[$id_cargo])) {
                continue;
            }

            $sigla = $a_cargos[$id_cargo];
            $a_posibles_cargos[] = ['id' => $id_cargo, 'sigla' => $sigla];
        }
        $jsondata['cargos'] = json_encode($a_posibles_cargos);
        break;
    case 'lst_cargos_libres':
        // todos los cargos
        $gesCargos = new GestorCargo();
        $a_todos_cargos = $gesCargos->getArrayCargosConUsuario();

        $aWhere = ['id_expediente' => $Q_id_expediente,
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        $a_posibles_cargos = [];
        $a_cargos_repetidos = [];
        $a_cargos_repetir = [];
        foreach ($cFirmas as $oFirma) {
            $id_cargo = $oFirma->getId_cargo();
            if (!empty($a_todos_cargos[$id_cargo])) {
                $a_cargos_repetidos[$id_cargo] = $a_todos_cargos[$id_cargo];
            }
            unset($a_todos_cargos[$id_cargo]);
        }
        foreach ($a_todos_cargos as $id => $sigla) {
            $a_posibles_cargos[] = ['id' => $id, 'sigla' => $sigla];
        }
        foreach ($a_cargos_repetidos as $id => $sigla) {
            $a_cargos_repetir[] = ['id' => $id, 'sigla' => $sigla];
        }
        $jsondata['cargos'] = json_encode($a_posibles_cargos);
        $jsondata['cargos_repetir'] = json_encode($a_cargos_repetir);
        break;
    case 'voto':
        $aWhere = ['id_expediente' => $Q_id_expediente,
            'id_cargo' => $id_cargo,
            'tipo' => Firma::TIPO_VOTO,
            '_ordre' => 'orden_tramite',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        if (is_array($cFirmas) && empty($cFirmas)) {
            $error_txt .= _("No puede Firmar");
        } else {
            $oExpediente = new Expediente($Q_id_expediente);
            $oExpediente->DBCargar();
            $estado = $oExpediente->getEstado();
            $f_hoy_iso = date(DateTimeInterface::ATOM);
            // Habrá que ver como se cambia un voto.
            // De momento sólo se firma el primero que no tenga valor.
            foreach ($cFirmas as $oFirma) {
                $valor = $oFirma->getValor();
                if (!($valor === Firma::V_NO || $valor === Firma::V_D_NO ||
                    $valor === Firma::V_OK || $valor === Firma::V_D_OK ||
                    $valor === Firma::V_D_VISTO_BUENO)) {
                    break;
                }
            }
            $oFirma->setValor($Q_voto);
            $oFirma->setValor($Q_voto);
            $oFirma->setObserv($Q_comentario);
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso, FALSE);
            if ($oFirma->DBGuardar() === FALSE) {
                $error_txt .= $oFirma->getErrorTxt();
            }
            // comprobar que ya han firmado todos, para:
            //  - en caso dl: pasarlo a scdl para distribuir (ok_scdl)
            //  - en caso ctr: marcar como acabado
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
                $bParaDistribuir = $gesFirmas->isParaDistribuir($Q_id_expediente);
                if ($bParaDistribuir) {
                    // guardar la firma de Cargo::CARGO_DISTRIBUIR;
                    $aWhere = ['id_expediente' => $Q_id_expediente,
                        'cargo_tipo' => Cargo::CARGO_DISTRIBUIR,
                        'tipo' => Firma::TIPO_VOTO,
                    ];
                    $cFirmaDistribuir = $gesFirmas->getFirmas($aWhere);
                    if (is_array($cFirmaDistribuir) && !empty($cFirmaDistribuir)) {
                        $oFirmaDistribuir = $cFirmaDistribuir[0];
                        $oFirmaDistribuir->DBCargar();
                        $oFirmaDistribuir->setId_usuario(ConfigGlobal::mi_id_usuario());
                        $oFirmaDistribuir->setValor($Q_voto);
                        $oFirmaDistribuir->setF_valor($f_hoy_iso, FALSE);
                        if ($oFirmaDistribuir->DBGuardar() === FALSE) {
                            $error_txt .= $oFirmaDistribuir->getErrorTxt();
                        }
                    } else {
                        $error_txt .= _("No se puede firmar el cargo_tipo distribuir");
                    }
                    // cambio el estado del expediente.
                    $oExpediente = new Expediente($Q_id_expediente);
                    $oExpediente->DBCargar();
                    switch ($Q_voto) {
                        case Firma::V_D_VISTO_BUENO:
                            $estado = Expediente::ESTADO_FIJAR_REUNION;
                            break;
                        case Firma::V_D_DILATA:
                            $estado = Expediente::ESTADO_DILATA;
                            break;
                        case Firma::V_D_ESPERA:
                            $estado = Expediente::ESTADO_ESPERA;
                            break;
                        case Firma::V_D_NO:
                            $estado = Expediente::ESTADO_NO;
                            break;
                        case Firma::V_D_RECHAZADO:
                            $estado = Expediente::ESTADO_RECHAZADO;
                            break;
                        default:
                            $estado = Expediente::ESTADO_ACABADO;
                    }
                    $oExpediente->setEstado($estado);
                    $oExpediente->setF_aprobacion($f_hoy_iso, FALSE);
                    $oExpediente->setF_aprobacion_escritos($f_hoy_iso, FALSE);
                    if ($oExpediente->DBGuardar() === FALSE) {
                        $error_txt .= $oExpediente->getErrorTxt();
                    }
                }
                // 22/2/21. Amplio a cambiar el estado para todos los casos.
                $bParaReunion = $gesFirmas->isParaReunion($Q_id_expediente);
                if ($bParaReunion) {
                    switch ($Q_voto) {
                        case Firma::V_D_VISTO_BUENO:
                            $estado = Expediente::ESTADO_FIJAR_REUNION;
                            break;
                        case Firma::V_D_DILATA:
                            $estado = Expediente::ESTADO_DILATA;
                            break;
                        case Firma::V_D_NO:
                            $estado = Expediente::ESTADO_NO;
                            break;
                        case Firma::V_D_RECHAZADO:
                            $estado = Expediente::ESTADO_RECHAZADO;
                            break;
                        default:
                            $estado = Expediente::ESTADO_ACABADO;
                    }
                    $oExpediente->setEstado($estado);
                    if ($oExpediente->DBGuardar() === FALSE) {
                        $error_txt .= $oExpediente->getErrorTxt();
                    }
                }
                // En caso de V_D_RECHAZADO o V_D_NO, anulo todos los escritos:
                if ($estado === Expediente::ESTADO_RECHAZADO || $estado === Expediente::ESTADO_NO) {
                    $oExpediente->anularEscritos();
                }
            }
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                if ($gesFirmas->hasTodasLasFirmas($Q_id_expediente)) {
                    // cambio el estado del expediente.
                    $oExpediente = new Expediente($Q_id_expediente);
                    $oExpediente->DBCargar();
                    $estado = Expediente::ESTADO_ACABADO;
                    $oExpediente->setEstado($estado);
                    $oExpediente->setF_aprobacion($f_hoy_iso, FALSE);
                    $oExpediente->setF_aprobacion_escritos($f_hoy_iso, FALSE);
                    if ($oExpediente->DBGuardar() === FALSE) {
                        $error_txt .= $oExpediente->getErrorTxt();
                    }
                }
            }
        }
        break;
    case 'respuesta':   // aclaracion_respuesta
        $valor = Firma::V_A_RESPUESTA; //Respuesta aclaración.
        // buscar la primera peticion vacia:
        $aWhere = ['id_expediente' => $Q_id_expediente,
            'tipo' => Firma::TIPO_ACLARACION,
            'observ_creador' => 'x',
            '_ordre' => 'orden_tramite DESC',
        ];
        $aOperador = [
            'observ_creador' => 'IS NULL',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere, $aOperador);
        if (is_array($cFirmas) && !empty($cFirmas)) {
            // Ya existe una aclaración. Busco la última, para saber el orden.
            $oFirmaAclaracion = $cFirmas[0];
            $oFirmaAclaracion->DBCargar();
            $oFirmaAclaracion->setObserv_creador($Q_comentario);
            if ($oFirmaAclaracion->DBGuardar() === FALSE) {
                $error_txt .= $oFirmaAclaracion->getErrorTxt();
            }
        }
        break;
    case 'nueva': // aclaracion_nueva
        $valor = Firma::V_A_NUEVA; //Nueva aclaración.
        $orden_oficina = 0;
        // Comprobar que no existe:
        $aWhere = ['id_expediente' => $Q_id_expediente,
            'id_cargo' => $id_cargo,
            'tipo' => Firma::TIPO_ACLARACION,
            '_ordre' => 'orden_tramite'
        ];
        $gesFirmas = new GestorFirma();
        $cFirmasA = $gesFirmas->getFirmas($aWhere);
        if (is_array($cFirmasA) && !empty($cFirmasA)) {
            // Ya existe una aclaración. Busco la última, para saber el orden.
            $oFirmaAclaracion = $cFirmasA[0];
            $orden_tramite = $oFirmaAclaracion->getOrden_tramite();
            $orden_oficina = $oFirmaAclaracion->getOrden_oficina();
            $cargo_tipo = $oFirmaAclaracion->getCargo_tipo();
        }
        // orden trámite: Del primer voto no firmado
        $in_valor = Firma::V_NO . ',' . Firma::V_D_NO . ',';
        $in_valor .= Firma::V_OK . ',' . Firma::V_D_OK . ',';
        $in_valor .= Firma::V_D_RECHAZADO;
        $aWhere = ['id_expediente' => $Q_id_expediente,
            'id_cargo' => $id_cargo,
            'tipo' => Firma::TIPO_VOTO,
            'valor' => $in_valor,
            '_ordre' => 'orden_tramite DESC, orden_oficina DESC'
        ];
        $aOperador = ['valor' => 'NOT IN'];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere, $aOperador);
        if (is_array($cFirmas) && empty($cFirmas)) {
            $error_txt .= _("No puede Firmar");
        } else {
            if (empty($orden_oficina)) {
                $oFirmaVoto = $cFirmas[0];
                $orden_tramite = $oFirmaVoto->getOrden_tramite();
                $orden = $oFirmaVoto->getOrden_oficina();
                $cargo_tipo = $oFirmaVoto->getCargo_tipo();
                // 1 más del que tengo.
                $orden_oficina = $orden + 1;
            } else {
                ++$orden_oficina;
            }

            $f_hoy_iso = date(DateTimeInterface::ATOM);
            $oFirma = new Firma();
            $oFirma->setTipo(Firma::TIPO_ACLARACION);
            $oFirma->setId_expediente($Q_id_expediente);
            $oFirma->setCargo_tipo($cargo_tipo);
            $oFirma->setId_cargo($id_cargo);
            $oFirma->setId_cargo_creador($id_ponente);
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setId_tramite($id_tramite);
            $oFirma->setOrden_tramite($orden_tramite);
            $oFirma->setOrden_oficina($orden_oficina);
            $oFirma->setValor($valor);
            $oFirma->setObserv($Q_comentario);
            $oFirma->setF_valor($f_hoy_iso, FALSE);
            if ($oFirma->DBGuardar() === FALSE) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (!empty($error_txt)) {
    $jsondata['success'] = FALSE;
    $jsondata['mensaje'] = $error_txt;
} else {
    $jsondata['success'] = TRUE;
}
//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);