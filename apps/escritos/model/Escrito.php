<?php

namespace escritos\model;

use config\model\entity\ConfigSchema;
use core\ConfigGlobal;
use documentos\model\Documento;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\entity\EscritoDB;
use escritos\model\entity\GestorEscritoAdjunto;
use etherpad\model\Etherpad;
use expedientes\model\entity\Accion;
use expedientes\model\entity\GestorAccion;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use tramites\model\entity\GestorFirma;
use usuarios\model\entity\Cargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\Protocolo;
use web\ProtocoloArray;
use const http\Client\Curl\PROXY_HTTP;


class Escrito extends EscritoDB
{
    /* CONST -------------------------------------------------------------- */

    // modo envío
    public const MODO_MANUAL = 1;
    public const MODO_XML = 2;
    // Acción
    public const ACCION_PROPUESTA = 1;
    public const ACCION_ESCRITO = 2;
    public const ACCION_PLANTILLA = 3;

    /* PROPIEDADES -------------------------------------------------------------- */
    /**
     *
     * @var string
     */
    private  $destinos_txt;

    private $nombre_escrito;


    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * @param integer|array iid_escrito
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_escrito') && $val_id !== '') {
                    $this->iid_escrito = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_escrito = (int)$a_id;
                $this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('escritos');
    }

    /**
     * añadir el detalle en el asunto.
     * tener en cuenta los permisos...
     *
     * return string
     */
    public function getAsuntoDetalle(): string
    {
        $detalle = $this->getDetalle();
        return empty($detalle) ? $this->getAsunto() : $this->getAsunto() . " [$detalle]";
    }

    /**
     * Recupera l'atribut sdetalle de Entrada teniendo en cuenta los permisos
     *
     * @return string|null sdetalle
     */
    public function getDetalle(): ?string
    {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this, 'detalle');

        $detalle = _("reservado");
        if ($perm > 0) {
            $detalle = $this->getDetalleDB();
        }
        return $detalle;
    }

    /**
     * Recupera l'atribut sasunto de Entrada teniendo en cuenta los permisos
     * (NOT NULL)
     *
     * @return string sasunto
     */
    public function getAsunto(): string
    {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this, 'asunto');

        $asunto = _("reservado");
        if ($perm > 0) {
            $asunto = $this->getAsuntoDB();
        }
        return $asunto;
    }

    /**
     * genera el número de protocolo local. y lo guarda.
     */
    public function generarProtocolo($id_lugar = null)
    {
        // si ya tiene no se toca:
        $prot_local = $this->getJson_prot_local();
        if (!empty(get_object_vars($prot_local))) {
            return TRUE;
        }

        $id_lugar_contador = $id_lugar;
        $gesLugares = new GestorLugar();
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            if (empty($id_lugar)) {
                $id_lugar_contador = $gesLugares->getId_sigla_local();
            }
        } else {
            if (empty($id_lugar)) {
                $id_lugar_contador = $gesLugares->getId_sigla_local();
            }
            // según si el destino es cr, iese o resto:
            $aProtDst = $this->getJson_prot_destino(TRUE);
            // es un array, pero sólo debería haber uno...
            foreach ($aProtDst as $json_prot_destino) {
                if (empty((array)$json_prot_destino)) {
                    exit (_("Error no hay destino"));
                }

                $id_lugar_contador = $json_prot_destino['id_lugar'];
            }
        }
        $prot_num = $_SESSION['oConfig']->getContador($id_lugar_contador);
        $prot_any = date('y');
        $prot_mas = '';

        $oConfigSchema = new ConfigSchema('id_lugar_cancilleria');
        $id_cancilleria = (int)$oConfigSchema->getValor();

        $oConfigSchema = new ConfigSchema('id_lugar_uden');
        $id_uden = (int)$oConfigSchema->getValor();

        if ($id_lugar_contador === $id_uden || $id_lugar_contador === $id_cancilleria) {
            $id_lugar_local = $id_cancilleria;
        } else {
            $id_lugar_local = $gesLugares->getId_sigla_local();
        }

        $oProtLocal = new Protocolo($id_lugar_local, $prot_num, $prot_any, $prot_mas);
        $prot_local = $oProtLocal->getProt();

        $this->DBCargar();
        $this->setJson_prot_local($prot_local);
        $this->DBGuardar();
    }

    /**
     * Elimina el escrito, sus adjuntos y el texto (etherpad...)
     */
    public function eliminarTodo()
    {
        $txt_err = '';
        // Tipo de texto:
        if ($this->getTipo_doc() == self::TIPO_ETHERPAD) {
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid_escrito);
            $rta = $oEtherpad->eliminarPad();
            if (!empty($rta)) {
                $txt_err .= $rta;
            }
        }
        // adjuntos:
        $gesAdjuntos = new GestorEscritoAdjunto();
        $cAdjuntos = $gesAdjuntos->getEscritoAdjuntos(['id_escrito' => $this->iid_escrito]);
        foreach ($cAdjuntos as $oAdjunto) {
            if ($oAdjunto->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha podido eliminar un adjunto");
                $txt_err .= "<br>";
            }
        }
        // el propio escrito
        if (parent::DBEliminar() === FALSE) {
            $txt_err .= _("No se ha podido eliminar el escrito");
            $txt_err .= "<br>";
        }
        if (empty($txt_err)) {
            return TRUE;
        } else {
            return $txt_err;
        }
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayModoEnvio()
    {
        return [
            self::MODO_MANUAL => _("manual"),
            self::MODO_XML => _("xml"),
        ];
    }

    public function getArrayAccion()
    {
        return [
            self::ACCION_PROPUESTA => _("propuesta"),
            self::ACCION_PLANTILLA => _("plantilla"),
            self::ACCION_ESCRITO => _("escrito"),
        ];
    }

    public function getHtmlAdjuntos($quitar = TRUE)
    {
        // devolver la lista completa (para sobreescribir)
        $html = '';
        $a_adjuntos = $this->getArrayIdAdjuntos(Documento::DOC_ETHERPAD);
        if (!empty($a_adjuntos)) {
            $html = '<ol>';
            foreach ($a_adjuntos as $id_adjunto => $nom) {
                $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_adjunto($id_adjunto);\" >$nom</span>";
                $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_adjunto('$id_adjunto');\" >" . _("borrar") . "</span>";

                if ($quitar === TRUE) {
                    $html .= "<li>$link_mod   $link_del</li>";
                } else {
                    $html .= "<li>$link_mod</li>";
                }
            }
            $html .= '</ol>';
        }
        return $html;
    }

    public function getArrayIdAdjuntos($tipo_doc = '')
    {
        $gesEscritoAdjuntos = new GestorEscritoAdjunto();
        return $gesEscritoAdjuntos->getArrayIdAdjuntos($this->iid_escrito, $tipo_doc);
    }

    public function getDestinosEscrito()
    {
        $a_grupos = [];
        $destinos_txt = '';

        // destinos individuales
        $json_prot_dst = $this->getJson_prot_destino(TRUE);
        $oArrayProtDestino = new ProtocoloArray($json_prot_dst, '', 'destinos');
        $destinos_txt = $oArrayProtDestino->ListaTxtBr();
        // añadir visibilidad destino
        // sigla +(visibilidad) + ref
        $oVisibilidad = new Visibilidad();
        $visibilidad_dst = $this->getVisibilidad_dst();
        if (!empty($visibilidad_dst) && $visibilidad_dst !== Visibilidad::V_CTR_TODOS) {
            $a_visibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();
            $visibilidad_txt = $a_visibilidad_dst[$visibilidad_dst];
            $destinos_txt .= " ($visibilidad_txt)";
        }
        // si hay grupos, tienen preferencia
        $a_grupos = $this->getId_grupos();
        if (!empty($a_grupos)) {
            //(según los grupos seleccionados)
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $descripcion_g = $oGrupo->getDescripcion();
                $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                $destinos_txt .= $descripcion_g;
            }
        } else {
            // puede ser un destino personalizado:
            $destinos = $this->getDestinos();
            if (!empty($destinos)) {
                $destinos_txt = $this->getDescripcion();
            }
        }


        $this->destinos_txt = $destinos_txt;
        return $this->destinos_txt;
    }

    /**
     * Devuelve el numero de prootcolo, y si no existe, el valor 'default' que se pasacomo parámetro.
     *
     * @param string $default
     * @return string
     */
    public function getProt_local_txt($default = '')
    {
        $json_prot_local = $this->getJson_prot_local();
        if (count(get_object_vars($json_prot_local)) == 0) {
            /* PARECE QUE YA NO LO USO ASI
            $err_txt = "No hay protocolo local";
            $_SESSION['oGestorErrores']->addError($err_txt,'generar PDF', __LINE__, __FILE__);
            $_SESSION['oGestorErrores']->recordar($err_txt);

            $origen_txt = $_SESSION['oConfig']->getSigla();
            */
            if (empty($default)) {
                $origen_txt = _("revisar");
            } else {
                $origen_txt = $default;
            }
        } else {
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_local->id_lugar);
            $oProtOrigen->setProt_num($json_prot_local->num);
            $oProtOrigen->setProt_any($json_prot_local->any);
            $oProtOrigen->setMas($json_prot_local->mas);

            $origen_txt = $oProtOrigen->ver_txt();

            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
                $oVisibilidad = new Visibilidad();
                $a_visibilidad = $oVisibilidad->getArrayVisibilidad();
                $visibilidad = $this->getVisibilidad();
                if (!empty($visibilidad) && $visibilidad != Visibilidad::V_CTR_TODOS) {
                    $visibilidad_txt = $a_visibilidad[$visibilidad];
                    $origen_txt .= " ($visibilidad_txt)";
                }
            }
        }

        return $origen_txt;
    }

    /**
     * id_lugar sólo se pasa cuando el escrito va dirigido a un grupo, y hay que generar escritos
     * individuales para cada ctr del grupo.
     *
     * @param string $id_lugar_de_grupo
     * @return string destinos | destino + ref.
     */
    public function cabeceraIzquierda($id_lugar_de_grupo = ''): string
    {
        $destinos_txt = '';
        $id_dst = '';

        $a_grupos = $this->getId_grupos();
        // si es un grupo:
        if (!empty($a_grupos)) {
            if (!empty($id_lugar_de_grupo)) { // individual: solo añado el nombre del destino
                $oLugar = new Lugar($id_lugar_de_grupo);
                $destinos_txt .= $oLugar->getSigla();
            } else {
                //(según los grupos seleccionados)
                foreach ($a_grupos as $id_grupo) {
                    $oGrupo = new Grupo($id_grupo);
                    $descripcion_g = $oGrupo->getDescripcion();
                    $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                    $destinos_txt .= $descripcion_g;
                }
            }
        } else { // si no es un grupo
            $a_json_prot_dst = $this->getJson_prot_destino(FALSE);
            if (!empty($id_lugar_de_grupo)) { // individual, con su protocolo.
                foreach ($a_json_prot_dst as $json_prot_dst) {
                    if (!property_exists($json_prot_dst, 'id_lugar')) {
                        continue;
                    }
                    $id_dst = $json_prot_dst->id_lugar;
                    if ($id_dst == $id_lugar_de_grupo) {
                        $oProtDestino = new Protocolo();
                        $oProtDestino->setJson($json_prot_dst);
                        $destinos_txt = $oProtDestino->ver_txt();
                        // segunda región, para escrito cabecera izquierda es: mi_dl
                        $segundaRegion = $_SESSION['oConfig']->getSigla();
                        $destinos_txt = $oProtDestino->addSegundaRegion($destinos_txt, $segundaRegion);
                    }
                }
            } else {
                //(según individuales)
                if (!empty((array)$a_json_prot_dst)) {
                    $json_prot_dst = $a_json_prot_dst[0];
                    if (!empty((array)$json_prot_dst)) {
                        $id_dst = $json_prot_dst->id_lugar;
                    } else {
                        $id_dst = '';
                    }
                }
                $oArrayProtDestino = new ProtocoloArray($a_json_prot_dst, '', 'destinos');
                // segunda región, para escrito cabecera izquierda es: mi_dl
                $segundaRegion = $_SESSION['oConfig']->getSigla();
                $destinos_txt = $oArrayProtDestino->ListaTxtBr($segundaRegion);
            }
        }
        // grupos personalizados...
        // Si no hay ni grupos ni json, miro ids
        if (empty($destinos_txt)) {
            if (!empty($id_lugar_de_grupo)) {
                $oLugar = new Lugar($id_lugar_de_grupo);
                $destinos_txt .= $oLugar->getSigla();
            } else {
                $descripcion_g = $this->getDescripcion();
                if (empty($descripcion_g)) {
                    $a_id_lugar = $this->getDestinos();
                    foreach ($a_id_lugar as $id_lugar) {
                        $oLugar = new Lugar($id_lugar);
                        $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                        $destinos_txt .= $oLugar->getSigla();
                    }
                } else {
                    $destinos_txt .= $descripcion_g;
                }
            }
        }

        // referencias:
        $a_json_prot_ref = $this->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($a_json_prot_ref, '', 'referencias');
        $oArrayProtRef->setRef(TRUE);
        $aRef = $oArrayProtRef->ArrayListaTxtBr($id_dst);
        // segunda región, para escrito cabecera izquierda es: mi_dl
        if (!empty($id_dst) && !empty($aRef)) {
            $segundaRegion = $_SESSION['oConfig']->getSigla();
            $aRef = $oArrayProtRef->addSegundaRegionEnArray($aRef, $segundaRegion);
        }

        if (!empty($aRef['dst_org'])) {
            $destinos_txt .= '<br>';
            $destinos_txt .= $aRef['dst_org'];
        }
        return $destinos_txt;
    }

    public function cabeceraDerecha()
    {
        // prot local + ref
        $id_dst = '';
        $a_json_prot_dst = $this->getJson_prot_destino();
        if (!empty((array)$a_json_prot_dst)) {
            $json_prot_dst = $a_json_prot_dst[0];
            if (!empty((array)$json_prot_dst)) {
                $id_dst = $json_prot_dst->id_lugar;
            } else {
                $id_dst = '';
            }
        }

        // referencias
        $a_json_prot_ref = $this->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($a_json_prot_ref, '', 'referencias');
        $oArrayProtRef->setRef(TRUE);
        $aRef = $oArrayProtRef->ArrayListaTxtBr($id_dst);
        // segunda región, para escrito cabecera derecha es la región destino
        if (!empty($id_dst) && !empty($aRef)) {
            $oLugar = new Lugar($id_dst);
            $segundaRegion = $oLugar->getSigla();
            $aRef = $oArrayProtRef->addSegundaRegionEnArray($aRef, $segundaRegion);
        }

        $json_prot_local = $this->getJson_prot_local();
        if (count(get_object_vars($json_prot_local)) === 0){
            $is_plantilla = $this->getAccion() === self::ACCION_PLANTILLA;
            $is_anulado = $this->getAnulado();
            if(!$is_plantilla && !$is_anulado) {
                $err_txt = "No hay protocolo local";
                // sacar mas info para ver de donde sale el error
                $json_prot_dst = json_encode($this->getJson_prot_destino(FALSE));
                $mas_info = $this->iid_escrito . ':::' . $json_prot_dst;
                $_SESSION['oGestorErrores']->addError($err_txt, "generar cabecera derecha: $mas_info", __LINE__, __FILE__);
                $_SESSION['oGestorErrores']->recordar($err_txt);
            }
            $origen_txt = $_SESSION['oConfig']->getSigla();
        } else {
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_local->id_lugar);
            $oProtOrigen->setProt_num($json_prot_local->num);
            $oProtOrigen->setProt_any($json_prot_local->any);
            $oProtOrigen->setMas($json_prot_local->mas);

            $origen_txt = $oProtOrigen->ver_txt();
            // segunda región, para escrito cabecera derecha es la región destino
            if (!empty($id_dst)) {
                $oLugar = new Lugar($id_dst);
                $segundaRegion = $oLugar->getSigla();
                $origen_txt = $oProtOrigen->addSegundaRegion($origen_txt, $segundaRegion);
            }
        }

        if (!empty($aRef['local'])) {
            $origen_txt .= '<br>';
            $origen_txt .= $aRef['local'];
        }

        return $origen_txt;
    }

    public function explotar(): bool
    {
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid_escrito);
        $sourceID = $oEtherpad->getPadId();

        // Si esta marcado como grupo de destinos, o destinos individuales.
        $aProtDst = $this->getJson_prot_destino(TRUE);
        if (empty($aProtDst)) {
            $aMiembros = $this->getDestinosIds();
            foreach ($aMiembros as $id_lugar) {
                $aProtDst[] = [
                    'id_lugar' => $id_lugar,
                    'num' => '',
                    'any' => '',
                    'mas' => '',
                ];
            }
        }

        // en el último destino, no lo creo nuevo sino que utilizo el
        // de referencia. Lo hago con el último, porque si hay algún error,
        // pueda conservar el de referencia.
        $max = count($aProtDst);
        $n = 0;
        foreach ($aProtDst as $oProtDst) {
            $n++;
            $aProt_dst = (array)$oProtDst;
            $aProtDestino[0] = [
                'id_lugar' => $aProt_dst['id_lugar'],
                'num' => $aProt_dst['num'],
                'any' => $aProt_dst['any'],
                'mas' => $aProt_dst['mas'],
            ];

            if ($n < $max) {
                $newEscrito = clone($this);
                // borrar todos los destinos y poner solo uno:
                // borro los grupos
                $newEscrito->setId_grupos();
                $newEscrito->setDestinos();
                $newEscrito->setJson_prot_destino($aProtDestino);
                $newEscrito->setId_grupos();
                $newEscrito->DBGuardar();
                $newId_escrito = $newEscrito->getId_escrito();
                // asociarlo al expediente:
                $gesAcciones = new GestorAccion();
                $cAcciones = $gesAcciones->getAcciones(['id_escrito' => $this->iid_escrito]);
                if (!empty($cAcciones)) {
                    $id_expediente = $cAcciones[0]->getId_expediente();
                    $tipo_accion = $cAcciones[0]->getTipo_accion();
                    $oAccion = new Accion();
                    $oAccion->setId_expediente($id_expediente);
                    $oAccion->setTipo_accion($tipo_accion);
                    $oAccion->setId_escrito($newId_escrito);
                    $oAccion->DBGuardar();
                } else {
                    continue;
                }
                // cambiar el id, y clonar el etherpad con el nuevo id
                $oNewEtherpad = new Etherpad();
                $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $newId_escrito);
                $destinationID = $oNewEtherpad->getPadID(); // Aquí crea el pad
                /* con el Html, (setHtml) no hace bien los centrados (quizá más)
                 * con el Text  (setText) no coge los formatos.
                 */
                $oEtherpad->copyPad($sourceID, $destinationID, 'true');

                // copiar los adjuntos
                $a_id_adjuntos = $this->getArrayIdAdjuntos();
                foreach (array_keys($a_id_adjuntos) as $id_item) {
                    $Adjunto = new EscritoAdjunto($id_item);
                    if ($Adjunto->DBCargar() === FALSE ){
                        $err_cargar = sprintf(_("OJO! no existe el adjunto en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_cargar);
                    }
                    $newAdjunto = clone($Adjunto);
                    $newAdjunto->setId_escrito($newId_escrito);
                    $newAdjunto->DBGuardar();
                }

            } else {
                // En el último, no clono, aprovecho el escrito y
                // sólo cambio los destinos:
                // borro los grupos
                $this->setId_grupos();
                $this->setDestinos();
                // añado destino individual
                $this->setJson_prot_destino($aProtDestino);
                $this->setId_grupos();
                $this->DBGuardar();
            }
        }
        return TRUE;
    }

    public function getDestinosIds(): array
    {
        $a_grupos = $this->getId_grupos();

        $aMiembros = [];
        if (!empty($a_grupos)) {
            //(según los grupos seleccionados)
            $a_miembros_g = [];
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $a_miembros_g[] = $oGrupo->getMiembros();
            }
            $aMiembros = array_merge([], ...$a_miembros_g);
            $aMiembros = array_unique($aMiembros);
            // los guardo individualmente
            $this->DBCargar();
            $this->setDestinos($aMiembros);
            $this->DBGuardar();
        } else {
            //(según individuales)
            $a_json_prot_dst = $this->getJson_prot_destino();
            foreach ($a_json_prot_dst as $json_prot_dst) {
                if (!property_exists($json_prot_dst, 'id_lugar')) {
                    continue;
                }
                $aMiembros[] = $json_prot_dst->id_lugar;
            }
        }
        // Si no hay ni grupos ni json, miro ids
        if (empty($aMiembros)) {
            $aMiembros = $this->getDestinos();
        }
        return $aMiembros;
    }

    /**
     * Para los escritos jurídicos (vienen de plantilla)
     *
     * @param $id_expediente
     * @param $json_prot_local
     * @return string
     */
    public function addConforme($id_expediente, $json_prot_local): string
    {

        $html_conforme = $this->getConforme($id_expediente, $json_prot_local);

        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid_escrito);
        $padID = $oEtherpad->getPadId();

        $html_1 = $oEtherpad->getHHtml();

        $html = $html_1 . $html_conforme;

        $oEtherpad->setHTML($padID, $html);

        return $html;
    }

    /**
     * Para los escritos jurídicos (vienen de plantilla)
     *
     * @param $id_expediente
     * @param $json_prot_local
     * @return string
     */
    public function getConforme($id_expediente, $json_prot_local): string
    {
        $oProtOrigen = new Protocolo();
        $oProtOrigen->setLugar($json_prot_local->id_lugar);
        $oProtOrigen->setProt_num($json_prot_local->num);
        $oProtOrigen->setProt_any($json_prot_local->any);
        $oProtOrigen->setMas($json_prot_local->mas);
        $protocol_txt = $oProtOrigen->ver_txt();

        $gesFirmas = new GestorFirma();
        $aRecorrido = $gesFirmas->getFirmasConforme($id_expediente);

        $conforme_txt = '<br>';
        $conforme_txt .= _("Conforme") . ':';
        $conforme_txt .= '<ul class="indent">';
        //$conforme_txt .= '<li><ul class="indent"><li><ul class="indent">';
        foreach ($aRecorrido as $firma => $fecha) {
            $conforme_txt .= '<li>';
            $conforme_txt .= "$firma ($fecha)";
            $conforme_txt .= '</li>';

        }
        $conforme_txt .= '</ul>';
        //$conforme_txt .= '</li></ul></li></ul>';

        // protocol
        $protocol_txt = "($protocol_txt)";

        // lugar y fecha:
        $oF_aprobacion = $this->getF_aprobacion();
        $dia = $oF_aprobacion->format('d');
        $mes_txt = $oF_aprobacion->getMesLocalTxt();
        $any = $oF_aprobacion->format('Y');

        $localidad = $_SESSION['oConfig']->getLocalidad();
        if (empty($localidad)) {
            $localidad = _("debe indicar la localidad en la configuración");
        }

        $conforme_txt .= '<p style="text-align:right">';
        $conforme_txt .= _("El Vicario de la Delegación");
        $conforme_txt .= '</p>';

        $conforme_txt .= $protocol_txt;
        $conforme_txt .= '<p style="text-align:right">';
        $conforme_txt .= "$localidad, $dia de $mes_txt de $any";
        $conforme_txt .= '</p>';

        return $conforme_txt;
    }

    /**
     * Devuelve el nombre del escrito: dlb_2012_22
     *
     * @param string $parentesi si existe se añade al nombre, entre paréntesis
     * @return string|mixed
     */
    public function getNombreEscrito(string $parentesi = '')
    {
        $json_prot_local = $this->getJson_prot_local();
        // nombre del archivo
        if (empty((array)$json_prot_local)) {
            // genero un id: fecha
            $f_hoy = date('Y-m-d');
            $hora = date('His');
            $this->nombre_escrito = $f_hoy . '_' . _("E12") . "($hora)";
        } else {
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_local->id_lugar);
            $oProtOrigen->setProt_num($json_prot_local->num);
            $oProtOrigen->setProt_any($json_prot_local->any);
            $oProtOrigen->setMas($json_prot_local->mas);
            $this->nombre_escrito = $this->renombrar($oProtOrigen->ver_txt());
        }
        if (!empty($parentesi)) {
            $this->nombre_escrito .= "($parentesi)";
        }
        return $this->nombre_escrito;
    }

    private function renombrar($string)
    {
        //cambiar ' ' por '_':
        //cambiar '/' por '_':
        return str_replace(array(' ', '/'), '_', $string);
    }

}