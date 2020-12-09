<?php
namespace entradas\model;

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\entity\GestorCargo;
use web\Hash;
use web\Protocolo;
use web\ProtocoloArray;


class EntradaLista {
    /**
     * 
     * @var string
     */
    private $filtro;
    /**
     * 
     * @var integer
     */
    private $id_entrada;
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
    private $a_entradaas_nuevos = [];
    
    /*
     * filtros posibles: 
    'entrada'
    'bypass'
    'permanentes'
    'avisos'
    'pendientes'
    */
    /**
     * 
     */
    private function setCondicion() {
        $aWhere = [];
        $aOperador = [];

        switch ($this->filtro) {
            case 'en_asignar':
                $aWhere['modo_entrada'] = 1;
                break;
            case 'entrada':
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                break;
            case 'bypass':
                // distribución cr
                $aWhere['bypass'] = 't';
                break;
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    public function mostrarTabla() {
        $this->setCondicion();
        $pagina_nueva = '';
        
        $oEntrada = new Entrada();
        $a_categorias = $oEntrada->getArrayCategoria();
        
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        
        switch ($this->filtro) {
            case 'en_asignar':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $this->getFiltro()]));
                break;
            case 'entrada':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $this->getFiltro()]));
                break;
            case 'bypass':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_bypass.php';
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $this->getFiltro()]));
        }
        $pagina_ver = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
        
        $oProtOrigen = new Protocolo();
        $a_entradas = [];
        if (!empty($this->aWhere)) {
            $gesEntradas = new GestorEntrada();
            $this->aWhere['_ordre'] = 'id_entrada';
            $cEntradas = $gesEntradas->getEntradas($this->aWhere,$this->aOperador);
            foreach ($cEntradas as $oEntrada) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oEntrada->getVisibilidad();
                
                $id_entrada = $oEntrada->getId_entrada();
                $row['id_entrada'] = $id_entrada;
                
                $a_cosas = [ 'id_entrada' => $id_entrada,
                              'filtro' => $this->getFiltro(),
                ];
                $link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >"._("ver")."</span>";
                //$row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >ver</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >mod</span>";
                
                $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
                $row['protocolo'] = $oProtOrigen->ver_txt();
                
                $json_ref = $oEntrada->getJson_prot_ref();
                $oArrayProtRef = new ProtocoloArray($json_ref,'','');
                $oArrayProtRef->setRef(TRUE);
                $row['referencias'] = $oArrayProtRef->ListaTxtBr();
                
                $id_categoria = $oEntrada->getCategoria();
                $row['categoria'] = $a_categorias[$id_categoria];
                $row['asunto'] = $oEntrada->getAsuntoDetalle();
                
                $id_ponente =  $oEntrada->getPonente();
                $a_resto_oficinas = $oEntrada->getResto_oficinas();
                $oficinas_txt = '';
                $oficinas_txt .= '<span class="text-danger">'.$a_posibles_cargos[$id_ponente].'</span>';
                foreach ($a_resto_oficinas as $id_oficina) {
                    $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
                    $oficinas_txt .= $a_posibles_cargos[$id_oficina];
                }
                $row['oficinas'] = $oficinas_txt;
                
                $row['f_entrada'] = $oEntrada->getF_entrada()->getFromLocal();
                $row['f_contestar'] = $oEntrada->getF_contestar()->getFromLocal();
                
                // mirar si tienen escrito
                $row['f_escrito'] = $oEntrada->getF_documento()->getFromLocal();
                $row['visibilidad'] = $visibilidad;
                
                $a_entradas[] = $row;
            }
        }
            
        $url_update = 'apps/entradas/controller/entrada_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $secretaria = FALSE;
        if ($_SESSION['session_auth']['role_actual'] === 'secretaria') {
            $secretaria = TRUE;
        }
        
        $a_campos = [
            //'id_entrada' => $id_entrada,
            //'oHash' => $oHash,
            'a_entradas' => $a_entradas,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $this->getFiltro(),
            'server' => $server,
            'secretaria' => $secretaria,
        ];
        
        $oView = new ViewTwig('entradas/controller');
        return $oView->renderizar('entrada_lista.html.twig',$a_campos);
    }
    
    public function getNumero() {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas($this->aWhere,$this->aOperador);
            $num = count($cEntradas);
        } else {
            $num = '';            
        }
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
    public function getId_entrada()
    {
        return $this->id_entrada;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro($filtro)
    {
        $this->filtro = $filtro;
    }

    /**
     * @param number $id_entrada
     */
    public function setId_entrada($id_entrada)
    {
        $this->id_entrada = $id_entrada;
    }

}