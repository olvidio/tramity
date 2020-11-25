<?php
namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\entity\GestorAccion;
use web\Hash;
use web\Protocolo;
use web\ProtocoloArray;
use function core\is_true;
use expedientes\model\entity\GestorEscritoDB;


class EscritoLista {
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
    
    /*
     * filtros posibles: 
    'lista'
    'enviar'
    */
    /**
     * 
     */
    private function setCondicion() {
        $aWhere = [];
        $aOperador = [];

        switch ($this->filtro) {
            case 'lista':
            case 'acabados':
                $aWhere['id_expediente'] = $this->id_expediente;
                $aWhere['_ordre'] = 'tipo_accion';
                break;
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
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
    
    public function mostrarTabla() {
        $this->setCondicion();
        
        $bdistribuir = $this->getDistribuir();
        
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($this->aWhere);
        $a_acciones = [];
        
        $oProtLocal = new Protocolo();
        $oProtLocal->setNombre('local');
        $todos_escritos = '';
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $id_expediente = $oAccion->getId_expediente();
            $todos_escritos .= (empty($todos_escritos))? '' : ',';
            $todos_escritos .= $id_escrito;
            
            $oEscrito = new Escrito($id_escrito);
            $f_salida = $oEscrito->getF_salida()->getFromLocal();
            $tipo_accion = $oEscrito->getAccion();
            
            if (!empty($f_salida)) {
                $a_accion['enviar'] = _("enviado")." ($f_salida)";
            } else {
                if ($tipo_accion == Escrito::ACCION_ESCRITO) {
                    // si es anulado NO enviar!
                    if (is_true($oEscrito->getAnulado())) {
                        $a_accion['enviar'] = "-";
                    } else {
                    $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito('$id_escrito');\" >"._("enviar")."</span>";
                    }
                } else {
                    $a_accion['enviar'] = "otra acción?";
                }
            }
            
            if ($bdistribuir) { 
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_distribuir_escrito('$id_escrito');\" >"._("ver")."</span>";
            } else {
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >"._("ver")."</span>";
            }
            if ($this->filtro == 'acabados') { 
                $a_cosas =  ['id_expediente' => $id_expediente,
                    'id_escrito' => $id_escrito,
                    'accion' => $tipo_accion,
                    'filtro' => $this->filtro,
                ];
                $pag_escrito = Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
                
                $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_update_div('#main','$pag_escrito');\" >mod</span>";
            }
            
            $a_json_prot_destino = $oEscrito->getJson_prot_destino();
            $oArrayProtDestino = new ProtocoloArray($a_json_prot_destino,'','');
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
                $adjuntos = '<i class="fas fa-paperclip fa-fw"></i>';
            }
            
            $json_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            
            $prot_local = "<span class=\"btn btn-link\" onclick=\"fnjs_revisar_escrito(event,'$id_escrito');\" >";
            $prot_local .= $prot_local_txt;
            $prot_local .= "</span>";
            
            $a_accion['prot_local'] = $prot_local;
            $a_accion['protocolo'] = $oArrayProtDestino->ListaTxtBr();
            $a_accion['ref'] = $oArrayProtRef->ListaTxtBr();
            $a_accion['categoria'] = '';
            $a_accion['asunto'] = $oEscrito->getAsunto();
            $a_accion['adjuntos'] = $adjuntos;
            
            $a_acciones[] = $a_accion;
        }
        $ver_todo = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$todos_escritos');\" >"._("ver todos")."</span>";
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $a_campos = [
            'filtro' => $this->filtro,
            'id_expediente' => $this->id_expediente,
            'a_acciones' => $a_acciones,
            'ver_todo' => $ver_todo,
            'server' => $server,
        ];
        
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
     * @return number
     */
    public function getId_expediente()
    {
        return $this->id_expediente;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro($filtro)
    {
        $this->filtro = $filtro;
    }

    /**
     * @param number $id_expediente
     */
    public function setId_expediente($id_expediente)
    {
        $this->id_expediente = $id_expediente;
    }

}