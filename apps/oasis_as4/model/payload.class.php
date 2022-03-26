<?php
namespace oasis_as4\model;

use function core\is_true;
use documentos\model\Documento;
use envios\model\MIMEAttachment;
use envios\model\MIMEContainer;
use escritos\model\entity\EscritoAdjunto;
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;



class Payload {
	
	// Type: formato del escrito
	const TYPE_ETHERAD_TXT    = 'etherpad_txt';
	const TYPE_ETHERAD_HTML   = 'etherpad_html';
	
	
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
	
	public function __construct() {
		$gesLugares = new GestorLugar();
		$this->aLugares = $gesLugares->getArrayLugares();
		
		$this->dom = new \DOMDocument('1.0', 'utf-8');
	}
	
	public function setPayload($oEscrito,$tipo_escrito) {
		if ($tipo_escrito == 'escrito') {
			$this->setPayloadEscrito($oEscrito);
		}
		if ($tipo_escrito == 'entrada') {
			$this->setPayloadEntrada($oEscrito);
		}
	}
	public function setPayloadEntrada($oEntrada) {
		
		$this->json_prot_local = $oEscrito->getJson_prot_local();
		// OJO hay que coger el destino que se tiene al enviar, 
		// no el del escrito, que puede ser a varios o un grupo.
		//$this->json_prot_dst = $oEscrito->getJson_prot_destino();
		
		$this->json_prot_ref = $oEscrito->getJson_prot_ref();
		
		$this->setF_entrada($oEscrito->getF_escrito());
		$this->setF_escrito($oEscrito->getF_escrito());
		$this->setF_salida($oEscrito->getF_salida());
		$this->setF_contestar($oEscrito->getF_contestar());
		
		$this->setAsunto($oEscrito->getAsunto());
		$this->setId_escrito($oEscrito->getId_escrito());
		$this->setVisibilidad($oEscrito->getVisibilidad_dst());

		$this->setA_id_adjuntos($oEscrito->getArrayIdAdjuntos());
		
		$this->nombre_escrito = $oEscrito->getNombreEscrito() . '.xml';
	}

	public function setPayloadEscrito($oEscrito) {
		
		$this->json_prot_local = $oEscrito->getJson_prot_local();
		// OJO hay que coger el destino que se tiene al enviar, 
		// no el del escrito, que puede ser a varios o un grupo.
		//$this->json_prot_dst = $oEscrito->getJson_prot_destino();
		
		$this->json_prot_ref = $oEscrito->getJson_prot_ref();
		
		$this->setF_entrada($oEscrito->getF_escrito());
		$this->setF_escrito($oEscrito->getF_escrito());
		$this->setF_salida($oEscrito->getF_salida());
		$this->setF_contestar($oEscrito->getF_contestar());
		
		$this->setAsunto($oEscrito->getAsunto());
		$this->setId_escrito($oEscrito->getId_escrito());
		$this->setVisibilidad($oEscrito->getVisibilidad_dst());

		$this->setA_id_adjuntos($oEscrito->getArrayIdAdjuntos());
		
		$this->nombre_escrito = $oEscrito->getNombreEscrito() . '.xml';
	}

	/*
	<PartInfo containment="body" location="payloads/simple_document.xml">
	  <PartProperties>
	    <Property name="original-file-name">simple_document.xml</Property>
	  </PartProperties>
	</PartInfo>
	<PartInfo containment="attachment" mimeType="image/jpeg" location="payloads/summerflower.jpg"/>
	*/
	public function getXml($dom) {
		$this->payload = $dom->createElement("PayloadInfo");
		$attr = new \DOMAttr('deleteFilesAfterSubmit',$this->deleteFilesAfterSubmit);
		$this->payload->setAttributeNode($attr);
		
		$location = "payloads/$this->nombre_escrito";
		
		$part_info = $dom->createElement('PartInfo');
		$attr_1 = new \DOMAttr('containment',"body");
		$part_info->setAttributeNode($attr_1);
		$attr_2 = new \DOMAttr('location',$location);
		$part_info->setAttributeNode($attr_2);
		
		$part_properties = $dom->createElement('PartProperties');
		$element_property = $dom->createElement('Property', $this->nombre_escrito);
		$attr_name = new \DOMAttr('name',"original-file-name");
		$element_property->setAttributeNode($attr_name);
		$part_properties->appendChild($element_property);
		
		$part_info->appendChild($part_properties);
		
		$this->payload->appendChild($part_info);
		
		return $this->payload;
	}
	
	public function createXmlFile() {
		$this->dom = new \DOMDocument('1.0', 'UTF-8');
		
		$this->escrito = $this->dom->createElement("escrito");
	
		$this->escrito->appendChild($this->getXmlProt_dst());
		$this->escrito->appendChild($this->getXmlProt_org());
		$this->escrito->appendChild($this->getXmlProt_ref());
		$this->escrito->appendChild($this->getXmlF_entrada());
		$this->escrito->appendChild($this->getXmlF_escrito());
		$this->escrito->appendChild($this->getXmlF_salida());
		$this->escrito->appendChild($this->getXmlF_contestar());
		$this->escrito->appendChild($this->getXmlAsunto());
		$this->escrito->appendChild($this->getXmlContent());
		$this->escrito->appendChild($this->getXmlVisibilidad());
		$this->escrito->appendChild($this->getXmlAdjuntos());
		
		$this->dom->preserveWhiteSpace = false;
		$this->dom->formatOutput = true;
		
		$this->dom->appendChild($this->escrito);
		
		if ( $this->dom->save($this->getFullFilename()) === FALSE) {
			exit ("Error al guardar el excrito en xml");
		}
	}
	
	// ---------------------------------------
	
	private function getFullFilename() {
		$dir = $_SESSION['oConfig']->getDock();
		return $dir .'/data/msg_out/payloads/'. $this->nombre_escrito;
	}
	
	/**
	 * 
	 * @param object $oProt
	 * @param string $tipo 'destino'|'origen'|'referencia'
	 */
	private function prot2xml($aProt, $tipo) {
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
			$name_nodo_array = $name_nodo.'s';
			$nodo_array = $this->dom->createElement($name_nodo_array);
			foreach ($aProt as $oProt) {
				$nombre_nodo = substr($name_nodo,5);
				$nodo = $this->explodeProt($oProt, $nombre_nodo, $sufijo);
				$nodo_array->appendChild($nodo);
			}
			return $nodo_array;
		} else {
			return $this->explodeProt($aProt, $name_nodo, $sufijo);
		}
	}
	
	private function explodeProt($oProt, $nombre_nodo, $sufijo) {
		$nodo = $this->dom->createElement($nombre_nodo);
		
		if (empty((array) $oProt) || !property_exists($oProt, 'lugar')) {
			return $nodo;
		}
		
		$id_lugar = $oProt->lugar;
		$lugar = empty($this->aLugares[$id_lugar])? '' : $this->aLugares[$id_lugar];
		$num = $oProt->num;
		$any = $oProt->any;
		$mas = $oProt->mas;
		
		// No se admiten propiedades vacias: No se incluyen.
		if (!empty($lugar)) {
			$nombre = "lugar_$sufijo";
			$nodo->appendChild($this->dom->createElement($nombre, $lugar));
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
	public function getXmlProt_dst() {
		$oProt = $this->json_prot_dst;
		return $this->prot2xml($oProt, 'dst');
	}
	/**
	 * @param mixed $prot_dst
	 */
	public function setJson_prot_dst($json_prot_dst) {
		$this->json_prot_dst = $json_prot_dst;
	}


	/**
	 * @return mixed
	 */
	public function getXmlProt_org() {
		$oProt = $this->json_prot_local;
		return $this->prot2xml($oProt, 'org');
	}

	/**
	 * @param mixed $prot_org
	 */
	public function setProt_org($prot_org) {
		$this->prot_org = $prot_org;
	}

	/**
	 * @return mixed
	 */
	public function getXmlProt_ref() {
		$oProt = $this->json_prot_ref;
		return $this->prot2xml($oProt, 'ref');
	}

	/**
	 * @param mixed $prot_ref
	 */
	public function setProt_ref($prot_ref) {
		$this->prot_ref = $prot_ref;
	}

	/**
	 * @return mixed
	 */
	public function getXmlF_entrada() {
		$f_iso = $this->f_entrada->getIso();
		return $this->dom->createElement('f_entrada',$f_iso);
	}

	/**
	 * @param mixed $f_entrada
	 */
	public function setF_entrada($f_entrada) {
		$this->f_entrada = $f_entrada;
	}

	/**
	 * @return mixed
	 */
	public function getXmlF_escrito() {
		$f_iso = $this->f_escrito->getIso();
		return $this->dom->createElement('f_escrito',$f_iso);
	}

	/**
	 * @param mixed $f_escrito
	 */
	public function setF_escrito($f_escrito) {
		$this->f_escrito = $f_escrito;
	}


	/**
	 * @return mixed
	 */
	public function getXmlF_salida() {
		$f_iso = $this->f_salida->getIso();
		return $this->dom->createElement('f_salida',$f_iso);
	}

	/**
	 * @param mixed $f_salida
	 */
	public function setF_salida($f_salida) {
		$this->f_salida = $f_salida;
	}

	/**
	 * @return mixed
	 */
	public function getXmlF_contestar() {
		$f_iso = $this->f_contestar->getIso();
		return $this->dom->createElement('f_contestar',$f_iso);
	}

	/**
	 * @param mixed $f_contestar
	 */
	public function setF_contestar($f_contestar) {
		$this->f_contestar = $f_contestar;
	}

	/**
	 * @return mixed
	 */
	public function getXmlAsunto() {
		return $this->dom->createElement('asunto',$this->asunto);
	}

	/**
	 * @param mixed $asunto
	 */
	public function setAsunto($asunto) {
		$this->asunto = $asunto;
	}

	/**
	 * @return mixed
	 */
	public function getXmlContent() {
		$oEtherpad = new Etherpad();
		$oEtherpad->setId (Etherpad::ID_ESCRITO,$this->id_escrito);
		
		switch ($this->getFormat()) {
			case 'pdf':
				exit ('falta para pdf');
				return $oEtherpad->generarPDF($a_header,$f_salida);
			case Payload::TYPE_ETHERAD_TXT:
			case 'txt':
				$txt = $oEtherpad->generarMD();
				$contenido_encoded = base64_encode($txt);
				$content = $this->dom->createElement('content',$contenido_encoded);
				$attr = new \DOMAttr('type', self::TYPE_ETHERAD_TXT);
				$content->setAttributeNode($attr);
				return $content;
				break;
			case Payload::TYPE_ETHERAD_HTML:
			case 'html':
				$txt = $oEtherpad->generarHtml();
				$contenido_encoded = base64_encode($txt);
				$content = $this->dom->createElement('content',$contenido_encoded);
				$attr = new \DOMAttr('type', self::TYPE_ETHERAD_HTML);
				$content->setAttributeNode($attr);
				return $content;
				break;
			default:
				$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
				exit ($err_switch);
		}
	}

	/**
	 * @param mixed $content
	 */
	public function setId_escrito($id_escrito) {
		$this->id_escrito = $id_escrito;
	}

	/**
	 * @return mixed
	 */
	public function getBypass() {
		return $this->bypass;
	}

	/**
	 * @param mixed $bypass
	 */
	public function setBypass($bypass) {
		$this->bypass = $bypass;
	}

	/**
	 * @return mixed
	 */
	public function getXmlVisibilidad() {
		return $this->dom->createElement('visibilidad',$this->visibilidad);
	}

	/**
	 * @param mixed $visibilidad
	 */
	public function setVisibilidad($visibilidad) {
		$this->visibilidad = $visibilidad;
	}

	/**
	 * @return mixed
	 */
	public function getXmlAdjuntos() {
		$a_adjuntos = [];
		foreach ($this->a_id_adjuntos as $item => $adjunto_filename) {
			$oEscritoAdjunto = new EscritoAdjunto($item);
			$tipo_doc = $oEscritoAdjunto->getTipo_doc();
			switch ($tipo_doc) {
				case Documento::DOC_UPLOAD:
					$escrito_txt = $oEscritoAdjunto->getAdjunto();
					$a_adjuntos[$adjunto_filename] = $escrito_txt;
					break;
				case Documento::DOC_ETHERPAD:
					$id_adjunto = $oEscritoAdjunto->getId_item();
					$oEtherpadAdj = new Etherpad();
					$oEtherpadAdj->setId (Etherpad::ID_ADJUNTO,$id_adjunto);
					$escrito_txt = $oEtherpadAdj->generarPDF();
					$a_adjuntos[$adjunto_filename] = $escrito_txt;
					break;
				default:
					$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
					exit ($err_switch);
			}
		}
		
		$nodo_array = $this->dom->createElement('adjuntos');
		
		if (!empty($this->a_id_adjuntos)) {
			// probar multipart:
			$mime = $this->getMIMEMultiPart($a_adjuntos);
			$nodo = $this->dom->createElement('adjunto',$mime);
			$nodo_array->appendChild($nodo);
		}
		
		return $nodo_array;
	}

	/**
	 * @param mixed $a_id_adjuntos
	 */
	public function setA_id_adjuntos($a_id_adjuntos) {
		$this->a_id_adjuntos = $a_id_adjuntos;
	}

	private function getMIMEMultiPart($a_adjuntos) {
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
	/**
	 * @return mixed
	 */
	public function getFormat() {
		$formato = empty($this->format)? 'md' : $this->format;
		return strtolower($formato);
	}

	/**
	 * @param mixed $format
	 */
	public function setFormat($format) {
		$this->format = $format;
	}
	/**
	 * @return boolean
	 */
	public function getDeleteFilesAfterSubmit() {
		return $this->deleteFilesAfterSubmit;
	}

	/**
	 * @param boolean $deleteFilesAfterSubmit
	 */
	public function setDeleteFilesAfterSubmit($deleteFilesAfterSubmit) {
		if (is_true($deleteFilesAfterSubmit)) {
			$this->deleteFilesAfterSubmit = 'true';
		} else {
			$this->deleteFilesAfterSubmit = 'false';
		}
	}



}