<?php

namespace oasis_as4\model;

use documentos\model\Documento;
use DOMAttr;
use DOMDocument;
use entradas\model\entity\EntradaAdjunto;
use envios\model\MIMEAttachment;
use envios\model\MIMEContainer;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\TextoDelEscrito;
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;
use stdClass;
use usuarios\model\Visibilidad;
use function core\borrar_tmp;
use function core\is_true;


class Payload
{

    // Type: formato del escrito
    public const TYPE_ETHERAD_TXT = 'etherpad_txt';
    public const TYPE_ETHERAD_HTML = 'etherpad_html';

    private $accion;

    private $aLugares;
    private $dom;
    private $format;
    private $payload;
    private $escrito;
    private $nombre_escrito;
    private $deleteFilesAfterSubmit = 'false';

    private $json_prot_dst;
    private $json_prot_local;
    private $json_prot_ref;
    private $f_entrada;
    private $f_escrito;
    private $f_salida;
    private $f_contestar;
    private $asunto;
    private $bypass;
    private $visibilidad;
    private $a_id_adjuntos;
    private $id_escrito;

    private $entrada_o_escrito;
    private $tipo_doc;
    private $descripcion;
    private $categoria;
    private $destinos;

    private string $sufijo_dst;
    private string $anular_txt;
    private mixed $prot_org;
    private mixed $prot_ref;

    public function __construct()
    {
        $gesLugares = new GestorLugar();
        $this->aLugares = $gesLugares->getArrayLugares();

        $this->dom = new DOMDocument('1.0', 'utf-8');
    }

    public function setPayload($oEscrito, $tipo_escrito, $sufijo_dst = ''): void
    {
        $this->entrada_o_escrito = $tipo_escrito;
        $this->sufijo_dst = $sufijo_dst;

        if ($this->entrada_o_escrito === 'escrito') {
            $this->setPayloadEscrito($oEscrito);
            $this->tipo_doc = $oEscrito->getTipo_doc();
        }
        if ($this->entrada_o_escrito === 'entrada') {
            $this->setPayloadEntrada($oEscrito);
            $this->tipo_doc = $oEscrito->getTipo_documento();
        }
    }

    /**
     * @throws \JsonException
     */
    private function setPayloadEscrito($oEscrito): void
    {
        $this->json_prot_local = $oEscrito->getJson_prot_local();
        // Si es "sin numerar", por lo menos pongo la sigla
        if (empty((array)$this->json_prot_local)) {
            // Busco el id_lugar de la dl.
            $gesLugares = new GestorLugar();
            $id_siga_local = $gesLugares->getId_sigla_local();
            $this->json_prot_local = new stdClass;
            $this->json_prot_local->id_lugar = $id_siga_local;
            $this->json_prot_local->num = '';
            $this->json_prot_local->any = '';
            $this->json_prot_local->mas = '';
        }

        // OJO hay que coger el destino que se tiene al enviar,
        // no el del escrito, que puede ser a varios o un grupo.
        //$this->json_prot_dst = $oEscrito->getJson_prot_destino();

        $this->json_prot_ref = $oEscrito->getJson_prot_ref();

        $this->setProt_org($this->json_prot_local);
        $this->setProt_ref($oEscrito->getJson_prot_ref());

        $this->setF_entrada($oEscrito->getF_escrito());
        $this->setF_escrito($oEscrito->getF_escrito());
        $this->setF_salida($oEscrito->getF_salida());
        $this->setF_contestar($oEscrito->getF_contestar());

        $this->setAsunto($oEscrito->getAsunto());
        $this->setId_escrito($oEscrito->getId_escrito());
        $this->setVisibilidad($oEscrito->getVisibilidad_dst());

        $this->setA_id_adjuntos($oEscrito->getArrayIdAdjuntos());

        $this->nombre_escrito = $oEscrito->getNombreEscrito($this->sufijo_dst) . '.xml';

        if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
            $this->json_prot_dst = $oEscrito->getJson_prot_destino();
            $this->descripcion = $oEscrito->getDestinosEscrito(); // para que salga la descripción del grupo.
            $this->categoria = $oEscrito->getCategoria();
            $this->destinos = $oEscrito->getDestinos();
        }
    }

    /**
     * @param mixed $f_entrada
     */
    public function setF_entrada($f_entrada): void
    {
        $this->f_entrada = $f_entrada;
    }


    /*
    <PartInfo containment="body" location="payloads/simple_document.xml">
      <PartProperties>
        <Property name="original-file-name">simple_document.xml</Property>
      </PartProperties>
    </PartInfo>
    <PartInfo containment="attachment" mimeType="image/jpeg" location="payloads/summerflower.jpg"/>
    */

    /**
     * @param mixed $f_escrito
     */
    public function setF_escrito($f_escrito): void
    {
        $this->f_escrito = $f_escrito;
    }

    /**
     * @param mixed $f_salida
     */
    public function setF_salida($f_salida): void
    {
        $this->f_salida = $f_salida;
    }

    // ---------------------------------------

    /**
     * @param mixed $f_contestar
     */
    public function setF_contestar($f_contestar): void
    {
        $this->f_contestar = $f_contestar;
    }

    /**
     * @param mixed $asunto
     */
    public function setAsunto($asunto): void
    {
        $this->asunto = $asunto;
    }

    /**
     * @param mixed $content
     */
    public function setId_escrito($id_escrito): void
    {
        $this->id_escrito = $id_escrito;
    }

    /**
     * @param mixed $visibilidad
     */
    public function setVisibilidad($visibilidad): void
    {
        $this->visibilidad = (string)$visibilidad;
    }

    /**
     * @param mixed $a_id_adjuntos
     */
    public function setA_id_adjuntos($a_id_adjuntos): void
    {
        $this->a_id_adjuntos = $a_id_adjuntos;
    }

    private function setPayloadEntrada($oEntradaBypass): void
    {
        $this->json_prot_local = $oEntradaBypass->getJson_prot_origen();
        // OJO hay que coger el destino que se tiene al enviar,
        // no el del escrito, que puede ser a varios o un grupo.
        //$this->json_prot_dst = $oEscrito->getJson_prot_destino();

        $this->json_prot_ref = $oEntradaBypass->getJson_prot_ref();

        $this->setF_entrada($oEntradaBypass->getF_entrada());
        $this->setF_escrito($oEntradaBypass->getF_documento());
        $this->setF_salida($oEntradaBypass->getF_salida());
        $this->setF_contestar($oEntradaBypass->getF_contestar());

        $this->setAsunto($oEntradaBypass->getAsunto());
        $this->setId_escrito($oEntradaBypass->getId_entrada());
        //$this->setVisibilidad($oEntrada->getVisibilidad_dst());

        $this->setA_id_adjuntos($oEntradaBypass->getArrayIdAdjuntos());

        $this->nombre_escrito = $oEntradaBypass->getNombreEscrito($this->sufijo_dst) . '.xml';

        if ($this->accion == As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion == As4CollaborationInfo::ACCION_REEMPLAZAR) {
            $this->json_prot_dst = $oEntradaBypass->getJson_prot_destino();
            ///$this->descripcion = $oEntrada->getDescripcion();
            $this->descripcion = $oEntradaBypass->cabeceraDistribucion_cr(); // descripción más completa
            $this->categoria = $oEntradaBypass->getCategoria();
            $aDestinos = $oEntradaBypass->getDestinosByPass();
            $this->destinos = $aDestinos['miembros'];
        }
    }

    public function createXml($dom)
    {
        $this->payload = $dom->createElement("PayloadInfo");
        $attr = new DOMAttr('deleteFilesAfterSubmit', $this->deleteFilesAfterSubmit);
        $this->payload->setAttributeNode($attr);

        $location = "payloads/$this->nombre_escrito";

        $part_info = $dom->createElement('PartInfo');
        $attr_1 = new DOMAttr('containment', "body");
        $part_info->setAttributeNode($attr_1);
        $attr_2 = new DOMAttr('location', $location);
        $part_info->setAttributeNode($attr_2);

        $part_properties = $dom->createElement('PartProperties');
        $element_property = $dom->createElement('Property', $this->nombre_escrito);
        $attr_name = new DOMAttr('name', "original-file-name");
        $element_property->setAttributeNode($attr_name);
        $part_properties->appendChild($element_property);

        $part_info->appendChild($part_properties);

        $this->payload->appendChild($part_info);

        return $this->payload;
    }

    public function createXmlFile(): void
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');

        $this->escrito = $this->dom->createElement("escrito");

        $saltar = FALSE;
        if ($this->accion === As4CollaborationInfo::ACCION_ORDEN_ANULAR) {
            $this->escrito->appendChild($this->createXmlAnular());
            $saltar = TRUE;
        }
        if ($this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
            $this->escrito->appendChild($this->createXmlAnular());
        }

        $this->escrito->appendChild($this->createXmlProt_dst());
        $this->escrito->appendChild($this->createXmlProt_org());
        $this->escrito->appendChild($this->createXmlProt_ref());

        if (!$saltar) {
            $this->escrito->appendChild($this->createXmlF_entrada());
            $this->escrito->appendChild($this->createXmlF_escrito());
            $this->escrito->appendChild($this->createXmlF_salida());
            $this->escrito->appendChild($this->createXmlF_contestar());
            $this->escrito->appendChild($this->createXmlAsunto());
            $this->escrito->appendChild($this->createXmlContent());
            $this->escrito->appendChild($this->createXmlVisibilidad());
            $this->escrito->appendChild($this->createXmlAdjuntos());
            if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
                || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
                $this->escrito->appendChild($this->createXmlCompartido());
            }
        }

        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $this->dom->appendChild($this->escrito);

        if ($this->dom->save($this->getFullFilename()) === FALSE) {
            exit ("Error al guardar el escrito en xml");
        }
    }

    /**
     * @return mixed
     */
    private function createXmlAnular()
    {
        return $this->dom->createElement('anular', $this->anular_txt);
    }

    /**
     * @return mixed
     */
    private function createXmlProt_dst()
    {
        $oProt = $this->json_prot_dst;
        return $this->prot2xml($oProt, 'dst');
    }

    /**
     * @param object $oProt
     * @param string $tipo 'destino'|'origen'|'referencia'
     */
    private function prot2xml($aProt, $tipo)
    {
        switch ($tipo) {
            case 'destino':
            case 'dst':
                $name_nodo = 'prot_destino';
                $sufijo = 'dst';
                break;
            case 'origen':
            case 'org':
                $name_nodo = 'prot_origen';
                $sufijo = 'org';
                break;
            case 'referencia':
            case 'ref':
                $name_nodo = 'prot_referencia';
                $sufijo = 'ref';
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }

        /*
        // el destino y las ref son un array
        // Para los arrays queda:
        <prot_referencias>
          <referencia>
            <lugar_ref>agdMontagut</lugar_ref>
            <num_ref>2</num_ref>
            <any_ref>22</any_ref>
            <mas_ref>mes1</mas_ref>
          </referencia>
          <referencia>
            <lugar_ref>H</lugar_ref>
            <num_ref>4</num_ref>
            <any_ref>21</any_ref>
            <mas_ref>mes2</mas_ref>
          </referencia>
        </prot_referencias>
        */
        if (is_array($aProt)) {
            $name_nodo_array = $name_nodo . 's';
            $nodo_array = $this->dom->createElement($name_nodo_array);
            foreach ($aProt as $oProt) {
                $nombre_nodo = substr($name_nodo, 5);
                $nodo = $this->explodeProt($oProt, $nombre_nodo, $sufijo);
                $nodo_array->appendChild($nodo);
            }
            return $nodo_array;
        }

        return $this->explodeProt($aProt, $name_nodo, $sufijo);
    }

    private function explodeProt($oProt, $nombre_nodo, $sufijo)
    {
        $nodo = $this->dom->createElement($nombre_nodo);

        if (empty((array)$oProt) || !property_exists($oProt, 'id_lugar')) {
            return $nodo;
        }

        $id_lugar = $oProt->id_lugar;
        $nom_lugar = empty($this->aLugares[$id_lugar]) ? '' : $this->aLugares[$id_lugar];
        $num = $oProt->num;
        $any = $oProt->any;
        $mas = $oProt->mas;

        // No se admiten propiedades vacias: No se incluyen.
        if (!empty($nom_lugar)) {
            $nombre = "lugar_$sufijo";
            $nodo->appendChild($this->dom->createElement($nombre, $nom_lugar));
        }
        if (!empty($num)) {
            $nombre = "num_$sufijo";
            $nodo->appendChild($this->dom->createElement($nombre, $num));
        }
        if (!empty($any)) {
            $nombre = "any_$sufijo";
            $nodo->appendChild($this->dom->createElement($nombre, $any));
        }
        if (!empty($mas)) {
            $nombre = "mas_$sufijo";
            $nodo->appendChild($this->dom->createElement($nombre, $mas));
        }
        return $nodo;
    }

    /**
     * @return mixed
     */
    private function createXmlProt_org()
    {
        $oProt = $this->json_prot_local;
        return $this->prot2xml($oProt, 'org');
    }

    /**
     * @return mixed
     */
    private function createXmlProt_ref()
    {
        $oProt = $this->json_prot_ref;
        return $this->prot2xml($oProt, 'ref');
    }

    /**
     * @return mixed
     */
    private function createXmlF_entrada()
    {
        $f_iso = $this->f_entrada->getIso();
        return $this->dom->createElement('f_entrada', $f_iso);
    }

    /**
     * @return mixed
     */
    private function createXmlF_escrito()
    {
        $f_iso = $this->f_escrito->getIso();
        return $this->dom->createElement('f_escrito', $f_iso);
    }

    /**
     * @return mixed
     */
    private function createXmlF_salida()
    {
        $f_iso = $this->f_salida->getIso();
        return $this->dom->createElement('f_salida', $f_iso);
    }

    /**
     * @return mixed
     */
    private function createXmlF_contestar()
    {
        $f_iso = $this->f_contestar->getIso();
        return $this->dom->createElement('f_contestar', $f_iso);
    }

    /**
     * @return mixed
     */
    private function createXmlAsunto()
    {
        return $this->dom->createElement('asunto', $this->asunto);
    }

    /**
     * @return mixed
     */
    private function createXmlContent()
    {
        /* Puede ser un bypass o simplemente una salida con múltiples destinos */
        if ($this->accion == As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion == As4CollaborationInfo::ACCION_REEMPLAZAR) {
            if ($this->entrada_o_escrito === 'entrada') {
                $oTextoDelEscrito = new TextoDelEscrito($this->tipo_doc,TextoDelEscrito::ID_ENTRADA, $this->id_escrito);
            }
            if ($this->entrada_o_escrito === 'escrito') {
                $oTextoDelEscrito = new TextoDelEscrito($this->tipo_doc,TextoDelEscrito::ID_ESCRITO, $this->id_escrito);
            }
        } else {
            $oTextoDelEscrito = new TextoDelEscrito($this->tipo_doc,TextoDelEscrito::ID_ESCRITO, $this->id_escrito);
        }

        switch ($this->getFormat()) {
            case 'pdf':
                exit ('falta para pdf');
            case Payload::TYPE_ETHERAD_TXT:
            case 'txt':
                $txt = $oTextoDelEscrito->generarMD();
                $mime = new MIMEContainer();
                $mime->set_content_type("multipart/mixed");
                $attachment = new MIMEAttachment();
                $attachment->setfilename('escrito');
                $attachment->set_content($txt);
                $mime->add_subcontainer($attachment);
                $contenido_mime = $mime->get_message();

                $content = $this->dom->createElement('content', $contenido_mime);
                $attr = new DOMAttr('type', self::TYPE_ETHERAD_TXT);
                $content->setAttributeNode($attr);
                return $content;
            case Payload::TYPE_ETHERAD_HTML:
            case 'html':
                $txt = $oTextoDelEscrito->generarHtml();
                $mime = new MIMEContainer();
                $mime->set_content_type("multipart/mixed");
                $attachment = new MIMEAttachment();
                $attachment->setfilename('escrito');
                $attachment->set_content($txt);
                $mime->add_subcontainer($attachment);
                $contenido_mime = $mime->get_message();

                $content = $this->dom->createElement('content', $contenido_mime);
                $attr = new DOMAttr('type', self::TYPE_ETHERAD_HTML);
                $content->setAttributeNode($attr);
                return $content;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        $formato = empty($this->format) ? 'md' : $this->format;
        return strtolower($formato);
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    private function createXmlVisibilidad()
    {
        // con pHP 8 no se puede pasar un valor null
        if (empty($this->visibilidad)) {
            $visibilidad_string = (string)Visibilidad::V_CTR_TODOS;
        } else {
            $visibilidad_string = (string)$this->visibilidad;
        }
        return $this->dom->createElement('visibilidad', $visibilidad_string);
    }

    /**
     * @return mixed
     */
    private function createXmlAdjuntos()
    {
        $a_adjuntos = [];
        foreach ($this->a_id_adjuntos as $item => $adjunto_filename) {
            if ($this->entrada_o_escrito === 'entrada') {
                $oEntradaAdjunto = new EntradaAdjunto($item);
                $escrito_txt = $oEntradaAdjunto->getAdjunto();
                $a_adjuntos[$adjunto_filename] = $escrito_txt;
            }
            if ($this->entrada_o_escrito === 'escrito') {
                $oEscritoAdjunto = new EscritoAdjunto($item);
                $tipo_doc = $oEscritoAdjunto->getTipo_doc();
                switch ($tipo_doc) {
                    case TextoDelEscrito::TIPO_UPLOAD:
                        $escrito_txt = $oEscritoAdjunto->getAdjunto();
                        $a_adjuntos[$adjunto_filename] = $escrito_txt;
                        break;
                    default:
                        $id_adjunto = $oEscritoAdjunto->getId_item();
                        $oTextoDelEscritoAdj = new TextoDelEscrito($tipo_doc, TextoDelEscrito::ID_ADJUNTO, $id_adjunto);
                        $oTextoDelEscritoAdj->addHeaders([],'');
                        $a_adjuntos[$adjunto_filename] = $oTextoDelEscritoAdj->getContentFormatPDF();
                }
            }
        }

        $nodo_array = $this->dom->createElement('adjuntos');

        if (!empty($this->a_id_adjuntos)) {
            // probar multipart:
            $mime = $this->getMIMEMultiPart($a_adjuntos);
            $nodo = $this->dom->createElement('adjunto', $mime);
            $nodo_array->appendChild($nodo);
        }

        return $nodo_array;
    }

    private function getMIMEMultiPart($a_adjuntos)
    {
        /*
        Content-Type:
        Content-Disposition: form-data; name="file1";
        filename="readme.txt"
        Content-Type: text/plain
        */

        $mime = new MIMEContainer();
        $mime->set_content_type("multipart/mixed");

        foreach ($a_adjuntos as $adjunto_filename => $escrito_txt) {
            $attachment = new MIMEAttachment();
            $attachment->setfilename($adjunto_filename);
            $attachment->set_content($escrito_txt);
            $mime->add_subcontainer($attachment);
        }

        return $mime->get_message();
    }

    private function createXmlCompartido()
    {
        $nodo_compartido = $this->dom->createElement('compartido');
        if (!empty($this->descripcion)) {
            $nodo = $this->dom->createElement('descripcion', $this->descripcion);
            $nodo_compartido->appendChild($nodo);
        }
        if (!empty($this->categoria)) {
            $nodo = $this->dom->createElement('categoria', $this->categoria);
            $nodo_compartido->appendChild($nodo);
        }
        if (!empty($this->destinos)) {
            $nodo_array = $this->dom->createElement('destinos');

            foreach ($this->destinos as $id_destinno) {
                $nodo = $this->dom->createElement('destino', $id_destinno);
                $nodo_array->appendChild($nodo);
            }
            $nodo_compartido->appendChild($nodo_array);
        }

        return $nodo_compartido;
    }

    private function getFullFilename()
    {
        $dir = $_SESSION['oConfig']->getDock();
        return $dir . '/data/msg_out/payloads/' . $this->nombre_escrito;
    }

    /**
     * @param mixed $prot_dst
     */
    public function setJson_prot_dst($json_prot_dst)
    {
        $this->json_prot_dst = $json_prot_dst;
    }

    /**
     * @param mixed $prot_org
     */
    public function setProt_org($prot_org)
    {
        $this->prot_org = $prot_org;
    }

    /**
     * @param mixed $prot_ref
     */
    public function setProt_ref($prot_ref)
    {
        $this->prot_ref = $prot_ref;
    }

    /**
     * @param mixed $asunto
     */
    public function setAnular($text)
    {
        $this->anular_txt = $text;
    }

    /**
     * @return mixed
     */
    public function getBypass()
    {
        return $this->bypass;
    }

    /**
     * @param mixed $bypass
     */
    public function setBypass($bypass)
    {
        $this->bypass = $bypass;
    }

    /**
     * @return boolean
     */
    public function getDeleteFilesAfterSubmit()
    {
        return $this->deleteFilesAfterSubmit;
    }

    /**
     * @param boolean $deleteFilesAfterSubmit
     */
    public function setDeleteFilesAfterSubmit($deleteFilesAfterSubmit)
    {
        if (is_true($deleteFilesAfterSubmit)) {
            $this->deleteFilesAfterSubmit = 'true';
        } else {
            $this->deleteFilesAfterSubmit = 'false';
        }
    }

    /**
     * @return mixed
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * @param mixed $accion
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;
    }


}