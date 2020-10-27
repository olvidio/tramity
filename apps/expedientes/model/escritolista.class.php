<?php
namespace expedientes\model;

use core\ViewTwig;
use expedientes\model\entity\GestorAccion;
use web\Protocolo;
use web\ProtocoloArray;


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
                $aWhere['id_expediente'] = $this->id_expediente;
                $aWhere['_ordre'] = 'tipo_accion';
                break;
            case 'enviar':
                //$aWhere['estado'] = Expediente::ESTADO_ACABADO;
                break;
            case 'archivados':
                $aWhere['f_aprobacion'] = 'x';
                $aOperador['f_aprobacion'] = 'IS NOT NULL';
                break;
            case 'copias':
                $aWhere['f_aprobacion'] = 'x';
                $aOperador['f_aprobacion'] = 'IS NOT NULL';
                break;
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    public function mostrarTabla() {
        $this->setCondicion();
        
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones($this->aWhere);
        $a_acciones = [];
        
        $oProtLocal = new Protocolo();
        $oProtLocal->setNombre('local');
        $todos_escritos = '';
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $todos_escritos .= (empty($todos_escritos))? '' : ',';
            $todos_escritos .= $id_escrito;
            
            $oEscrito = new Escrito($id_escrito);
            
            $a_accion['link_ver'] = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >"._("ver")."</span>";
            $a_accion['enviar'] = "<span class=\"btn btn-link\" onclick=\"fnjs_enviar_escrito('$id_escrito');\" >"._("enviar")."</span>";
            
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
            
            $a_acciones[] = $a_accion;
        }
        $ver_todo = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$todos_escritos');\" >"._("ver todos")."</span>";
        
        $a_campos = [
            'a_acciones' => $a_acciones,
            'ver_todo' => $ver_todo,
        ];
        
        $oView = new ViewTwig('expedientes/controller');
        switch ($this->filtro) {
            case 'enviar':
                return $oView->renderizar('escrito_lst_enviar.html.twig',$a_campos);
                break;
            default:
                return $oView->renderizar('escrito_lista.html.twig',$a_campos);
        }
    }
    
    public function getNumero() {
        $this->setCondicion();
        $gesExpedientes = new GestorExpediente();
        $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere,$this->aOperador);
        $num = count($cExpedientes);
    
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