<?php
namespace tramites\model\entity;
use core;
/**
 * GestorFirma
 *
 * Classe per gestionar la llista d'objectes de la clase Firma
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 19/6/2020
 */

class GestorFirma Extends core\ClaseGestor {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */


	/**
	 * Constructor de la classe.
	 *
	 * @return $gestor
	 *
	 */
	function __construct() {
		$oDbl = $GLOBALS['oDBT'];
		$this->setoDbl($oDbl);
		$this->setNomTabla('expediente_firmas');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/

	/**
	 * devuelve el objeto Firma. El primero que tiene que firmar el expediente.
	 * Al ponerlo a circular, si soy el primero, lo firmo directamente.
	 *  
	 * @param integer $id_expediente
	 * @return object $oFirma
	 */
	public function getPrimeraFirma($id_expediente) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
        // posibles orden_tramite:
        $sQuery = "SELECT * 
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente
                    ORDER BY orden_tramite, orden_oficina DESC LIMIT 1";
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorFirma.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		// el primero es el actual, el segundo (si existe) es el anterior.
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oFirma = new Firma($a_pkey);
			$oFirma->setAllAtributes($aDades);
		}
		return $oFirma;
	}
	
	/**
	 * Comprobar si el bloque de orden_tramite anterior està todo firmado.
	 * 
	 * @param integer $id_expediente
	 * @param integer $orden_tramite
	 * @return boolean 
	 */
    public function getAnteriorOK($id_expediente,$orden_tramite) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
        // posibles orden_tramite:
        $sQuery = "SELECT DISTINCT orden_tramite 
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND orden_tramite <= $orden_tramite
                    ORDER BY orden_tramite DESC";
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorFirma.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		// el primero es el actual, el segundo (si existe) es el anterior.
		$i = 0;
		$num = [];
		foreach ($oDbl->query($sQuery) as $aDades) {
		      $i++;
		      $num[$i] = $aDades['orden_tramite'];
		}
		if (empty($num[2])) { // No existe, el primero es el actual: ok
		    return TRUE;
		} else {
		    $orden_anterior = $num[2];
            $sQuery = "SELECT *
                        FROM $nom_tabla
                        WHERE id_expediente = $id_expediente AND orden_tramite = $orden_anterior
                        ";
            if (($oDbl->query($sQuery)) === FALSE) {
                $sClauError = 'GestorFirma.query';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            // Contar que todos sean ok:
            foreach ($oDbl->query($sQuery) as $aDades) {
                $tipo = $aDades['tipo'];
                $valor = $aDades['valor'];
                /*
                const TIPO_VOTO          = 1;
                const TIPO_ACLARACION    = 2;
                // valor
                const V_VISTO        = 1;  // leído, pensando
                const V_ESPERA       = 2;  // distinto a no leído
                const V_NO           = 3;  // voto negativo
                const V_OK           = 4;  // voto positivo
                const V_DILATA       = 22;  // sólo vcd
                const V_RECHAZADO    = 23;  // sólo vcd
                const V_VISTO_BUENO  = 24;  // sólo vcd VºBº
                */
                if ($tipo == FIRMA::TIPO_ACLARACION) {
                    return FALSE;
                }
                if ($valor == Firma::V_NO OR $valor == Firma::V_OK) {
                } else {
                    return FALSE;
                }
            }
            return TRUE;
		}
    }


	/**
	 * retorna l'array d'objectes de tipus Firma
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus Firma
	 */
	function getFirmasQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oFirmaSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorFirma.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oFirma= new Firma($a_pkey);
			$oFirma->setAllAtributes($aDades);
			$oFirmaSet->add($oFirma);
		}
		return $oFirmaSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus Firma
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus Firma
	 */
	function getFirmas($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oFirmaSet = new core\Set();
		$oCondicion = new core\Condicion();
		$aCondi = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp == '_ordre') continue;
			if ($camp == '_limit') continue;
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) $aCondi[]=$a;
			// operadores que no requieren valores
			if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') unset($aWhere[$camp]);
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') unset($aWhere[$camp]);
            if ($sOperador == 'TXT') unset($aWhere[$camp]);
		}
		$sCondi = implode(' AND ',$aCondi);
		if ($sCondi!='') $sCondi = " WHERE ".$sCondi;
		$sOrdre = '';
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
		if (isset($aWhere['_ordre'])) unset($aWhere['_ordre']);
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') $sLimit = ' LIMIT '.$aWhere['_limit'];
		if (isset($aWhere['_limit'])) unset($aWhere['_limit']);
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorFirma.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorFirma.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_item' => $aDades['id_item']);
			$oFirma = new Firma($a_pkey);
			$oFirma->setAllAtributes($aDades);
			$oFirmaSet->add($oFirma);
		}
		return $oFirmaSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
