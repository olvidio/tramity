<?php

namespace oasis_as4\model;

use core\ConfigGlobal;
use davical\model\Davical;
use DOMDocument;
use entidades\model\entity\GestorEntidadesDB;
use entradas\model\entity\EntradaCompartida;
use entradas\model\entity\EntradaCompartidaAdjunto;
use entradas\model\entity\EntradaDocDB;
use entradas\model\entity\GestorEntradaCompartida;
use entradas\model\Entrada;
use entradas\model\EntradaEntidad;
use entradas\model\EntradaEntidadAdjunto;
use entradas\model\EntradaEntidadDoc;
use entradas\model\GestorEntrada;
use escritos\model\entity\GestorEscritoEntidad;
use escritos\model\GestorEscrito;
use etherpad\model\Etherpad;
use Exception;
use lugares\model\entity\GestorLugar;
use pendientes\model\Pendiente;
use SimpleXMLElement;
use stdClass;
use usuarios\model\CargoEntidad;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use web\DateTimeLocal;
use web\Protocolo;
use web\StringLocal;
use function core\is_true;

/**
 * No se usa el simpleXml porque con los adjuntos grandes se acaba la memoria...
 *
 * @author dani
 *
 */
class As4Entregar extends As4CollaborationInfo
{

    private string $msg;
    private SimpleXMLElement $xmldata;

    private string $location;
    private string $sigla_destino;

    private $service;
    private $dom;

    private stdClass $oProt_dst;
    private stdClass $oProt_org;
    private array $a_Prot_ref;
    private ?DateTimeLocal $oF_entrada;
    private ?DateTimeLocal $oF_escrito;
    private ?DateTimeLocal $oF_contestar;

    private string $asunto;
    private string $content;
    /**
     * type del content. No lo llamo content-type para no confundir con el MIME
     */
    private string $type;
    private int $visibilidad;
    private bool $bypass;
    private array $a_adjuntos;
    private array $a_destinos;
    private string $descripcion;
    private int $categoria;

    private array $aLugares;
    private array $aEntidadesNombre;
    private array $aEntidadesSchema;

    private $anular_txt;

    private string $asunto_secretaria;
    private ?string $detalle;
    private ?int $id_ponente;
    private array $oficinas;

    /**
     * @param $xmldata  SimpleXMLElement
     */
    public function __construct(SimpleXMLElement $xmldata)
    {
        $gesLugares = new GestorLugar();
        $this->aLugares = $gesLugares->getArrayLugares();

        $this->xmldata = $xmldata;
        $this->explotar_xml();
    }

    private function explotar_xml(): void
    {
        // MessageProperties
        $this->getMessageProperties();

        // CollaborationInfo
        $this->getCollaborationInfo();

        // Payload
        $this->getPayload();
    }

    private function getMessageProperties(): void
    {
        $this->getProtocolos();
    }

    private function getProtocolos(): void
    {
        // conseguir los protocolos origen y destino de las propiedades del mensaje:
        // MessageProperties
        $messageProperties = $this->xmldata->MessageProperties;

        $a_prot = [];
        $lugar_org = '';
        $num_org = '';
        $any_org = '';
        $mas_org = '';
        $lugar_dst = '';
        $num_dst = '';
        $any_dst = '';
        $mas_dst = '';
        // para evitar el mensaje: "Node no longer exists" poner '@'
        // a partir de php 8:
        if (is_countable($messageProperties->children())) {
            foreach ($messageProperties->children() as $node_property) {
                $name = (string)$node_property->attributes()->name;
                $value = (string)$node_property;

                // origen
                if ($name === 'lugar_org') {
                    $lugar_org = $value;
                }
                if ($name === 'num_org') {
                    $num_org = $value;
                }
                if ($name === 'any_org') {
                    $any_org = $value;
                }
                if ($name === 'mas_org') {
                    $mas_org = $value;
                }

                // sigla destino
                if ($name === 'lugar_dst') {
                    $lugar_dst = $value;
                }
                if ($name === 'num_dst') {
                    $num_dst = $value;
                }
                if ($name === 'any_dst') {
                    $any_dst = $value;
                }
                if ($name === 'mas_dst') {
                    $mas_dst = $value;
                }

            }
        }
        $a_prot['org'] = [
            'lugar' => $lugar_org,
            'num' => $num_org,
            'any' => $any_org,
            'mas' => $mas_org,
        ];

        $a_prot['dst'] = [
            'lugar' => $lugar_dst,
            'num' => $num_dst,
            'any' => $any_dst,
            'mas' => $mas_dst,
        ];

        $this->sigla_destino = (string)$lugar_dst;
        // si no existen, hay que mirar dentro del mensaje
    }

    private function getCollaborationInfo(): void
    {
        $this->setService((string)$this->xmldata->CollaborationInfo->Service);
        $this->setAccion((string)$this->xmldata->CollaborationInfo->Action);
    }

    private function getPayload(): void
    {
        $payload = $this->xmldata->PayloadInfo;
        $xmlFileName = $payload->PartInfo->attributes()->location;
        $this->setLocation($xmlFileName);

        switch ($this->accion) {
            case As4CollaborationInfo::ACCION_ORDEN_ANULAR:
                // propiamente no hay escrito:
                $this->getEscritoAnularFromFileName($xmlFileName);
                break;
            case As4CollaborationInfo::ACCION_REEMPLAZAR:
                $this->getEscritoAnularFromFileName($xmlFileName);
                $this->getEscritoFromFileName($xmlFileName);
                break;
            default:
                $this->getEscritoFromFileName($xmlFileName);
        }
    }

    private function getEscritoAnularFromFileName($xmlFileName): void
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->load($xmlFileName, LIBXML_PARSEHUGE);

        $this->oProt_org = $this->getProt_org();
        $this->anular_txt = $this->getAnular();
    }

    private function getProt_org(): stdClass
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_origen')->item(0);
        return $this->xml2prot_simple($xml_prot, 'org');
    }

    /**
     * @param $xml
     * @param $sufijo string
     * @return stdClass
     */
    private function xml2prot_simple($xml, string $sufijo): stdClass
    {
        $ilugar = '';
        $num = '';
        $any = '';
        $mas = '';
        // para evitar el mensaje: "Node no longer exists"
        if ($xml !== null && !is_null($xml->childNodes) && is_countable($xml->childNodes)) {
            foreach ($xml->childNodes as $node) {
                $name = $node->nodeName;
                $value = $node->nodeValue;

                if ($name === 'lugar_' . $sufijo) {
                    $nom_lugar = (string)$value;
                    // pasarlo de texto al id correspondiente:
                    $ilugar = array_search($nom_lugar, $this->aLugares, true);
                }
                if ($name === 'num_' . $sufijo) {
                    $num = (string)$value;
                }
                if ($name === 'any_' . $sufijo) {
                    $any = (string)$value;
                }
                if ($name === 'mas_' . $sufijo) {
                    $mas = (string)$value;
                }
            }
        }

        if (empty($num)) {
            $any = '';
            $mas = '';
        }

        return (new Protocolo($ilugar, $num, $any, $mas))->getProt();
    }

    private function getAnular(): string
    {
        return $this->getValorTag('anular');
    }

    private function getValorTag($tagname): string
    {
        $rta = '';
        $nodelist = $this->dom->getElementsByTagName($tagname);
        if ($nodelist->length > 0) {
            $rta = $nodelist->item(0)->nodeValue;
        }
        return $rta;
    }

    private function getEscritoFromFileName($xmlFileName): void
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->load($xmlFileName, LIBXML_PARSEHUGE);

        $this->oProt_dst = $this->getProt_dst();
        $this->oProt_org = $this->getProt_org();
        $this->a_Prot_ref = $this->getProt_ref();
        // Si el destino tiene número de protocolo se añade a las referencias
        if (!empty((array)$this->oProt_dst)) {
            $oProtDst = $this->oProt_dst;
            if (!empty($oProtDst->num)) {
                array_unshift($this->a_Prot_ref, $oProtDst);
            }
        }

        $this->oF_entrada = $this->getF_entrada();
        $this->oF_escrito = $this->getF_escrito();
        $this->oF_contestar = $this->getF_contestar();
        $this->asunto = $this->getAsunto();
        $this->content = $this->getContent();
        $this->type = $this->getType();
        $this->visibilidad = $this->getVisibilidad();
        $this->a_adjuntos = $this->getAdjuntos();
        $this->bypass = $this->getByPass();

        // compartido
        if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
            $this->getCompartido();
        }
    }

    private function buscar_ref($siglaDestino): void
    {
        // Copiado de buscar_ajax.php
        // 'buscar_referencia_correspondiente':

        // solamente la primera referencia
        $oProtDst = $this->a_Prot_ref[0];
        $id_lugar = $oProtDst->id_lugar;
        $num = null;
        $any = '';
        if (property_exists($oProtDst, 'num')) {
            $num = $oProtDst->num;
        }
        if (property_exists($oProtDst, 'any')) {
            $any = empty($oProtDst->num)? '' : $oProtDst->any;
        }

        if (!empty($id_lugar) && !empty($num) && !empty($any)) {
            // Escritos
            $aProt_local = ['id_lugar' => $id_lugar,
                'num' => $num,
                'any' => $any,
            ];
            // Asegurarme que es el primero. Ordeno (si hubiera más de uno)
            $aWhere = ['anulado' => 'f', '_ordre' => "f_aprobacion DESC, id_escrito DESC"];
            $gesEscritos = new GestorEscritoEntidad($siglaDestino);
            $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_local, $aWhere);
            if (!empty($cEscritos)) {
                $oEscrito = $cEscritos[0];
                if ($oEscrito->DBCargar() !== FALSE) {
                    $this->asunto_secretaria = $oEscrito->getAsunto();
                    $this->detalle = $oEscrito->getDetalle();
                    $this->categoria = $oEscrito->getCategoria();
                    // los escritos van por cargos, las entradas por oficinas: pongo al director de la oficina:
                    $id_ponente = $oEscrito->getPonente();
                    $a_firmas = $oEscrito->getResto_oficinas();

                    $oCargo = new CargoEntidad($siglaDestino);
                    $oCargo->setId_cargo($id_ponente);
                    $id_of_ponente = $oCargo->getId_oficina();
                    $this->id_ponente = $id_of_ponente;
                    $a_oficinas = [];
                    foreach ($a_firmas as $id_cargo) {
                        $oCargo = new CargoEntidad($siglaDestino);
                        $oCargo->setId_cargo($id_cargo);
                        $id_oficina = $oCargo->getId_oficina();
                        $a_oficinas[] = $id_oficina;
                    }
                    $this->oficinas = $a_oficinas;
                }
            }
        }
    }

    private function getProt_dst(): stdClass
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_destino')->item(0);
        if (is_null($xml_prot)) {
            // los que son compartidos pueden tener el destino = null
            return new stdClass();
        }
        return $this->xml2prot_simple($xml_prot, 'dst');
    }

    private function getProt_ref(): array
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_referencias')->item(0);
        return $this->xml2prot_array($xml_prot, 'ref');
    }

    private function xml2prot_array($xml, $sufijo): array
    {
        $a_json_prot = [];
        // para evitar el mensaje: "Node no longer exists"
        if ($xml !== null && !is_null($xml->childNodes) && is_countable($xml->childNodes)) {
            foreach ($xml->childNodes as $node) {
                $a_json_prot[] = $this->xml2prot_simple($node, $sufijo);
            }
        }
        return $a_json_prot;
    }

    /**
     * @throws Exception
     */
    private function getF_entrada()
    {
        $f_entrada_iso = $this->getValorTag('f_entrada');
        if (!empty($f_entrada_iso)) {
            return new DateTimeLocal($f_entrada_iso);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function getF_escrito()
    {
        $f_escrito_iso = $this->getValorTag('f_escrito');
        if (!empty($f_escrito_iso)) {
            return new DateTimeLocal($f_escrito_iso);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function getF_contestar()
    {
        $f_contestar_iso = $this->getValorTag('f_contestar');
        if (!empty($f_contestar_iso)) {
            return new DateTimeLocal($f_contestar_iso);
        }

        return null;
    }

    private function getAsunto(): string
    {
        return $this->getValorTag('asunto');
    }

    private function getContent()
    {
        $xml_adjuntos = $this->dom->getElementsByTagName('content')->item(0);
        if (!empty($xml_adjuntos)) {
            $name = $xml_adjuntos->nodeName;
            if ($name === 'content') {
                $value = $xml_adjuntos->nodeValue;
                $a_mime = $this->descomponerMime($value);
                foreach ($a_mime as $mime) {
                    $aAdjuntos[] = $mime;
                }
            }
            // normalmente el escrito es sólo uno, aunque se use el MIME/multipart
            $escrito = $aAdjuntos[0];
            $doc_encoded = $escrito['contenido'];
            $doc = base64_decode($doc_encoded);
        } else {
            $doc = '';
        }

        return $doc;
    }

    private function descomponerMime($mime_txt): array
    {

        $mime = mailparse_msg_create();
        mailparse_msg_parse($mime, $mime_txt);

        $structure = mailparse_msg_get_structure($mime);
        // chop message parts into array
        $parts = [];
        foreach ($structure as $s) {
            $part = mailparse_msg_get_part($mime, $s);
            $part_data = mailparse_msg_get_part_data($part);
            $content_type = $part_data['content-type'];
            // no se coge el primero, que es el grupo:
            if ($content_type === 'multipart/mixed') {
                continue;
            }
            $starting_pos_body = $part_data['starting-pos-body'];
            $ending_pos_body = $part_data['ending-pos-body'];
            $chunked_str = substr($mime_txt, $starting_pos_body, ($ending_pos_body - $starting_pos_body)); // copy data into array
            $long_str = str_replace("\r\n", "", $chunked_str);
            $parts[$s]['contenido'] = $long_str;
            $parts[$s]['filename'] = empty($part_data['content-filename']) ? _("sin nombre") : $part_data['content-filename'];
        }
        mailparse_msg_free($mime);

        return $parts;
    }

    private function getType(): string
    {
        $nodo_content = $this->dom->getElementsByTagName('content')->item(0);
        foreach ($nodo_content->attributes as $attribute) {
            if ($attribute->name === 'type') {
                return (string)$attribute->value;
            }
        }
        // si no hay devolver 'html' por defecto
        return 'html';
    }

    private function getVisibilidad(): int
    {
        return (integer)$this->getValorTag('visibilidad');
    }

    private function getAdjuntos(): array
    {
        $nodelist = $this->dom->getElementsByTagName('adjuntos');

        $aAdjuntos = [];
        if ($nodelist->length > 0) {
            $xml_adjuntos = $this->dom->getElementsByTagName('adjuntos')->item(0);
            if (!empty($xml_adjuntos)) {
                foreach ($xml_adjuntos->childNodes as $adjunto) {
                    $name = $adjunto->nodeName;
                    if ($name === 'adjunto') {
                        $value = $adjunto->nodeValue;

                        $a_mime = $this->descomponerMime($value);
                        foreach ($a_mime as $mime) {
                            $aAdjuntos[] = $mime;
                        }
                    }
                }
            }
        }
        return $aAdjuntos;
    }

    private function getByPass(): bool
    {
        return is_true($this->getValorTag('bypass'));
    }

    private function getCompartido(): void
    {
        $nodelist = $this->dom->getElementsByTagName('compartido');
        if ($nodelist->length > 0) {
            $xml_compartido = $nodelist->item(0);
            foreach ($xml_compartido->childNodes as $node) {

                $name = $node->nodeName;
                if ($name === 'descripcion') {
                    $this->descripcion = (string)$node->nodeValue;
                }
                if ($name === 'categoria') {
                    $this->categoria = (integer)$node->nodeValue;
                }
                if ($name === 'destinos') {
                    $this->a_destinos = $this->getDestinos($node);
                }
            }
        }
    }

    private function getDestinos($xml_destinos): array
    {
        $aDestinos = [];
        if (!empty($xml_destinos)) {
            foreach ($xml_destinos->childNodes as $node) {
                $name = $node->nodeName;
                if ($name === 'destino') {
                    $aDestinos[] = (integer)$node->nodeValue;
                }
            }
        }
        return $aDestinos;
    }

    /**
     * asignar la entrada a la nombre_entidad correspondiente
     * Mirar a quien va dirigido y introducirlo en su BD
     *
     */
    public function introducirEnDB()
    {
        $this->msg = '';
        $success = TRUE;
        // service + acción: que hay que hacer
        if ($this->getService() === 'correo') {
            switch ($this->getAccion()) {
                case As4CollaborationInfo::ACCION_NUEVO:
                    // comprobar que existe destino (sigla)
                    if (in_array($this->getSiglaDestino(), $this->getEntidadesPlataforma(), true)) {
                        // introducir los datos del mensaje en el tramity
                        $err_txt = $this->nuevo();
                        if (!empty($err_txt)) {
                            $this->msg .= $err_txt;
                            $success = FALSE;
                        }
                    } else {
                        $success = FALSE;
                    }
                    break;
                case As4CollaborationInfo::ACCION_COMPARTIR:
                    // comprobar que existe algún destino
                    if (!empty($this->a_destinos)) {
                        $success = $this->entrada_compartida();
                    } else {
                        $this->msg = _("No hay destinos");
                        $success = FALSE;
                    }
                    break;
                case As4CollaborationInfo::ACCION_ELIMINAR:
                case As4CollaborationInfo::ACCION_ANULAR:
                    break;
                case As4CollaborationInfo::ACCION_REEMPLAZAR:
                    $success = $this->orden_reemplazar();
                    break;
                case As4CollaborationInfo::ACCION_ORDEN_ANULAR:
                    $success = $this->orden_anular_entrada_compartida();
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            return $success;
        }
        return FALSE;
    }

    /**
     * @return string
     */
    private function getService(): string
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    private function setService(string $service): void
    {
        $this->service = strtolower($service);
    }

    /**
     * @return mixed
     */
    private function getSiglaDestino()
    {
        return $this->sigla_destino;
    }

    private function getEntidadesPlataforma(): array
    {
        if (!isset($this->aEntidadesNombre)) {
            $gesEntidades = new GestorEntidadesDB();
            $cEntidades = $gesEntidades->getEntidadesDB(['anulado' => 'false']);
            $aEntidades = [];
            foreach ($cEntidades as $oEntidad) {
                $id = $oEntidad->getId_entidad();
                $nombre = $oEntidad->getNombre();

                $aEntidades[$id] = $nombre;
            }
            $this->aEntidadesNombre = $aEntidades;
        }
        return $this->aEntidadesNombre;
    }

    private function nuevo(): string
    {
        $error_txt = '';
        // hay que conectar con la nombre_entidad destino:
        $siglaDestino = $this->getSiglaDestino();
        $id_entrada = $this->nuevaEntrada($siglaDestino);

        if (!empty($this->content)) {
            $error_txt = $this->cargarContenido($id_entrada, $siglaDestino);
            // Creo que habría que borrar la entrada si no se ha podido cargar el contenido
            if (!empty($error_txt)) {
                $oEntrada = new Entrada($id_entrada);
                if ($oEntrada->DBEliminar() === FALSE) {
                    $error_txt .= $oEntrada->getErrorTxt();
                }
            }
        }
        // cargar los adjuntos una vez se ha creado la entrada y se tiene el id:
        if (!empty($this->a_adjuntos) && empty($error_txt)) {
            $this->cargarAdjunto($this->a_adjuntos, $id_entrada);
        }

        // Compruebo si hay que generar un pendiente
        if (!empty($this->oF_contestar) && empty($error_txt)
            && ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO)
            )
        {

            $this->nuevoPendiente($id_entrada, $siglaDestino);
        }
        return $error_txt;
    }

    private function nuevaEntrada($siglaDestino, $id_entrada_compartida = null): int
    {
        $oEntrada = new EntradaEntidad($siglaDestino);
        $oEntrada->DBCargar();
        $oEntrada->setModo_entrada(Entrada::MODO_MANUAL);
        $oEntrada->setJson_prot_origen($this->oProt_org);
        $oEntrada->setJson_prot_ref($this->a_Prot_ref);
        $oEntrada->setAsunto_entrada($this->asunto);
        $oEntrada->setAsunto($this->asunto);
        $oEntrada->setId_entrada_compartida($id_entrada_compartida);
        $oHoy = new DateTimeLocal();
        $oEntrada->setF_entrada($oHoy);
        $oEntrada->setF_contestar($this->oF_contestar);
        $oEntrada->setVisibilidad($this->visibilidad);
        if (empty($this->categoria)) {
            $oEntrada->setCategoria(Categoria::CAT_NORMAL); // valor por defecto
        } else {
            $oEntrada->setCategoria($this->categoria);
        }
        // Buscar en referencias por campos adicionales (ponente, asunto secretaria, detalle)
        // solamente para las dl.
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            if (!empty($this->a_Prot_ref)) {
                $this->buscar_ref($siglaDestino);
            }
        }
        if (!empty($this->asunto_secretaria)) {
            $oEntrada->setAsunto($this->asunto_secretaria);
        }
        if (!empty($this->detalle)) {
            $oEntrada->setDetalle($this->detalle);
        }
        if (!empty($this->id_ponente)) {
            $oEntrada->setPonente($this->id_ponente);
        }
        if (!empty($this->oficinas)) {
            $oEntrada->setResto_oficinas($this->oficinas);
        }

        $estado = Entrada::ESTADO_INGRESADO;
        $oEntrada->setEstado($estado);
        $oEntrada->setBypass($this->bypass);

        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit ($error_txt);
        }

        return $oEntrada->getId_entrada();
    }

    private function cargarContenido($id_entrada, $siglaDestino = '', $compartido = FALSE): string
    {
        $error_txt = '';
        $oHoy = new DateTimeLocal();
        switch ($this->type) {
            case Payload::TYPE_ETHERAD_TXT:
                // guardar el texto del escrito
                $oEtherpad = new Etherpad();
                if ($compartido) {
                    $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada, $siglaDestino);
                    $oEtherpad->setTextContent($this->content);
                    $oEtherpad->getPadId(); // Aquí crea el pad y utiliza el $this->content
                } else {
                    $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
                    $oEtherpad->setTextContent($this->content);
                    $oEtherpad->getPadId(); // Aquí crea el pad y utiliza el $this->content
                    // la relación con la entrada y la fecha
                    $oEntradaDocDB = new EntradaEntidadDoc($id_entrada, $siglaDestino);
                    // no hace falta DBCargar, porque es nuevo y todavía no está en la DB.
                    if (!empty($this->oF_escrito)) {
                        $oEntradaDocDB->setF_doc($this->oF_escrito->getIso(), FALSE);
                    } else {
                        // No puede ser NULL
                        $oEntradaDocDB->setF_doc($oHoy);
                    }
                    $oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
                    if ($oEntradaDocDB->DBGuardar() === FALSE) {
                         $error_txt .= $oEntradaDocDB->getErrorTxt();
                    }
                }
                break;
            case Payload::TYPE_ETHERAD_HTML:
            case 'html':
                // guardar el texto del escrito
                $oEtherpad = new Etherpad();
                if ($compartido) {
                    $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada, $siglaDestino);
                    $pad_id = $oEtherpad->getPadId(); // Aquí crea el pad
                    $oEtherpad->setHTML($pad_id, $this->content);
                } else {
                    $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
                    $pad_id = $oEtherpad->getPadId(); // Aquí crea el pad
                    $oEtherpad->setHTML($pad_id, $this->content);
                    // la relación con la entrada y la fecha
                    $oEntradaDocDB = new EntradaEntidadDoc($id_entrada, $siglaDestino);
                    // no hace falta DBCargar, porque es nuevo y todavía no está en la DB.
                    if (!empty($this->oF_escrito)) {
                        $oEntradaDocDB->setF_doc($this->oF_escrito->getIso(), FALSE);
                    } else {
                        // No puede ser NULL
                        $oEntradaDocDB->setF_doc($oHoy);
                    }
                    $oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
                    if ($oEntradaDocDB->DBGuardar() === FALSE) {
                        $error_txt .= $oEntradaDocDB->getErrorTxt();
                    }
                }
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        return $error_txt;
    }

    private function cargarAdjunto($a_adjuntos, $id_entrada): void
    {

        foreach ($a_adjuntos as $adjunto) {
            $filename = $adjunto['filename'];
            $doc_encoded = $adjunto['contenido'];
            $doc = base64_decode($doc_encoded);

            $oEntradaAdjunto = new EntradaEntidadAdjunto($this->getSiglaDestino());
            $oEntradaAdjunto->setId_entrada($id_entrada);
            $oEntradaAdjunto->setNom($filename);
            $oEntradaAdjunto->setAdjunto($doc);

            $oEntradaAdjunto->DBGuardar();
        }
    }

    private function nuevoPendiente($id_entrada, $siglaDestino): void
    {
        $oHoy = new DateTimeLocal();
        $id_cargo_role = ConfigGlobal::role_id_cargo();
        $oCargo = new Cargo($id_cargo_role);
        $id_oficina = $oCargo->getId_oficina();
        // nombre normalizado del usuario y oficina:
        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
        $user_davical = $oDavical->getUsernameDavical($id_cargo_role);

        // Para dl, Hace falta el nombre de la oficina;
        // para ctr, uso el nombre del esquema. Pero si es una entrada compartida,
        // hay que saber para que ctr. (no sirve el esquema que siempre es el mismo).
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $cal_oficina = $oDavical->getNombreRecursoPorIdOficina($id_oficina);
        } else {
            $id_oficina = Cargo::OFICINA_ESQUEMA;
            $sigla_norm = StringLocal::toRFC952($siglaDestino);
            $cal_oficina = $sigla_norm . "_oficina";
        }
        $calendario = 'oficina';

        $f_entrada = $oHoy->getFromLocal();
        $f_plazo = $this->oF_contestar->getFromLocal();

        $id_origen = $this->oProt_org->id_lugar;
        $prot_num = $this->oProt_org->num;
        $prot_any = $this->oProt_org->any;

        $pendiente_location = $this->aLugares[$id_origen];
        $pendiente_location .= empty($prot_num) ? '' : ' ' . $prot_num;
        $pendiente_location .= empty($prot_any) ? '' : '/' . $prot_any;

        $prot_mas = $this->oProt_org->mas;

        $id_reg = 'EN' . $id_entrada; // (para calendario='registro': REN = Registro Entrada, para 'oficina': EN)
        $oPendiente = new Pendiente($cal_oficina, $calendario, $user_davical);
        $oPendiente->setId_reg($id_reg);
        $oPendiente->setAsunto($this->asunto);
        $oPendiente->setStatus("NEEDS-ACTION");
        $oPendiente->setF_inicio($f_entrada);
        $oPendiente->setF_plazo($f_plazo);
        $oPendiente->setVisibilidad($this->visibilidad);
        $oPendiente->setPendiente_con($id_origen);
        $oPendiente->setLocation($pendiente_location);
        $oPendiente->setRef_prot_mas($prot_mas);
        $oPendiente->setId_oficina($id_oficina);
        // las firmas son cargos, buscar las oficinas implicadas:
        $oPendiente->setOficinasArray([]);
        if ($oPendiente->Guardar() === FALSE) {
            exit(_("No se han podido guardar el nuevo pendiente"));
        }
    }

    /**
     * Se debe crear una entrada_compartida en public (y adjuntos si hay)
     * y posteriormente una entrada para cada destino, con referencia al id_entrada_compartida.
     *
     * @param boolean $avisoIndividual . Si se debe mandar o no la entrada a cada destino
     */
    private function entrada_compartida(bool $avisoIndividual = TRUE): bool
    {
        // Valores que no pueden ser NULL:
        if (empty($this->descripcion)) {
            $this->msg = _("La entrada no tiene descripción");
        }
        if (empty($this->asunto)) {
            $this->msg = _("La entrada no tiene asunto");
        }

        $oEntradaCompartida = new EntradaCompartida();
        $oEntradaCompartida->setDescripcion($this->descripcion);
        $oEntradaCompartida->setDestinos($this->a_destinos);
        $oEntradaCompartida->setF_documento($this->oF_escrito);

        /* Finalmente se añaden estos campos, para poder gestionar
         * las entradas permanentes de cr independientemente de si el ctr
         * tiene una entrada particular o no. Útil en el caso de un nuevo ctr,
         * no hay que crear entradas con fechas antiguas, simplemente incorporarlo a la
         * lista de destino del escrito compartido.
         */
        $oEntradaCompartida->setJson_prot_origen($this->oProt_org);
        $oEntradaCompartida->setJson_prot_ref($this->a_Prot_ref);
        $oEntradaCompartida->setCategoria($this->categoria);
        $oEntradaCompartida->setAsunto_entrada($this->asunto);
        $oEntradaCompartida->setF_entrada($this->oF_entrada);
        $oEntradaCompartida->setAnulado('');


        if ($oEntradaCompartida->DBGuardar() === FALSE) {
            return FALSE;
        }

        $id_entrada_compartida = $oEntradaCompartida->getId_entrada_compartida();
        // contenido de la entrada compartida
        if (!empty($this->content)) {
            $this->cargarContenido($id_entrada_compartida, '', TRUE);
        }

        // adjuntos de la entrada compartida
        if (!empty($this->a_adjuntos)) {
            $this->cargarAdjuntoCompartido($this->a_adjuntos, $id_entrada_compartida);
        }

        // crear entradas individuales para cada destino
        if ($avisoIndividual) {
            foreach ($this->a_destinos as $id_destino) {
                // puede ser que no exista el ctr en la lista (o esté anulado)...
                $siglaDestino = empty($this->aLugares[$id_destino]) ? '' : $this->aLugares[$id_destino];
                // comprobar que el destino está en la plataforma, sino, no se crea la entrada
                if (in_array($siglaDestino, $this->getEntidadesPlataforma(), true)) {
                    $id_entrada = $this->nuevaEntrada($siglaDestino, $id_entrada_compartida);
                    // Compruebo si hay que generar un pendiente
                    if (!empty($this->oF_contestar)
                        && ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                           || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO)
                    )
                    {
                        $this->nuevoPendiente($id_entrada, $siglaDestino);
                    }
                }
            }
        }
        return TRUE;
    }

    private function cargarAdjuntoCompartido($a_adjuntos, $id_entrada): void
    {
        foreach ($a_adjuntos as $adjunto) {
            $filename = $adjunto['filename'];
            $doc_encoded = $adjunto['contenido'];
            $doc = base64_decode($doc_encoded);

            $oEntradaAdjunto = new EntradaCompartidaAdjunto();
            $oEntradaAdjunto->setId_entrada_compartida($id_entrada);
            $oEntradaAdjunto->setNom($filename);
            $oEntradaAdjunto->setAdjunto($doc);

            $oEntradaAdjunto->DBGuardar();
        }
    }

    /**
     * Debe anular la entrada con este prot_origen, y seguidamente crear una nueva entrada.
     *
     * @return boolean
     */
    private function orden_reemplazar(): bool
    {
        if ($this->orden_anular_entrada_compartida()) {
            return $this->entrada_compartida(FALSE);
        }

        return FALSE;
    }

    private function orden_anular_entrada_compartida(): bool
    {
        $success = FALSE;
        $aProt_org = ['id_lugar' => $this->oProt_org->id_lugar,
            'num' => $this->oProt_org->num,
            'any' => $this->oProt_org->any,
            'mas' => '',
        ];

        $gesEntradasCompartidas = new GestorEntradaCompartida();
        $cEntradasCompartidas = $gesEntradasCompartidas->getEntradasByProtOrigenDB($aProt_org);
        foreach ($cEntradasCompartidas as $oEntradaCompartida) {
            $anulado = $oEntradaCompartida->getAnulado();
            if (!empty($anulado)) {
                continue;
            }

            $oEntradaCompartida->setAnulado($this->anular_txt);
            $oEntradaCompartida->setCategoria(Categoria::CAT_NORMAL);
            if ($oEntradaCompartida->DBGuardar() === FALSE) {
                $error_txt = $oEntradaCompartida->getErrorTxt();
                exit ($error_txt);
            }
            $id_entrada_compartida = $oEntradaCompartida->getId_entrada_compartida();
            // Anular también las entradas normales:
            $gesEntradas = new GestorEntrada();
            $gesEntradas->anularCompartidas($id_entrada_compartida, $this->anular_txt, $this->getSchemaEntidadesPlataforma());
            $success = TRUE;
        }
        return $success;
    }

    private function getSchemaEntidadesPlataforma(): array
    {
        if (!isset($this->aEntidadesSchema)) {
            $gesEntidades = new GestorEntidadesDB();
            $cEntidades = $gesEntidades->getEntidadesDB(['anulado' => 'false']);
            $aEntidades = [];
            foreach ($cEntidades as $oEntidad) {
                $id = $oEntidad->getId_entidad();
                $schema = $oEntidad->getSchema();

                $aEntidades[$id] = $schema;
            }
            $this->aEntidadesSchema = $aEntidades;
        }
        return $this->aEntidadesSchema;
    }

    /**
     * path dónde está el fichero del xml: "payloads/cr_19_22.xml"
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    private function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

}