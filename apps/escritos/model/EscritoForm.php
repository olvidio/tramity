<?php

namespace escritos\model;

/**
 * Confierto la pagina inicial en una clase para poderla ejecutar desde dos paginas:
 *  - escrito_form.php
 *  - escrit_form_entrada.php
 */

use core\ConfigGlobal;
use core\ViewTwig;
use DateInterval;
use documentos\model\Documento;
use entradas\model\Entrada;
use expedientes\model\Expediente;
use lugares\model\entity\GestorGrupo;
use lugares\model\entity\GestorLugar;
use stdClass;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\DateTimeLocal;
use web\Desplegable;
use web\DesplegableArray;
use web\Hash;
use web\ProtocoloArray;

class EscritoForm
{

    private int $Q_id_expediente;
    private int $Q_id_escrito;
    private int $Q_accion;
    private string $Q_filtro;
    private string $Q_modo;
    private string $Q_volver_a;


    /**
     *
     * @var int
     */
    private int $id_entrada;

    /**
     *
     * @var string
     */
    private string $str_condicion = '';

    public function __construct(int $Q_id_expediente, int $Q_id_escrito, int $Q_accion, string $Q_filtro, string $Q_modo, string $Q_volver_a = '')
    {

        $this->Q_id_expediente = $Q_id_expediente;
        $this->Q_id_escrito = $Q_id_escrito;
        $this->Q_accion = $Q_accion;
        $this->Q_filtro = $Q_filtro;
        $this->Q_modo = $Q_modo;
        $this->Q_volver_a = $Q_volver_a;
    }

    public function render()
    {
        $post_max_size = $_SESSION['oConfig']->getMax_filesize_en_kilobytes();

        $gesLugares = new GestorLugar();
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $a_posibles_lugares = $gesLugares->getArrayBusquedas();
            $a_posibles_lugares_ref = $gesLugares->getArrayBusquedas();

            $gesGrupo = new GestorGrupo();
            $a_posibles_grupos = $gesGrupo->getArrayGrupos();
            $json_prot_dst = [];
        } else {
            $a_posibles_grupos = [];

            $sigla_local = $_SESSION['oConfig']->getSigla();
            $id_sigla_local = $gesLugares->getId_sigla_local();

            $id_sup = $gesLugares->getSigla_superior($sigla_local, TRUE);
            $sigla_sup = $gesLugares->getSigla_superior($sigla_local);

            $id_sup2 = $gesLugares->getSigla_superior($sigla_sup, TRUE);
            $sigla_sup2 = $gesLugares->getSigla_superior($sigla_sup);

            $a_posibles_lugares = [$id_sup => $sigla_sup];

            $oJSON = new stdClass;

            $oJSON->id_lugar = (int)$id_sup;
            $oJSON->any = date('y');
            $oJSON->num = '';
            $oJSON->mas = '';
            $json_prot_dst[0] = $oJSON;

            $a_posibles_lugares_ref = [
                $id_sigla_local => $sigla_local,
                $id_sup => $sigla_sup,
                $id_sup2 => $sigla_sup2,
            ];
        }


        $txt_option_cargos = '';
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        foreach ($a_posibles_cargos as $id_cargo => $cargo) {
            $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
        }

        $estado = 0;
        $visibilidad = 0;
        $visibilidad_dst = Visibilidad::V_CTR_TODOS;
        if (!empty($this->Q_id_expediente)) {
            $oExpediente = new Expediente($this->Q_id_expediente);
            $visibilidad = $oExpediente->getVisibilidad();
            $estado = $oExpediente->getEstado();
        }

        $oEscrito = new Escrito($this->Q_id_escrito);
        // categoría
        $oCategoria = new Categoria();
        $aOpcionesVisibilidad = $oCategoria->getArrayCategoria();
        $oDesplCategoria = new Desplegable();
        $oDesplCategoria->setNombre('categoria');
        $oDesplCategoria->setOpciones($aOpcionesVisibilidad);
        $oDesplCategoria->setTabIndex(80);


        $chk_grupo_dst = '';
        $descripcion = '';
        $comentario = '';
        $anulado_txt = '';

        // visibilidad
        $oVisibilidad = new Visibilidad();
        $aOpcionesVisibilidad = $oVisibilidad->getArrayVisibilidad(TRUE);
        $oDesplVisibilidad = new Desplegable();
        $oDesplVisibilidad->setNombre('visibilidad');
        $oDesplVisibilidad->setOpciones($aOpcionesVisibilidad);
        $oDesplVisibilidad->setOpcion_sel($visibilidad);

        $aOpcionesVisibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();
        $oDesplVisibilidad_dst = new Desplegable();
        $oDesplVisibilidad_dst->setNombre('visibilidad_dst');
        $oDesplVisibilidad_dst->setOpciones($aOpcionesVisibilidad_dst);
        $oDesplVisibilidad_dst->setOpcion_sel($visibilidad_dst);

        // plazo para contestar al enviar.
        $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
        $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
        $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
        $error_fecha = $_SESSION['oConfig']->getPlazoError();
        // Plazo
        $aOpcionesPlazo = [
            'hoy' => ucfirst(_("no")),
            'normal' => ucfirst(sprintf(_("en %s días"), $plazo_normal)),
            'rápido' => ucfirst(sprintf(_("en %s días"), $plazo_rapido)),
            'urgente' => ucfirst(sprintf(_("en %s días"), $plazo_urgente)),
            'fecha' => ucfirst(_("el día")),
        ];
        $oDesplPlazo = new Desplegable();
        $oDesplPlazo->setNombre('plazo');
        $oDesplPlazo->setOpciones($aOpcionesPlazo);
        $oDesplPlazo->setAction("fnjs_comprobar_plazo('select')");

        if (!empty($this->Q_id_escrito)) {
            // destinos individuales
            $json_prot_dst = $oEscrito->getJson_prot_destino(TRUE);
            $oArrayProtDestino = new ProtocoloArray($json_prot_dst, $a_posibles_lugares, 'destinos');
            $oArrayProtDestino->setBlanco('t');
            $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');
            // si hay grupos, tienen preferencia
            $a_grupos = $oEscrito->getId_grupos();
            if (!empty($a_grupos)) {
                $chk_grupo_dst = 'checked';
            } else {
                // puede ser un destino personalizado:
                $destinos = $oEscrito->getDestinos();
                if (!empty($destinos)) {
                    $a_posibles_grupos['custom'] = _("personalizado");
                    $a_grupos = 'custom';
                    $chk_grupo_dst = 'checked';
                    $descripcion = $oEscrito->getDescripcion();
                }
            }
            $oArrayDesplGrupo = new DesplegableArray($a_grupos, $a_posibles_grupos, 'grupos');
            $oArrayDesplGrupo->setBlanco('t');
            $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');

            $json_prot_ref = $oEscrito->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($json_prot_ref, $a_posibles_lugares_ref, 'referencias');
            $oArrayProtRef->setBlanco('t');
            $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

            $asunto = $oEscrito->getAsunto();
            $anulado = $oEscrito->getAnulado();
            if ($anulado === TRUE) {
                $anulado_txt = _("ANULADO");
            }
            $detalle = $oEscrito->getDetalle();

            $id_ponente = $oEscrito->getCreador();
            $categoria = $oEscrito->getCategoria();
            $oDesplCategoria->setOpcion_sel($categoria);
            $visibilidad = $oEscrito->getVisibilidad() ?? Visibilidad::V_PERSONAL;
            $oDesplVisibilidad->setOpcion_sel($visibilidad);
            if (!empty($oEscrito->getVisibilidad_dst())) {
                $visibilidad_dst = $oEscrito->getVisibilidad_dst();
                $oDesplVisibilidad_dst->setOpcion_sel($visibilidad_dst);
            }

            // Adjuntos Upload
            $a_adjuntos = $oEscrito->getArrayIdAdjuntos(Documento::DOC_UPLOAD);
            $preview = [];
            $config = [];
            foreach ($a_adjuntos as $id_item => $nom) {
                $preview[] = "'$nom'";
                $config[] = [
                    'key' => $id_item,
                    'caption' => $nom,
                    'url' => 'apps/escritos/controller/adjunto_delete.php', // server api to delete the file based on key
                ];
            }
            $initialPreview = implode(',', $preview);
            $json_config = json_encode($config);

            $f_contestar = $oEscrito->getF_contestar()->getFromLocal();
            if (!empty($f_contestar)) {
                $oDesplPlazo->setOpcion_sel('fecha');
            }
            // mirar si tienen escrito
            $f_escrito = $oEscrito->getF_escrito()->getFromLocal();
            $tipo_doc = $oEscrito->getTipo_doc();

            $titulo = _("modificar");
            switch ($this->Q_accion) {
                case Escrito::ACCION_ESCRITO:
                    $titulo = _("modificar escrito");
                    break;
                case Escrito::ACCION_PROPUESTA:
                    $titulo = _("modificar propuesta");
                    break;
                case Escrito::ACCION_PLANTILLA:
                    $titulo = _("modificar plantilla");
                    break;
                default:
                    $titulo = _("modificar entrada");
            }

            $oPermisoregistro = new PermRegistro();
            $perm_asunto = $oPermisoregistro->permiso_detalle($oEscrito, 'asunto');
            $perm_detalle = $oPermisoregistro->permiso_detalle($oEscrito, 'detalle');
            $asunto_readonly = ($perm_asunto < PermRegistro::PERM_MODIFICAR) ? 'readonly' : '';
            $detalle_readonly = ($perm_detalle < PermRegistro::PERM_MODIFICAR) ? 'readonly' : '';

            $perm_cambio_visibilidad = $oPermisoregistro->permiso_detalle($oEscrito, 'cambio');
            if ($perm_cambio_visibilidad < PermRegistro::PERM_MODIFICAR) {
                $oDesplVisibilidad->setDisabled(TRUE);
            }

            $comentario = $oEscrito->getComentarios();
        } else {
            // Puedo venir como respuesta a una entrada. Hay que copiar algunos datos de la entrada
            if (!empty($this->id_entrada)) {
                $this->Q_accion = Escrito::ACCION_ESCRITO;
                $oEntrada = new Entrada($this->id_entrada);
                $asunto = $oEntrada->getAsunto();
                $detalle = $oEntrada->getDetalle();
                // ProtocoloArray espera un array.
                $json_prot_dst = []; // inicializar variable. Puede tener cosas.
                $json_prot_dst[] = $oEntrada->getJson_prot_origen();
                $oArrayProtDestino = new ProtocoloArray($json_prot_dst, $a_posibles_lugares, 'destinos');
                $oArrayProtDestino->setBlanco('t');
                $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');

                $visibilidad = empty($oEntrada->getVisibilidad()) ? $oExpediente->getVisibilidad() : $oEntrada->getVisibilidad();
                $oDesplVisibilidad->setOpcion_sel($visibilidad);

                $f_contestar = '';
                $f_escrito = '';
                $initialPreview = '';
                $json_config = '{}';
                $tipo_doc = '';
            } else {
                // Valores por defecto: los del expediente:
                if (!empty($this->Q_id_expediente)) {
                    $oExpediente = new Expediente($this->Q_id_expediente);
                    $asunto = $oExpediente->getAsunto();
                    $visibilidad = $oExpediente->getVisibilidad();
                    $oDesplVisibilidad->setOpcion_sel($visibilidad);
                } else {
                    $asunto = '';
                    $visibilidad = '';
                    $oDesplVisibilidad->setOpcion_sel($visibilidad);
                }
                $detalle = '';
                $f_contestar = '';
                $f_escrito = '';
                $initialPreview = '';
                $json_config = '{}';
                $tipo_doc = '';

                $oArrayProtDestino = new ProtocoloArray($json_prot_dst, $a_posibles_lugares, 'destinos');
                $oArrayProtDestino->setBlanco('t');
                $oArrayProtDestino->setAccionConjunto('fnjs_mas_destinos()');
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                    || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
                    $oArrayProtDestino->setAdd(FALSE);
                }

            }
            $titulo = _("nuevo");
            switch ($this->Q_accion) {
                case Escrito::ACCION_ESCRITO:
                    $titulo = _("nuevo escrito");
                    break;
                case Escrito::ACCION_PROPUESTA:
                    $titulo = _("nueva propuesta");
                    break;
                case Escrito::ACCION_PLANTILLA:
                    $titulo = _("nueva plantilla");
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }

            $oArrayDesplGrupo = new DesplegableArray('', $a_posibles_grupos, 'grupos');
            $oArrayDesplGrupo->setBlanco('t');
            $oArrayDesplGrupo->setAccionConjunto('fnjs_mas_grupos()');

            $oArrayProtRef = new ProtocoloArray('', $a_posibles_lugares_ref, 'referencias');
            $oArrayProtRef->setBlanco('t');
            $oArrayProtRef->setAccionConjunto('fnjs_mas_referencias()');

            $id_ponente = ConfigGlobal::role_id_cargo();

            $asunto_readonly = '';
            $detalle_readonly = '';
        }

        // Adjuntos Etherpad
        $lista_adjuntos_etherpad = $oEscrito->getHtmlAdjuntos();

        $url_update = 'apps/escritos/controller/escrito_update.php';
        $a_cosas = ['id_expediente' => $this->Q_id_expediente,
            'filtro' => $this->Q_filtro,
            'modo' => $this->Q_modo,
            'volver_a' => $this->Q_volver_a,
            'vista' => ConfigGlobal::getVista(),
        ];

        $explotar = FALSE;
        if ($estado === Expediente::ESTADO_ACABADO_ENCARGADO
            || ($estado === Expediente::ESTADO_ACABADO_SECRETARIA)) {
            // Posibilidad de explotar en varios escritos, uno para cada ctr destino.
            $ctr_dest = $oArrayProtDestino->getArray_sel();
            if (count($ctr_dest) > 1 || !empty($a_grupos)) {
                $explotar = TRUE;
            }
        }

        $ver_plazo = TRUE;
        $devolver = FALSE;
        switch ($this->Q_filtro) {
            case 'acabados':
            case 'acabados_encargados':
            case 'distribuir':
                if ($this->Q_volver_a === 'escrito_lista_correo') {
                    $pagina_cancel = Hash::link('apps/escritos/controller/escrito_lista_correo.php?' . http_build_query($a_cosas));
                } else {
                    $pagina_cancel = Hash::link('apps/expedientes/controller/expediente_distribuir.php?' . http_build_query($a_cosas));
                }
                break;
            case 'enviar':
                $devolver = TRUE;
                $pagina_cancel = Hash::link('apps/escritos/controller/escrito_lista.php?' . http_build_query($a_cosas));
                break;
            case 'en_buscar':
                $a_condicion = [];
                parse_str($this->str_condicion, $a_condicion);
                $a_condicion['filtro'] = $this->Q_filtro;
                $pagina_cancel = Hash::link('apps/busquedas/controller/buscar_escrito.php?' . http_build_query($a_condicion));
                break;
            default:
                if ($this->Q_volver_a === 'expediente_ver') {
                    $pagina_cancel = Hash::link('apps/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));
                } else {
                    $pagina_cancel = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
                }
        }

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR && $this->Q_filtro === 'circulando') {
            $pagina_cancel = Hash::link('apps/expedientes/controller/expediente_ver.php?' . http_build_query($a_cosas));
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $pagina_cancel = Hash::link('apps/escritos/controller/escrito_lista_correo.php?' . http_build_query($a_cosas));
        }


        $pagina_nueva = Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query(['filtro' => $this->Q_filtro]));
        $url_escrito = 'apps/escritos/controller/escrito_form.php';

        $esEscrito = $this->Q_accion == Escrito::ACCION_ESCRITO;

        // para cambiar destinos en nueva ventana.
        $a_cosas = [
            'filtro' => $this->Q_filtro,
            'id_expediente' => $this->Q_id_expediente,
            'id_escrito' => $this->Q_id_escrito,
            'accion' => $this->Q_accion,
            'condicion' => $this->str_condicion,
        ];
        $pagina_actualizar = Hash::link('apps/escritos/controller/escrito_form.php?' . http_build_query($a_cosas));

        // datepicker
        $oFecha = new DateTimeLocal();
        $format = $oFecha::getFormat();
        $yearStart = date('Y');
        $yearEnd = (int)$yearStart + 2;
        $error_fecha = $_SESSION['oConfig']->getPlazoError();
        $error_fecha_txt = 'P' . $error_fecha . 'D';
        $oHoy = new DateTimeLocal();
        $oHoy->sub(new DateInterval($error_fecha_txt));
        $minIso = $oHoy->format('Y-m-d');

        // texto para conmutar
        if ($this->Q_accion === 1) {
            $conmutar_txt = _("pasar a escrito");
        } else {
            $conmutar_txt = _("pasar a propuesta");
        }

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $a_campos = [
                'titulo' => $titulo,
                'id_expediente' => $this->Q_id_expediente,
                'id_escrito' => $this->Q_id_escrito,
                'accion' => $this->Q_accion,
                'filtro' => $this->Q_filtro,
                'modo' => $this->Q_modo,
                'esEscrito' => $esEscrito,
                'id_ponente' => $id_ponente,
                //'oHash' => $oHash,
                'oArrayProtDestino' => $oArrayProtDestino,
                'oArrayProtRef' => $oArrayProtRef,
                'f_escrito' => $f_escrito,
                'tipo_doc' => $tipo_doc,
                'asunto' => $asunto,
                'anulado_txt' => $anulado_txt,
                'asunto_readonly' => $asunto_readonly,
                'detalle' => $detalle,
                'detalle_readonly' => $detalle_readonly,
                'oDesplCategoria' => $oDesplCategoria,
                'oDesplVisibilidad' => $oDesplVisibilidad,
                'hidden_visibilidad' => $visibilidad,
                //'a_adjuntos' => $a_adjuntos,
                'initialPreview' => $initialPreview,
                'post_max_size' => $post_max_size,
                'lista_adjuntos_etherpad' => $lista_adjuntos_etherpad,
                'json_config' => $json_config,
                'txt_option_cargos' => $txt_option_cargos,
                'url_update' => $url_update,
                'url_escrito' => $url_escrito,
                'pagina_cancel' => $pagina_cancel,
                'pagina_nueva' => $pagina_nueva,
                'explotar' => $explotar,
                'devolver' => $devolver,
                // datepicker
                'format' => $format,
                'yearStart' => $yearStart,
                'yearEnd' => $yearEnd,
                'minIso' => $minIso,
                // para cambiar destinos en nueva ventana
                'pagina_actualizar' => $pagina_actualizar,
                // si vengo de buscar
                'str_condicion' => $this->str_condicion,
                // para ver comentario cuando se devuelve a la oficina
                'comentario' => $comentario,
                'conmutar_txt' => $conmutar_txt,
                'vista' => ConfigGlobal::getVista(),
            ];

            $oView = new ViewTwig('escritos/controller');
            $oView->renderizar('escrito_form_ctr.html.twig', $a_campos);
        } else {
            $a_campos = [
                'titulo' => $titulo,
                'id_expediente' => $this->Q_id_expediente,
                'id_escrito' => $this->Q_id_escrito,
                'accion' => $this->Q_accion,
                'filtro' => $this->Q_filtro,
                'modo' => $this->Q_modo,
                'esEscrito' => $esEscrito,
                'id_ponente' => $id_ponente,
                //'oHash' => $oHash,
                'chk_grupo_dst' => $chk_grupo_dst,
                'oArrayDesplGrupo' => $oArrayDesplGrupo,
                'oArrayProtDestino' => $oArrayProtDestino,
                'oArrayProtRef' => $oArrayProtRef,
                'f_escrito' => $f_escrito,
                'tipo_doc' => $tipo_doc,
                'asunto' => $asunto,
                'anulado_txt' => $anulado_txt,
                'asunto_readonly' => $asunto_readonly,
                'detalle' => $detalle,
                'detalle_readonly' => $detalle_readonly,
                'oDesplCategoria' => $oDesplCategoria,
                'oDesplVisibilidad' => $oDesplVisibilidad,
                'hidden_visibilidad' => $visibilidad,
                // destino ctr
                'oDesplVisibilidad_dst' => $oDesplVisibilidad_dst,
                'oDesplPlazo' => $oDesplPlazo,
                'f_contestar' => $f_contestar,
                'ver_plazo' => $ver_plazo,
                // para la pagina js destino ctr
                'plazo_normal' => $plazo_normal,
                'plazo_urgente' => $plazo_urgente,
                'plazo_rapido' => $plazo_rapido,
                //'a_adjuntos' => $a_adjuntos,
                'initialPreview' => $initialPreview,
                'post_max_size' => $post_max_size,
                'lista_adjuntos_etherpad' => $lista_adjuntos_etherpad,
                'json_config' => $json_config,
                'txt_option_cargos' => $txt_option_cargos,
                //'txt_option_ref' => $txt_option_ref,
                'url_update' => $url_update,
                'url_escrito' => $url_escrito,
                'pagina_cancel' => $pagina_cancel,
                'pagina_nueva' => $pagina_nueva,
                'explotar' => $explotar,
                'devolver' => $devolver,
                // datepicker
                'format' => $format,
                'yearStart' => $yearStart,
                'yearEnd' => $yearEnd,
                'minIso' => $minIso,
                // para cambiar destinos en nueva ventana
                'pagina_actualizar' => $pagina_actualizar,
                'descripcion' => $descripcion,
                // si vengo de buscar
                'str_condicion' => $this->str_condicion,
                // para ver comentario cuando se devuelve a la oficina
                'comentario' => $comentario,
            ];

            $oView = new ViewTwig('escritos/controller');
            $oView->renderizar('escrito_form.html.twig', $a_campos);
        }
    }

    /**
     * @return int
     */
    public function getId_entrada(): int
    {
        return $this->id_entrada;
    }

    /**
     * @param int $id_entrada
     */
    public function setId_entrada(int $id_entrada): void
    {
        $this->id_entrada = $id_entrada;
    }

    /**
     * @return string
     */
    public function getStr_condicion(): string
    {
        return $this->str_condicion;
    }

    /**
     * @param string $str_condicion
     */
    public function setStr_condicion(string $str_condicion): void
    {
        $this->str_condicion = $str_condicion;
    }

}