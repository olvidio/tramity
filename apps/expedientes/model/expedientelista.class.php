<?php
namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use entradas\model\Entrada;
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
    private $a_expedientes_espera = [];
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
    
    /**
     * 
     * @var array
     */
    private $a_exp_reunion_falta_firma = [];
    
    /*
     * filtros posibles: 
    'borrador'
    'firmar'
    'fijar_reunion'
    'seg_reunion'
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
                $aWhere['ponente'] = ConfigGlobal::role_id_cargo();
                break;
            case 'borrador_oficina':
                $mi_cargo = ConfigGlobal::role_id_cargo();
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
                
                if (ConfigGlobal::role_actual() === 'secretaria') {
                    $a_tipos_acabado = [ Expediente::ESTADO_NO,
                                         Expediente::ESTADO_DILATA,
                                         Expediente::ESTADO_RECHAZADO,
                                         Expediente::ESTADO_CIRCULANDO,
                                        ];
                    $aWhere['estado'] = implode(',',$a_tipos_acabado);
                    $aOperador['estado'] = 'IN';
                } else {
                    $a_tipos_acabado = [ Expediente::ESTADO_CIRCULANDO,
                                         Expediente::ESTADO_ESPERA,
                                        ];
                    $aWhere['estado'] = implode(',',$a_tipos_acabado);
                    $aOperador['estado'] = 'IN';
                }
                //pendientes de mi firma, pero ya circulando
                $aWhereFirma['id_cargo'] = ConfigGlobal::role_id_cargo();
                $aWhereFirma['tipo'] = Firma::TIPO_VOTO;
                $aWhereFirma['valor'] = 'x';
                $aOperadorFirma['valor'] = 'IS NULL';
                $gesFirmas = new GestorFirma();
                $cFirmasNull = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                // Sumar los firmados, pero no OK
                $aWhereFirma['valor'] = Firma::V_VISTO .','. Firma::V_ESPERA .','. Firma::V_D_ESPERA;
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
                                'id_cargo' => ConfigGlobal::role_id_cargo(),
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
                                'id_cargo' => ConfigGlobal::role_id_cargo(),
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
                                'id_cargo_creador' => ConfigGlobal::role_id_cargo(),
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
                $aWhere['f_reunion'] = 'x';
                $aOperador['f_reunion'] = 'IS NULL';
                break;
            case 'reunion':
                //pendientes de mi firma, con fecha de reunión
                $aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
                $aWhere['f_reunion'] = 'x';
                $aOperador['f_reunion'] = 'IS NOT NULL';
                
                //pendientes de mi firma
                $aWhereFirma = [
                            'id_cargo' => ConfigGlobal::role_id_cargo(),
                            'tipo' => Firma::TIPO_VOTO,
                            'valor' => 'x',
                            ];
                $aOperadorFirma = [
                            'valor' => 'IS NULL',
                            ];
                $gesFirmas = new GestorFirma();
                $cFirmasNull = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                // Sumar los firmados, pero no OK
                $aWhereFirma = [
                            'id_cargo' => ConfigGlobal::role_id_cargo(),
                            'tipo' => Firma::TIPO_VOTO,
                            'valor' =>  Firma::V_VISTO .','. Firma::V_ESPERA .','. Firma::V_D_ESPERA,
                            ];
                $aOperadorFirma = [
                            'valor' => 'IN',
                            ];
                $cFirmasVisto = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $cFirmas = array_merge($cFirmasNull, $cFirmasVisto);
                $a_expedientes = [];
                $this->a_expedientes_espera = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if (!$gesFirmas->getAnteriorOK($id_expediente,$orden_tramite)) {
                        continue;
                    }
                    
                    if ($oFirma->getValor() == Firma::V_D_ESPERA) {
                        $this->a_expedientes_espera[] = $id_expediente;
                    }
                    $a_expedientes[] = $id_expediente;
                }
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',',$a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'seg_reunion':
                $aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
                $aWhere['f_reunion'] = 'x';
                $aOperador['f_reunion'] = 'IS NOT NULL';
                
                //////// mirar los que falta alguna firma para marcarlos en color /////////
                $gesFirmas = new GestorFirma();
                $this->a_exp_reunion_falta_firma = $gesFirmas->faltaFirmarReunion();
                
                //que tengan de mi firma, independiente de firmado o no
                $aWhereFirma = [
                            'id_cargo' => ConfigGlobal::role_id_cargo(),
                            'tipo' => Firma::TIPO_VOTO,
                            ];
                $cFirmas = $gesFirmas->getFirmas($aWhereFirma);
                $a_expedientes = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if (!$gesFirmas->getAnteriorOK($id_expediente,$orden_tramite)) {
                        continue;
                    }
                    
                    $a_expedientes[] = $id_expediente;
                }
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',',$a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'circulando':
                if (ConfigGlobal::mi_usuario_cargo() === 'vcd') {
                    $a_tipos_acabado = [ Expediente::ESTADO_CIRCULANDO,
                                         Expediente::ESTADO_ESPERA,
                                        ];
                    $aWhere['estado'] = implode(',',$a_tipos_acabado);
                    $aOperador['estado'] = 'IN';
                } else {
                    $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                    unset($aOperador['estado']);
                }
                // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
                if (is_true(ConfigGlobal::soy_dtor())) {
                    // posibles oficiales de la oficina:
                    $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
                    $id_oficina = $oCargo->getId_oficina();
                    $gesCargos = new GestorCargo();
                    $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                    $a_cargos = [];
                    foreach (array_keys($a_cargos_oficina) as $id_cargo) {
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
                    $aWhere['ponente'] = ConfigGlobal::role_id_cargo();
                }
                break;
            case 'distribuir':
                $a_tipos_acabado = [ Expediente::ESTADO_ACABADO,
                                     Expediente::ESTADO_NO,
                                     Expediente::ESTADO_DILATA,
                                     Expediente::ESTADO_RECHAZADO,
                                    ];
                $aWhere['estado'] = implode(',',$a_tipos_acabado);
                $aOperador['estado'] = 'IN';
                // todavia sin marcar por scdl con ok.
                break;
            case 'acabados':
                // Ahora (16/12/2020) todos los de la oficina si es dtor.
                // sino los encargados (salto a otro estado)
                $aWhere['estado'] = Expediente::ESTADO_ACABADO_SECRETARIA;
                // solo los de la oficina:
                // posibles oficiales de la oficina:
                $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
                $id_oficina = $oCargo->getId_oficina();
                $gesCargos = new GestorCargo();
                $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                $a_cargos = [];
                foreach (array_keys($a_cargos_oficina) as $id_cargo) {
                    $a_cargos[] = $id_cargo;
                }
                if (!empty($a_cargos)) {
                    $aWhere['ponente'] = implode(',',$a_cargos);
                    $aOperador['ponente'] = 'IN';
                } else {
                    // para que no salga nada pongo 
                    $aWhere = [];
                }
                break;
            case 'acabados_encargados':
                $aWhere['estado'] = Expediente::ESTADO_ACABADO_ENCARGADO;
                // Si es el director los ve todos
                if (is_true(ConfigGlobal::soy_dtor())) {
                    // posibles oficiales de la oficina:
                    $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
                    $id_oficina = $oCargo->getId_oficina();
                    $gesCargos = new GestorCargo();
                    $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                    $a_cargos = [];
                    foreach (array_keys($a_cargos_oficina) as $id_cargo) {
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
                    $aWhere['ponente'] = ConfigGlobal::role_id_cargo();
                }
                break;
            case 'archivados':
                $aWhere['estado'] = Expediente::ESTADO_ARCHIVADO;
                // solo los de la oficina:
                // posibles oficiales de la oficina:
                $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
                $id_oficina = $oCargo->getId_oficina();
                $gesCargos = new GestorCargo();
                $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                $a_cargos = [];
                foreach (array_keys($a_cargos_oficina) as $id_cargo) {
                    $a_cargos[] = $id_cargo;
                }
                if (!empty($a_cargos)) {
                    $aWhere['ponente'] = implode(',',$a_cargos);
                    $aOperador['ponente'] = 'IN';
                } else {
                    // para que no salga nada pongo 
                    $aWhere = [];
                }
                break;
            case 'copias':
                $aWhere['estado'] = Expediente::ESTADO_COPIAS;
                // solo los de la oficina:
                // posibles oficiales de la oficina:
                $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
                $id_oficina = $oCargo->getId_oficina();
                $gesCargos = new GestorCargo();
                $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
                $a_cargos = [];
                foreach (array_keys($a_cargos_oficina) as $id_cargo) {
                    $a_cargos[] = $id_cargo;
                }
                if (!empty($a_cargos)) {
                    $aWhere['ponente'] = implode(',',$a_cargos);
                    $aOperador['ponente'] = 'IN';
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
        
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        $oExpediente = new Expediente();
        $a_estados = $oExpediente->getArrayEstado();
        
        $txt_ver = '';
        $txt_mod = '';
        $col_mod = 0;
        $col_ver = 0;
        $presentacion = 0;
        $pagina_ver = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
        $pagina_accion = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_accion.php';
        switch ($this->filtro) {
            case 'borrador_propio':
            case 'borrador_oficina':
                $a_cosas = [ 'filtro' => $this->getFiltro() ];
                $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?'.http_build_query($a_cosas));
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 1;
                break;
            case 'firmar':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 1;
                $txt_ver = _("revisar");
                break;
            case 'fijar_reunion':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/fecha_reunion.php';
                $txt_mod = _("fecha");
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 1;
                $txt_ver = _("revisar");
                break;
            case 'reunion':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 3;
                $txt_ver = _("revisar");
                break;
            case 'seg_reunion':
                // Solo en el caso de secretaria:
                if (ConfigGlobal::role_actual() === 'secretaria') {
                    $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/fecha_reunion.php';
                    $txt_mod = _("fecha");
                    $col_mod = 1;
                } else {
                    $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                    $col_mod = 0;
                }
                $col_ver = 1;
                $presentacion = 3;
                $txt_ver = _("revisar");
                break;
            case 'circulando':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 1;
                $txt_ver = _("revisar");
                break;
            case 'distribuir':
            case 'acabados':
            case 'acabados_encargados':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_distribuir.php';
                $pagina_ver = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_distribuir.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 4;
                break;
            case 'archivados':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                $pagina_ver = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_distribuir.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 2;
                break;
            case 'copias':
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 2;
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb().'/apps/expedientes/controller/expediente_form.php';
        }

        $a_expedientes = [];
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere,$this->aOperador);
                
            //lista de tramites
            $gesTramites = new GestorTramite();
            $a_tramites = $gesTramites->getArrayAbrevTramites();
            // array visibilidades
            $oEntrada = new Entrada();
            $a_visibilidad = $oEntrada->getArrayVisibilidad();
            foreach ($cExpedientes as $oExpediente) {
                $row = [];
                // mirar permisos...
                
                $id_expediente = $oExpediente->getId_expediente();
                $row['id_expediente'] = $id_expediente;
                $id_tramite = $oExpediente->getId_tramite();
                $tramite_txt = $a_tramites[$id_tramite];
                $id_ponente = $oExpediente->getPonente();
                $estado = $oExpediente->getEstado();
                
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
                // reunion. faltan firmas:
                $bfalta_firma = FALSE;
                if ($this->filtro == 'seg_reunion') {
                    if (in_array($id_expediente, $this->a_exp_reunion_falta_firma)) {
                        $bfalta_firma = TRUE;
                    }
                }

                $a_cosas_ver = [ 'id_expediente' => $id_expediente,
                            'filtro' => $this->getFiltro(),
                            'modo' => 'ver',
                            ];
                $a_cosas_mod = [ 'id_expediente' => $id_expediente,
                            'filtro' => $this->getFiltro(),
                            'modo' => 'mod',
                            ];
                $link_ver = Hash::link($pagina_ver.'?'.http_build_query($a_cosas_ver));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas_mod));
                $link_accion = Hash::link($pagina_accion.'?'.http_build_query($a_cosas_mod));
                $txt_ver = empty($txt_ver)? _("ver") : $txt_ver;
                $txt_mod = empty($txt_mod)? _("mod") : $txt_mod;
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >$txt_ver</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >$txt_mod</span>";
                $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >"._("acción")."</span>";
                
                $row['class_row'] = '';
                if ($bfalta_firma) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($baclaracion || $bpeticion) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($brespuesta) {
                    $row['class_row'] = 'bg-success';
                }
                // color para los rechazados
                if ($estado == Expediente::ESTADO_DILATA) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($estado == Expediente::ESTADO_RECHAZADO) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($estado == Expediente::ESTADO_ESPERA) {
                    $row['class_row'] = 'bg-light';
                }
                if ($estado == Expediente::ESTADO_FIJAR_REUNION) {
                    if (in_array($id_expediente, $this->a_expedientes_espera)) {
                        $row['class_row'] = 'bg-light';
                    }
                }
                
                $row['estado'] = $a_estados[$estado];
                $row['prioridad'] = $oExpediente->getPrioridad();
                $row['tramite'] = $tramite_txt;

                $visibilidad = $oExpediente->getVisibilidad();
                $visibilidad_txt = empty($a_visibilidad[$visibilidad])? '' : $a_visibilidad[$visibilidad];
                $row['visibilidad'] = $visibilidad_txt;

                
                if ($bstrong) {
                    $row['asunto'] = "<strong>".$oExpediente->getAsuntoEstado()."</strong>";
                } else {
                    $row['asunto'] = $oExpediente->getAsuntoEstado();
                }
                
                $row['entradilla'] = $oExpediente->getEntradilla();
                
                $a_resto_oficinas = $oExpediente->getResto_oficinas();
                $oficinas_txt = '';
                $oficinas_txt .= '<span class="text-danger">'.$a_posibles_cargos[$id_ponente].'</span>';
                foreach ($a_resto_oficinas as $id_oficina) {
                    $oficinas_txt .= empty($oficinas_txt)? '' : ', ';
                    $oficinas_txt .= $a_posibles_cargos[$id_oficina];
                }
                $row['oficinas'] = $oficinas_txt;
                // A: contiene antecedentes, E: contiene escritos, P: contiene propuestas
                $row['contenido'] = $oExpediente->getContenido();
                $row['etiquetas'] = $oExpediente->getEtiquetasVisiblesTxt();

                $row['f_ini'] =  $oExpediente->getF_ini_circulacion()->getFromLocal();
                $row['f_aprobacion'] =  $oExpediente->getF_aprobacion()->getFromLocal();
                $row['f_reunion'] =  $oExpediente->getF_reunion()->getFromLocalHora();
                $row['f_contestar'] =  $oExpediente->getF_contestar()->getFromLocal();
                
                $row['col_mod'] = $col_mod;
                $row['col_ver'] = $col_ver;
                
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
            'presentacion' => $presentacion,
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