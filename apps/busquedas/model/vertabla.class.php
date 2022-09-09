<?php
namespace busquedas\model;

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\Categoria;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use web\Lista;
use web\Protocolo;
use web\ProtocoloArray;

class VerTabla {
    
    /**
     * Id_sigla
     *
     * @var integer
     */
    private $id_sigla;

    /**
     * Id_lugar
     *
     * @var integer
     */
    private $id_lugar;

    /**
     * Prot_num
     *
     * @var integer
     */
    private $prot_num;

    /**
     * Prot_any
     *
     * @var integer
     */
    private $prot_any;

    /**
     * Collection
     *
     * @var array
     */
    private $aCollection;

    /**
     * Key (entradas | escritos)
     *
     * @var string
     */
    private $sKey;

    /**
     * condicion de la búsqueda
     *
     * @var string
     */
    private $sCondicion;

    /**
     * 
     * @var string
     */
    private $sTitulo;

    /**
     * Botones
     *
     * @var array
     */
    private $aBotones;

    private $dt_op_dom;
    private $dt_op_buttons;
    
    /**
     * @return number
     */
    public function getId_sigla()
    {
        return $this->id_sigla;
    }

    /**
     * @return number
     */
    public function getId_lugar()
    {
        return $this->id_lugar;
    }

    /**
     * @return number
     */
    public function getProt_num()
    {
        return $this->prot_num;
    }

    /**
     * @return number
     */
    public function getProt_any()
    {
        return $this->prot_any;
    }

    /**
     * @param number $id_sigla
     */
    public function setId_sigla($id_sigla)
    {
        $this->id_sigla = $id_sigla;
    }

    /**
     * @param number $id_lugar
     */
    public function setId_lugar($id_lugar)
    {
        $this->id_lugar = $id_lugar;
    }

    /**
     * @param number $prot_num
     */
    public function setProt_num($prot_num)
    {
        $this->prot_num = $prot_num;
    }

    /**
     * @param number $prot_any
     */
    public function setProt_any($prot_any)
    {
        $this->prot_any = $prot_any;
    }

    /**
     * @param array $aCollection
     */
    public function setCollection($Collection)
    {
        $this->aCollection = $Collection;
    }

    /**
     * @param string $sKey
     */
    public function setKey($key)
    {
        $this->sKey = $key;
    }

    /**
     * @param string $sCondicion
     */
    public function setCondicion($condicion)
    {
        $this->sCondicion = $condicion;
    }

    /**
     * @param string $sFiltro
     */
    public function setFiltro($filtro)
    {
        $this->sFiltro = $filtro;
    }

    
    public function mostrarTabla() {
        switch ($this->sKey) {
            case 'entradas_ref':
            	if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
					$this->sTitulo = _("escritos recibidos en el centro con referencias al escrito");
            	} else {
					$this->sTitulo = _("escritos recibidos en la Delegación con referencias al escrito");
            	}
                $this->tabla_entradas($this->aCollection);
                break;
            case 'entradas_bypass':
				$this->sTitulo = _("escritos de cr para el centro");
                $this->tabla_entradas($this->aCollection);
                break;
            case 'entradas':
            	if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
					$this->sTitulo = _("escritos recibidos en el centro");
            	} else {
					$this->sTitulo = _("escritos recibidos en la Delegación");
            	}
                $this->tabla_entradas($this->aCollection);
                break;
            case 'entradas_compartidas':
            	if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
					$this->sTitulo = _("escritos recibidos en el centro");
            	} else {
					$this->sTitulo = _("escritos recibidos en la Delegación");
            	}
                $this->tabla_entradas_compartidas($this->aCollection);
                break;
            case 'escritos_ref':
            	if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
					$this->sTitulo = _("escritos aprobados en el centro con referencias al escrito");
            	} else {
					$this->sTitulo = _("escritos aprobados en la Delegación con referencias al escrito");
            	}
                $this->tabla_escritos($this->aCollection);
                break;
            case 'escritos':
            	if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
					$this->sTitulo = _("escritos aprobados en el centro");
            	} else {
					$this->sTitulo = _("escritos aprobados en la Delegación");
            	}
                $this->tabla_escritos($this->aCollection);
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
    }
    // ---------------------------------- tablas ----------------------------

    public function tabla_entradas_compartidas($aCollection) {
        $oCategoria = new Categoria();
        $a_categorias = $oCategoria->getArrayCategoria();
        
        $a_cabeceras=array( array('name'=>ucfirst(_("protocolo origen")),'formatter'=>'clickFormatter'),
                            ucfirst(_("ref.")),
                            _("categoria"),
                            array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
                            array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
                            array('name'=>ucfirst(_("fecha entrada")),'class'=>'fecha')
                            );
        
        $oProtOrigen = new Protocolo();
        $a_valores = [];
        $i=0;
        foreach ($aCollection as $oEntrada) {
        	// permisos: NO TIENE el campo visibilidad.
            $i++;
            
			$id_entrada=$oEntrada->getId_entrada_compartida();
            $f_entrada=$oEntrada->getF_entrada();
            
            $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
            $protocolo = $oProtOrigen->ver_txt();
            
            // referencias
            $json_ref = $oEntrada->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            $referencias = $oArrayProtRef->ListaTxtBr();
            
            $asunto = $oEntrada->getAsunto_entrada();
            $categoria = $oEntrada->getCategoria();
            $categoria_txt = empty($a_categorias[$categoria])? '' : $a_categorias[$categoria];
            $f_doc = $oEntrada->getF_documento();
            
            $a_valores[$i]['sel']="$id_entrada";
            $a_valores[$i][1]=$protocolo;
            $a_valores[$i][2]=$referencias;
            $a_valores[$i][3]=$categoria_txt;
            $a_valores[$i][4]= $asunto;
            $a_valores[$i][5]=$f_doc->getFromLocal();
            $a_valores[$i][6]=$f_entrada->getFromLocal();
        }
        
        $oTabla = new Lista();
        $oTabla->setId_tabla('ver_tabla_'.$this->sKey);
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($this->aBotones);
        $oTabla->setDatos($a_valores);
        $oTabla->setDataTable_options_dom($this->dt_op_dom);
        $oTabla->setDataTable_options_buttons($this->dt_op_buttons);
        
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $vista = ConfigGlobal::getVista();
        
        $a_campos = [
            'titulo' => $this->sTitulo,
            'oTabla' => $oTabla,
            'key' => $this->sKey,
            'condicion' => $this->sCondicion,
            //'oHash' => $oHash,
            'server' => $server,
            'filtro' => $this->sFiltro,
            // tabs_show
            'vista' => $vista,
            ];
        
        $oView = new ViewTwig('busquedas/controller');
        echo $oView->renderizar('ver_tabla.html.twig',$a_campos);
    }

    public function tabla_entradas($aCollection) {
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
        
        $oCategoria = new Categoria();
        $a_categorias = $oCategoria->getArrayCategoria();
        $oVisibilidad = new Visibilidad();
        $a_visibilidad = $oVisibilidad->getArrayVisibilidad();
        
        $a_cabeceras=array( array('name'=>ucfirst(_("protocolo origen")),'formatter'=>'clickFormatter'),
                            ucfirst(_("ref.")),
                            _("categoria"),
                            _("visibilidad"),
                            array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
                            ucfirst(_("oficinas")),
                            array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
                            array('name'=>ucfirst(_("contestar antes de")),'class'=>'fecha'),
                            array('name'=>ucfirst(_("fecha entrada")),'class'=>'fecha')
                            );
        
        $oProtOrigen = new Protocolo();
        $a_valores = [];
        $i=0;
        $oPermRegistro = new PermRegistro();
        foreach ($aCollection as $oEntrada) {
        	// mirar permisos visibilidad:...
        	$visibilidad = $oEntrada->getVisibilidad();
        	$visibilidad_txt = empty($a_visibilidad[$visibilidad])? '?' : $a_visibilidad[$visibilidad];
        	
        	$perm_ver_escrito = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
        	if ($perm_ver_escrito < PermRegistro::PERM_VER) {
        		continue;
        	}
            $i++;
            
			$id_entrada=$oEntrada->getId_entrada();
            $f_entrada=$oEntrada->getF_entrada();
            
            $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
            $protocolo = $oProtOrigen->ver_txt();
            
            // referencias
            $json_ref = $oEntrada->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            $referencias = $oArrayProtRef->ListaTxtBr();
            
            // oficinas
            $id_of_ponente =  $oEntrada->getPonente();
            $a_resto_oficinas = $oEntrada->getResto_oficinas();
            $oficinas_txt = '';
            if (!empty($id_of_ponente)) {
            	$of_ponente_txt = empty($a_posibles_oficinas[$id_of_ponente])? '??' : $a_posibles_oficinas[$id_of_ponente];
                $oficinas_txt .= '<span class="text-danger">'.$of_ponente_txt.'</span>';
            }
            foreach ($a_resto_oficinas as $id_oficina) {
                $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
                $oficinas_txt .= empty($a_posibles_oficinas[$id_oficina])? '?' : $a_posibles_oficinas[$id_oficina];
            }
            $oficinas = $oficinas_txt;
            
            $asunto = $oEntrada->getAsuntoDetalle();
            $categoria = $oEntrada->getCategoria();
            $categoria_txt = empty($a_categorias[$categoria])? '' : $a_categorias[$categoria];
            $f_doc = $oEntrada->getF_documento();
            $f_contestar = $oEntrada->getF_contestar();
            
            $a_valores[$i]['sel']="$id_entrada";
            $a_valores[$i][1]=$protocolo;
            $a_valores[$i][2]=$referencias;
            $a_valores[$i][3]=$categoria_txt;
            $a_valores[$i][4]=$visibilidad_txt;
            $a_valores[$i][5]= $asunto;
            $a_valores[$i][6]=$oficinas;
            $a_valores[$i][7]=$f_doc->getFromLocal();
            $a_valores[$i][8]=$f_contestar->getFromLocal();
            $a_valores[$i][9]=$f_entrada->getFromLocal();
        }
        
        $oTabla = new Lista();
        $oTabla->setId_tabla('ver_tabla_'.$this->sKey);
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($this->aBotones);
        $oTabla->setDatos($a_valores);
        $oTabla->setDataTable_options_dom($this->dt_op_dom);
        $oTabla->setDataTable_options_buttons($this->dt_op_buttons);
        
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $vista = ConfigGlobal::getVista();
        
        $a_campos = [
            'titulo' => $this->sTitulo,
            'oTabla' => $oTabla,
            'key' => $this->sKey,
            'condicion' => $this->sCondicion,
            //'oHash' => $oHash,
            'server' => $server,
            'filtro' => $this->sFiltro,
            // tabs_show
            'vista' => $vista,
            ];
        
        $oView = new ViewTwig('busquedas/controller');
        echo $oView->renderizar('ver_tabla.html.twig',$a_campos);
    }

    public function tabla_escritos($cCollection) {
        // salidas
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        
        $oCategoria = new Categoria();
        $a_categorias = $oCategoria->getArrayCategoria();
        $oVisibilidad = new Visibilidad();
        $a_visibilidad = $oVisibilidad->getArrayVisibilidad();
        
        $a_cabeceras=array( array('name'=>ucfirst(_("protocolo origen")),'formatter'=>'clickFormatter'),
                            ucfirst(_("destinos")),
                            ucfirst(_("ref.")),
                            _("categoria"),
                            _("visibilidad"),
                            array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
                            ucfirst(_("cargos")),
                            array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
                            array('name'=>ucfirst(_("fecha aprobación")),'class'=>'fecha'),
                            ucfirst(_("enviado")), // no puede ser class fecha, porque a veces se añade el modo de envio.
                            );
        
        $i=0;
        $a_valores = [];
        $oPermRegistro = new PermRegistro();
        foreach ($cCollection as $oEscrito) {
        	// mirar permisos visibilidad:...
        	$visibilidad = $oEscrito->getVisibilidad();
        	$visibilidad_txt = empty($a_visibilidad[$visibilidad])? '?' : $a_visibilidad[$visibilidad];
        	
        	$perm_ver_escrito = $oPermRegistro->permiso_detalle($oEscrito, 'escrito');
        	if ($perm_ver_escrito < PermRegistro::PERM_VER) {
        		continue;
        	}
            $i++;
            $asunto = $oEscrito->getAsuntoDetalle();
            $anulado = $oEscrito->getAnulado();
            
            // protocolo local
            $protocolo_local = $oEscrito->getProt_local_txt();
            // destinos
            $destino_txt = $oEscrito->getDestinosEscrito();
            
            $id_escrito=$oEscrito->getId_escrito();
            $f_aprobacion=$oEscrito->getF_aprobacion();
            $f_escrito=$oEscrito->getF_escrito();
            $f_salida=$oEscrito->getF_salida();
            
            // referencias
            $json_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_ref,'','');
            $oArrayProtRef->setRef(TRUE);
            $referencias = $oArrayProtRef->ListaTxtBr();
            
            // oficinas
            $id_ponente =  $oEscrito->getCreador();
            $a_resto_oficinas = $oEscrito->getResto_oficinas();
            $oficina_txt = empty($a_posibles_cargos[$id_ponente])? '?' : $a_posibles_cargos[$id_ponente];
            $oficinas_txt = '';
            $oficinas_txt .= '<span class="text-danger">'.$oficina_txt.'</span>';
            foreach ($a_resto_oficinas as $id_oficina) {
                $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
                $oficinas_txt .= empty($a_posibles_cargos[$id_oficina])? '' : $a_posibles_cargos[$id_oficina];
            }
            $oficinas = $oficinas_txt;
            
            if (!empty($anulado)) { $asunto=_("ANULADO")." ($anulado) $asunto"; }
            
            $categoria = $oEscrito->getCategoria();
            if (empty($categoria)) {
                $categoria_txt = _("Sin definir la categoría");
            } elseif (empty($a_categorias[$categoria])) {
                $categoria_txt = _("No se encuentra la categoría");
            } else {
                $categoria_txt = $a_categorias[$categoria];
            }

            $a_valores[$i]['sel']="$id_escrito";
            $a_valores[$i][1]=$protocolo_local;
            $a_valores[$i][2]=$destino_txt;
            $a_valores[$i][3]=$referencias;
            $a_valores[$i][4]=$categoria_txt;
            $a_valores[$i][5]=$visibilidad_txt;
            $a_valores[$i][6]=$asunto;
            $a_valores[$i][7]=$oficinas;
            $a_valores[$i][8]=$f_escrito->getFromLocal();
            $a_valores[$i][9]=$f_aprobacion->getFromLocal();
            $a_valores[$i][10]=$f_salida->getFromLocal();
        }

        $oTabla = new Lista();
        $oTabla->setId_tabla('ver_tabla_'.$this->sKey);
        $oTabla->setCabeceras($a_cabeceras);
        $oTabla->setBotones($this->aBotones);
        $oTabla->setDatos($a_valores);
        $oTabla->setDataTable_options_dom($this->dt_op_dom);
        $oTabla->setDataTable_options_buttons($this->dt_op_buttons);
        
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $a_campos = [
            'titulo' => $this->sTitulo,
            'oTabla' => $oTabla,
            'key' => $this->sKey,
            'condicion' => $this->sCondicion,
            //'oHash' => $oHash,
            'server' => $server,
            'filtro' => $this->sFiltro,
            ];
        
        $oView = new ViewTwig('busquedas/controller');
        echo $oView->renderizar('ver_tabla.html.twig',$a_campos);
    }
    
    // ---------------------------------- botones ----------------------------
    
    public function setBotones($a_botones) {
    	$this->aBotones = $a_botones;
    }
    
    public function setBotonesDefault() {
    	
        switch ($this->sKey) {
            case 'entradas_bypass':
            case 'entradas_ref':
            case 'entradas':
            	if (ConfigGlobal::role_actual() === 'secretaria') {
					$this->aBotones = [
            				[ 'txt' => _('modificar'), 'click' =>"fnjs_modificar_entrada(\"#$this->sKey\")" ],
            				[ 'txt' => _('eliminar'), 'click' =>"fnjs_borrar_entrada(\"#$this->sKey\")" ],
            				[ 'txt' => _('anular'), 'click' =>"fnjs_anular_entrada(\"#$this->sKey\")" ],
            		];
            	}
            	
            	$this->aBotones[] = [ 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det_entrada(\"#$this->sKey\")" ];
            	$this->aBotones[] = [ 'txt' => _('ver'), 'click' =>"fnjs_buscar_ver_entrada(\"#$this->sKey\")" ];
            	$this->aBotones[] = [ 'txt' => _('acción'), 'click' =>"fnjs_buscar_accion_entrada(\"#$this->sKey\")" ];
                break;
            case 'entradas_compartidas':
            	if (ConfigGlobal::role_actual() === 'secretaria') {
            		$this->aBotones = [
            				[ 'txt' => _('modificar'), 'click' =>"fnjs_modificar_entrada(\"#$this->sKey\")" ],
            				[ 'txt' => _('eliminar'), 'click' =>"fnjs_borrar_entrada(\"#$this->sKey\")" ],
            				[ 'txt' => _('anular'), 'click' =>"fnjs_anular_entrada(\"#$this->sKey\")" ],
            		];
            	}
            	$this->aBotones[] = [ 'txt' => _('ver'), 'click' =>"fnjs_buscar_ver_entrada_compartida(\"#$this->sKey\")" ];
            	break;
            case 'escritos_ref':
            case 'escritos':
            	if (ConfigGlobal::role_actual() === 'secretaria') {
            		$this->aBotones = [ ['txt' => _('modificar'), 'click' =>"fnjs_modificar_escrito(\"#$this->sKey\")" ],
            							['txt' => _('eliminar'), 'click' =>"fnjs_borrar_escrito(\"#$this->sKey\")" ],
            						];
            	}
            	
            	$this->aBotones[] = [ 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det_escrito(\"#$this->sKey\")" ];
            	$this->aBotones[] = [ 'txt' => _('ver'), 'click' =>"fnjs_buscar_ver_escrito(\"#$this->sKey\")" ];
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }

    }
    
    
	/**
	 * @return mixed
	 */
	public function getDataTable_options_dom() {
		return $this->dt_op_dom;
	}

	/**
	 * @param mixed $dt_op_dom
	 */
	public function setDataTable_options_dom($dt_op_dom) {
		$this->dt_op_dom = $dt_op_dom;
	}

	/**
	 * @return mixed
	 */
	public function getDataTable_options_buttons() {
		return $this->dt_op_buttons;
	}

	/**
	 * @param mixed $dt_op_buttons
	 */
	public function setDataTable_options_buttons($dt_op_buttons) {
		$this->dt_op_buttons = $dt_op_buttons;
	}

    
}