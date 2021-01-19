<?php
namespace web;

use core\ConfigGlobal;
/**
 * Classe per les dates. Afageix a la clase del php la vista amn num. romans.
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2010
 */
class NullDateTimeLocal Extends \DateTime {
	private $oData;

	public static function Meses() {
		$aMeses = array('1'=>_("enero"),
			'2'=>_("febrero"),
			'3'=>_("marzo"),
			'4'=>_("abril"),
			'5'=>_("mayo"),
			'6'=>_("junio"),
			'7'=>_("julio"),
			'8'=>_("agosto"),
			'9'=>_("septiembre"),
			'10'=>_("octubre"),
			'11'=>_("noviembre"),
			'12'=>_("diciembre")	
		);
		return $aMeses;
	}
	static private function getFormat() {
	    $idioma = $_SESSION['session_auth']['idioma'];
	    # Si no hemos encontrado ningún idioma que nos convenga, mostramos la web en el idioma por defecto
	    if (!isset($idioma)){ $idioma = $_SESSION['oConfig']->getIdioma_default(); }  
		$a_idioma = explode('.',$idioma);
		$code_lng = $a_idioma[0];
		//$code_char = $a_idioma[1];
	    switch ($code_lng) {
	        case 'en_US':
	            $format = 'm/j/Y';
	            break;
	        default:
	            $format = 'j/m/Y';
	    }
	    return $format;
	}
	
	static public function createFromLocal($data) {
        return '';
		$format = self::getFormat();
		
	    $extnd_dt = new static();
	    $parent_dt = parent::createFromFormat($format,$data);
	    
	    if (!$parent_dt) {
	        return false;
	    }
	    
	    $extnd_dt->setTimestamp($parent_dt->getTimestamp());
	    /* corregir en el caso que el año tenga dos digitos 
	     * No sirve para el siglo I (0-99) ;-) */
	    $yy = $extnd_dt->format('y');
	    $yyyy = $extnd_dt->format('Y');
	    if (($yyyy - $yy) == 0) {
	        $currentY4 = date('Y');
	        $currentY2 = date('y');
	        $currentMilenium = $currentY4 - $currentY2;
	        
	        $extnd_dt->add(new \DateInterval('P'.$currentMilenium.'Y'));
	    }
	    
		return $extnd_dt;
	}
	
	public function getFromLocal() {
        return '';
	}  
	
	public function getIsoTime() {
	    return '';
	}
	public function getIso() {
	    return '';
	}
	
	public function getFromLocalHora() {
	    return '';
	}

	static public function createFromFormat($format,$data, \DateTimeZone $TimeZone=NULL) {
        return '';
	    $extnd_dt = new static();
	    $parent_dt = parent::createFromFormat($format,$data,$TimeZone);
	    
	    if (!$parent_dt) {
	        return false;
	    }
	    $extnd_dt->setTimestamp($parent_dt->getTimestamp());
		return $extnd_dt;
	}

    public function format($format) {
        return '';
    }
    public function formatRoman() {
        return '';
    }

    public function duracion($oDateDiff) {
        return '';
	}
    public function duracionAjustada($oDateDiff) {
        return '';
	}
}
