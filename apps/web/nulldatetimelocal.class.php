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

	static public function createFromLocal($data) {
        return '';
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
