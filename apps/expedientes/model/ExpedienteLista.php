<?php

namespace expedientes\model;

use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\domain\entity\Expediente;
use tramites\domain\repositories\TramiteRepository;
use usuarios\domain\entity\Cargo;
use usuarios\domain\PermRegistro;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\Visibilidad;
use web\Hash;


class ExpedienteLista
{
    private int $prioridad_sel = 0;
    private string $filtro;
    private array $cExpedientes;
    private array $condiciones_busqueda;

    private FormatoLista $oFormatoLista;
    private ExpedientesDeColor $oExpedientesDeColor;

    public function __construct(array $cExpedientes, FormatoLista $oFormatoLista, ExpedientesDeColor $oExpedientesDeColor)
    {
        $this->cExpedientes = $cExpedientes;
        $this->oFormatoLista = $oFormatoLista;
        $this->oExpedientesDeColor = $oExpedientesDeColor;
    }


    public function mostrarTabla(): void
    {
        $CargoRepository = new CargoRepository();
        $a_posibles_cargos = $CargoRepository->getArrayCargos();
        $a_usuarios_oficina = $CargoRepository->getArrayUsuariosOficina(ConfigGlobal::role_id_oficina(), TRUE);

        $pagina_accion = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_accion.php';

        $a_expedientes = [];
        if (!empty($this->cExpedientes)) {
            //lista de tramites
            $TramiteRepository = new TramiteRepository();
            $a_tramites = $TramiteRepository->getArrayAbrevTramites();
            // array visibilidades
            $oVisibilidad = new Visibilidad();
            $a_visibilidad = $oVisibilidad->getArrayVisibilidad();
            $oPermiso = new PermRegistro();
            foreach ($this->cExpedientes as $oExpediente) {
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
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpedientesNuevos(), true)) {
                        $bstrong = TRUE;
                    }
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpAclaracion(), true)) {
                        $baclaracion = TRUE;
                    }
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpPeticion(), true)) {
                        $bpeticion = TRUE;
                    }
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpRespuesta(), true)) {
                        $brespuesta = TRUE;
                    }
                }
                // reunion. faltan firmas:
                $bfalta_mi_firma = FALSE;
                $bfalta_firma = FALSE;
                if ($this->filtro === 'reunion' || $this->filtro === 'seg_reunion') {
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpReunionFaltaMiFirma(), true)) {
                        $bfalta_mi_firma = TRUE;
                    }
                    if (in_array($id_expediente, $this->oExpedientesDeColor->getExpReunionFaltaFirma(), true)) {
                        $bfalta_firma = TRUE;
                    }
                }

                $a_cosas_ver = ['id_expediente' => $id_expediente,
                    'filtro' => $this->filtro,
                    'prioridad_sel' => $this->prioridad_sel,
                    'modo' => 'ver',
                ];
                $a_cosas_mod = ['id_expediente' => $id_expediente,
                    'filtro' => $this->filtro,
                    'prioridad_sel' => $this->prioridad_sel,
                    'modo' => 'mod',
                ];
                if ($this->filtro === 'archivados') {
                    $a_cosas_ver['condiciones'] = $this->condiciones_busqueda;
                }

                $link_ver = Hash::link($this->oFormatoLista->getPaginaVer() . '?' . http_build_query($a_cosas_ver));
                $link_mod = Hash::link($this->oFormatoLista->getPaginaMod() . '?' . http_build_query($a_cosas_mod));
                $link_accion = Hash::link($pagina_accion . '?' . http_build_query($a_cosas_mod));
                $txt_ver = empty($this->oFormatoLista->getTxtColumnaVer()) ? _("ver") : $this->oFormatoLista->getTxtColumnaVer();
                $txt_mod = empty($this->oFormatoLista->getTxtColumnaMod()) ? _("mod") : $this->oFormatoLista->getTxtColumnaMod();
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
                if (($estado === Expediente::ESTADO_FIJAR_REUNION) && in_array($id_expediente, $this->oExpedientesDeColor->getExpedientesEspera(), true)) {
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

        $filtro = $this->filtro;
        $url_cancel = 'apps/expedientes/controller/expediente_lista.php';
        $pagina_cancel = Hash::link($url_cancel . '?' . http_build_query(['filtro' => $filtro, 'prioridad_sel' => $this->prioridad_sel]));

        $vista = ConfigGlobal::getVista();

        $titulo = '';
        $cabecera_oficina = _("oficinas");
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $cabecera_oficina = _("cargo");
            switch ($filtro) {
                case 'borrador_propio':
                case 'borrador_oficina':
                    $titulo = _("borrador");
                    break;
                case 'firmar':
                    $titulo = _("para firmar");
                    break;
                case 'circulando':
                    $titulo = _("circulando");
                    break;
                case 'permanentes_cl':
                    $titulo = _("permanentes de cl");
                    break;
                case 'acabados':
                    $titulo = _("acabados");
                    break;
                case 'archivados':
                    $titulo = _("archivados");
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
        }

        $a_campos = [
            'filtro' => $filtro,
            'titulo' => $titulo,
            'a_expedientes' => $a_expedientes,
            'url_update' => $url_update,
            'pagina_nueva' => $this->oFormatoLista->getPaginaNueva(),
            'pagina_cancel' => $pagina_cancel,
            'presentacion' => $this->oFormatoLista->getPresentacion(),
            'is_columna_f_ini_visible' => $this->oFormatoLista->isColumnaFIniVisible(),
            'is_columna_mod_visible' => $this->oFormatoLista->isColumnaModVisible(),
            'is_columna_ver_visible' => $this->oFormatoLista->isColumnaVerVisible(),
            'cabecera_oficina' => $cabecera_oficina,
            // tabs_show
            'vista' => $vista,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('expediente_lista.html.twig', $a_campos);
    }


    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    public function setPrioridad_sel(string $prioridad_sel): void
    {
        $this->prioridad_sel = $prioridad_sel;
    }

    /**
     * @param array $condiciones_busqueda
     */
    public function setCondicionesBusqueda(array $condiciones_busqueda): void
    {
        $this->condiciones_busqueda = $condiciones_busqueda;
    }

}