<?php
namespace oasis_as4\model;

/*
 * IMPORTANTE:
 * 
 * Para conocer como generar los xml, mirar las definiciones xsd del holodeck en:
 *	 	/holodeckb2b/repository/xsd
 *
 */
class As4Remove {
	
	private $xmldata;
	
    private $dor_dock;
    private $location;
    
    
    public function __construct() {
		$dir = $_SESSION['oConfig']->getDock();
		$this->dir_dock = $dir . '/data/msg_out';
    }
    
    private function getFiles() {
		
		$a_scan = scandir($this->dir_dock);
		$a_files = array_diff($a_scan, ['.','..']);
		
		// mensajes de accepted
		$a_files_accepted = [];
		foreach ($a_files as $filename) {
			$matches = [];
			$pattern = "/(.*)\.accepted/";
			
			if (preg_match($pattern, $filename, $matches)) {
				$a_files_accepted[] = $this->dir_dock.'/'.$matches[0];
			}
		}
		
		return $a_files_accepted;
    }
    
    public function remove_accepted() {
		// cada mensaje que llega hay que descomponer y borrar los payloads.
		$a_files_accepted = $this->getFiles();
		$txt = '';
		foreach ($a_files_accepted as $file_accepted) {
			$this->xmldata = simplexml_load_file($file_accepted);
				
			$location = $this->getLocation();
			
			if (!empty($location)) {
				if (unlink($location) === FALSE) {
					$txt .= sprintf(_("No se ha podido eliminar el fichero %s"), $location);
				}
			}
			// el mensaje
			if (unlink($file_accepted) === FALSE) {
				$txt .= sprintf(_("No se ha podido eliminar el mensaje %s"), $file_accepted);
			}
		}
		
		return $txt;
    }
    
    private function getPayload() {
    	$payload = $this->xmldata->PayloadInfo;
    	//$containment = $payload->PartInfo->attributes()->containment;
    	//$filename = $payload->PartInfo->PartProperties->property;
    	$location = $payload->PartInfo->attributes()->location;
		$location = $this->dir_dock.'/'.$location;
    	$this->setLocation($location);
    }
    
    /**
     * @return mixed
     */
    public function getLocation() {
    	$this->getPayload();
    	
    	return $this->location;
    }
    
    /**
     * @param mixed $location
     */
    public function setLocation($location) {
    	$this->location = $location;
    }
}