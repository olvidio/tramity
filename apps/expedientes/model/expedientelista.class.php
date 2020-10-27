<?php
namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramite;
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
    
    /*
     * filtros posibles: 
    'borrador'
    'firmar'
    'reunion'
    'circulando'
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
            case 'borrador':
                $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                //$aWhere['f_ini_circulacion'] = 'x';
                //$aOperador['f_ini_circulacion'] = 'IS NULL';
                //$aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                break;
            case 'firmar':
                $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                //pendientes de mi firma, pero ya circulando
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
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',',$a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo 
                    $aWhere = [];
                }
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
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
                //$aWhere['f_ini_circulacion'] = 'x';
                //$aOperador['f_ini_circulacion'] = 'IS NOT NULL';
                $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                break;
            case 'acabados':
                // marcados por scdl con ok.
                $aWhere['f_aprobacion'] = 'x';
                $aOperador['f_aprobacion'] = 'IS NOT NULL';
                $aWhere['ok'] = 't';
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
        $pagina_nueva = '';
        
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        $oExpediente = new Expediente();
        $a_estados = $oExpediente->getArrayEstado();
        
        switch ($this->filtro) {
            case 'borrador':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
                $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query([]));
                break;
            case 'firmar':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'reunion':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'circulando':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                break;
            case 'acabados':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
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
                
                // negrita para los no visualizados
                $bstrong = FALSE;
                if ($this->filtro == 'firmar') {
                    if (in_array($id_expediente, $this->a_expedientes_nuevos)) {
                        $bstrong = TRUE;
                    }
                }

                $a_cosas = [ 'id_expediente' => $id_expediente,
                            'filtro' => $this->getFiltro(),
                ];
                $link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >ver</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >mod</span>";
                
                $estado = $oExpediente->getEstado();
                $row['estado'] = $a_estados[$estado];
                $row['prioridad'] = $oExpediente->getPrioridad();
                $row['tramite'] = $tramite_txt;
                
                if ($bstrong) {
                    $row['asunto'] = "<strong>".$oExpediente->getAsunto()."</strong>";
                } else {
                    $row['asunto'] = $oExpediente->getAsunto();
                }
                
                $row['entradilla'] = $oExpediente->getEntradilla();
                
                $id_ponente =  $oExpediente->getPonente();
                $row['ponente'] =  $a_posibles_cargos[$id_ponente];
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

        $a_campos = [
            //'id_expediente' => $this->id_expediente,
            //'oHash' => $oHash,
            'a_expedientes' => $a_expedientes,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $this->getFiltro(),
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