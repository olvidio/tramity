<?php
use core\ConfigGlobal;
use expedientes\model\Expediente;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Usuario;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Qque = (string) \filter_input(INPUT_POST, 'que');

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qvoto = (integer) \filter_input(INPUT_POST, 'voto');
$Qcomentario = (string) \filter_input(INPUT_POST, 'comentario');

$id_cargo = ConfigGlobal::mi_id_cargo();
$oExpediente = new Expediente($Qid_expediente);
$id_tramite = $oExpediente->getId_tramite();
$id_ponente = $oExpediente->getPonente();

$error_txt = '';
$jsondata = [];
switch ($Qque) {
    case 'recorrido':
        $gesCargos = new GestorCargo();
        $aCargos =$gesCargos->getArrayCargos();
        
        // Comentarios y Aclaraciones
        $aWhere = ['id_expediente' => $Qid_expediente,
            '_ordre' => 'orden_tramite, orden_oficina ASC, tipo ASC'
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        $comentarios = '';
        $a_recorrido = [];
        $oFirma = new Firma();
        $a_valores = $oFirma->getArrayValor('all');
        foreach ($cFirmas as $oFirma) {
            $a_rec = [];
            $tipo = $oFirma->getTipo();
            $valor = $oFirma->getValor();
            $f_valor = $oFirma->getF_valor()->getFromLocal();
            $id_cargo = $oFirma->getId_cargo();
            $id_usuario = $oFirma->getId_usuario();
            $oUsuario = new Usuario($id_usuario);
            $nom_usuario = $oUsuario->getNom_usuario();
            $cargo = $aCargos[$id_cargo];
            if (!empty($valor)) {
                $voto = $a_valores[$valor];
                $observ = $oFirma->getObserv();
                $observ_ponente = $oFirma->getObserv_creador();
                if ($tipo == Firma::TIPO_VOTO) {
                    if (!empty($observ)) {
                        $comentarios .= empty($comentarios)? '' : "<br>";
                        $comentarios .= " $cargo($nom_usuario) [$voto]: $observ";
                    }
                    switch ($valor) {
                        case Firma::V_NO:
                        case Firma::V_D_NO:
                        case Firma::V_D_RECHAZADO:
                            $a_rec['class'] = "list-group-item-danger";
                            break;
                        case Firma::V_OK:
                        case Firma::V_D_OK:
                            $a_rec['class'] = "list-group-item-success";
                            break;
                        default:
                            $a_rec['class'] = "list-group-item-info";
                    }
                    $a_rec['valor'] = "$f_valor $cargo($nom_usuario) [$voto]";
                    $a_recorrido[] = $a_rec;
                }
                if ($tipo == Firma::TIPO_ACLARACION) {
                    $voto = _("aclaración");
                    $comentarios .= empty($comentarios)? '' : "<br>";
                    $comentarios .= " $cargo($nom_usuario) [$voto]: $observ";
                    if (!empty($observ_ponente)) {
                        $comentarios .= " rta: $observ_ponente";
                    }
                }
            } else {
                if ($tipo == Firma::TIPO_VOTO) {
                    $a_rec['class'] = "";
                    $a_rec['valor'] = $cargo;
                    $a_recorrido[] = $a_rec;
                }
            }
        }
        $jsondata['recorrido'] = json_encode($a_recorrido);
        break;
    
    case 'add':
        $Qa_cargos = (array)  \filter_input(INPUT_POST, 'a_cargos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // buscar el orden del ultimo:
        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $id_tramite, 'id_cargo' => Cargo::CARGO_VARIAS]);
        $oTramiteCargo = $cTramiteCargos[0];
        $orden_tramite = $oTramiteCargo->getOrden_tramite();
        // buscar el orden dentro de las firmas
        $aWhere = ['id_expediente' => $Qid_expediente,
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
        foreach ($Qa_cargos as $id_cargo) {
            $orden_oficina++;
            $oFirma = new Firma();
            $oFirma->setId_expediente($Qid_expediente);
            $oFirma->setId_tramite($id_tramite);
            $oFirma->setId_cargo_creador($id_ponente);
            $oFirma->setCargo_tipo(Cargo::CARGO_VARIAS);
            $oFirma->setId_cargo($id_cargo);
            $oFirma->setOrden_tramite($orden_tramite);
            $oFirma->setOrden_oficina($orden_oficina);
            // Al inicializar, sólo pongo los votos.
            $oFirma->setTipo(Firma::TIPO_VOTO);
            $oFirma->DBGuardar();
        }
        break;
    case 'del':
        $Qa_cargos = (array)  \filter_input(INPUT_POST, 'a_cargos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        foreach ($Qa_cargos as $id_cargo) {
            $aWhere = ['id_expediente' => $Qid_expediente,
                        'cargo_tipo' => Cargo::CARGO_TODOS_DIR,
                        'id_cargo' => $id_cargo,
            ];
            $gesFirmas = new GestorFirma();
            $cFirmas = $gesFirmas->getFirmas($aWhere);
            foreach ($cFirmas as $oFirma) {
                $oFirma->DBEliminar();
            }
        }
        break;
    case 'lst_falta_firma':
        // todos los cargos
        $gesCargos = new GestorCargo();
        $a_cargos = $gesCargos->getArrayCargos();
        
        $gesFirmas = new GestorFirma();
        $aCargosFaltan = $gesFirmas->faltaFirmarReunionExpediente($Qid_expediente);
        $a_posibles_cargos = [];
        foreach ($aCargosFaltan as $id_cargo) {
            // Sólo los cargos de personas, no los genereicos (sin oficina):
            if (empty($a_cargos[$id_cargo])) {
                continue;
            } else {
                $sigla = $a_cargos[$id_cargo];
            }
            $a_posibles_cargos[] = ['id'=>$id_cargo, 'sigla'=>$sigla ];
        }
        $jsondata['cargos'] = json_encode($a_posibles_cargos);
        break;
    case 'lst_cargos_libres':
        // todos los cargos
        $gesCargos = new GestorCargo();
        $a_todos_cargos = $gesCargos->getArrayCargos();
        
        $aWhere = ['id_expediente' => $Qid_expediente,
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        $a_posibles_cargos = [];
        foreach ($cFirmas as $oFirma) {
            $id_cargo = $oFirma->getId_cargo();
            unset($a_todos_cargos[$id_cargo]);
        }
        foreach ($a_todos_cargos as $id => $sigla) {
            $a_posibles_cargos[] = ['id'=>$id, 'sigla'=>$sigla ];
        }
        $jsondata['cargos'] = json_encode($a_posibles_cargos);
        break;
    case 'voto':
        $aWhere = ['id_expediente' => $Qid_expediente,
                    'id_cargo' => $id_cargo,
                    'tipo' => Firma::TIPO_VOTO,
                    '_ordre' => 'orden_tramite',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere);
        if (is_array($cFirmas) && count($cFirmas) == 0) {
            $error_txt .= _("No puede Firmar");
        } else {
            //$f_hoy_iso = date('Y-m-d');
            $f_hoy_iso = date(\DateTimeInterface::ISO8601);
            // Habrá que ver como se cambia un voto.
            // De momento sólo se firma el primero que no tenga valor.
            foreach ($cFirmas as $oFirma) {
                $valor = $oFirma->getValor();
                if ($valor == Firma::V_NO OR $valor == Firma::V_D_NO OR
                    $valor == Firma::V_OK OR $valor == Firma::V_D_OK OR
                    $valor == Firma::V_D_VISTO_BUENO) {
                    continue;
                } else {
                    break;
                }
            }
            $oFirma->setValor($Qvoto);
            $oFirma->setObserv($Qcomentario);
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setF_valor($f_hoy_iso,FALSE);
            if ($oFirma->DBGuardar() === FALSE ) {
                $error_txt .= $oFirma->getErrorTxt();
            }
            // comprobar que ya ha firmado todo el mundo, para 
            // pasarlo a scdl para distribuir (ok_scdl)
            $bParaDistribuir = $gesFirmas->paraDistribuir($Qid_expediente);
            if ($bParaDistribuir) {
                // guardar la firma de Cargo::CARGO_DISTRIBUIR;
                $aWhere = ['id_expediente' => $Qid_expediente,
                    'cargo_tipo' => Cargo::CARGO_DISTRIBUIR,
                    'tipo' => Firma::TIPO_VOTO,
                ];
                $cFirmaDistribuir = $gesFirmas->getFirmas($aWhere);
                if (is_array($cFirmaDistribuir) && count($cFirmaDistribuir) > 0) {
                    $oFirmaDistribuir = $cFirmaDistribuir[0];
                    $oFirmaDistribuir->setId_usuario(ConfigGlobal::mi_id_usuario());
                    $oFirmaDistribuir->setValor($Qvoto);
                    $oFirmaDistribuir->setF_valor($f_hoy_iso,FALSE);
                    if ($oFirmaDistribuir->DBGuardar() === FALSE ) {
                        $error_txt .= $oFirma->getErrorTxt();
                    }
                } else {
                    $error_txt .= _("No se puede firmar el cargo_tipo distribuir");
                }
                // cambio el estado del expediente.
                $oExpediente = new Expediente($Qid_expediente);
                $oExpediente->DBCarregar();
                switch ($Qvoto) {
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
                $oExpediente->setF_aprobacion($f_hoy_iso,FALSE); 
                if ($oExpediente->DBGuardar() === FALSE ) {
                    $error_txt .= $oExpediente->getErrorTxt();
                }
            }
            $bParaReunion = $gesFirmas->paraReunion($Qid_expediente);
            if($bParaReunion) {
                $oExpediente = new Expediente($Qid_expediente);
                $oExpediente->DBCarregar();
                $oExpediente->setEstado(Expediente::ESTADO_FIJAR_REUNION);
                if ($oExpediente->DBGuardar() === FALSE ) {
                    $error_txt .= $oExpediente->getErrorTxt();
                }
            }
        }
        break;
    case 'respuesta':   // aclaracion_respuesta
        $valor = Firma::V_A_RESPUESTA; //Respuesta aclaración.
        // buscar la primera peticion vacia:
        $aWhere = ['id_expediente' => $Qid_expediente,
            'tipo' => Firma::TIPO_ACLARACION,
            'observ_creador' => 'x',
            '_ordre' => 'orden_tramite DESC',
        ];
        $aOperador = [
            'observ_creador' => 'IS NULL',
        ];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere,$aOperador);
        if (is_array($cFirmas) && count($cFirmas) > 0) {
            // Ya existe una aclaración. Busco la última, para saber el orden.
            $oFirmaAclaracion = $cFirmas[0];
            $oFirmaAclaracion->DBCarregar();
            $oFirmaAclaracion->setObserv_creador($Qcomentario);
            if ($oFirmaAclaracion->DBGuardar() === FALSE ) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
        break;
    case 'nueva': // aclaracion_nueva
        $valor = Firma::V_A_NUEVA; //Nueva aclaración.
        $orden_oficina = 0;
        // Comprobar que no existe:
        $aWhere = ['id_expediente' => $Qid_expediente,
                    'id_cargo' => $id_cargo,
                    'tipo' => Firma::TIPO_ACLARACION,
                    '_ordre' => 'orden_tramite'
        ];
        $gesFirmas = new GestorFirma();
        $cFirmasA = $gesFirmas->getFirmas($aWhere);
        if (is_array($cFirmasA) && count($cFirmasA) > 0) {
            // Ya existe una aclaración. Busco la última, para saber el orden.
            $oFirmaAclaracion = $cFirmasA[0];
            $orden_tramite = $oFirmaAclaracion->getOrden_tramite();
            $orden_oficina = $oFirmaAclaracion->getOrden_oficina();
            $cargo_tipo = $oFirmaAclaracion->getCargo_tipo();
        }
        // orden trámite: Del primer voto no firmado
        $in_valor =  Firma::V_NO.','.Firma::V_D_NO.',';
        $in_valor .=  Firma::V_OK.','.Firma::V_D_OK.',';
        $in_valor .=  Firma::V_D_RECHAZADO;
        $aWhere = ['id_expediente' => $Qid_expediente,
                    'id_cargo' => $id_cargo,
                    'tipo' => Firma::TIPO_VOTO,
                    'valor' => $in_valor,
                    '_ordre' => 'orden_tramite DESC, orden_oficina DESC'
        ];
        $aOperador = ['valor' => 'NOT IN'];
        $gesFirmas = new GestorFirma();
        $cFirmas = $gesFirmas->getFirmas($aWhere, $aOperador);
        if (is_array($cFirmas) && count($cFirmas) == 0) {
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
                $orden_oficina = $orden_oficina + 1;
            }
            
            $f_hoy_iso = date(\DateTimeInterface::ISO8601);
            $oFirma = new Firma();
            $oFirma->setTipo(Firma::TIPO_ACLARACION);
            $oFirma->setId_expediente($Qid_expediente);
            $oFirma->setCargo_tipo($cargo_tipo);
            $oFirma->setId_cargo($id_cargo);
            $oFirma->setId_cargo_creador($id_ponente);
            $oFirma->setId_usuario(ConfigGlobal::mi_id_usuario());
            $oFirma->setId_tramite($id_tramite);
            $oFirma->setOrden_tramite($orden_tramite);
            $oFirma->setOrden_oficina($orden_oficina);
            $oFirma->setValor($valor);
            $oFirma->setObserv($Qcomentario);
            $oFirma->setF_valor($f_hoy_iso,FALSE);
            if ($oFirma->DBGuardar() === FALSE ) {
                $error_txt .= $oFirma->getErrorTxt();
            }
        }
    break;
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