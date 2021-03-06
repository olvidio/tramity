<?php
namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use expedientes\model\entity\GestorAccion;
use expedientes\model\entity\GestorEscritoDB;
use usuarios\model\PermRegistro;
use usuarios\model\entity\GestorCargo;
use web\Hash;
use web\Protocolo;
use web\ProtocoloArray;


class EscritoLista {
    /**
     * 
     * @var string
     */
    private $modo;
    /**
     * 
     * @var string
     */
    private $filtro;
    /**
     * 
     * @var integer
     */
    private $id_expediente;
    /**
     * 
     * @var array
     */
    private $aWhere;
    /**
     * 
     * @var array
     */
    private $aOperador;
    /**
     * 
     * @var array
     */
    private $a_expedientes_nuevos = [];
    /**
     * 
     * @var boolean
     */
    private $todos_escritos_enviados;
    

    /*
     * filtros posibles: 
    'distribuir'
    'enviar'
    */
    /**
     * 
     */
    private function setCondicion() {
        $aWhere = [];
        $aOperador = [];

        $aWhere['id_expediente'] = $this->id_expediente;
        $aWhere['_ordre'] = 'tipo_accion';

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    private function getEscritosParaEnviar($fecha) {
        if (empty($fecha)) {
            $fecha = date(\DateTimeInterface::ISO8601);
        }
        $gesEscritos = new GestorEscrito();
        // No enviados
        $aWhere = [ 'accion' => Escrito::ACCION_ESCRITO,
                    'f_salida' => 'x',
                    'ok' => Escrito::OK_OFICINA,
                ];
        $aOperador = [ 'f_salida' => 'IS NULL',
                ];
        $cEscritosNoEnviados = $gesEscritos->getEscritos($aWhere,$aOperador);
        // Enviados a partir de $fecha
        $aWhere = [ 'accion' => Escrito::ACCION_ESCRITO,
                    'f_salida' => $fecha,
                    'ok' => Escrito::OK_OFICINA,
                ];
        $aOperador = [ 'f_salida' => '>=',
                ];
        $cEscritosEnviadosFecha = $gesEscritos->getEscritos($aWhere,$aOperador);
        
        $cEscritos = array_merge($cEscritosNoEnviados, $cEscritosEnviadosFecha);
        return $cEscritos;
    }

    private function getDistribuir() {
        $oExpediente = new Expediente($this->id_expediente);
        $estado = $oExpediente->getEstado();
        if ($estado == Expediente::ESTADO_ACABADO) {
            $bdistribuir = TRUE;
        } else {
            $bdistribuir = FALSE;
        }
        return $bdistribuir;
    }
    
    public function mostrarTablaEnviar($fecha='') {
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
            $ponente_txt = empty($a_cargos[$ponente])? '?' : $a_cargos[$ponente];
            
            
            $a_cosas =  ['id_expediente' => $this->id_expediente,
                'id_escrito' => $id_escrito,
                'filtro' => $this->filtro,
                'modo' => $this->modo,
            ];
            $pag_escrito =  Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
            $pag_rev =  Hash::link('apps/expedientes/controller/escrito_rev.php?'.http_build_query($a_cosas));
            
            $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >"._("mod.datos")."</span>";
            $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_rev');\" >"._("rev.texto")."</span>";
            
            
            if (!empty($f_salida)) {
                $a_accion['enviar'] = _("enviado")." ($f_salida)";
            } else {
                // si es anulado NO enviar!
                if (is_true($oEscrito->getAnulado())) {
                    $a_accion['enviar'] = "-";
                } else {
                    $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito('$id_escrito');\" >"._("enviar")."</span>";
                }
            }
            
            $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >"._("ver")."</span>";
            
            $destino_txt = $oEscrito->getDestinosEscrito();
            $json_prot_local = $oEscrito->getJson_prot_local();
            if (count(get_object_vars($json_prot_local)) == 0) {
                // Todavía no está definido el protocolo local;
                $prot_local_txt = _("revisar");
            } else {
                $oProtLocal->setLugar($json_prot_local->lugar);
                $oProtLocal->setProt_num($json_prot_local->num);
                $oProtLocal->setProt_any($json_prot_local->any);
                if (property_exists($json_prot_local, 'mas')) {
                    $oProtLocal->setMas($json_prot_local->mas);
                }
                $prot_local_txt = $oProtLocal->ver_txt();
            }
            // Tiene adjuntos?
            $adjuntos = '';
            $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
            if (!empty($a_id_adjuntos)) {
                $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto('$id_escrito');\"  ></i>";
            }
            
            $json_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            
            if ($this->getModo() == 'mod') {
                $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >";
                $prot_local .= $prot_local_txt;
                $prot_local .= "</span>";
            } else {
                $prot_local = $prot_local_txt;
            }
            
            
            if (!empty($oEscrito->getOk()) && $oEscrito->getOk() != Escrito::OK_NO) {
                $ok = '<i class="fas fa-check"></i>'; 
            } else {
                $ok = '';
            }

            $asunto_detalle = $oEscrito->getAsuntoDetalle();
            if (is_true($oEscrito->getAnulado())) {
                $anulado_txt = _("ANULADO");
                $asunto_detalle = $anulado_txt.' '.$asunto_detalle;
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
        
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        if ($estado == Expediente::ESTADO_ACABADO_ENCARGADO
            OR ($estado == Expediente::ESTADO_ACABADO_SECRETARIA) ) {
            $ver_ok = TRUE;
        } else {
            $ver_ok = FALSE;
        }
        $a_campos = [
            'filtro' => $this->filtro,
            'modo' => $this->modo,
            'a_acciones' => $a_acciones,
            'server' => $server,
            'ver_ok' => $ver_ok,
        ];
        
        $oView = new ViewTwig('expedientes/controller');
        return $oView->renderizar('escrito_lst_enviar.html.twig',$a_campos);
    }
    
    public function mostrarTabla() {
        $a_campos = $this->getCamposTabla();
        
        $oView = new ViewTwig('expedientes/controller');
        switch ($this->filtro) {
            case 'acabados':
            case 'enviar':
                return $oView->renderizar('escrito_lst_enviar.html.twig',$a_campos);
                break;
            default:
                return $oView->renderizar('escrito_lista.html.twig',$a_campos);
        }
    }

    private function getCamposTabla() {
        $oExpediente = new Expediente($this->id_expediente);
        $estado = $oExpediente->getEstado();
        
        $this->setCondicion();
        $bdistribuir = $this->getDistribuir();
        
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
            
            $todos_escritos .= (empty($todos_escritos))? '' : ',';
            $todos_escritos .= $id_escrito;
            
            $oEscrito = new Escrito($id_escrito);
            $f_salida = $oEscrito->getF_salida()->getFromLocal();
            $tipo_accion = $oEscrito->getAccion();
            
            $enviado = FALSE;
            if (!empty($f_salida)) {
                $a_accion['enviar'] = _("enviado")." ($f_salida)";
                $enviado = TRUE;
            } else {
                if ($tipo_accion == Escrito::ACCION_ESCRITO) {
                    // si es anulado NO enviar!
                    if (is_true($oEscrito->getAnulado())) {
                        $a_accion['enviar'] = "-";
                    } else {
                        // Se pasa a secretaria
                        $todos_escritos_enviados = FALSE;
                        $ok = $oEscrito->getOk();
                        if ($ok == EScrito::OK_OFICINA) {
                            $a_accion['enviar'] = _("en secretaría");
                            $enviado = TRUE;
                        } else {
                            $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_a_secretaria('$id_escrito');\" >"._("pasar a secretaría")."</span>";
                        }
                    }
                } else {
                    $a_accion['enviar'] = _("otra acción?");
                }
            }
            
            $a_cosas =  ['id_expediente' => $this->id_expediente,
                'id_escrito' => $id_escrito,
                'accion' => $tipo_accion,
                'filtro' => $this->filtro,
                'modo' => $this->modo,
            ];
            $pag_escrito =  Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
            $pag_rev =  Hash::link('apps/expedientes/controller/escrito_rev.php?'.http_build_query($a_cosas));
            
            if ($enviado) {
                $a_accion['link_mod'] = "-";
            } else {
                $a_accion['link_mod'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >"._("mod.datos")."</span>";
            }
            $a_accion['link_rev'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_rev');\" >"._("rev.texto")."</span>";
            
            if ($bdistribuir) { 
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_distribuir_escrito('$id_escrito');\" >"._("ver")."</span>";
                $prot_local_header = _("prot. local/rev.texto");
            } else {
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >"._("ver")."</span>";
                $prot_local_header = _("rev.texto");
            }

            $destino_txt = $oEscrito->getDestinosEscrito();
            $json_prot_local = $oEscrito->getJson_prot_local();
            if (count(get_object_vars($json_prot_local)) == 0) {
                // Todavía no está definido el protocolo local;
                $prot_local_txt = _("revisar");
            } else {
                $oProtLocal->setLugar($json_prot_local->lugar);
                $oProtLocal->setProt_num($json_prot_local->num);
                $oProtLocal->setProt_any($json_prot_local->any);
                if (property_exists($json_prot_local, 'mas')) {
                    $oProtLocal->setMas($json_prot_local->mas);
                }
                $prot_local_txt = $oProtLocal->ver_txt();
            }
            // Tiene adjuntos?
            $adjuntos = '';
            $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
            if (!empty($a_id_adjuntos)) {
                $adjuntos = "<i class=\"fas fa-paperclip fa-fw\" onclick=\"fnjs_revisar_adjunto('$id_escrito');\"  ></i>";
            }
            
            $json_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            
            if ($this->getModo() == 'mod' && !$enviado) {
                $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito('$id_escrito');\" >";
                $prot_local .= $prot_local_txt;
                $prot_local .= "</span>";
            } else {
                $prot_local = $prot_local_txt;
            }

            if (!empty($oEscrito->getOk()) && $oEscrito->getOk() != Escrito::OK_NO) {
                $ok = '<i class="fas fa-check"></i>'; 
            } else {
                $ok = '';
            }
            
            $asunto_detalle = $oEscrito->getAsuntoDetalle();
            if (is_true($oEscrito->getAnulado())) {
                $anulado_txt = _("ANULADO");
                $asunto_detalle = $anulado_txt.' '.$asunto_detalle;
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
        $this->setTodos_escritos_enviados($todos_escritos_enviados);
        $ver_todo = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$todos_escritos');\" >"._("ver todos")."</span>";
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        if ($estado == Expediente::ESTADO_ACABADO_ENCARGADO
            OR ($estado == Expediente::ESTADO_ACABADO_SECRETARIA) ) {
            $ver_ok = TRUE;
        } else {
            $ver_ok = FALSE;
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
        ];
        
        return $a_campos;
    }
    
    public function getNumeroEnviar($fecha='') {
        $cEscritos = $this->getEscritosParaEnviar($fecha);
        $num = count($cEscritos);
    
        return $num;
    }

    public function getNumero() {
        $this->setCondicion();
        $gesEscritos = new GestorEscritoDB();
        $cEscritos = $gesEscritos->getEscritosDB($this->aWhere,$this->aOperador);
        $num = count($cEscritos);
    
        return $num;
    }

    
    /**
     * @return string
     */
    public function getFiltro()
    {
        return $this->filtro;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro($filtro)
    {
        $this->filtro = $filtro;
    }

    /**
     * @return number
     */
    public function getId_expediente()
    {
        return $this->id_expediente;
    }

    /**
     * @param number $id_expediente
     */
    public function setId_expediente($id_expediente)
    {
        $this->id_expediente = $id_expediente;
    }
    /**
     * @return string
     */
    public function getModo()
    {
        return $this->modo;
    }

    /**
     * @param string $modo
     */
    public function setModo($modo)
    {
        $this->modo = $modo;
    }
    
    /**
     * @return boolean
     */
    public function isTodos_escritos_enviados()
    {
        if (!isset($this->todos_escritos_enviados)) {
            $this->getCamposTabla();
        }
        return $this->todos_escritos_enviados;
    }

    /**
     * @param boolean $todos_escritos_enviados
     */
    public function setTodos_escritos_enviados($todos_escritos_enviados)
    {
        $this->todos_escritos_enviados = $todos_escritos_enviados;
    }


}