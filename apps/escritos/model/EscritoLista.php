<?php

namespace escritos\model;

use core\ConfigGlobal;
use core\ViewTwig;
use DateTimeInterface;
use escritos\model\entity\EscritoDB;
use escritos\model\entity\GestorEscritoDB;
use expedientes\model\entity\GestorAccion;
use expedientes\model\Expediente;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\Hash;
use web\Protocolo;
use web\ProtocoloArray;
use function core\is_true;


class EscritoLista
{
    /**
     *
     * @var string
     */
    private string $modo;
    /**
     *
     * @var string
     */
    private string $filtro;
    /**
     *
     * @var boolean
     */
    private bool $show_tabs = FALSE;
    /**
     *
     * @var integer
     */
    private int $id_expediente;
    /**
     *
     * @var array
     */
    private array $aWhere;
    /**
     *
     * @var array
     */
    private array $aOperador;
    /**
     *
     * @var boolean
     */
    private bool $estan_todos_los_escritos_enviados;


    /*
     * filtros posibles: 
    'distribuir'
    'enviar'
    */

    public function mostrarTablaEnviar(string $fecha = ''): void
    {
        // visibilidad
        $oVisibilidad = new Visibilidad();
        $a_visibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();

        if (isset($this->id_expediente)) {
            $oExpediente = new Expediente($this->id_expediente);
            $estado = $oExpediente->getEstado();
            $cEscritos = $this->getEscritosParaEnviar($fecha);

            $gesCargos = new GestorCargo();
            $a_cargos = $gesCargos->getArrayCargos();

            $oProtLocal = new Protocolo();
            $oProtLocal->setNombre('local');
            $a_acciones = [];
            $oPermRegistro = new PermRegistro();
            foreach ($cEscritos as $oEscrito) {
                $perm_ver_escrito = $oPermRegistro->permiso_detalle($oEscrito, 'escrito');
                if ($perm_ver_escrito < PermRegistro::PERM_VER) {
                    continue;
                }

                $id_escrito = $oEscrito->getId_escrito();
                $f_salida = $oEscrito->getF_salida()->getFromLocal();
                $ponente = $oEscrito->getCreador();
                $ponente_txt = empty($a_cargos[$ponente]) ? '?' : $a_cargos[$ponente];

                $tipo_accion = $oEscrito->getAccion();

                $a_cosas = ['id_expediente' => $this->id_expediente,
                    'id_escrito' => $id_escrito,
                    'filtro' => $this->filtro,
                    'modo' => $this->modo,
                    'accion' => $tipo_accion,
                ];
                $pag_escrito = Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
                $pag_rev = Hash::link('apps/escritos/controller/escrito_rev.php?' . http_build_query($a_cosas));

                $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >" . _("mod.datos") . "</span>";
                $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_rev');\" >" . _("rev.texto") . "</span>";


                if (!empty($f_salida)) {
                    $a_accion['enviar'] = _("enviado") . " ($f_salida)";
                } else {
                    // si es anulado NO enviar!
                    if (is_true($oEscrito->getAnulado())) {
                        $a_accion['enviar'] = "-";
                    } else {
                        $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito('$id_escrito');\" >" . _("enviar") . "</span>";
                    }
                }

                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >" . _("ver") . "</span>";

                $destino_txt = $oEscrito->getDestinosEscrito();
                $visibilidad_dst = $oEscrito->getVisibilidad_dst();
                if (!empty($visibilidad_dst) && $visibilidad_dst !== Visibilidad::V_CTR_TODOS) {
                    $visibilidad_txt = $a_visibilidad_dst[$visibilidad_dst];
                    $destino_txt .= " ($visibilidad_txt)";
                }
                $prot_local_txt = $oEscrito->getProt_local_txt();
                // Tiene adjuntos?
                $adjuntos = '';
                $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
                if (!empty($a_id_adjuntos)) {
                    $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto('$id_escrito');\"  ></i>";
                }

                $json_ref = $oEscrito->getJson_prot_ref();
                $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
                $oArrayProtRef->setRef(TRUE);

                if ($this->getModo() === 'mod') {
                    $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >";
                    $prot_local .= $prot_local_txt;
                    $prot_local .= "</span>";
                } else {
                    $prot_local = $prot_local_txt;
                }


                if (!empty($oEscrito->getOk()) && $oEscrito->getOk() !== EscritoDB::OK_NO) {
                    $ok = '<i class="fas fa-check"></i>';
                } else {
                    $ok = '';
                }

                $asunto_detalle = $oEscrito->getAsuntoDetalle();
                if (is_true($oEscrito->getAnulado())) {
                    $anulado_txt = _("ANULADO");
                    $asunto_detalle = $anulado_txt . ' ' . $asunto_detalle;
                }
                $a_accion['ok'] = $ok;
                $a_accion['prot_local'] = $prot_local;
                $a_accion['tipo'] = '';
                $a_accion['ponente'] = $ponente_txt;
                $a_accion['destino'] = $destino_txt;
                $a_accion['ref'] = $oArrayProtRef->ListaTxtBr();
                $a_accion['categoria'] = '';
                $a_accion['asunto'] = $asunto_detalle;
                $a_accion['adjuntos'] = $adjuntos;

                $a_acciones[] = $a_accion;
            }

            if ($estado === Expediente::ESTADO_ACABADO_ENCARGADO
                || ($estado === Expediente::ESTADO_ACABADO_SECRETARIA)) {
                $ver_ok = TRUE;
            } else {
                $ver_ok = FALSE;
            }
        } else {
            $a_acciones = [];
            $ver_ok = FALSE;
        }

        $server = ConfigGlobal::getWeb(); //http://tramity.local
        $vista_dl = TRUE;
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $vista_dl = FALSE;
        }

        $a_campos = [
            'filtro' => $this->filtro,
            'modo' => $this->modo,
            'a_acciones' => $a_acciones,
            'server' => $server,
            'ver_ok' => $ver_ok,
            // para que el javascript sepa actualizar la vista
            'vista_dl' => $vista_dl,
        ];

        $oView = new ViewTwig('escritos/controller');
        $oView->renderizar('escrito_lst_enviar.html.twig', $a_campos);
    }

    private function getEscritosParaEnviar(string $fecha): array
    {
        if (empty($fecha)) {
            $fecha = date(DateTimeInterface::ATOM);
        }
        $gesEscritos = new GestorEscrito();
        // No enviados
        $aWhere = ['accion' => Escrito::ACCION_ESCRITO,
            'f_salida' => 'x',
            'ok' => entity\EscritoDB::OK_OFICINA,
        ];
        $aOperador = ['f_salida' => 'IS NULL',
        ];
        $cEscritosNoEnviados = $gesEscritos->getEscritos($aWhere, $aOperador);
        // Enviados a partir de $fecha
        $aWhere = ['accion' => Escrito::ACCION_ESCRITO,
            'f_salida' => $fecha,
            'ok' => entity\EscritoDB::OK_OFICINA,
        ];
        $aOperador = ['f_salida' => '>=',
        ];
        $cEscritosEnviadosFecha = $gesEscritos->getEscritos($aWhere, $aOperador);

        return array_merge($cEscritosNoEnviados, $cEscritosEnviadosFecha);
    }

    /**
     * @return string
     */
    public function getModo(): string
    {
        return $this->modo;
    }

    /**
     * @param string $modo
     */
    public function setModo(string $modo): void
    {
        $this->modo = $modo;
    }

    public function mostrarTabla(): void
    {
        $a_campos = $this->getCamposTabla();

        $oView = new ViewTwig('escritos/controller');
        switch ($this->filtro) {
            case 'acabados':
            case 'acabados_encargados':
            case 'enviar':
                $oView->renderizar('escrito_lst_enviar.html.twig', $a_campos);
                break;
            default:
                $oView->renderizar('escrito_lista.html.twig', $a_campos);
        }
    }

    private function getCamposTabla(): array
    {
        // visibilidad destino
        $oVisibilidad = new Visibilidad();
        $a_visibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();

        $oExpediente = new Expediente($this->id_expediente);
        $estado = $oExpediente->getEstado();

        $this->setCondicion();
        $bdistribuir = $this->isDistribuir();

        $oEscrito = new Escrito();
        $aAcciones = $oEscrito->getArrayAccion();

        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($this->aWhere);

        $oProtLocal = new Protocolo();
        $oProtLocal->setNombre('local');
        $todos_escritos = '';
        $prot_local_header = _("rev.texto");
        $a_acciones = [];
        $todos_escritos_enviados = TRUE;
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $tipo_accion = $oAccion->getTipo_accion();
            $txt_tipo = $aAcciones[$tipo_accion];

            $todos_escritos .= (empty($todos_escritos)) ? '' : ',';
            $todos_escritos .= $id_escrito;

            $oEscrito = new Escrito($id_escrito);
            $f_salida = $oEscrito->getF_salida()->getFromLocal();
            $tipo_accion = $oEscrito->getAccion();

            $enviado = FALSE;
            if (!empty($f_salida)) {
                $a_accion['enviar'] = _("enviado") . " ($f_salida)";
                $enviado = TRUE;
            } else {
                if ($tipo_accion === Escrito::ACCION_ESCRITO) {
                    // si es anulado NO enviar!
                    if (is_true($oEscrito->getAnulado())) {
                        $a_accion['enviar'] = "-";
                    } else {
                        // Se pasa a secretaria
                        $todos_escritos_enviados = FALSE;
                        $ok = $oEscrito->getOk();
                        if ($ok === EscritoDB::OK_OFICINA) {
                            $a_accion['enviar'] = _("en secretaría");
                            $enviado = TRUE;
                        } else {
                            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                                $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito('$id_escrito');\" >" . _("enviar") . "</span>";
                            } else {
                                $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_a_secretaria('$id_escrito');\" >" . _("pasar a secretaría") . "</span>";
                            }
                        }
                    }
                } else {
                    $a_accion['enviar'] = _("otra acción?");
                }
            }
             // solamente para los centros
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR && !$enviado) {
                $a_accion['eliminar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_eliminar_escrito('$id_escrito');\" >" . _("eliminar") . "</span>";
            } else {
                $a_accion['eliminar'] = "<span class=\"btn\" title=\"" . _("no se puede eliminar") . "\">---</span>";
            }

            $a_cosas = ['id_expediente' => $this->id_expediente,
                'id_escrito' => $id_escrito,
                'accion' => $tipo_accion,
                'filtro' => $this->filtro,
                'modo' => $this->modo,
            ];
            $pag_escrito = Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));
            $pag_rev = Hash::link('apps/escritos/controller/escrito_rev.php?' . http_build_query($a_cosas));

            if ($enviado) {
                $a_accion['link_mod'] = "-";
            } else {
                $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >" . _("mod.datos") . "</span>";
            }
            $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_rev');\" >" . _("rev.texto") . "</span>";

            if ($bdistribuir) {
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_distribuir_escrito('$id_escrito');\" >" . _("ver") . "</span>";
                $prot_local_header = _("prot. local/rev.texto");
            } else {
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >" . _("ver") . "</span>";
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    if ($enviado) {
                        $a_accion['link_ver'] = "<span class=\"btn\" title=\"" . _("los datos no se pueden modificar") . "\">---</span>";
                    } else {
                        $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >" . _("mod.datos") . "</span>";
                    }
                }
                if ($this->filtro === 'archivados') {
                    $prot_local_header = _("prot. local");
                } else {
                    $prot_local_header = _("rev.texto");
                }
            }

            $destino_txt = $oEscrito->getDestinosEscrito();
            $visibilidad_dst = $oEscrito->getVisibilidad_dst();
            if (!empty($visibilidad_dst) && $visibilidad_dst != Visibilidad::V_CTR_TODOS) {
                $visibilidad_txt = $a_visibilidad_dst[$visibilidad_dst];
                $destino_txt .= " ($visibilidad_txt)";
            }

            $default = ($this->filtro === 'archivados') ? '-' : '';
            $prot_local_txt = $oEscrito->getProt_local_txt($default);
            // Tiene adjuntos?
            $adjuntos = '';
            $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
            if (!empty($a_id_adjuntos)) {
                $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto('$id_escrito');\"  ></i>";
            }

            $json_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
            $oArrayProtRef->setRef(TRUE);

            if (($this->getModo() === 'mod') && !$enviado) {
                $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >";
                $prot_local .= $prot_local_txt;
                $prot_local .= "</span>";
            } else {
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >$prot_local_txt</span>";
                } else {
                    $prot_local = $prot_local_txt;
                }
            }

            if ($oEscrito->getOk() !== null && $oEscrito->getOk() !== EscritoDB::OK_NO) {
                $ok = '<i class="fas fa-check"></i>';
            } else {
                $comentarios = $oEscrito->getComentarios();
                if (!empty($comentarios)) {
                    $ok = "<span class='text-danger'>" . _("devuelto") . "</span>";
                } else {
                    $ok = '';
                }
            }

            $asunto_detalle = $oEscrito->getAsuntoDetalle();
            if (is_true($oEscrito->getAnulado())) {
                $anulado_txt = _("ANULADO");
                $asunto_detalle = $anulado_txt . ' ' . $asunto_detalle;
            }

            $a_accion['ok'] = $ok;
            $a_accion['prot_local'] = $prot_local;
            $a_accion['tipo'] = $txt_tipo;
            $a_accion['destino'] = $destino_txt;
            $a_accion['ref'] = $oArrayProtRef->ListaTxtBr();
            $a_accion['categoria'] = '';
            $a_accion['asunto'] = $asunto_detalle;
            $a_accion['adjuntos'] = $adjuntos;

            $a_acciones[] = $a_accion;
        }
        $this->setEstanTodosLosEscritosEnviados($todos_escritos_enviados);
        $ver_todo = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$todos_escritos');\" >" . _("ver todos") . "</span>";
        $server = ConfigGlobal::getWeb(); //http://tramity.local

        if ($estado === Expediente::ESTADO_ACABADO_ENCARGADO
            || ($estado === Expediente::ESTADO_ACABADO_SECRETARIA)) {
            $ver_ok = TRUE;
        } else {
            $ver_ok = FALSE;
        }

        $vista = ConfigGlobal::getVista();
        $vista_dl = TRUE;
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $vista_dl = FALSE;
        }

        $a_campos = [
            'filtro' => $this->filtro,
            'modo' => $this->modo,
            'id_expediente' => $this->id_expediente,
            'a_acciones' => $a_acciones,
            'ver_todo' => $ver_todo,
            'server' => $server,
            'bdistribuir' => $bdistribuir,
            'prot_local_header' => $prot_local_header,
            'ver_ok' => $ver_ok,
            // tabs_show
            'vista' => $vista,
            'vista_dl' => $vista_dl,
            'show_tabs' => $this->isShow_tabs(),
        ];

        return $a_campos;
    }

    /**
     *
     */
    private function setCondicion(): void
    {
        $aWhere = [];
        $aOperador = [];

        $aWhere['id_expediente'] = $this->id_expediente;
        $aWhere['_ordre'] = 'tipo_accion';

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    private function isDistribuir(): bool
    {
        $oExpediente = new Expediente($this->id_expediente);
        $estado = $oExpediente->getEstado();
        return $estado === Expediente::ESTADO_ACABADO;
    }

    /**
     * @return boolean
     */
    public function isShow_tabs(): bool
    {
        return $this->show_tabs;
    }

    /**
     * @param boolean $show_tabs
     */
    public function setShow_tabs(bool $show_tabs): void
    {
        $this->show_tabs = $show_tabs;
    }

    public function getNumeroEnviar(string $fecha = ''): int
    {
        $cEscritos = $this->getEscritosParaEnviar($fecha);
        return count($cEscritos);
    }

    public function getNumero(): int
    {
        $this->setCondicion();
        $gesEscritos = new GestorEscritoDB();
        $cEscritos = $gesEscritos->getEscritosDB($this->aWhere, $this->aOperador);
        return count($cEscritos);
    }

    /**
     * @return string
     */
    public function getFiltro(): string
    {
        return $this->filtro;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    /**
     * @return int
     */
    public function getId_expediente(): int
    {
        return $this->id_expediente;
    }

    /**
     * @param int $id_expediente
     */
    public function setId_expediente(int $id_expediente): void
    {
        $this->id_expediente = $id_expediente;
    }

    /**
     * @return boolean
     */
    public function EstanTodosLosEscritosEnviados(): bool
    {
        if (!isset($this->estan_todos_los_escritos_enviados)) {
            $this->getCamposTabla();
        }
        return $this->estan_todos_los_escritos_enviados;
    }

    /**
     * @param boolean $estan_todos_los_escritos_enviados
     */
    public function setEstanTodosLosEscritosEnviados(bool $estan_todos_los_escritos_enviados): void
    {
        $this->estan_todos_los_escritos_enviados = $estan_todos_los_escritos_enviados;
    }


}