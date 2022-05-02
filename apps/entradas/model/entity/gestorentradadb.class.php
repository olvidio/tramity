<?php
namespace entradas\model\entity;
use core\ClaseGestor;
use core\Condicion;
use core\Set;
use function core\any_2;
use entradas\model\Entrada;
use usuarios\model\entity\Cargo;
use usuarios\model\Categoria;

/**
 * GestorEntradaDB
 *
 * Classe per gestionar la llista d'objectes de la clase EntradaDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

class GestorEntradaDB Extends ClaseGestor {
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
		$this->setNomTabla('entradas');
	}


	/* METODES PUBLICS -----------------------------------------------------------*/
	
	public function posiblesYear() {
	    $oDbl = $this->getoDbl();
	    $nom_tabla = $this->getNomTabla();

        $sql_anys="SELECT json_prot_origen -> 'any' as a 
                    FROM $nom_tabla
                    WHERE categoria = ". Categoria::CAT_PERMANATE ."
                    GROUP BY a ORDER BY a";
        
        if (($oDblSt=$oDbl->Query($sql_anys)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_anys = []; 
        foreach ($oDblSt as $a_year) {
            $year = trim($a_year['a'],'"') ;
            $iyear = intval($year);
            if ($iyear > 70) {
                $iany = 1900 + $iyear;
            } else {
                $iany = 2000 + $iyear;
            }
            
            $a_anys[] = $iany;
        }
        sort($a_anys);
            
        return $a_anys;
	}
	
    /**
     * retorna l'array d'objectes de tipus EntradaDB amb visto = false
     *
     * @param integer id_oficina
     * @param string tipo_oficina (ponente|resto|encargado|centro) Seleccionar por
     * @param array a_visibilidad para filtrar (caso centros?)
     * @return array Una col·lecció d'objectes de tipus EntradaDB
     */
    function getEntradasNoVistoDB($oficina,$tipo_oficina,$a_visibilidad=[]) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
        
        $estado = Entrada::ESTADO_ACEPTADO;
        
        $json = '';
        // Quitar las vistas
		$json .= "\"oficina\":\"$oficina\"";
		$json .= empty($json)? '' : ',';
		$json .= "\"visto\":true";
        
        // Todas las de la oficina
        switch ($tipo_oficina) {
            case 'ponente':
                // en el caso de la oficina ponente, lo considero visto si está encargado a alguien
                $sCondi = "ponente = $oficina AND estado = $estado AND encargado IS NULL";
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                break;
            case 'resto':
                $sCondi = "$oficina = ANY (resto_oficinas) AND estado = $estado";
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                break;
            case 'encargado':
                // si es encargado se le pasa el id_cargo:
                $encargado = $oficina;
                $oCargo = new Cargo($encargado);
                $oficina = $oCargo->getId_oficina();
                $sCondi = "encargado = $encargado AND estado = $estado";
                // comprobar visibilidad:
                if (!empty($a_visibilidad)) {
                	$visibilidad_csv = implode(',',$a_visibilidad);
                	$sCondi .= " AND (visibilidad IN ($visibilidad_csv) OR visibilidad IS NULL)";
                }
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                $json .= empty($json)? '' : ',';
                $json .= "\"cargo\":\"$encargado\"";
                break;
            case 'centro':
            	// para los ctr
				$estado = Entrada::ESTADO_INGRESADO;
                $sCondi = "estado = $estado";
                // comprobar visibilidad:
                if (!empty($a_visibilidad)) {
                	$visibilidad_csv = implode(',',$a_visibilidad);
                	$sCondi .= " AND (visibilidad IN ($visibilidad_csv) OR visibilidad IS NULL)";
                }
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                break;
            default:
            	$err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            	exit ($err_switch);
        }
        
        if (!empty($json)) {
        	$Where_json = "json_visto @> '[{".$json."}]'";
        } else {
        	$Where_json = '';
        }
        
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json. " AND ". $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi)? '' : "WHERE ".$where_condi;
        
        $select_vistas = "SELECT * FROM $nom_tabla $where_condi";
        
        $sQry =  "$select_todas EXCEPT $select_vistas";
        
        if (($oDblSt = $oDbl->query($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
    }
    
    /**
     * retorna l'array d'objectes de tipus EntradaDB amb visto = false
     *
	 * @param array $aVisto = ['oficina' => xx, 'visto' => xx, 'cargo' => xx]
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus EntradaDB
     */
    function getEntradasByVistoDB($aVisto=[],$aWhere=[],$aOperators=[]) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') { continue; }
            if ($camp == '_limit') { continue; }
            if ($camp == 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";
                
                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
        }
        $sCondi = implode(' AND ',$aCondi);

        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND ".$COND_OR;
            } else {
                $sCondi .= " WHERE ".$COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
        if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
        if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
        
        // Where del visto
        $Where_json = '';
        $json = '';
        if (!empty($aVisto['oficina'])) {
            $oficina = $aVisto['oficina'];
            $json .= empty($json)? '' : ',';
            $json .= "\"oficina\":\"$oficina\"";
        }
        if (!empty($aVisto['visto'])) {
            $visto = $aVisto['visto'];
            $json .= empty($json)? '' : ',';
            $json .= "\"visto\":$visto";
        }
        if (!empty($aVisto['cargo'])) {
            $cargo = $aVisto['cargo'];
            $json .= empty($json)? '' : ',';
            $json .= "\"cargo\":\"$cargo\"";
        }
        if (!empty($json)) {
        	$Where_json = "json_visto @> '[{".$json."}]'";
        }
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json. " AND ". $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi)? '' : "WHERE ".$where_condi;
        
        $sQry = "SELECT * FROM $nom_tabla $where_condi".$sOrdre.$sLimit;
        
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
    }
    
	/**
	 * Devuelve la colección de entradas, segun las condiciones del protcolo de referencias, más las normales
	 * 
	 * @param array $aProt_ref = ['id_lugar' => xx, 'num' => xx, 'any' => xx, 'mas' => xx]
	 * @param array $aWhere
	 * @param array $aOperators
	 * @return boolean|array
	 */
	function getEntradasByRefDB($aProt_ref=[], $aWhere=[], $aOperators=[]) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
        
        /* {"any": 20, "mas": null, "num": 15, "lugar": 58} */
        
		$oCondicion = new Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') { continue; }
            if ($camp == '_limit') { continue; }
            $sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
        }
        $sCondi = implode(' AND ',$aCondi);
        
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
        if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
        if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
        
        // Where del prot_ref
        $Where_json = '';
        $json = '';
        if (!empty($aProt_ref['id_lugar'])) {
            $id_lugar = $aProt_ref['id_lugar'];
            $json .= empty($json)? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_ref['num'])) {
            $num = $aProt_ref['num'];
            $json .= empty($json)? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_ref['any'])) {
            $any = $aProt_ref['any'];
            $any_2 = any_2($any);
            $json .= empty($json)? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_ref['mas'])) {
            $mas = $aProt_ref['mas'];
            $json .= empty($json)? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }
        if (!empty($json)) {
        	$Where_json = "json_prot_ref @> '[{".$json."}]'";
        }
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json. " AND ". $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi)? '' : "WHERE ".$where_condi;
        
        // pongo tipo 'text' en todos los campos del json, porque si hay algun null devuelve error syntax
        $sQry = "SELECT * FROM $nom_tabla $where_condi".$sOrdre.$sLimit;
        
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
	}
	
	
	/**
	 * Devuelve la colección de entradas, segun las condiciones del protcolo de entrada, más las normales
	 * 
	 * @param array $aProt_origen = ['id_lugar' => xx, 'num' => xx, 'any' => xx, 'mas' => xx]
	 * @param array $aWhere
	 * @param array $aOperators
	 * @return boolean|array
	 */
	function getEntradasByProtOrigenDB($aProt_origen=[], $aWhere=[], $aOperators=[]) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
		$oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') { continue; }
            if ($camp == '_limit') { continue; }
            if ($camp == 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";
                
                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
        }
        $sCondi = implode(' AND ',$aCondi);
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND ".$COND_OR;
            } else {
                $sCondi .= " WHERE ".$COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] != '' ) {
            $sOrdre = ' ORDER BY '.$aWhere['_ordre'];
        } else {
            $sOrdre = " ORDER BY CASE WHEN anulado IS NULL THEN 1 WHEN anulado = '' THEN 1 ELSE 2 END , f_entrada DESC";
        }
        if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
        if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
        
        // Where del prot_origen
        $Where_json = '';
        $json = '';
        if (!empty($aProt_origen['id_lugar'])) {
            $id_lugar = $aProt_origen['id_lugar'];
            $json .= empty($json)? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_origen['num'])) {
            $num = $aProt_origen['num'];
            $json .= empty($json)? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_origen['any'])) {
            $any = $aProt_origen['any'];
            $any_2 = any_2($any);
            $json .= empty($json)? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_origen['mas'])) {
            $mas = $aProt_origen['mas'];
            $json .= empty($json)? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }
        if (!empty($json)) {
        	$Where_json = "json_prot_origen @> '{".$json."}'";
        }
        
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json. " AND ". $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi)? '' : "WHERE ".$where_condi;
        
        $sQry = "SELECT * FROM $nom_tabla $where_condi".$sOrdre.$sLimit;
                        
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
	}
	
	function getEntradasByLugarDB($id_lugar, $aWhere=array(),$aOperators=array()) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();
		$oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp == '_ordre') { continue; }
            if ($camp == '_limit') { continue; }
            if ($camp == 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";
                
                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
            if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
            // operadores que no requieren valores
            if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
        }
        $sCondi = implode(' AND ',$aCondi);
        if (empty($sCondi)) {
        	$sCondi = " WHERE json_prot_origen @> '{\"id_lugar\":$id_lugar}'";
        } else {
        	$sCondi = " WHERE json_prot_origen @> '{\"id_lugar\":$id_lugar}' AND ".$sCondi;
        }
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND ".$COND_OR;
            } else {
                $sCondi .= " WHERE ".$COND_OR;
            }
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
        if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
        if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
        if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
        
        $sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
        
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_entrada' => $aDades['id_entrada']);
            $oEntradaDB = new Entrada($a_pkey);
            $oEntradaDB->setAllAtributes($aDades);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
	}
	
	/**
	 * retorna l'array d'objectes de tipus EntradaDB
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d'objectes de tipus EntradaDB
	 */
	function getEntradasDBQuery($sQuery='') {
		$oDbl = $this->getoDbl();
		$oEntradaDBSet = new Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = 'GestorEntradaDB.query';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {
			$a_pkey = array('id_entrada' => $aDades['id_entrada']);
			$oEntradaDB= new EntradaDB($a_pkey);
			$oEntradaDBSet->add($oEntradaDB);
		}
		return $oEntradaDBSet->getTot();
	}

	/**
	 * retorna l'array d'objectes de tipus EntradaDB
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d'objectes de tipus EntradaDB
	 */
	function getEntradasDB($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$oEntradaDBSet = new Set();
		$oCondicion = new Condicion();
		$aCondi = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp == '_ordre') { continue; }
			if ($camp == '_limit') { continue; }
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
			// operadores que no requieren valores
			if ($sOperador == 'BETWEEN' || $sOperador == 'IS NULL' || $sOperador == 'IS NOT NULL' || $sOperador == 'OR') { unset($aWhere[$camp]); }
            if ($sOperador == 'IN' || $sOperador == 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador == 'TXT') { unset($aWhere[$camp]); }
		}
		$sCondi = implode(' AND ',$aCondi);
		if ($sCondi!='') { $sCondi = " WHERE ".$sCondi; }
		$sOrdre = '';
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre']!='') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit']!='') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = 'GestorEntradaDB.llistar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = 'GestorEntradaDB.llistar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {
			$a_pkey = array('id_entrada' => $aDades['id_entrada']);
			$oEntradaDB = new EntradaDB($a_pkey);
			$oEntradaDBSet->add($oEntradaDB);
		}
		return $oEntradaDBSet->getTot();
	}

	/* METODES PROTECTED --------------------------------------------------------*/

	/* METODES GET i SET --------------------------------------------------------*/
}
