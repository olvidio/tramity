<?php

namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorFirma;
use tramites\model\entity\GestorTramite;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\Hash;
use function core\is_true;


class ExpedienteListaOld
{
    /**
     *
     * @var integer
     */
    private int $prioridad_sel = 0;
    /**
     *
     * @var string
     */
    private string $filtro;
    /**
     *
     * @var integer
     */
    private int $id_expediente;
    /**
     *
     * @var array
     */
    private array $aWhere;
    /**
     *
     * @var array
     */
    private array $aOperador;
    /**
     *
     * @var array
     */
    private array $aWhereADD = [];
    /**
     *
     * @var array
     */
    private array $aOperadorADD = [];
    /**
     * @var array
     */
    private array $aCondiciones = [];
    /**
     *
     * @var array
     */
    private array $a_expedientes_espera = [];
    /**
     *
     * @var array
     */
    private array $a_expedientes_nuevos = [];
    /**
     *
     * @var array
     */
    private array $a_exp_aclaracion = [];
    /**
     *
     * @var array
     */
    private array $a_exp_peticion = [];
    /**
     *
     * @var array
     */
    private array $a_exp_respuesta = [];

    /**
     *
     * @var array
     */
    private array $a_exp_reunion_falta_firma = [];

    /**
     *
     * @var array
     */
    private array $a_exp_reunion_falta_mi_firma = [];

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

    public function mostrarTabla(): void
    {
        $this->setCondicion();
        $pagina_nueva = '';

        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        $a_usuarios_oficina = $gesCargos->getArrayUsuariosOficina(ConfigGlobal::role_id_oficina(), TRUE);

        $txt_ver = '';
        $txt_mod = '';
        $col_mod = 0;
        $col_ver = 0;
        $ver_f_ini = TRUE;
        $presentacion = 0;
        $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
        $pagina_accion = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_accion.php';
        switch ($this->filtro) {
            case 'borrador_propio':
            case 'borrador_oficina':
                $a_cosas = ['filtro' => $this->getFiltro(), 'prioridad_sel' => $this->prioridad_sel];
                $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_form.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 6; // añado las etiquetas
                $ver_f_ini = FALSE;
                break;
            case 'firmar':
            case 'circulando':
            case 'permanentes_cl':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 1;
                $txt_ver = _("revisar");
                break;
            case 'fijar_reunion':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/fecha_reunion.php';
                $txt_mod = _("fecha");
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 1;
                $txt_ver = _("revisar");
                break;
            case 'reunion':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 3;
                $txt_ver = _("revisar");
                break;
            case 'seg_reunion':
                // Solo en el caso de secretaria:
                if (ConfigGlobal::role_actual() === 'secretaria') {
                    $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/fecha_reunion.php';
                    $txt_mod = _("fecha");
                    $col_mod = 1;
                } else {
                    $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
                    $col_mod = 0;
                }
                $col_ver = 1;
                $presentacion = 3;
                $txt_ver = _("revisar");
                break;
            case 'distribuir':
            case 'acabados':
            case 'acabados_encargados':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';
                $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 5;
                break;
            case 'archivados':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
                $pagina_ver = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_distribuir.php';
                $col_mod = 0;
                $col_ver = 1;
                $presentacion = 2;
                break;
            case 'copias':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_ver.php';
                $col_mod = 1;
                $col_ver = 1;
                $presentacion = 2;
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_form.php';
        }

        $a_expedientes = [];
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);

            //lista de tramites
            $gesTramites = new GestorTramite();
            $a_tramites = $gesTramites->getArrayAbrevTramites();
            // array visibilidades
            $oVisibilidad = new Visibilidad();
            $a_visibilidad = $oVisibilidad->getArrayVisibilidad();
            $oPermiso = new PermRegistro();
            foreach ($cExpedientes as $oExpediente) {
                $row = [];
                $tramite_txt = '';
                // mirar permisos...
                $visibilidad = $oExpediente->getVisibilidad();
                if (!empty($visibilidad) && !$oPermiso->isVisibleDtor($visibilidad)) {
                    continue;
                }
                $visibilidad_txt = empty($a_visibilidad[$visibilidad]) ? '' : $a_visibilidad[$visibilidad];
                $row['visibilidad'] = $visibilidad_txt;

                $id_expediente = $oExpediente->getId_expediente();
                $row['id_expediente'] = $id_expediente;
                $id_tramite = $oExpediente->getId_tramite();
                if (empty($a_tramites[$id_tramite])) {
                    $asunto = $oExpediente->getAsuntoEstado();
                    echo sprintf(_("No existe el trámite para el expediente: '%s' (id:%s)"), $asunto, $id_expediente);
                } else {
                    $tramite_txt = $a_tramites[$id_tramite];
                }
                $id_ponente = $oExpediente->getPonente();
                $estado = $oExpediente->getEstado();

                // negrita para los no visualizados
                $bstrong = FALSE;
                // marcar los que necesitan aclaración
                $baclaracion = FALSE;
                $bpeticion = FALSE;
                $brespuesta = FALSE;
                if ($this->filtro === 'firmar') {
                    if (in_array($id_expediente, $this->a_expedientes_nuevos, true)) {
                        $bstrong = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_aclaracion, true)) {
                        $baclaracion = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_peticion, true)) {
                        $bpeticion = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_respuesta, true)) {
                        $brespuesta = TRUE;
                    }
                }
                // reunion. faltan firmas:
                $bfalta_mi_firma = FALSE;
                $bfalta_firma = FALSE;
                if ($this->filtro === 'reunion' || $this->filtro === 'seg_reunion') {
                    if (in_array($id_expediente, $this->a_exp_reunion_falta_mi_firma, true)) {
                        $bfalta_mi_firma = TRUE;
                    }
                    if (in_array($id_expediente, $this->a_exp_reunion_falta_firma, true)) {
                        $bfalta_firma = TRUE;
                    }
                }

                $a_cosas_ver = ['id_expediente' => $id_expediente,
                    'filtro' => $this->getFiltro(),
                    'prioridad_sel' => $this->prioridad_sel,
                    'modo' => 'ver',
                ];
                $a_cosas_mod = ['id_expediente' => $id_expediente,
                    'filtro' => $this->getFiltro(),
                    'prioridad_sel' => $this->prioridad_sel,
                    'modo' => 'mod',
                ];
                if ($this->filtro === 'archivados') {
                    $a_cosas = $this->getACondiciones();
                    $a_cosas_ver['condiciones'] = $a_cosas;
                }

                $link_ver = Hash::link($pagina_ver . '?' . http_build_query($a_cosas_ver));
                $link_mod = Hash::link($pagina_mod . '?' . http_build_query($a_cosas_mod));
                $link_accion = Hash::link($pagina_accion . '?' . http_build_query($a_cosas_mod));
                $txt_ver = empty($txt_ver) ? _("ver") : $txt_ver;
                $txt_mod = empty($txt_mod) ? _("mod") : $txt_mod;
                $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_ver');\" >$txt_ver</span>";
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >$txt_mod</span>";
                $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >" . _("acción") . "</span>";

                $row['class_row'] = '';
                if ($bfalta_mi_firma) {
                    $row['class_row'] = 'bg-warning';
                }
                if (ConfigGlobal::role_actual() === 'secretaria' && $bfalta_firma) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($baclaracion || $bpeticion) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($brespuesta) {
                    $row['class_row'] = 'respuesta';
                }
                // color para los rechazados
                if ($estado === Expediente::ESTADO_DILATA) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($estado === Expediente::ESTADO_RECHAZADO || $estado === Expediente::ESTADO_NO) {
                    $row['class_row'] = 'bg-warning';
                }
                if ($estado === Expediente::ESTADO_ESPERA) {
                    $row['class_row'] = 'bg-light';
                }
                if (($estado === Expediente::ESTADO_FIJAR_REUNION) && in_array($id_expediente, $this->a_expedientes_espera, true)) {
                    $row['class_row'] = 'bg-light';
                }
                // Si ya lo han visto todos (y hay alguno marcado):
                if ($estado === Expediente::ESTADO_BORRADOR && $oExpediente->isVistoTodos()) {
                    $row['class_row'] = 'bg-warning';
                }
                // Acabados devueltos por secretaria
                if (($this->filtro === 'acabados_encargados' || $this->filtro === 'acabados') && $oExpediente->isDevueltoAlguno()) {
                    $row['class_row'] = 'bg-warning';
                }

                $prioridad = $oExpediente->getPrioridad();
                $row['prioridad'] = $prioridad;
                $row['tramite'] = $tramite_txt;

                // color prioridad:
                if ($prioridad === Expediente::PRIORIDAD_URGENTE) {
                    $row['class_row'] = 'bg-light text-danger';
                }

                if ($bstrong) {
                    $row['asunto'] = "<strong>" . $oExpediente->getAsuntoEstado() . "</strong>";
                } else {
                    $row['asunto'] = $oExpediente->getAsuntoEstado();
                }

                $row['entradilla'] = $oExpediente->getEntradilla();

                $a_resto_oficinas = $oExpediente->getResto_oficinas();
                $cargo_txt = empty($a_posibles_cargos[$id_ponente]) ? '?' : $a_posibles_cargos[$id_ponente];
                $oficinas_txt = '';
                $oficinas_txt .= '<span class="text-danger">' . $cargo_txt . '</span>';
                foreach ($a_resto_oficinas as $id_oficina) {
                    $oficinas_txt .= empty($oficinas_txt) ? '' : ', ';
                    $oficinas_txt .= empty($a_posibles_cargos[$id_oficina]) ? '?' : $a_posibles_cargos[$id_oficina];
                }
                $row['oficinas'] = $oficinas_txt;
                // nombre encargado (ponente)
                if ($this->filtro === 'acabados_encargados' || $this->filtro === 'acabados') {
                    $nom_encargado = $a_usuarios_oficina[$id_ponente];
                    $row['nom_encargado'] = $nom_encargado;
                }
                // A: contiene antecedentes, E: contiene escritos, P: contiene propuestas
                $row['contenido'] = $oExpediente->getContenido();
                $row['etiquetas'] = $oExpediente->getEtiquetasVisiblesTxt();

                $row['f_ini'] = $oExpediente->getF_ini_circulacion()->getFromLocal();
                $row['f_aprobacion'] = $oExpediente->getF_aprobacion()->getFromLocal();
                $row['f_reunion'] = $oExpediente->getF_reunion()->getFromLocalHora();
                $row['f_contestar'] = $oExpediente->getF_contestar()->getFromLocal();

                // para ordenar. Si no añado id_expediente, sobre escribe.
                if (empty($oExpediente->getF_aprobacion()->getIso())) {
                    if (empty($oExpediente->getF_ini_circulacion()->getIso())) {
                        $num_orden = 'a' . "0000-00-00";
                    } else {
                        $num_orden = 'b' . $oExpediente->getF_ini_circulacion()->getIso();
                    }
                } else {
                    $num_orden = 'c' . $oExpediente->getF_aprobacion()->getIso();
                }
                $num_orden .= $id_expediente;

                $a_expedientes[$num_orden] = $row;
            }
            krsort($a_expedientes, SORT_STRING);
        }

        $url_update = 'apps/expedientes/controller/expediente_update.php';

        $filtro = $this->getFiltro();
        $url_cancel = 'apps/expedientes/controller/expediente_lista.php';
        $pagina_cancel = Hash::link($url_cancel . '?' . http_build_query(['filtro' => $filtro, 'prioridad_sel' => $this->prioridad_sel]));

        $vista = ConfigGlobal::getVista();

        $cabecera_oficina = _("oficinas");
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $cabecera_oficina = _("cargo");
        }

        $a_campos = [
            //'id_expediente' => $this->id_expediente,
            //'oHash' => $oHash,
            'a_expedientes' => $a_expedientes,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'pagina_cancel' => $pagina_cancel,
            'filtro' => $filtro,
            'presentacion' => $presentacion,
            'ver_f_ini' => $ver_f_ini,
            'col_mod' => $col_mod,
            'col_ver' => $col_ver,
            'cabecera_oficina' => $cabecera_oficina,
            // tabs_show
            'vista' => $vista,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('expediente_lista_old.html.twig', $a_campos);
    }

    /**
     *
     */
    private function setCondicion(): void
    {
        $aWhere = [];
        $aOperador = [];
        switch ($this->filtro) {
            case 'borrador_propio':
                if (!empty($this->aWhereADD)) {
                    $aWhere = $this->aWhereADD;
                    if (!empty($this->aOperadorADD)) {
                        $aOperador = $this->aOperadorADD;
                    }
                }
                $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                // solo los propios:
                $aWhere['ponente'] = ConfigGlobal::role_id_cargo();
                break;
            case 'borrador_oficina':
                if (!empty($this->aWhereADD)) {
                    $aWhere = $this->aWhereADD;
                    if (!empty($this->aOperadorADD)) {
                        $aOperador = $this->aOperadorADD;
                    }
                }
                $mi_cargo = ConfigGlobal::role_id_cargo();
                $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
                // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
                if (is_true(ConfigGlobal::soy_dtor())) {
                    $visto = 'todos';
                } else {
                    $visto = 'no_visto';
                }
                $gesExpedientes = new GestorExpediente();
                $a_expedientes = $gesExpedientes->getIdExpedientesPreparar($mi_cargo, $visto);
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',', $a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'firmar':
                // Quito los permanentes_cl (de momento para los ctr)
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR ) {
                    $aWhere['vida'] = Expediente::VIDA_PERMANENTE;
                    $aOperador['vida'] = '!=';
                }
                // añadir las que requieren aclaración.
                if (ConfigGlobal::role_actual() === 'secretaria') {
                    $a_tipos_acabado = [Expediente::ESTADO_NO,
                        Expediente::ESTADO_DILATA,
                        Expediente::ESTADO_RECHAZADO,
                        Expediente::ESTADO_CIRCULANDO,
                    ];
                } else {
                    $a_tipos_acabado = [Expediente::ESTADO_CIRCULANDO,
                        Expediente::ESTADO_ESPERA,
                    ];
                }
                $aWhere['estado'] = implode(',', $a_tipos_acabado);
                $aOperador['estado'] = 'IN';
                //pendientes de mi firma, pero ya circulando
                $aWhereFirma['id_cargo'] = ConfigGlobal::role_id_cargo();
                $aWhereFirma['tipo'] = Firma::TIPO_VOTO;
                $aWhereFirma['valor'] = 'x';
                $aOperadorFirma['valor'] = 'IS NULL';
                $gesFirmas = new GestorFirma();
                $cFirmasNull = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                // Sumar los firmados, pero no OK
                $aWhereFirma['valor'] = Firma::V_VISTO . ',' . Firma::V_ESPERA . ',' . Firma::V_D_ESPERA;
                $aOperadorFirma['valor'] = 'IN';
                $cFirmasVisto = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $cFirmas = array_merge($cFirmasNull, $cFirmasVisto);
                $a_expedientes = [];
                $this->a_expedientes_nuevos = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if ($_SESSION['oConfig']->getAmbito() !== Cargo::AMBITO_CTR ) {
                        // Para los ctr NO. Puede ser que el d no haya firmado (nivel ponente) y
                        // no se debe impedir firmar a otros (nivel oficiales).
                        if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                            continue;
                        }
                    }

                    $a_expedientes[] = $id_expediente;
                    $tipo = $oFirma->getTipo();
                    $valor = $oFirma->getValor();
                    if ($tipo === Firma::TIPO_VOTO && empty($valor)) {
                        $this->a_expedientes_nuevos[] = $id_expediente;
                    }
                }
                //////// mirar los que se ha pedido aclaración para marcarlos en naranja /////////
                $aWhereFirma2 = ['tipo' => Firma::TIPO_ACLARACION,
                    'valor' => Firma::V_A_NUEVA,
                    'observ_creador' => 'x',
                    'id_cargo' => ConfigGlobal::role_id_cargo(),
                ];
                $aOperadorFirma2 = ['observ_creador' => 'IS NULL'];
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
                $aOperadorFirma2 = ['observ_creador' => 'IS NOT NULL'];
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
                $aOperadorFirma = ['observ_creador' => 'IS NULL'];
                // 31.5.2021 Que también el dtor de la oficina pueda responder.
                $gesCargos = new GestorCargo();
                $a_cargos_oficina = $gesCargos->getArrayCargosOficina(ConfigGlobal::role_id_oficina());
                if (ConfigGlobal::soy_dtor()) {
                    $ids_cargos = array_keys($a_cargos_oficina);
                    if (!empty($ids_cargos)) {
                        $aWhereFirma['id_cargo_creador'] = implode(',', $ids_cargos);
                        $aOperadorFirma['id_cargo_creador'] = 'IN';
                    }
                }

                $cFirmas = $gesFirmas->getFirmas($aWhereFirma, $aOperadorFirma);
                $a_exp_aclaracion = [];
                $this->a_exp_aclaracion = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $a_exp_aclaracion[] = $id_expediente;
                    $this->a_exp_aclaracion[] = $id_expediente;
                }
                // sumar los dos: nuevos + aclaraciones.
                $a_exp_suma = array_merge($a_expedientes, $a_exp_aclaracion);
                if (!empty($a_exp_suma)) {
                    $aWhere['id_expediente'] = implode(',', $a_exp_suma);
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
                    'valor' => Firma::V_VISTO . ',' . Firma::V_ESPERA . ',' . Firma::V_D_ESPERA,
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
                    if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                        continue;
                    }

                    if ($oFirma->getValor() === Firma::V_D_ESPERA) {
                        $this->a_expedientes_espera[] = $id_expediente;
                    } else {
                        $this->a_exp_reunion_falta_mi_firma[] = $id_expediente;
                    }
                    $a_expedientes[] = $id_expediente;
                }
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',', $a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                /*
                break;
            case 'seg_reunion':
                $aWhere['estado'] = Expediente::ESTADO_FIJAR_REUNION;
                $aWhere['f_reunion'] = 'x';
                $aOperador['f_reunion'] = 'IS NOT NULL';
                */

                //////// mirar los que falta alguna firma para marcarlos en color /////////
                $gesFirmas = new GestorFirma();
                $this->a_exp_reunion_falta_firma = $gesFirmas->faltaFirmarReunion();

                //que tengan de mi firma, independiente de firmado o no
                $cFirmas = $gesFirmas->getFirmasReunion(ConfigGlobal::role_id_cargo());
                $a_expedientes = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                        continue;
                    }

                    $a_expedientes[] = $id_expediente;
                }
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',', $a_expedientes);
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
                $cFirmas = $gesFirmas->getFirmasReunion(ConfigGlobal::role_id_cargo());
                $a_expedientes = [];
                foreach ($cFirmas as $oFirma) {
                    $id_expediente = $oFirma->getId_expediente();
                    $orden_tramite = $oFirma->getOrden_tramite();
                    // Sólo a partir de que el orden_tramite anterior ya lo hayan firmado todos
                    if (!$gesFirmas->getAnteriorOK($id_expediente, $orden_tramite)) {
                        continue;
                    }

                    $a_expedientes[] = $id_expediente;
                }
                if (!empty($a_expedientes)) {
                    $aWhere['id_expediente'] = implode(',', $a_expedientes);
                    $aOperador['id_expediente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'circulando':
                // Quito los permanentes_cl (de momento para los ctr)
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR ) {
                    $aWhere['vida'] = Expediente::VIDA_PERMANENTE;
                    $aOperador['vida'] = '!=';
                }
                if (ConfigGlobal::role_actual() === 'vcd') {
                    $a_tipos_acabado = [Expediente::ESTADO_CIRCULANDO,
                        Expediente::ESTADO_ESPERA,
                    ];
                    $aWhere['estado'] = implode(',', $a_tipos_acabado);
                    $aOperador['estado'] = 'IN';
                } else {
                    $aWhere['estado'] = Expediente::ESTADO_CIRCULANDO;
                    unset($aOperador['estado']);
                }
                // Si es el director los ve todos, no sólo los pendientes de poner 'visto'.
                // para los centros, todos ven igual que el director
                if (is_true(ConfigGlobal::soy_dtor()) || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR ) {
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
                        $aWhere['ponente'] = implode(',', $a_cargos);
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
                $a_tipos_acabado = [Expediente::ESTADO_ACABADO,
                    Expediente::ESTADO_NO,
                    Expediente::ESTADO_DILATA,
                    Expediente::ESTADO_RECHAZADO,
                ];
                $aWhere['estado'] = implode(',', $a_tipos_acabado);
                $aOperador['estado'] = 'IN';
                // todavía sin marcar por scdl con ok.
                break;
            case 'permanentes_cl':
                $aWhere['vida'] = Expediente::VIDA_PERMANENTE;
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
                    $aWhere['ponente'] = implode(',', $a_cargos);
                    $aOperador['ponente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            case 'acabados':
                // Ahora (16/12/2020) todos los de la oficina si es director.
                // sino los encargados (salto a otro estado)
                // para los centros buscar en ESTADO_ACABADO
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    $aWhere['estado'] = Expediente::ESTADO_ACABADO;
                } else {
                    $aWhere['estado'] = Expediente::ESTADO_ACABADO_SECRETARIA;
                }
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
                    $aWhere['ponente'] = implode(',', $a_cargos);
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
                        $aWhere['ponente'] = implode(',', $a_cargos);
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
                if (!empty($this->aWhereADD)) {
                    $aWhere = $this->aWhereADD;
                    if (!empty($this->aOperadorADD)) {
                        $aOperador = $this->aOperadorADD;
                    }
                }
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
                    $aWhere['ponente'] = implode(',', $a_cargos);
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
                    $aWhere['ponente'] = implode(',', $a_cargos);
                    $aOperador['ponente'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            default:
                $aWhere = [];
                $aOperador = [];
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    /**
     * @return integer
     */
    public function getId_expediente(): int
    {
        return $this->id_expediente;
    }

    /**
     * @param number $id_expediente
     */
    public function setId_expediente($id_expediente): void
    {
        $this->id_expediente = (int)$id_expediente;
    }

    /**
     * @return string
     */
    public function getFiltro(): string
    {
        return $this->filtro;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    /**
     * @return array
     */
    public function getACondiciones(): array
    {
        return $this->aCondiciones;
    }

    /**
     * @param array $aCondicion
     */
    public function setACondiciones($aCondiciones): void
    {
        $this->aCondiciones = $aCondiciones;
    }

    public function getNumero()
    {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesExpedientes = new GestorExpediente();
            $this->aWhere['_ordre'] = 'id_expediente';
            $cExpedientes = $gesExpedientes->getExpedientes($this->aWhere, $this->aOperador);
            $num = count($cExpedientes);
        } else {
            $num = '';
        }
        return $num;
    }

    /**
     * @param string $prioridad_sel
     */
    public function setPrioridad_sel(string $prioridad_sel): void
    {
        $this->prioridad_sel = $prioridad_sel;
    }

    /**
     * @return array
     */
    public function getAWhereADD(): array
    {
        return $this->aWhereADD;
    }

    /**
     * @param array $aWhereADD
     */
    public function setAWhereADD(array $aWhereADD): void
    {
        $this->aWhereADD = $aWhereADD;
    }

    /**
     * @return array
     */
    public function getAOperadorADD(): array
    {
        return $this->aOperadorADD;
    }

    /**
     * @param array $aOperadorADD
     */
    public function setAOperadorADD(array $aOperadorADD): void
    {
        $this->aOperadorADD = $aOperadorADD;
    }


}