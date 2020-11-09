<?php
namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramite;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\Hash;


class ExpedienteLista {
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
     * @var array
     */
    private $a_exp_aclaracion = [];
    /**
     * 
     * @var array
     */
    private $a_exp_peticion = [];
    /**
     * 
     * @var array
     */
    private $a_exp_respuesta = [];
    
    /*
     * filtros posibles: 
    'borrador'
    'firmar'
    'fijar_reunion'
    'reunion'
    'circulando'
    'distribuir'
    'acabados'
    'archivados'
    'copias'
    'entradas'
    'escritos'
    'cr'
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
            case 'borrador_propio':
                $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                break;
            case 'borrador_oficina':
                $mi_cargo = ConfigGlobal::mi_id_cargo();
                $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
                if (is_true(ConfigGlobal::soy_dtor())) {
                    $visto = 'todos';
                } else {
                    $visto = 'no_visto';
                }
                $gesExpedientes = new GestorExpediente();
                $a_expedientes = $gesExpedientes->getIdExpedientesPreparar($mi_cargo,$visto);
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',',$a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'firmar':
                // añadir las que requieren aclaración.
                
                $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                //pendientes de mi firma, pero ya circulando
                $aWhereFirma['id_cargo'] = ConfigGlobal::mi_id_cargo();
                $aWhereFirma['tipo'] = Firma::TIPO_VOTO;
                $aWhereFirma['valor'] = 'x';
                $aOperadorFirma['valor'] = 'IS NULL';
                $gesFirmas = new GestorFirma();
                $cFirmasNull = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                // Sumar los firmados, pero no OK
                $aWhereFirma['valor'] = Firma::V_VISTO .','. Firma::V_A_ESPERA;
                $aOperadorFirma['valor'] = 'IN';
                $cFirmasVisto = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $cFirmas = array_merge($cFirmasNull, $cFirmasVisto);
                $a_expedientes = [];
                $this->a_expedientes_nuevos = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if (!$gesFirmas->getAnteriorOK($id_expediente,$orden_tramite)) {
                        continue;
                    }
                    
                    $a_expedientes[] = $id_expediente;
                    $tipo = $oFirma->getTipo();
                    $valor = $oFirma->getValor();
                    if ($tipo === Firma::TIPO_VOTO && empty($valor)) {
                        $this->a_expedientes_nuevos[] = $id_expediente;
                    }
                }
                //////// mirar los que se ha pedido aclaracion para marcarlos en ambar /////////
                $aWhereFirma2 = ['tipo' => Firma::TIPO_ACLARACION,
                                'valor' => Firma::V_A_NUEVA,
                                'observ_creador' => 'x',
                                'id_cargo' => ConfigGlobal::mi_id_cargo(),
                            ];
                $aOperadorFirma2 = ['observ_creador' => 'IS NULL' ];
                $cFirmas2 = $gesFirmas->getFirmas($aWhereFirma2, $aOperadorFirma2);
                $this->a_exp_peticion = [];
                foreach ($cFirmas2 as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $this->a_exp_peticion[] = $id_expediente;
                }
                //////// mirar los que ya se ha contestado para marcarlos en verde /////////
                $aWhereFirma2 = ['tipo' => Firma::TIPO_ACLARACION,
                                'valor' => Firma::V_A_NUEVA,
                                'observ_creador' => 'x',
                                'id_cargo' => ConfigGlobal::mi_id_cargo(),
                            ];
                $aOperadorFirma2 = ['observ_creador' => 'IS NOT NULL' ];
                $cFirmas2 = $gesFirmas->getFirmas($aWhereFirma2, $aOperadorFirma2);
                $this->a_exp_respuesta = [];
                foreach ($cFirmas2 as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $this->a_exp_respuesta[] = $id_expediente;
                }
                
                //////// añadir las que requieren aclaración. //////////////////////////////
                $aWhereFirma = ['tipo' => Firma::TIPO_ACLARACION,
                                'valor' => Firma::V_A_NUEVA,
                                'observ_creador' => 'x',
                                'id_cargo_creador' => ConfigGlobal::mi_id_cargo(),
                            ];
                $aOperadorFirma = ['observ_creador' => 'IS NULL' ];
                $cFirmas = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $a_exp_aclaracion = [];
                $this->a_exp_aclaracion = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    $a_exp_aclaracion[] = $id_expediente;
                    $this->a_exp_aclaracion[] = $id_expediente;
                }
                // sumar los dos: nuevos + aclaraciones.
                $a_exp_suma = array_merge($a_expedientes, $a_exp_aclaracion);
                if (!empty($a_exp_suma)) {
                    $aWhere['id_expediente'] = implode(',',$a_exp_suma);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo 
                    $aWhere = [];
                }
                break;
            case 'fijar_reunion':
                $aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
                break;
            case 'reunion':
                //pendientes de mi firma, pero ya circulando, y con fecha de reunión
                $aWhereFirma['id_cargo'] = ConfigGlobal::mi_id_cargo();
                $aWhereFirma['tipo'] = Firma::TIPO_VOTO;
                $aWhereFirma['valor'] = Firma::V_VISTO .','. Firma::V_A_ESPERA;
                $aOperadorFirma['valor'] = 'IN';
                $gesFirmas = new GestorFirma();
                $cFirmas = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $a_expedientes = [];
                $this->a_expedientes_nuevos = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $a_expedientes[] = $id_expediente;
                    $tipo = $oFirma->getTipo();
                    $valor = $oFirma->getValor();
                    if ($tipo === Firma::TIPO_VOTO && empty($valor)) {
                        $this->a_expedientes_nuevos[] = $id_expediente;
                    }
                }
                $aWhere['f_ini_circulacion'] = 'x';
                $aOperador['f_ini_circulacion'] = 'IS NOT NULL';
                $aWhere['f_reunion'] = 'x';
                $aOperador['f_reunion'] = 'IS NOT NULL';
                break;
            case 'circulando':
                $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
                if (is_true(ConfigGlobal::soy_dtor())) {
                    // posibles oficiales de la oficina:
                    $oCargo = new Cargo(ConfigGlobal::mi_id_cargo());
                    $id_oficina = $oCargo->getId_oficina();
                    $gesCargos = new GestorCargo();
                    $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                    $a_cargos = [];
                    foreach ($a_cargos_oficina as $id_cargo => $cargo) {
                        $a_cargos[] = $id_cargo;
                    }
                    if (!empty($a_cargos)) {
                        $aWhere['ponente'] = implode(',',$a_cargos);
                        $aOperador['ponente'] = 'IN';
                    } else {
                        // para que no salga nada pongo 
                        $aWhere = [];
                    }
                } else {
                    // solo los propios:
                    $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                }
                break;
            case 'distribuir':
                $aWhere['estado'] = Expediente::ESTADO_ACABADO;
                // todavia sin marcar por scdl con ok.
                $aWhere['ok'] = 'f';
                break;
            case 'acabados':
                $aWhere['estado'] = Expediente::ESTADO_ACABADO;
                // marcados por scdl con ok.
                $aWhere['ok'] = 't';
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                break;
            case 'archivados':
                $aWhere['estado'] = Expediente::ESTADO_TERMINADO;
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
        $pagina_nueva = '';
        
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        $oExpediente = new Expediente();
        $a_estados = $oExpediente->getArrayEstado();
        
        switch ($this->filtro) {
            case 'borrador_propio':
            case 'borrador_oficina':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
                $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query([]));
                break;
            case 'firmar':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'fijar_reunion':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'reunion':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'circulando':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'distribuir':
            case 'acabados':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_distribuir.php';
                break;
            case 'archivados':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'copias':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
        }
        $pagina_ver = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';

        $a_expedientes = [];
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere,$this->aOperador);
                
            //lista de tramites
            $gesTramites = new GestorTramite();
            $a_tramites = $gesTramites->getArrayAbrevTramites();
            foreach ($cExpedientes as $oExpediente) {
                $row = [];
                // mirar permisos...
                
                $id_expediente = $oExpediente->getId_expediente();
                $row['id_expediente'] = $id_expediente;
                $id_tramite = $oExpediente->getId_tramite();
                $tramite_txt = $a_tramites[$id_tramite];
                $id_ponente = $oExpediente->getPonente();
                
                // negrita para los no visualizados
                $bstrong = FALSE;
                // marcar los que necesitan aclaración
                $baclaracion = FALSE;
                $bpeticion = FALSE;
                $brespuesta = FALSE;
                if ($this->filtro == 'firmar') {
                    if (in_array($id_expediente, $this->a_expedientes_nuevos)) {
                        $bstrong = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_aclaracion)) {
                        $baclaracion = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_peticion)) {
                        $bpeticion = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_respuesta)) {
                        $brespuesta = TRUE;
                    }
                }

                $a_cosas = [ 'id_expediente' => $id_expediente,
                            'filtro' => $this->getFiltro(),
                ];
                $link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >"._("ver")."</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >"._("mod")."</span>";
                $row['link_eliminar'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_exp_eliminar('$id_expediente');\" >"._("eliminar")."</span>";
                $row['link_a_borrador'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_exp_a_borrador('$id_expediente');\" >"._("a borrador")."</span>";
                
                if ($baclaracion || $bpeticion) {
                    $row['class_row'] = 'bg-warning';
                } elseif ($brespuesta) {
                    $row['class_row'] = 'bg-success';
                } else {
                    $row['class_row'] = '';
                }
                $estado = $oExpediente->getEstado();
                $row['estado'] = $a_estados[$estado];
                if ($estado == Expediente::ESTADO_BORRADOR) {
                    $row['eliminar'] = 1;
                } else {
                    $row['eliminar'] = 0;
                    if ($id_ponente == ConfigGlobal::mi_id_cargo()) {
                        $row['a_borrador'] = 1;
                    } else {
                        $row['a_borrador'] = 0;
                    }
                }
                $row['prioridad'] = $oExpediente->getPrioridad();
                $row['tramite'] = $tramite_txt;
                
                if ($bstrong) {
                    $row['asunto'] = "<strong>".$oExpediente->getAsunto()."</strong>";
                } else {
                    $row['asunto'] = $oExpediente->getAsunto();
                }
                
                $row['entradilla'] = $oExpediente->getEntradilla();
                
                $row['ponente'] = $a_posibles_cargos[$id_ponente];
                $row['f_ini'] =  $oExpediente->getF_ini_circulacion()->getFromLocal();
                $row['f_aprobacion'] =  $oExpediente->getF_aprobacion()->getFromLocal();
                $row['f_reunion'] =  $oExpediente->getF_reunion()->getFromLocal();
                $row['f_contestar'] =  $oExpediente->getF_contestar()->getFromLocal();
                
                // mirar si tienen escrito
                //$row['f_escrito'] = $oExpediente->getF_documento()->getFromLocal();
                $a_expedientes[] = $row;
            }
        }

        $url_update = 'apps/expedientes/controller/expediente_update.php';
        
        $filtro = $this->getFiltro();
        $url_cancel = 'apps/expedientes/controller/expediente_lista.php';
        $pagina_cancel = Hash::link($url_cancel.'?'.http_build_query(['filtro' => $filtro]));

        $a_campos = [
            //'id_expediente' => $this->id_expediente,
            //'oHash' => $oHash,
            'a_expedientes' => $a_expedientes,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'pagina_cancel' => $pagina_cancel,
            'filtro' => $filtro,
        ];

        $oView = new ViewTwig('expedientes/controller');
        return $oView->renderizar('expediente_lista.html.twig',$a_campos);
    }
    
    public function getNumero() {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere,$this->aOperador);
            $num = count($cExpedientes);
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