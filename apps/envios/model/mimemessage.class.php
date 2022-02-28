<?php
namespace envios\model;

class MIMEmessage extends MIMEContainer {
	
	public function create() {
		
		
		$addheaders = (is_array($this->add_header)) ? implode($this->add_header, "\r\n") : '';
		
		$headers  = "Content-Type: {$this->content_type}\r\n";
		$headers .= "Content-Transfer-Encoding: {$this->content_enc}\r\n$addheaders\r\n";
		$headers .= $this->content."\r\n";
		
		return $headers;
		
	}
	
}
