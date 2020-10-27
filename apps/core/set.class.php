<?php
namespace core;

/**
 * Set
 *
 * Classe per a gestionar una col·lecció d'objectes.
 *
 * @package delegación
 * @subpackage model
 * @author 
 * @version 1.0
 * @created 22/9/2010
 */
class Set {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * getTot() Array de objetos
	 *
	 * @var array
	 */
	 private $aCollection = array();
	 private $count = 0;

	/* CONSTRUCTOR -------------------------------------------------------------- */

	function __construct() {
		// constructor buit
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	function add($oElement) {
		$this->aCollection[$this->count++]=$oElement;
	}

	function getTot() {
		return $this->aCollection;
	}
	function getElement($count) {
		return $this->aCollection[$count];
	}
	function setElement($count,$oElement) {
		$this->aCollection[$count]=$oElement;
	}

}