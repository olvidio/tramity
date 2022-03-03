<?php
namespace entradas\model;

use core\ConfigGlobal;
use entidades\model\entity\GestorEntidadesDB;
use entradas\model\entity\EntradaDocDB;
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;
use oasis_as4\model\As4CollaborationInfo;
use oasis_as4\model\Payload;
use web\DateTimeLocal;
use web\Protocolo;

class As4Distribuir extends As4CollaborationInfo {
	
	private $xmldata;
	
	
	private $location;
	private $sigla_destino;
	private $sigla_origen;

	private $service;
	private $xml_escrito;
	
	/**
	 * a_Prot_dst
	 * @var array
	 */
	private	$a_Prot_dst;
	/**
	 * a_Prot_dst
	 * @var array
	 */
	private	$a_Prot_org;
	/**
	 * a_Prot_dst
	 * @var array
	 */
	private	$a_Prot_ref;
	/**
	 * oF_entrada
	 * @var DateTimeLocal
	 */
	private	$oF_entrada;
	/**
	 * oF_escrito
	 * @var DateTimeLocal
	 */
	private	$oF_escrito;
	/**
	 * oF_contestar
	 * @var DateTimeLocal
	 */
	private	$oF_contestar;

	/**
	 * 
	 * @var string
	 */
	private	$asunto;
	/**
	 * 
	 * @var string
	 */
	private	$content;
	/**
	 * type del content. No lo llamo content-type para no confundir con el MIME
	 * 
	 * @var string
	 */
	private	$type;
	/**
	 * 
	 * @var integer
	 */
	private	$categoria;
	private	$bypass;
	
	/**
	 * 
	 * @var array
	 */
	private $a_adjuntos;
	
	
	public function __construct($xmldata) {
		$this->xmldata = $xmldata;
		$this->explotar_xml();
	}
	
	
	/**
	 * asignar la entrada a la entidad correspondiente
	 * Mirar a quien va dirigido y introducirlo en su BD
	 * 
	 */
	public function distribuir() {
		// comprobar que existe destino (sigla)
		if ( in_array($this->getSiglaDestino(), $this->getEntidadesPlataforma()) ) {
			// introducir los datos del mensaje en el tramity
			return $this->introducir();	
		}
	}
	
	/**
	 * Introducir en tramity.
	 * 
	 */
	public function introducir() {
		// service + accion: que hay que hacer
		if ($this->getService() == 'correo') {
			switch ($this->getAccion()) {
				case 'nuevo':
					$this->nuevo();
					break;
				case 'anular':
					break;
				case 'reemplazar':
					break;
				case 'eliminar':
					break;
				default:
					$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
					exit ($err_switch);
			}
			return TRUE;
		}
		
	}
	
	private function nuevo() {
		// hay que conectar con la entidad destino:
		$siglaDestino = $this->getSiglaDestino();
		
		
		$oEntrada = new EntradaEntidad($siglaDestino);
		$oEntrada->setModo_entrada(Entrada::MODO_MANUAL);
		$oEntrada->setJson_prot_origen($this->a_Prot_org);
		$oEntrada->setJson_prot_ref($this->a_Prot_ref);
		$oEntrada->setAsunto_entrada($this->asunto);
		$oHoy = new DateTimeLocal();
		$oEntrada->setF_entrada($oHoy);
		$oEntrada->setF_contestar($this->oF_contestar);
		$oEntrada->setCategoria($this->categoria);
		$oficina = ConfigGlobal::role_id_oficina();
		$oEntrada->setPonente($oficina);
		
		/*
		// 5º Compruebo si hay que generar un pendiente
		switch ($Qplazo) {
			case 'hoy':
				$oEntrada->setF_contestar('');
				break;
			case 'normal':
				$plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
				$periodo = 'P'.$plazo_normal.'D';
				$oF = new DateTimeLocal();
				$oF->add(new DateInterval($periodo));
				$oEntrada->setF_contestar($oF);
				break;
			case 'rápido':
				$plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
				$periodo = 'P'.$plazo_rapido.'D';
				$oF = new DateTimeLocal();
				$oF->add(new DateInterval($periodo));
				$oEntrada->setF_contestar($oF);
				break;
			case 'urgente':
				$plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
				$periodo = 'P'.$plazo_urgente.'D';
				$oF = new DateTimeLocal();
				$oF->add(new DateInterval($periodo));
				$oEntrada->setF_contestar($oF);
				break;
			case 'fecha':
				$oEntrada->setF_contestar($Qf_plazo);
				break;
			default:
				// Si no hay $Qplazo, No pongo ninguna fecha a contestar
		}
		*/
		
		$estado = Entrada::ESTADO_INGRESADO;
		$oEntrada->setEstado($estado);
		$oEntrada->setBypass($this->bypass);
		
		if ($oEntrada->DBGuardar() === FALSE ) {
			$error_txt .= $oEntrada->getErrorTxt();
		} else {
			$id_entrada = $oEntrada->getId_entrada();
			if (!empty($this->content)) {
				switch ($this->type) {
					case Payload::TYPE_ETHERAD_TXT:
						// guardar el texto del escrito
						$oEtherpad = new Etherpad();
						$oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
						$oEtherpad->setText($this->content);
						$oEtherpad->getPadId(); // Aqui crea el pad y utiliza el $this->content
						// la relacion con la entrada y la fecha
						$oEntradaDocDB = new EntradaEntidadDoc($id_entrada,$siglaDestino);
						$oEntradaDocDB->setF_doc($this->oF_escrito->getIso(),FALSE);
						$oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
						$oEntradaDocDB->DBGuardar();
						break;
					case Payload::TYPE_ETHERAD_HTML:
					case 'html':
						// guardar el texto del escrito
						$oEtherpad = new Etherpad();
						$oEtherpad->setId(Etherpad::ID_ENTRADA, $id_entrada, $siglaDestino);
						$pad_id = $oEtherpad->getPadId(); // Aqui crea el pad 
						$oEtherpad->setHTML($pad_id, $this->content);
						// la relacion con la entrada y la fecha
						$oEntradaDocDB = new EntradaEntidadDoc($id_entrada,$siglaDestino);
						$oEntradaDocDB->setF_doc($this->oF_escrito->getIso(),FALSE);
						$oEntradaDocDB->setTipo_doc(EntradaDocDB::TIPO_ETHERPAD);
						$oEntradaDocDB->DBGuardar();
						break;
					default:
						$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
						exit ($err_switch);
				}
			}
			// cargar los adjuntos una vez se ha creado la entrada y se tiene el id:
			if (!empty($this->a_adjuntos)) {
				$this->cargarAdjunto($this->a_adjuntos, $id_entrada);	
			}
		}
	}
	
	private function getProtocolos() {
		// consegir los protocolos origen y destino de las propiedades del mensaje:
		// MessageProperties
		$messageProperties = $this->xmldata->MessageProperties;
		
		$a_prot = [] ;
		$lugar_org = '';
		$num_org = '';
		$any_org = '';
		$mas_org = '';
		$lugar_dst = '';
		$num_dst = '';
		$any_dst = '';
		$mas_dst = '';
		foreach($messageProperties->children() as $node_property) {
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
			
		$this->sigla_origen = (string) $lugar_org;
		$this->sigla_destino = (string) $lugar_dst;
		// si no existen, hay que mirar dentro del mensaje
		return $a_prot;
	}
	
	public function getEntidadesPlataforma() {
		
		$gesEntidades = new GestorEntidadesDB(); 
		$cEntidades = $gesEntidades->getEntidadesDB(['anulado' => 'false']);
		$aEntidades = [];
		foreach ($cEntidades as $oEntidad) {
			$id = $oEntidad->getId_entidad();
			$nombre = $oEntidad->getNombre();
			
			$aEntidades[$id] = $nombre;
		}
		return $aEntidades;
	}
	
	private function explotar_xml() {
		// MessageProperties
		$this->getMessageProperties();
		
		// CollaborationInfo
		$this->getCollaborationInfo();
		
		// Papyload
		$this->getPayload();
	}
	
	private function getPayload() {
		$payload = $this->xmldata->PayloadInfo;
		//$containment = $payload->PartInfo->attributes()->containment;
		//$filename = $payload->PartInfo->PartProperties->property;
		$location = $payload->PartInfo->attributes()->location;
		$this->setLocation($location);

		$this->getEscrito($location);
	}
		
	private function getEscrito($location) {
		$this->xml_escrito = simplexml_load_file($location);
		
		$this->a_Prot_dst = $this->getProt_dst();
		$this->a_Prot_org = $this->getProt_org();
		$this->a_Prot_ref = $this->getProt_ref();
		// Si el destino tiene número de protocolo se añade a las referencias
		if (!empty((array) $this->a_Prot_dst)) {
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
		$this->categoria = $this->getCategoria();
		$this->a_adjuntos = $this->getAdjuntos();
		$this->bypass = $this->getByPass();
	}
	
	private function getType() {
		$nodo_content = $this->xml_escrito->content;
		foreach ($nodo_content->attributes() as $name => $value) {
			if ($name == 'type') {
				return (string) $value;
			}
		}
		// si no hay devolver 'html' por defecto
		return 'html';
	}
	
	private function getContent() {
		$contenido_encoded = (string) $this->xml_escrito->content;
		return base64_decode($contenido_encoded);
	}
	
	private function getByPass() {
		return (string) $this->xml_escrito->bypass;
	}
	
	private function getCategoria() {
		return (string) $this->xml_escrito->categoria;
	}
	
	private function getAsunto() {
		return (string) $this->xml_escrito->asunto;
	}
	
	private function getF_entrada() {
		$f_entrada_iso = $this->xml_escrito->f_entrada;
		return new DateTimeLocal($f_entrada_iso);
	}
	
	private function getF_escrito() {
		$f_escrito_iso = $this->xml_escrito->f_escrito;
		return new DateTimeLocal($f_escrito_iso);
	}
	
	private function getF_contestar() {
		$f_contesta_iso = $this->xml_escrito->f_contestar;
		return new DateTimeLocal($f_contesta_iso);
	}
	
	private function getProt_dst() {
		$xml_prot = $this->xml_escrito->prot_destino;
		return $this->xml2prot_simple($xml_prot, 'dst');
	}
	
	private function getProt_org() {
		$xml_prot = $this->xml_escrito->prot_origen;
		return $this->xml2prot_simple($xml_prot, 'org');
	}
	
	private function getProt_ref() {
		$xml_prot = $this->xml_escrito->prot_referencias;
		return $this->xml2prot_array($xml_prot, 'ref');
	}
	
	private function getCollaborationInfo() {
		$this->setService( (string) $this->xmldata->CollaborationInfo->Service);
		$this->setAccion( (string) $this->xmldata->CollaborationInfo->Action);
	}
	
	private function getMessageProperties() {
		return $this->getProtocolos();
	}
	
	private function getAdjuntos() {
		$xml_adjuntos = $this->xml_escrito->adjuntos;
		
		$aAdjuntos = [];
		foreach($xml_adjuntos->children() as $node) {
			$name = $node->getName();
			if ($name == 'adjunto') {
				$value = $node;
				
				$a_mime = $this->descomponerMime($value);
				foreach ($a_mime as $mime) {
					$aAdjuntos[] = $mime;
				}
			}
		}
		return $aAdjuntos;
	}

	private function descomponerMime($mime_txt) {
		
		$mime = mailparse_msg_create();
		mailparse_msg_parse($mime,$mime_txt);
		
		$structure = mailparse_msg_get_structure($mime);
		// chop message parts into array
		$parts = [];
		foreach ($structure as $s){
			$part = mailparse_msg_get_part($mime, $s);
			$part_data = mailparse_msg_get_part_data($part);
			$content_type = $part_data['content-type'];
			// no se coje el primero, que es el grupo:
			if ($content_type == 'multipart/mixed') { continue; }
			$starting_pos_body = $part_data['starting-pos-body'];
			$ending_pos_body    = $part_data['ending-pos-body'];
			$chunked_str = substr($mime_txt,$starting_pos_body,($ending_pos_body - $starting_pos_body)); // copy data into array
			$long_str = str_replace( "\r\n", "", $chunked_str );
			$parts[$s]['contenido'] = $long_str;
			$parts[$s]['filename'] = empty($part_data['content-filename'])? _("sin nombre") : $part_data['content-filename'];
		}
		mailparse_msg_free($mime);

		return $parts;
	}
	
	
	
	private function cargarAdjunto($a_adjuntos, $id_entrada){
		
		foreach ($a_adjuntos as $adjunto) {
			$doc_encoded = $adjunto['contenido'];
			$filename = $adjunto['filename'];
		
			$oEntradaAdjunto = new EntradaEntidadAdjunto($this->getSiglaDestino());
			$oEntradaAdjunto->setId_entrada($id_entrada);
			$oEntradaAdjunto->setNom($filename);
			$oEntradaAdjunto->setAdjunto(base64_decode($doc_encoded));
			
			$oEntradaAdjunto->DBGuardar();
		}
	}
	
	private function xml2prot_array($xml, $sufijo) {
		$a_json_prot = [];
		foreach($xml->children() as $node) {
			$a_json_prot[] = $this->xml2prot_simple($node, $sufijo);
		}
		
		return $a_json_prot;
	}
	
	private function xml2prot_simple($xml, $sufijo) {
		// tabla de siglas:
		$gesLugares = new GestorLugar();
		$aLugares = $gesLugares->getArrayLugares();
		
		$lugar = '';
		$ilugar = '';
		$num = '';
		$any = '';
		$mas = '';
		foreach($xml->children() as $node) {
			$name = $node->getName();
			$value = $node;

			if ($name == 'lugar_'.$sufijo) {
				$lugar = (string) $value;
				// pasarlo de texto al id correspondiente:
				$ilugar = array_search($lugar, $aLugares);
			}
			if ($name == 'num_'.$sufijo) {
				$num = (string) $value;
			}
			if ($name == 'any_'.$sufijo) {
				$any = (string) $value;
			}
			if ($name == 'mas_'.$sufijo) {
				$mas = (string) $value;
			}
		}
		
		if (empty($num)) {
			$any = '';
			$mas = '';
		}
		
		$oProtOrigen = new Protocolo($ilugar,$num,$any,$mas);
		return $oProtOrigen->getProt();
	}
	
	/**
	 * @return mixed
	 */
	public function getSiglaDestino() {
		return $this->sigla_destino;
	}
	
	/**
	 * @return string
	 */
	public function getService() {
		return $this->service;
	}

	/**
	 * @param string $service
	 */
	public function setService($service) {
		$this->service = strtolower($service);
	}
	/**
	 * @return mixed
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @param mixed $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}


}