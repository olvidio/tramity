<?php
namespace oasis_as4\model;

use web\DateTimeLocal;
use function core\any_2;

class As4SignalMessage {
	
	private $xmldata;
	
	private $signalmessage;
	
	private $messageinfo;
	private $timestamp;
	private $message_id;
	private $ref_to_message_id;

	private $receipt;
	private $receipt_child;
	
	private $error;
	private $error_detail;
	private $errorCode;
	private $severity;
	private $origin;
	private $category;
	private $refToMessageInError;
	private $shortDescription;
	
	public function __construct($filename) {
		$content = file_get_contents($filename);
		
		$this->xmldata = new \DOMDocument();
		$this->xmldata->loadXML($content);
		
		$this->explotar_xml();
	}
	
	
	public function getErrorRef_to_messsage() {
		return $this->refToMessageInError;
	}
	
	public function getErrorDetail() {
		return $this->error_detail;
	}
	
	public function getError() {
		return !empty($this->error);
	}

	public function getTimeStamp() {
		$f_timestamp = $this->timestamp;
		return new DateTimeLocal($f_timestamp);
	}
	
	private function explotar_xml() {
		// namespace:
		//<eb3:Messaging xmlns:eb3="http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/">
		$url = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/';
		
		foreach ($this->xmldata->getElementsByTagNameNS($url, '*') as $element) {
			//echo 'local name: ', $element->localName, ', value: ', $element->nodeValue,  ', prefix: ', $element->prefix, "<br>";
			
			switch ($element->localName) {
				case 'Timestamp':
					$this->timestamp = $element->nodeValue;
					break;
				case 'MessageId':
					$this->message_id = $element->nodeValue;
					break;
				case 'RefToMessageId':
					$this->ref_to_message_id = $element->nodeValue;
					break;
				case 'Receipt':
					$this->receipt = $element->nodeValue;
					break;
				case 'MessageInfo':
					$this->messageinfo = $element->nodeValue;
					break;
				case 'SignalMessage':
					$this->signalmessage = $element->nodeValue;
					break;
				case 'Messaging':
					//$this->me = $element->nodeValue;
					break;
				case 'Error':
					$this->error = $element->nodeValue;
					$this->refToMessageInError = $element->getAttribute('refToMessageInError');
					$this->errorCode = $element->getAttribute('errorCode');
					$this->severity = $element->getAttribute('severity');
					$this->origin = $element->getAttribute('origin');
					$this->category = $element->getAttribute('category');
					$this->shortDescription = $element->getAttribute('shortDescription');
					break;
				case 'ErrorDetail':
					$this->error_detail = $element->nodeValue;
					break;
				default:
					$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
					exit ($err_switch);
			}
		}
	}
	
	/*
	 * deberia conseguir el protocolo, para notificar
	 */
	public function getInfoMessage($message_id) {
		$a_rta = [];
		$a_message_id = explode('@',$message_id);
		$a_rta['dst'] = empty($a_message_id[0])? '' : $a_message_id[0];
		if (!empty($a_message_id[1])) {
			$a_prot = explode('-',$a_message_id[1]);
			$nom_lugar = empty($a_prot[0])? '' : $a_prot[0];
			$any = empty($a_prot[1])? '' : $a_prot[1];
			$num = empty($a_prot[2])? '' : $a_prot[2];
			$prot = $nom_lugar . ' ' . $num . '/' . any_2($any);
			$a_rta['prot_org'] = $prot;
		} else {
			$a_rta['prot_org'] = '';
		}
		$a_rta['id_escrito'] = empty($a_message_id[2])? '' : $a_message_id[2];
		
		return $a_rta;
	}
}