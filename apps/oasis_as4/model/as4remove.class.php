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
	private $bLoaded=FALSE;	
	private $xmldata;
	
    private $dor_dock;
    private $location;
    
    private $a_files_accepted;
    private $a_files_rejected;
    
    
    public function __construct() {
		$dir = $_SESSION['oConfig']->getDock();
		$this->dir_dock = $dir . '/data/msg_out';
    }
    
    private function getFiles() {
    	if (!$this->bLoaded) {
			$a_scan = scandir($this->dir_dock);
			$a_files = array_diff($a_scan, ['.','..']);
			
			// mensajes de accepted
			$this->a_files_accepted = [];
			// mensajes de rejected
			$this->a_files_rejected = [];
			foreach ($a_files as $filename) {
				$matches = [];
				$pattern = "/(.*)\.accepted/";
				if (preg_match($pattern, $filename, $matches)) {
					$this->a_files_accepted[] = $this->dir_dock.'/'.$matches[0];
				}
				$pattern = "/(.*)\.rejected/";
				if (preg_match($pattern, $filename, $matches)) {
					$this->a_files_rejected[] = $this->dir_dock.'/'.$matches[0];
				}
			}
			$this->bLoaded = TRUE;
    	}
    }
    
    public function remove_rejected() {
		// cada mensaje buscar el error
		// y  el payload
		$this->getFiles();
		$txt = '';
		foreach ($this->a_files_rejected as $file_rejected) {
			$file_err = str_replace("rejected", "err", $file_rejected);
			$txt .= $this->getErrMsg($file_err);
			if (unlink($file_err) === FALSE) {
				$txt .= sprintf(_("No se ha podido eliminar el fichero %s"), $file_err);
			}
			
			$this->xmldata = simplexml_load_file($file_rejected);
				
			$location = $this->getLocation();
			if (!empty($location)) {
				if (unlink($location) === FALSE) {
					$txt .= sprintf(_("No se ha podido eliminar el fichero %s"), $location);
				}
			}
			// el mensaje
			if (unlink($file_rejected) === FALSE) {
				$txt .= sprintf(_("No se ha podido eliminar el mensaje %s"), $file_rejected);
			}
		}
		
		return $txt;
    }
    
    public function remove_accepted() {
		// cada mensaje que llega hay que descomponer y borrar los payloads.
		$this->getFiles();
		$txt = '';
		foreach ($this->a_files_accepted as $file_accepted) {
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
    	$location = $payload->PartInfo->attributes()->location;
		$location = $this->dir_dock.'/'.$location;
    	$this->setLocation($location);
    }
    
    private function getErrMsg($file_err) {
    	$txt = '';
    	$err = file_get_contents($file_err);
		$pattern = "/.*(Exception cause:.*\n).*/";
		$matches = [];
		if (preg_match($pattern, $err, $matches)) {
			$txt = _("Fichero").": $file_err<br>";
			$txt = $matches[1];
		}
		return $txt;
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