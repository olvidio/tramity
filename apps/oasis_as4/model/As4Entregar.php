<?php

namespace oasis_as4\model;

use core\ConfigGlobal;
use davical\model\Davical;
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
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;
use pendientes\model\Pendiente;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use web\DateTimeLocal;
use web\Protocolo;
use web\StringLocal;

/**
 * No se usa el simpleXml porque con los adjuntos grandes se acaba la memoria...
 *
 * @author dani
 *
 */
class As4Entregar extends As4CollaborationInfo
{

    private $msg;
    private $xmldata;

    private $location;
    private $sigla_destino;
    private $sigla_origen;

    private $service;
    private $xml_escrito;
    private $dom;

    /**
     * a_Prot_dst
     * @var array
     */
    private $a_Prot_dst;
    /**
     * a_Prot_dst
     * @var array
     */
    private $a_Prot_org;
    /**
     * a_Prot_dst
     * @var array
     */
    private $a_Prot_ref;
    /**
     * oF_entrada
     * @var DateTimeLocal
     */
    private $oF_entrada;
    /**
     * oF_escrito
     * @var DateTimeLocal
     */
    private $oF_escrito;
    /**
     * oF_contestar
     * @var DateTimeLocal
     */
    private $oF_contestar;

    /**
     *
     * @var string
     */
    private $asunto;
    /**
     *
     * @var string
     */
    private $content;
    /**
     * type del content. No lo llamo content-type para no confundir con el MIME
     *
     * @var string
     */
    private $type;
    /**
     *
     * @var integer
     */
    private $visibilidad;
    private $bypass;

    /**
     *
     * @var array
     */
    private $a_adjuntos;

    /**
     *
     * @var array
     */
    private $a_destinos;
    private $descripcion;
    private $categoria;

    /**
     * tabla de siglas:
     * @var array
     */
    private $aLugares;
    private $aEntidades;

    /**
     *
     * @var string
     */
    private $anular_txt;


    public function __construct($xmldata)
    {
        $gesLugares = new GestorLugar();
        $this->aLugares = $gesLugares->getArrayLugares();

        $this->xmldata = $xmldata;
        $this->explotar_xml();
    }

    private function explotar_xml()
    {
        // MessageProperties
        $this->getMessageProperties();

        // CollaborationInfo
        $this->getCollaborationInfo();

        // Papyload
        $this->getPayload();
    }

    private function getMessageProperties()
    {
        return $this->getProtocolos();
    }

    private function getProtocolos()
    {
        // consegir los protocolos origen y destino de las propiedades del mensaje:
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
        // para evitar el mensaje: "Node no longer exists"
        if (@count($messageProperties->children())) {
            foreach ($messageProperties->children() as $node_property) {
                $name = $node_property->attributes()->name;
                $value = $node_property;

                // origen
                if ($name == 'lugar_org') {
                    $lugar_org = $value;
                }
                if ($name == 'num_org') {
                    $num_org = $value;
                }
                if ($name == 'any_org') {
                    $any_org = $value;
                }
                if ($name == 'mas_org') {
                    $mas_org = $value;
                }

                // sigla destino
                if ($name == 'lugar_dst') {
                    $lugar_dst = $value;
                }
                if ($name == 'num_dst') {
                    $num_dst = $value;
                }
                if ($name == 'any_dst') {
                    $any_dst = $value;
                }
                if ($name == 'mas_dst') {
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

        $this->sigla_origen = (string)$lugar_org;
        $this->sigla_destino = (string)$lugar_dst;
        // si no existen, hay que mirar dentro del mensaje
        return $a_prot;
    }

    private function getCollaborationInfo()
    {
        $this->setService((string)$this->xmldata->CollaborationInfo->Service);
        $this->setAccion((string)$this->xmldata->CollaborationInfo->Action);
    }

    private function getPayload()
    {
        $payload = $this->xmldata->PayloadInfo;
        $location = $payload->PartInfo->attributes()->location;
        $this->setLocation($location);

        switch ($this->accion) {
            case As4CollaborationInfo::ACCION_ORDEN_ANULAR:
                // propiamente no hay escrito:
                $this->getEscritoAnular($location);
                break;
            case As4CollaborationInfo::ACCION_REEMPLAZAR:
                $this->getEscritoAnular($location);
                $this->getEscrito($location);
                break;
            default:
                $this->getEscrito($location);
        }
    }

    private function getEscritoAnular($location)
    {
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->load($location, LIBXML_PARSEHUGE);

        $this->a_Prot_org = $this->getProt_org();
        $this->anular_txt = $this->getAnular();
    }

    private function getProt_org()
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_origen')->item(0);
        return $this->xml2prot_simple($xml_prot, 'org');
    }

    private function xml2prot_simple($xml, $sufijo)
    {
        $nom_lugar = '';
        $ilugar = '';
        $num = '';
        $any = '';
        $mas = '';
        // para evitar el mensaje: "Node no longer exists"
        if (@count($xml->childNodes)) {
            foreach ($xml->childNodes as $node) {
                $name = $node->nodeName;
                $value = $node->nodeValue;

                if ($name == 'lugar_' . $sufijo) {
                    $nom_lugar = (string)$value;
                    // pasarlo de texto al id correspondiente:
                    $ilugar = array_search($nom_lugar, $this->aLugares);
                }
                if ($name == 'num_' . $sufijo) {
                    $num = (string)$value;
                }
                if ($name == 'any_' . $sufijo) {
                    $any = (string)$value;
                }
                if ($name == 'mas_' . $sufijo) {
                    $mas = (string)$value;
                }
            }
        }

        if (empty($num)) {
            $any = '';
            $mas = '';
        }

        $oProtOrigen = new Protocolo($ilugar, $num, $any, $mas);
        return $oProtOrigen->getProt();
    }

    private function getAnular()
    {
        return $this->getValorTag('anular');
    }

    private function getValorTag($tagname)
    {
        $rta = '';
        $nodelist = $this->dom->getElementsByTagName($tagname);
        if ($nodelist->length > 0) {
            $rta = $nodelist->item(0)->nodeValue;
        }
        return $rta;
    }

    private function getEscrito($location)
    {
        //$this->xml_escrito = simplexml_load_file($location);
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->load($location, LIBXML_PARSEHUGE);
        //$this->dom->load($location);

        $this->a_Prot_dst = $this->getProt_dst();
        $this->a_Prot_org = $this->getProt_org();
        $this->a_Prot_ref = $this->getProt_ref();
        // Si el destino tiene número de protocolo se añade a las referencias
        if (!empty((array)$this->a_Prot_dst)) {
            $oProtDst = $this->a_Prot_dst;
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
        if ($this->accion == As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion == As4CollaborationInfo::ACCION_REEMPLAZAR) {
            $this->getCompartido();
        }
    }

    private function getProt_dst()
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_destino')->item(0);
        return $this->xml2prot_simple($xml_prot, 'dst');
    }

    private function getProt_ref()
    {
        $xml_prot = $this->dom->getElementsByTagName('prot_referencias')->item(0);
        return $this->xml2prot_array($xml_prot, 'ref');
    }

    private function xml2prot_array($xml, $sufijo)
    {
        $a_json_prot = [];
        // para evitar el mensaje: "Node no longer exists"
        if (@count($xml->childNodes)) {
            foreach ($xml->childNodes as $node) {
                $a_json_prot[] = $this->xml2prot_simple($node, $sufijo);
            }
        }
        return $a_json_prot;
    }

    private function getF_entrada()
    {
        $f_entrada_iso = (string)$this->getValorTag('f_entrada');
        if (!empty($f_entrada_iso)) {
            return new DateTimeLocal($f_entrada_iso);
        } else {
            return '';
        }
    }

    private function getF_escrito()
    {
        $f_escrito_iso = (string)$this->getValorTag('f_escrito');
        if (!empty($f_escrito_iso)) {
            return new DateTimeLocal($f_escrito_iso);
        } else {
            return '';
        }
    }

    private function getF_contestar()
    {
        $f_contestar_iso = (string)$this->getValorTag('f_contestar');
        if (!empty($f_contestar_iso)) {
            return new DateTimeLocal($f_contestar_iso);
        } else {
            return '';
        }
    }

    private function getAsunto()
    {
        return $this->getValorTag('asunto');
    }

    private function getContent()
    {
        $xml_adjuntos = $this->dom->getElementsByTagName('content')->item(0);
        if (!empty($xml_adjuntos)) {
            $name = $xml_adjuntos->nodeName;
            if ($name == 'content') {
                $value = $xml_adjuntos->nodeValue;
                $a_mime = $this->descomponerMime($value);
                foreach ($a_mime as $mime) {
                    $aAdjuntos[] = $mime;
                }
            }
            // normalmente el escrito es sólo uno, aunque se use el MIME/multipart
            $escrito = $aAdjuntos[0];
            // $filename = $escrito['filename']
            $doc_encoded = $escrito['contenido'];
            $doc = base64_decode($doc_encoded);
        } else {
            $doc = '';
        }

        return $doc;
    }

    private function descomponerMime($mime_txt)
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
            // no se coje el primero, que es el grupo:
            if ($content_type == 'multipart/mixed') {
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

    private function getType()
    {
        $nodo_content = $this->dom->getElementsByTagName('content')->item(0);
        foreach ($nodo_content->attributes as $attribute) {
            if ($attribute->name == 'type') {
                return (string)$attribute->value;
            }
        }
        // si no hay devolver 'html' por defecto
        return 'html';
    }

    private function getVisibilidad()
    {
        return $this->getValorTag('visibilidad');
    }

    private function getAdjuntos()
    {
        $nodelist = $this->dom->getElementsByTagName('adjuntos');

        $aAdjuntos = [];
        if ($nodelist->length > 0) {
            $xml_adjuntos = $this->dom->getElementsByTagName('adjuntos')->item(0);
            if (!empty($xml_adjuntos)) {
                foreach ($xml_adjuntos->childNodes as $adjunto) {
                    $name = $adjunto->nodeName;
                    if ($name == 'adjunto') {
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

    private function getByPass()
    {
        return $this->getValorTag('bypass');
    }

    private function getCompartido()
    {
        $nodelist = $this->dom->getElementsByTagName('compartido');
        if ($nodelist->length > 0) {
            $xml_compartido = $nodelist->item(0);
            foreach ($xml_compartido->childNodes as $node) {

                $name = $node->nodeName;
                if ($name == 'descripcion') {
                    $this->descripcion = $node->nodeValue;
                }
                if ($name == 'categoria') {
                    $this->categoria = $node->nodeValue;
                }
                if ($name == 'destinos') {
                    $this->a_destinos = $this->getDestinos($node);
                }
            }
        }
    }

    private function getDestinos($xml_destinos)
    {
        $aDestinos = [];
        if (!empty($xml_destinos)) {
            foreach ($xml_destinos->childNodes as $node) {
                $name = $node->nodeName;
                if ($name == 'destino') {
                    $aDestinos[] = (integer)$node->nodeValue;
                }
            }
        }
        return $aDestinos;
    }

    /**
     * asignar la entrada a la entidad correspondiente
     * Mirar a quien va dirigido y introducirlo en su BD
     *
     */
    public function introducirEnDB()
    {
        $this->msg = '';
        $success = TRUE;
        // service + accion: que hay que hacer
        if ($this->getService() == 'correo') {
            switch ($this->getAccion()) {
                case As4CollaborationInfo::ACCION_NUEVO:
                    // comprobar que existe destino (sigla)
                    if (in_array($this->getSiglaDestino(), $this->getEntidadesPlataforma())) {
                        // introducir los datos del mensaje en el tramity
                        $this->nuevo();
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
                case As4CollaborationInfo::ACCION_ANULAR:
                    break;
                case As4CollaborationInfo::ACCION_REEMPLAZAR:
                    $success = $this->orden_reemplazar();
                    break;
                case As4CollaborationInfo::ACCION_ELIMINAR:
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

    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = strtolower($service);
    }

    /**
     * @return mixed
     */
    public function getSiglaDestino()
    {
        return $this->sigla_destino;
    }

    public function getEntidadesPlataforma()
    {
        if (!isset($this->aEntidades)) {
            $gesEntidades = new GestorEntidadesDB();
            $cEntidades = $gesEntidades->getEntidadesDB(['anulado' => 'false']);
            $aEntidades = [];
            foreach ($cEntidades as $oEntidad) {
                $id = $oEntidad->getId_entidad();
                $nombre = $oEntidad->getNombre();

                $aEntidades[$id] = $nombre;
            }
            $this->aEntidades = $aEntidades;
        }
        return $this->aEntidades;
    }

    private function nuevo()
    {
        // hay que conectar con la entidad destino:
        $siglaDestino = $this->getSiglaDestino();
        $id_entrada = $this->nuevaEntrada($siglaDestino);

        if (!empty($this->content)) {
            $this->cargarContenido($id_entrada, $siglaDestino, FALSE);
        }
        // cargar los adjuntos una vez se ha creado la entrada y se tiene el id:
        if (!empty($this->a_adjuntos)) {
            $this->cargarAdjunto($this->a_adjuntos, $id_entrada);
        }

        // Compruebo si hay que generar un pendiente
        if (!empty($this->oF_contestar) && $_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $this->nuevoPendiente($id_entrada, $siglaDestino);
        }
    }

    private function nuevaEntrada($siglaDestino, $id_entrada_compartida = '')
    {
        $oEntrada = new EntradaEntidad($siglaDestino);
        $oEntrada->DBCarregar();
        $oEntrada->setModo_entrada(Entrada::MODO_MANUAL);
        $oEntrada->setJson_prot_origen($this->a_Prot_org);
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
        // dejo la oficina en blanco

        $estado = Entrada::ESTADO_INGRESADO;
        $oEntrada->setEstado($estado);
        $oEntrada->setBypass($this->bypass);

        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit ($error_txt);
        } else {
            $id_entrada = $oEntrada->getId_entrada();
        }

        return $id_entrada;
    }

    private function cargarContenido($id_entrada, $siglaDestino = '', $compartido = FALSE)
    {
        $oHoy = new DateTimeLocal();
        switch ($this->type) {
            case Payload::TYPE_ETHERAD_TXT:
                // guardar el texto del escrito
                $oEtherpad = new Etherpad();
                if ($compartido) {
                    $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada, $siglaDestino);
                    $oEtherpad->setText($this->content);
                    $oEtherpad->getPadId(); // Aqui crea el pad y utiliza el $this->content
                } else {
                    $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
                    $oEtherpad->setText($this->content);
                    $oEtherpad->getPadId(); // Aqui crea el pad y utiliza el $this->content
                    // la relacion con la entrada y la fecha
                    $oEntradaDocDB = new EntradaEntidadDoc($id_entrada, $siglaDestino);
                    // no hace falta, porque es nuevo y todavia no está en la DB. $oEntradaDocDB->DBCarregar();
                    if (!empty($this->oF_escrito)) {
                        $oEntradaDocDB->setF_doc($this->oF_escrito->getIso(), FALSE);
                    } else {
                        // No puede ser NULL
                        $oEntradaDocDB->setF_doc($oHoy);
                    }
                    $oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
                    $oEntradaDocDB->DBGuardar();
                }
                break;
            case Payload::TYPE_ETHERAD_HTML:
            case 'html':
                // guardar el texto del escrito
                $oEtherpad = new Etherpad();
                if ($compartido) {
                    $oEtherpad->setId(Etherpad::ID_COMPARTIDO, $id_entrada, $siglaDestino);
                    $pad_id = $oEtherpad->getPadId(); // Aqui crea el pad
                    $oEtherpad->setHTML($pad_id, $this->content);
                } else {
                    $oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
                    $pad_id = $oEtherpad->getPadId(); // Aqui crea el pad
                    $oEtherpad->setHTML($pad_id, $this->content);
                    // la relacion con la entrada y la fecha
                    $oEntradaDocDB = new EntradaEntidadDoc($id_entrada, $siglaDestino);
                    // no hace falta, porque es nuevo y todavia no está en la DB. $oEntradaDocDB->DBCarregar();
                    if (!empty($this->oF_escrito)) {
                        $oEntradaDocDB->setF_doc($this->oF_escrito->getIso(), FALSE);
                    } else {
                        // No puede ser NULL
                        $oEntradaDocDB->setF_doc($oHoy);
                    }
                    $oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
                    $oEntradaDocDB->DBGuardar();
                }
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }

    }

    private function cargarAdjunto($a_adjuntos, $id_entrada)
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

    private function nuevoPendiente($id_entrada, $siglaDestino)
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
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $id_oficina = Cargo::OFICINA_ESQUEMA;
            $sigla_norm = StringLocal::lowerNormalized($siglaDestino);
            $cal_oficina = $sigla_norm . "_oficina";
        } else {
            $cal_oficina = $oDavical->getNombreRecurso($id_oficina);
        }
        $calendario = 'oficina';

        $f_entrada = $oHoy->getFromLocal();
        $f_plazo = $this->oF_contestar->getFromLocal();

        $id_origen = $this->a_Prot_org->id_lugar;
        $prot_num = $this->a_Prot_org->num;
        $prot_any = $this->a_Prot_org->any;

        $location = $this->aLugares[$id_origen];
        $location .= empty($prot_num) ? '' : ' ' . $prot_num;
        $location .= empty($prot_any) ? '' : '/' . $prot_any;

        $prot_mas = $this->a_Prot_org->mas;

        $id_reg = 'EN' . $id_entrada; // (para calendario='registro': REN = Regitro Entrada, para 'oficina': EN)
        $oPendiente = new Pendiente($cal_oficina, $calendario, $user_davical);
        $oPendiente->setId_reg($id_reg);
        $oPendiente->setAsunto($this->asunto);
        $oPendiente->setStatus("NEEDS-ACTION");
        $oPendiente->setF_inicio($f_entrada);
        $oPendiente->setF_plazo($f_plazo);
        $oPendiente->setVisibilidad($this->visibilidad);
        $oPendiente->setPendiente_con($id_origen);
        $oPendiente->setLocation($location);
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
     * @param boolean $avisoIndividual . Si se debe mandar o no la entrada a cada detino
     */
    private function entrada_compartida($avisoIndividual = TRUE)
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
        $oEntradaCompartida->setJson_prot_origen($this->a_Prot_org);
        $oEntradaCompartida->setJson_prot_ref($this->a_Prot_ref);
        $oEntradaCompartida->setCategoria($this->categoria);
        $oEntradaCompartida->setAsunto_entrada($this->asunto);
        $oEntradaCompartida->setF_entrada($this->oF_entrada);
        $oEntradaCompartida->setAnulado('');


        if ($oEntradaCompartida->DBGuardar() === FALSE) {
            return FALSE;
        } else {
            $id_entrada_compartida = $oEntradaCompartida->getId_entrada_compartida();
        }
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
                $siglaDestino = $this->aLugares[$id_destino];
                // comprobar que el destino está en la plataforma, sino, no se crea la entrada
                if (in_array($siglaDestino, $this->getEntidadesPlataforma())) {
                    $id_entrada = $this->nuevaEntrada($siglaDestino, $id_entrada_compartida);
                    // Compruebo si hay que generar un pendiente
                    if (!empty($this->oF_contestar) && $_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
                        $this->nuevoPendiente($id_entrada, $siglaDestino);
                    }
                }
            }
        }
        return TRUE;
    }

    private function cargarAdjuntoCompartido($a_adjuntos, $id_entrada)
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
    private function orden_reemplazar()
    {
        if ($this->orden_anular_entrada_compartida()) {
            return $this->entrada_compartida(FALSE);
        } else {
            return FALSE;
        }
    }

    private function orden_anular_entrada_compartida()
    {
        $success = FALSE;
        $aProt_org = ['id_lugar' => $this->a_Prot_org->id_lugar,
            'num' => $this->a_Prot_org->num,
            'any' => $this->a_Prot_org->any,
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

    public function getSchemaEntidadesPlataforma()
    {
        if (!isset($this->aEntidades)) {
            $gesEntidades = new GestorEntidadesDB();
            $cEntidades = $gesEntidades->getEntidadesDB(['anulado' => 'false']);
            $aEntidades = [];
            foreach ($cEntidades as $oEntidad) {
                $id = $oEntidad->getId_entidad();
                $schema = $oEntidad->getSchema();

                $aEntidades[$id] = $schema;
            }
            $this->aEntidades = $aEntidades;
        }
        return $this->aEntidades;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }


}