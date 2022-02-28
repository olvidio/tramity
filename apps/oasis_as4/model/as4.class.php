<?php
namespace oasis_as4\model;

use DOMAttr;
use lugares\model\entity\GestorLugar;
use web\Protocolo;

class As4 extends As4CollaborationInfo {
    
    /**
     *
     * @var object
     */
    private $dom;
    
    private $json_prot_org;
    private $json_prot_dst;
    
    /**
     *
     * @var object
     */
    private $oEscrito;
    
    
    public function __construct() {

        /* create a dom document with encoding utf8 */
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }
    
    public function writeOnDock($filename) {
    	$err_txt = '';
        $dir = $_SESSION['oConfig']->getDock();
        $full_filename = $dir .'/data/msg_out/'. $filename .'.mmd';
        
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        
        $this->dom->appendChild($this->getMessageMetaData());
        
        if ( $this->dom->save($full_filename) === FALSE) {
            $err_txt .= _("Error al escribir el as4.xml");
        }
        return $err_txt;
    }
    
    
    public function getMessageMetaData() {
    	// crear el nodo:
    	$message_meta_data = $this->dom->createElement("MessageMetaData");
    	$attr_1 = new DOMAttr('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
    	$message_meta_data->setAttributeNode($attr_1);
    	$attr_1 = new DOMAttr('xsi:schemaLocation',"http://holodeck-b2b.org/schemas/2014/06/mmd ../repository/xsd/messagemetadata.xsd");
    	$message_meta_data->setAttributeNode($attr_1);
    	
    	$attr_1 = new DOMAttr('xmlns',"http://holodeck-b2b.org/schemas/2014/06/mmd");
    	$message_meta_data->setAttributeNode($attr_1);
    	
    	// añadir subnodos
    	$message_meta_data->appendChild($this->getCollaborationInfo());
    	$message_meta_data->appendChild($this->getPayloadInfo());
    	$message_meta_data->appendChild($this->getMessageProperties());
    	
    	return $message_meta_data;
    }

    public function getCollaborationInfo() {
    	$pm_id = $this->getPm_id();
        // crear el nodo:
        $colaborador_info = $this->dom->createElement("CollaborationInfo");
        
        $agreement = $this->dom->createElement('AgreementRef');
        $attr = new DOMAttr('pmode', $pm_id);
        $agreement->setAttributeNode($attr);
        
        $colaborador_info->appendChild($agreement);

		$json_prot_origen = $this->getJson_prot_org();
        $oProtOrigen = new Protocolo();
        $oProtOrigen->setLugar($json_prot_origen->lugar);
        $oProtOrigen->setProt_num($json_prot_origen->num);
        $oProtOrigen->setProt_any($json_prot_origen->any);
        $oProtOrigen->setMas($json_prot_origen->mas);
        $conversation_id = $oProtOrigen->conversation_id();
        
        $conversation = $this->dom->createElement('ConversationId', $conversation_id);
        $colaborador_info->appendChild($conversation);
        
        return $colaborador_info;
    }
    
    public function getPayloadInfo() {
        
    	$oPayload = new Payload();
    	$oPayload->setJson_prot_dst($this->json_prot_dst);
    	
    	$oPayload->setPayload($this->oEscrito);
    	// formato del texto: pdf|text|html
    	$oPayload->setFormat(Payload::TYPE_ETHERAD_HTML);
    	$oPayload->createXmlFile();
    	
    	return $oPayload->getXml($this->dom);
    }
    
    /**
     * 
    <eb:MessageProperties>
	<eb:Property name="lugar_org">ctragdMontagut</eb:Property>
	<eb:Property name="num_org">3</eb:Property>
	<eb:Property name="any_org">21</eb:Property>
	<eb:Property name="mas_org">a)</eb:Property>
	<eb:Property name="lugar_dst">dlb</eb:Property>
	<eb:Property name="num_dst">355</eb:Property>
	<eb:Property name="any_dst">21</eb:Property>
	<eb:Property name="mas_dst">a)</eb:Property>
    </eb:MessageProperties>
     */
    public function getMessageProperties() {
    	// tabla de siglas:
    	$gesLugares = new GestorLugar();
    	$aLugares = $gesLugares->getArrayLugares();
        // crear el nodo:
        $message_properties = $this->dom->createElement("MessageProperties");
        
        $json_prot_org = $this->getJson_prot_org();
        if (!empty((array)$json_prot_org)) {
            $id_lugar_org = $json_prot_org->lugar;
			$lugar_org = empty($aLugares[$id_lugar_org])? '' : $aLugares[$id_lugar_org];
            $num_org = $json_prot_org->num;
            $any_org = $json_prot_org->any;
            $mas_org = $json_prot_org->mas;
            
            // No se admiten propiedades vacias: No se incluyen.
            if (!empty($lugar_org)) {
            	$message_properties->appendChild($this->newPropertyName('lugar_org', $lugar_org));
            }
            if (!empty($num_org)) {
				$message_properties->appendChild($this->newPropertyName('num_org', $num_org));
            }
            if (!empty($any_org)) {
				$message_properties->appendChild($this->newPropertyName('any_org', $any_org));
            }
            if (!empty($mas_org)) {
				$message_properties->appendChild($this->newPropertyName('mas_org', $mas_org));
            }
        }

        // puede ser 'sin_numerar (E12)'
		$json_prot_dst = $this->getJson_prot_dst();
        if (!empty((array)$json_prot_dst)) {
            $id_lugar_dst = $json_prot_dst->lugar;
            $lugar_dst = empty($aLugares[$id_lugar_dst])? '' : $aLugares[$id_lugar_dst];
            $num_dst = $json_prot_dst->num;
            $any_dst = $json_prot_dst->any;
            $mas_dst = $json_prot_dst->mas;
        
            // No se admiten propiedades vacias: No se incluyen.
            if (!empty($lugar_dst)) {
				$message_properties->appendChild($this->newPropertyName('lugar_dst', $lugar_dst));
            }
            if (!empty($num_dst)) {
				$message_properties->appendChild($this->newPropertyName('num_dst', $num_dst));
            }
            if (!empty($any_dst)) {
				$message_properties->appendChild($this->newPropertyName('any_dst', $any_dst));
            }
            if (!empty($mas_dst)) {
				$message_properties->appendChild($this->newPropertyName('mas_dst', $mas_dst));
            }
        }
        
        return $message_properties;
    }
    
    private function newPropertyName($name, $value) {
		$element_property = $this->dom->createElement('Property', $value);
		$attr_name = new DOMAttr('name',$name);
		$element_property->setAttributeNode($attr_name);
		
		return $element_property;
    }
    
	/**
	 * @return mixed
	 */
	public function getJson_prot_org() {
		return $this->json_prot_org;
	}

	/**
	 * @return mixed
	 */
	public function getJson_prot_dst() {
		return $this->json_prot_dst;
	}

	/**
	 * @param mixed $json_prot_org
	 */
	public function setJson_prot_org($json_prot_org) {
		$this->json_prot_org = $json_prot_org;
	}

	/**
	 * @param mixed $json_prot_dst
	 */
	public function setJson_prot_dst($json_prot_dst) {
		$this->json_prot_dst = $json_prot_dst;
	}
	/**
	 * @return object
	 */
	public function getEscrito() {
		return $this->oEscrito;
	}

	/**
	 * @param object $oEscrito
	 */
	public function setEscrito($oEscrito) {
		$this->oEscrito = $oEscrito;
	}

    
}