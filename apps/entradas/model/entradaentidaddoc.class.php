<?php
namespace entradas\model;

use core\ConfigDB;
use core\dbConnection;
use entradas\model\entity\EntradaDocDB;
use web\StringLocal;

class EntradaEntidadDoc Extends EntradaDocDB {
	
	/**
	 * Constructor de la classe.
	 * Se l'hi ha de dir a quin esquema s'ha de conectar.
	 *
	 * @param string	$entidad. Nombre de la entidad donde hay que crear la entradaDocDB.
	 */
	function __construct($a_id, $entidad) {
		// El nombre del esquema es en minúsculas porque si se accede via nombre del
		// servidor, éste está en minúscula (agdmontagut.tramity.local)
		// http://www.ietf.org/rfc/rfc2616.txt: Field names are case-insensitive.
		$schema = strtolower($entidad);
		// tambien lo normalizo:
		$schema = StringLocal::toRFC952($schema);
		
		$oConfigDB = new ConfigDB('tramity'); //de la database comun
		$config = $oConfigDB->getEsquema($schema);
		$oConexion = new dbConnection($config);
		$oDbl = $oConexion->getPDO();
		
		if (is_array($a_id)) {
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
				if (($nom_id == 'id_entrada') && $val_id !== '') { $this->iid_entrada = (int)$val_id; } // evitem SQL injection fent cast a integer
			}
		} else {
			if (isset($a_id) && $a_id !== '') {
				$this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
				$this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
			}
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('entrada_doc');
	}
	
}