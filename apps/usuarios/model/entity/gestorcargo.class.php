<?php
namespace usuarios\model\entity;
use core;
use web\Desplegable;
/**
 * GestorCargo
 *
 * Classe per gestionar la llista d'objectes de la clase Cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 12/11/2020
 */

class GestorCargo Extends core\ClaseGestor {
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
		$this->setNomTabla('aux_cargos');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	public function getDirectorOficina($id_oficina) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                WHERE id_oficina=$id_oficina AND director = 't'
                ";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave=$aClave[0];
        }
        return $clave;
	}
	/**
	 * retorna un Array
	 * Els posibles cargos directors (per entrades)
	 *
	 * @return Array
	 */
	function getArrayCargosDirector() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                WHERE id_oficina > 0 AND director = 't'
                ORDER BY cargo";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones=array();
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave=$aClave[0];
            $val=$aClave[1];
            $aOpciones[$clave]=$val;
        }
        return $aOpciones;
	}
	
	/**
	 * retorna un Array
	 * Els posibles noms d'usuaris d'una oficina
	 *
	 * @param integer $id_oficina
	 * @return Array [id_cargo => nom_usuario]
	 */
	function getArrayUsuariosOficina($id_oficina='',$sin_cargo=FALSE) {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    //$Where = '';
	    $Where = "WHERE id_oficina > 0";
	    if (!empty($id_oficina)) {
	        $Where .= " AND id_oficina = $id_oficina";
	    }
	    $sQuery="SELECT id_cargo, id_usuario, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }

	    $a_usuarios_oficina = [];
        foreach ($oDbl->query($sQuery) as $aClave) {
            $id_cargo=$aClave[0];
            $id_usuario=$aClave[1];
            $cargo=$aClave[2];
            if (empty($id_usuario)) { continue; } // el titular puede estar en blanco.
            $oUsuario = new Usuario($id_usuario);
            $nom_usuario = $oUsuario->getNom_usuario();
            if ($sin_cargo) {
                $a_usuarios_oficina[$id_cargo] = "$nom_usuario";
            } else {
                $a_usuarios_oficina[$id_cargo] = "$nom_usuario ($cargo)";
            }
        }
	    return $a_usuarios_oficina;
	}
	/**
	 * retorna un Array
	 * Els posibles cargos de una oficina
	 *
	 * @param integer $id_oficina
	 * @return Array
	 */
	function getArrayCargosOficina($id_oficina='') {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    //$Where = '';
	    $Where = "WHERE id_oficina > 0";
	    if (!empty($id_oficina)) {
	        $Where .= " AND id_oficina = $id_oficina";
	    }
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorAsignaturaTipo.lista';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones=array();
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave=$aClave[0];
            $val=$aClave[1];
            $aOpciones[$clave]=$val;
        }
        return $aOpciones;
	}
	
	/**
	 * retorna un Array
	 * Els posibles cargos(nom usuario)
	 *
	 * @param boolean $conOficina default=TRUE: sólo las que tienen oficina. FALSE: todas.
	 * @return Array [ id_cargo => sigla(nombre usuario) ]
	 */
	function getArrayCargosConUsuario($conOficina=TRUE) {
        $aOpciones = $this->getArrayCargos($conOficina);
        $a_cargos_usuario = [];
        foreach ($aOpciones as $id_cargo => $sigla) {
            // buscar el usuario para cada cargo
            $oCargo = new Cargo($id_cargo);
            $id_suplente = $oCargo->getId_suplente();
            $id_usuario = $oCargo->getId_usuario();
            $id_nom = empty($id_suplente)? $id_usuario : $id_suplente;
            $oUsuario = new Usuario($id_nom);
            $nom_usuario = $oUsuario->getNom_usuario();
            $a_cargos_usuario[$id_cargo] = "$sigla($nom_usuario)";
        }
        return $a_cargos_usuario;    
	}
	
	/**
	 * retorna un Array
	 * Els posibles cargos
	 *
	 * @param boolean $conOficina default=TRUE: sólo las que tienen oficina. FALSE: todas.
	 * @return Array
	 */
	function getArrayCargos($conOficina=TRUE) {
	    $id_ambito = $_SESSION['oConfig']->getAmbito();
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where = "WHERE id_ambito = $id_ambito";
	    if ($conOficina) {
	       $Where .= " AND id_oficina > 0";
	    }
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY director DESC, cargo";
                if (($oDbl->query($sQuery)) === false) {
                    $sClauError = 'GestorAsignaturaTipo.lista';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                    return false;
                }
                $aOpciones=array();
                foreach ($oDbl->query($sQuery) as $aClave) {
                    $clave=$aClave[0];
                    $val=$aClave[1];
                    $aOpciones[$clave]=$val;
                }
                return $aOpciones;
	}
	
	/**
	 * retorna un Array
	 * Els posibles cargos de ref al tramite
	 *
	 * @return Array
	 */
	function getArrayCargosRef() {
	    $id_ambito = $_SESSION['oConfig']->getAmbito();
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where = "WHERE id_ambito = $id_ambito AND (id_oficina = 0 OR id_oficina IS NULL)";
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY director DESC, cargo";
                if (($oDbl->query($sQuery)) === false) {
                    $sClauError = 'GestorAsignaturaTipo.lista';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                    return false;
                }
                $aOpciones=array();
                foreach ($oDbl->query($sQuery) as $aClave) {
                    $clave=$aClave[0];
                    $val=$aClave[1];
                    $aOpciones[$clave]=$val;
                }
                return $aOpciones;
	}
	
	/**
	 * retorna un objecte del tipus Desplegable
	 * Els posibles cargos d'un usuari
	 *
	 * @param integer $id_usuario
	 * @return Desplegable
	 */
	function getDesplCargosUsuario($id_usuario) {
	    $id_ambito = $_SESSION['oConfig']->getAmbito();
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where = '';
	    if (!empty($id_ambito)) {
	        $Where = "WHERE id_ambito = $id_ambito
                         AND (id_usuario = $id_usuario OR id_suplente = $id_usuario)";
	    }
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
                if (($oDbl->query($sQuery)) === false) {
                    $sClauError = 'GestorAsignaturaTipo.lista';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                    return false;
                }
                $aOpciones=array();
                foreach ($oDbl->query($sQuery) as $aClave) {
                    $clave=$aClave[0];
                    $val=$aClave[1];
                    $aOpciones[$clave]=$val;
                }
                return new Desplegable('',$aOpciones,'',true);
	}
	
	/**
	 * retorna un objecte del tipus Desplegable
	 * Els posibles cargos
	 *
	 * @param integer|string $id_oficina si es 'x' se omiten los que no tienen oficina
	 * @param boolean $bdirector: true = sólo directores
	 * @return Desplegable
	 */
	function getDesplCargos($id_oficina='', $bdirector=FALSE) {
	    $id_ambito = $_SESSION['oConfig']->getAmbito();
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();
	    
	    $Where = '';
	    if (!empty($id_ambito)) {
	        $Where = "WHERE id_ambito = $id_ambito";
	        if (!empty($id_oficina)) {
	            if ($id_oficina == 'x') {
	                $Where .= " AND (id_oficina IS NOT NULL AND id_oficina != 0)";
	            } else {
	                $Where .= " AND id_oficina = $id_oficina";
	            }
	        }
	    } else {
	        if (!empty($id_oficina)) {
	            if ($id_oficina == 'x') {
	                $Where .= "WHERE (id_oficina IS NOT NULL AND id_oficina != 0)";
	            } else {
	                $Where .= "WHERE id_oficina = $id_oficina";
	            }
	        }
	        
	    }
	    if ($bdirector) {
	        $Where .= " AND director = 't'";
	    }
	    $sQuery="SELECT id_cargo, cargo FROM $nom_tabla
                $Where ORDER BY cargo";
                if (($oDbl->query($sQuery)) === false) {
                    $sClauError = 'GestorAsignaturaTipo.lista';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                    return false;
                }
                $aOpciones=array();
                foreach ($oDbl->query($sQuery) as $aClave) {
                    $clave=$aClave[0];
                    $val=$aClave[1];
                    $aOpciones[$clave]=$val;
                }
                return new Desplegable('',$aOpciones,'',true);
	}
	
	/**
	 * retorna l'array d'objectes de tipus Cargo
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus Cargo
	 */
	function getCargosQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oCargoSet = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorCargo.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_cargo' => $aDades['id_cargo']);
			$oCargo= new Cargo($a_pkey);
			$oCargo->setAllAtributes($aDades);
			$oCargoSet->add($oCargo);
		}
		return $oCargoSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus Cargo
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus Cargo
	 */
	function getCargos($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oCargoSet = new core\Set();
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
			$sClauError = 'GestorCargo.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorCargo.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_cargo' => $aDades['id_cargo']);
			$oCargo = new Cargo($a_pkey);
			$oCargo->setAllAtributes($aDades);
			$oCargoSet->add($oCargo);
		}
		return $oCargoSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
