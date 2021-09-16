<?php
namespace entradas\model;

use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\entity\GestorEntradaBypass;
use lugares\model\entity\GestorLugar;
use usuarios\model\PermRegistro;
use usuarios\model\entity\GestorOficina;
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
    private $aWhereADD = [];
    /**
     *
     * @var array
     */
    private $aOperadorADD = [];
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
    'escritos_cr'
    */
    /**
     * 
     */
    private function setCondicion() {
        $aWhere = [];
        $aOperador = [];

        $aWhere['_ordre'] = 'f_entrada DESC';
        switch ($this->filtro) {
            case 'en_ingresado':
                $aWhere['estado'] = Entrada::ESTADO_INGRESADO;
                break;
            case 'en_admitido':
                $aWhere['estado'] = Entrada::ESTADO_ADMITIDO;
                break;
            case 'en_asignado':
                $aWhere['estado'] = Entrada::ESTADO_ASIGNADO;
                break;
            case 'en_encargado':
                $encargado =  ConfigGlobal::role_id_cargo(); // valor por defecto
                if (!empty($this->aWhereADD['encargado'])) {
                    $encargado = $this->aWhereADD['encargado'];
                }
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                $aWhere['encargado'] = $encargado;
                
                // No marcado como visto:
                $gesEntradas = new GestorEntrada();
                $cEntradas = $gesEntradas->getEntradasNoVistoDB($encargado,'encargado');
                $a_entradas_encargado = [];
                foreach ($cEntradas as $oEntrada) {
                    $id_entrada = $oEntrada->getId_entrada();
                    $a_entradas_encargado[] = $id_entrada;
                }
                if (!empty($a_entradas_encargado)) {
                    $aWhere['id_entrada'] = implode(',',$a_entradas_encargado);
                    $aOperador['id_entrada'] = 'IN';
                } else {
                    $aWhere['id_entrada'] = 1;
                }
                break;
            case 'en_aceptado':
                $oficina = 'propia'; // valor por defecto
                if (!empty($this->aWhereADD['ponente'])) {
                    $oficina = $this->aWhereADD['ponente'];
                }
                
                $a_entradas_ponente = [];
                if ($oficina == 'propia') {
                    $id_oficina = ConfigGlobal::role_id_oficina();
                    
                    // No marcado como visto:
                    $gesEntradas = new GestorEntrada();
                    $cEntradas = $gesEntradas->getEntradasNoVistoDB($id_oficina,'ponente');
                    $a_entradas_ponente = [];
                    foreach ($cEntradas as $oEntrada) {
                        $id_entrada = $oEntrada->getId_entrada();
                        $a_entradas_ponente[] = $id_entrada;
                    }
                }
                
                //////// las oficina implicadas //////////////////////////////
                $a_entradas_resto = [];
                if ($oficina == 'resto') {
                    $id_oficina_role = ConfigGlobal::role_id_oficina();
                    if (!empty($id_oficina_role)) {
                        $id_oficina = ConfigGlobal::role_id_oficina();
                        $gesEntradas = new GestorEntrada();
                        $cEntradas = $gesEntradas->getEntradasNoVistoDB($id_oficina,'resto');
                        foreach ($cEntradas as $oEntrada) {
                            $id_entrada = $oEntrada->getId_entrada();
                            $a_entradas_resto[] = $id_entrada;
                        }
                    }
                }

                // sumar los dos: nuevos + aclaraciones.
                $a_entradas_suma = array_merge($a_entradas_ponente, $a_entradas_resto);
                $aWhere = [];
                $aOperador = [];
                if (!empty($a_entradas_suma)) {
                    $aWhere['id_entrada'] = implode(',',$a_entradas_suma);
                    $aOperador['id_entrada'] = 'IN';
                }
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
            case 'escritos_cr':
                // recibidos los ultimos 7 dias
                $oHoy = new DateTimeLocal();
                $oIni = new DateTimeLocal();
                $oIni->sub(new \DateInterval('P7D'));

                $gesLugares = new GestorLugar();
                $id_cr = $gesLugares->getId_cr();
                
                $a_condicion['lista_lugar'] = $id_cr;
                $str_condicion = http_build_query($a_condicion);
                
                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($id_cr);
                $oBuscar->setF_max($oHoy->getIso(),FALSE);
                $oBuscar->setF_min($oIni->getIso(),FALSE);
                
                $aCollection = $oBuscar->getCollection(5);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($this->filtro);
                    echo $oTabla->mostrarTabla();
                }
                exit();
                break;
              default:
                exit (_("No ha escogido ningún filtro"));
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
        
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
        
        
        //$pagina_ver = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
        $pagina_accion = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_accion.php';
        switch ($filtro) {
            case 'en_encargado':
                $encargado = $this->aWhereADD['encargado'];
                //$pagina_accion =  ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_accion.php';
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = '';
                break;
            case 'en_aceptado':
                $oficina = $this->aWhereADD['ponente'];
                $pagina_accion =  ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_accion.php';
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = '';
                break;
            case 'en_ingresado':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query(['filtro' => $filtro]));
                if (ConfigGlobal::role_actual() === 'vcd') {
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
            $oPermRegistro = new PermRegistro();
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas($this->aWhere,$this->aOperador);
            foreach ($cEntradas as $oEntrada) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oEntrada->getVisibilidad();
                $visibilidad_txt = empty($a_visibilidad[$visibilidad])? '?' : $a_visibilidad[$visibilidad];
                
                $perm_ver_escrito = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
                $id_entrada = $oEntrada->getId_entrada();
                $row['id_entrada'] = $id_entrada;
                
                $a_cosas = [ 'id_entrada' => $id_entrada,
                              'filtro' => $filtro,
                              'slide_mode' => $this->slide_mode,
                ];
                if ($filtro == 'en_aceptado') {
                    $a_cosas['oficina'] = $oficina;
                }
                if ($filtro == 'en_encargado') {
                    $a_cosas['encargado'] = $encargado;
                }
                
                $link_accion = Hash::link($pagina_accion.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                if ($perm_ver_escrito >= PermRegistro::PERM_VER) {
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >"._("ver")."</span>";
                    $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >"._("acción")."</span>";
                }
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
                
                $id_of_ponente =  $oEntrada->getPonente();
                $a_resto_oficinas = $oEntrada->getResto_oficinas();
                $of_ponente_txt = empty($a_posibles_oficinas[$id_of_ponente])? '?' : $a_posibles_oficinas[$id_of_ponente];
                $oficinas_txt = '';
                $oficinas_txt .= '<span class="text-danger">'.$of_ponente_txt.'</span>';
                foreach ($a_resto_oficinas as $id_oficina) {
                    $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
                    $oficinas_txt .= $a_posibles_oficinas[$id_oficina];
                }
                $row['oficinas'] = $oficinas_txt;
                
                $row['f_entrada'] = $oEntrada->getF_entrada()->getFromLocal();
                $row['f_contestar'] = $oEntrada->getF_contestar()->getFromLocal();
                
                // mirar si tienen escrito
                $row['f_escrito'] = $oEntrada->getF_documento()->getFromLocal();
                $row['visibilidad'] = $visibilidad_txt;
                // para ordenar. Si no añado id_entrada, sobre escribe.
                $f_entrada_iso = $oEntrada->getF_entrada()->getIso() . $id_entrada;
                $a_entradas[$f_entrada_iso] = $row;
            }
        }
        // ordenar por f_entrada:
        krsort($a_entradas,SORT_STRING);
            
        $url_update = 'apps/entradas/controller/entrada_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $a_cosas = [ 'filtro' => $filtro,
                      'slide_mode' => $this->slide_mode,
        ];
        if ($filtro == 'en_aceptado') {
            $a_cosas['oficina'] = $oficina;
        }
        if ($filtro == 'en_encargado') {
            $a_cosas['encargado'] = $encargado;
        }
        $pagina_cancel = Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($a_cosas));
        
        $txt_btn_new = '';
        $btn_new = FALSE;
        $secretaria = FALSE;
        if ( ConfigGlobal::role_actual() === 'secretaria') {
            $secretaria = TRUE;
            $btn_new = TRUE;
            $txt_btn_new = _("nueva entrada");
        }
        if (ConfigGlobal::role_actual() === 'vcd') {
            $btn_new = TRUE;
            $txt_btn_new = _("procesar");
        }
        if ($this->filtro == 'bypass') {
            $btn_new = FALSE;
        }
        
        $ver_accion = FALSE;
        if ($this->filtro == 'en_aceptado' || $this->filtro == 'en_encargado') {
            $ver_accion = TRUE;
        }
        
        $vista = (ConfigGlobal::role_actual() === 'secretaria')? 'secretaria' : 'home';
        
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
            //tabs_show
            'vista' => $vista,
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
    /**
     * @return array
     */
    public function getAWhereADD()
    {
        return $this->aWhereADD;
    }

    /**
     * @param array $aWhereADD
     */
    public function setAWhereADD($aWhereADD)
    {
        $this->aWhereADD = $aWhereADD;
    }

    /**
     * @return array
     */
    public function getAOperadorADD()
    {
        return $this->aOperadorADD;
    }

    /**
     * @param array $aOperadorADD
     */
    public function setAOperadorADD($aOperadorADD)
    {
        $this->aOperadorADD = $aOperadorADD;
    }


}