<?php
namespace config\model\entity;
use core;
/**
 * Fitxer amb la Classe que accedeix a la taula x_config_schema
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */
/**
 * Classe que implementa l'entitat x_config_schema
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */
class ConfigSchemaPublic Extends ConfigSchema {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/* ATRIBUTS QUE NO SÓN CAMPS------------------------------------------------- */
    
	/* CONSTRUCTOR -------------------------------------------------------------- */

	/**
	 * Constructor de la classe.
	 * Si només necessita un valor, se li pot passar un integer.
	 * En general se li passa un array amb les claus primàries.
	 *
	 * Parametros comunes a todo el servidor: para todos los esquemas
	 * 
	 * @param integer|array sparametro
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */
	function __construct($a_id='') {
		$oDbl = $GLOBALS['oDBP'];
		if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
			    if (($nom_id == 'parametro') && $val_id !== '') {
			        $this->sparametro = (string)$val_id; // evitem SQL injection fent cast a string
			    }
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->sparametro = $a_id;
				$this->aPrimary_key = array('parametro' => $this->sparametro);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('x_config');
	}

}