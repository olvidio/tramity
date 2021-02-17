<?php
namespace entradas\model;

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\entity\GestorEntradaBypass;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
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
                $gesCargos = new GestorCargo();
                // solo los propios (si soy oficial miro el cargo de dtor)
                $mi_cargo = ConfigGlobal::mi_id_cargo();
                $oCargo = new Cargo($mi_cargo);
                $director = $oCargo->getDirector();
                if (is_true($director)) {
                    $aWhere['ponente'] = $mi_cargo;
                } else {
                    $mi_id_oficina = $oCargo->getId_oficina();
                    $id_dtor_oficina = $gesCargos->getDirectorOficina($mi_id_oficina);
                    $aWhere['ponente'] = $id_dtor_oficina;
                }
                // + si soy oficina implicada:
                $role_actual = $_SESSION['session_auth']['role_actual'];
                $cCargos = $gesCargos->getCargos(['cargo' => $role_actual]);
                if (!empty($cCargos[0])) {
                    $id_oficina_role = $cCargos[0]->getId_oficina();
                } else {
                    $id_oficina_role = 0;
                }
                $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina_role); 
                $a_id_cargos_oficina = array_keys($a_cargos_oficina);
                $aWhere['resto_oficinas'] = '{'.implode(', ',$a_id_cargos_oficina).'}';
                $aOperador['resto_oficinas'] = 'OVERLAP';
                    
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                // De una semana
                $oHoy = new DateTimeLocal();
                $oHoy->sub(new \DateInterval('P7D'));
                $aWhere['f_entrada'] = $oHoy->getIso();
                $aOperador['f_entrada'] = '>';
                break;
            case 'bypass':
                // distribución cr
                $aWhere['bypass'] = 't';
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                // que no estén enviados
                $aWhereBypass = ['f_salida' => 'x'];
                $aOperadorBypass = ['f_salida' => 'IS NULL'];
                $gesEntradaBypass = new GestorEntradaBypass();
                $cEntradasBypass = $gesEntradaBypass->getEntradasBypass($aWhereBypass, $aOperadorBypass);
                $a_bypass = [];
                foreach ($cEntradasBypass as $oEntradaBypass) {
                    $a_bypass[] = $oEntradaBypass->getId_entrada();
                }
                if (!empty($a_bypass)) {
                    $aWhere['id_entrada'] = implode(',',$a_bypass);
                    $aOperador['id_entrada'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
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
        $a_posibles_cargos = $gesCargos->getArrayCargosDirector();
        
        
        //$pagina_ver = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
        $pagina_accion = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_accion.php';
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
                
                $link_accion = Hash::link($pagina_accion.'?'.http_build_query($a_cosas));
                //$link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >"._("ver")."</span>";
                //$row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >ver</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >mod</span>";
                $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >"._("acción")."</span>";
                
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
        
        $ver_accion = FALSE;
        if ($this->filtro == 'en_aceptado') {
            $ver_accion = TRUE;
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
            'ver_accion' => $ver_accion,
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