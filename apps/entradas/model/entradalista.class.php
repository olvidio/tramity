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
    'en_ingresado':
    'en_admitido':
    'en_asignado':
    'en_aceptado':
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

        $aWhere['_ordre'] = 'id_entrada';
        switch ($this->filtro) {
            case 'en_ingresado':
                $aWhere['estado'] = Entrada::ESTADO_INGRESADO;
                $aWhere['_ordre'] = 'id_entrada DESC';
                break;
            case 'en_admitido':
                $aWhere['estado'] = Entrada::ESTADO_ADMITIDO;
                break;
            case 'en_asignado':
                $aWhere['estado'] = Entrada::ESTADO_ASIGNADO;
                break;
            case 'en_aceptado':
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                break;
            case 'bypass':
                // distribución cr
                $aWhere['bypass'] = 't';
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                break;
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    public function mostrarTabla() {
        $this->setCondicion();
        $pagina_nueva = '';
        $filtro = $this->getFiltro();
        
        $oEntrada = new Entrada();
        $a_categorias = $oEntrada->getArrayCategoria();
        $a_visibilidad = $oEntrada->getArrayVisibilidad();
        
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        
        
        //$pagina_ver = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
        switch ($this->filtro) {
            case 'en_ingresado':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
                if (ConfigGlobal::mi_usuario_cargo() === 'vcd') {
                    //$slide_mode = 't';
                    $aQuery = [ 'filtro' => $filtro, 'slide_mode' => 't'];
                    $pagina_nueva = Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
                }
                break;
            case 'en_admitido':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
                break;
            case 'en_asignado':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
                break;
            case 'entrada':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
                break;
            case 'bypass':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_bypass.php';
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
        }
        
        $oProtOrigen = new Protocolo();
        $a_entradas = [];
        $id_entrada = '';
        if (!empty($this->aWhere)) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas($this->aWhere,$this->aOperador);
            foreach ($cEntradas as $oEntrada) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oEntrada->getVisibilidad();
                $visibilidad_txt = $a_visibilidad[$visibilidad];
                
                $id_entrada = $oEntrada->getId_entrada();
                $row['id_entrada'] = $id_entrada;
                
                $a_cosas = [ 'id_entrada' => $id_entrada,
                              'filtro' => $filtro,
                              'slide_mode' => $this->slide_mode,
                ];
                //$link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas));
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
                $row['visibilidad'] = $visibilidad_txt;
                
                $a_entradas[] = $row;
            }
        }
            
        $url_update = 'apps/entradas/controller/entrada_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $pagina_cancel = Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query(['filtro' => $filtro]));
        
        $txt_btn_new = '';
        $btn_new = FALSE;
        $secretaria = FALSE;
        if ($_SESSION['session_auth']['role_actual'] === 'secretaria') {
            $secretaria = TRUE;
            $btn_new = TRUE;
            $txt_btn_new = _("nueva entrada");
        }
        if ($_SESSION['session_auth']['role_actual'] === 'vcd') {
            $btn_new = TRUE;
            $txt_btn_new = _("procesar");
        }
        if ($this->filtro == 'bypass') {
            $btn_new = FALSE;
        }
        
        $a_campos = [
            //'id_entrada' => $id_entrada,
            //'oHash' => $oHash,
            'a_entradas' => $a_entradas,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $filtro,
            'server' => $server,
            'secretaria' => $secretaria,
            'btn_new' => $btn_new,
            'txt_btn_new' => $txt_btn_new,
            'pagina_cancel' => $pagina_cancel,
        ];
        
        $oView = new ViewTwig('entradas/controller');
        if ($this->slide_mode === 't') {
            include ('apps/entradas/controller/entrada_ver_slide.php');
        } else {
            return $oView->renderizar('entrada_lista.html.twig',$a_campos);
        }
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
     * @param string $slide_mode
     */
    public function setSlide_mode($slide_mode)
    {
        $this->slide_mode = $slide_mode;
    }

    /**
     * @param number $id_entrada
     */
    public function setId_entrada($id_entrada)
    {
        $this->id_entrada = $id_entrada;
    }

}