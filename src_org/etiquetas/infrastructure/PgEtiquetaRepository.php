<?php

namespace etiquetas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConfigGlobal;
use core\Set;
use PDO;
use PDOException;

use etiquetas\domain\entity\Etiqueta;
use etiquetas\domain\repositories\EtiquetaRepositoryInterface;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;
use web\Desplegable;


use function core\is_true;
/**
 * Clase que adapta la tabla etiquetas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 13/12/2022
 */
class PgEtiquetaRepository extends ClaseRepository implements EtiquetaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas');
    }

/* -------------------- GESTOR BASE ---------------------------------------- */

	/**
	 * devuelve una colección (array) de objetos de tipo Etiqueta
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo Etiqueta
	
	 */
	public function getEtiquetas(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$EtiquetaSet = new Set();
		$oCondicion = new Condicion();
		$aCondicion = array();
		foreach ($aWhere as $camp => $val) {
			if ($camp === '_ordre') { continue; }
			if ($camp === '_limit') { continue; }
			$sOperador = $aOperators[$camp] ?? '';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondicion[]=$a; }
			// operadores que no requieren valores
			if ($sOperador === 'BETWEEN' || $sOperador === 'IS NULL' || $sOperador === 'IS NOT NULL' || $sOperador === 'OR') { unset($aWhere[$camp]); }
            if ($sOperador === 'IN' || $sOperador === 'NOT IN') { unset($aWhere[$camp]); }
            if ($sOperador === 'TXT') { unset($aWhere[$camp]); }
		}
		$sCondicion = implode(' AND ',$aCondicion);
		if ($sCondicion !=='') { $sCondicion = " WHERE ".$sCondicion; }
		$sOrdre = '';
        $sLimit = '';
		if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') { $sOrdre = ' ORDER BY '.$aWhere['_ordre']; }
		if (isset($aWhere['_ordre'])) { unset($aWhere['_ordre']); }
		if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') { $sLimit = ' LIMIT '.$aWhere['_limit']; }
		if (isset($aWhere['_limit'])) { unset($aWhere['_limit']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondicion.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClaveError = 'PgEtiquetaRepository.listar.prepare';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClaveError = 'PgEtiquetaRepository.listar.execute';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		
		$filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $Etiqueta = new Etiqueta();
            $Etiqueta->setAllAttributes($aDatos);
			$EtiquetaSet->add($Etiqueta);
		}
		return $EtiquetaSet->getTot();
	}

/* -------------------- ENTIDAD --------------------------------------------- */

	public function Eliminar(Etiqueta $Etiqueta): bool
    {
        $id_etiqueta = $Etiqueta->getId_etiqueta();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta")) === FALSE) {
            $sClaveError = 'PgEtiquetaRepository.eliminar';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

	
	/**
	 * Si no existe el registro, hace un insert, si existe, se hace el update.
	
	 */
	public function Guardar(Etiqueta $Etiqueta): bool
    {
        $id_etiqueta = $Etiqueta->getId_etiqueta();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_etiqueta);

		$aDatos = [];
		$aDatos['nom_etiqueta'] = $Etiqueta->getNom_etiqueta();
		$aDatos['id_cargo'] = $Etiqueta->getId_cargo();
		$aDatos['oficina'] = $Etiqueta->isOficina();
		array_walk($aDatos, 'core\poner_null');
		//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
		if ( is_true($aDatos['oficina']) ) { $aDatos['oficina']='true'; } else { $aDatos['oficina']='false'; }

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					nom_etiqueta             = :nom_etiqueta,
					id_cargo                 = :id_cargo,
					oficina                  = :oficina";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_etiqueta = $id_etiqueta")) === FALSE) {
				$sClaveError = 'PgEtiquetaRepository.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
				
            try {
                $oDblSt->execute($aDatos);
            } catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
		} else {
			// INSERT
			$aDatos['id_etiqueta'] = $Etiqueta->getId_etiqueta();
			$campos="(id_etiqueta,nom_etiqueta,id_cargo,oficina)";
			$valores="(:id_etiqueta,:nom_etiqueta,:id_cargo,:oficina)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClaveError = 'PgEtiquetaRepository.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
            try {
                $oDblSt->execute($aDatos);
            } catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEtiquetaRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
			}
		}
		return TRUE;
	}
	
    private function isNew(int $id_etiqueta): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta")) === FALSE) {
			$sClaveError = 'PgEtiquetaRepository.isNew';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }
	
    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     * 
     * @param int $id_etiqueta
     * @return array|bool
	
     */
    public function datosById(int $id_etiqueta): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta = $id_etiqueta")) === FALSE) {
			$sClaveError = 'PgEtiquetaRepository.getDatosById';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
		$aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }
    
	
    /**
     * Busca la clase con id_etiqueta en la base de datos .
	
     */
    public function findById(int $id_etiqueta): ?Etiqueta
    {
        $aDatos = $this->datosById($id_etiqueta);
        if (empty($aDatos)) {
            return null;
        }
        return (new Etiqueta())->setAllAttributes($aDatos);
    }
	
    public function getNewId_etiqueta(): int
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('etiquetas_id_etiqueta_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

/* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getArrayMisEtiquetas(int $id_cargo = null): array
    {
        $a_posibles_etiquetas = [];
        foreach ($this->getMisEtiquetas($id_cargo) as $oEtiqueta) {
            $id_etiqueta = $oEtiqueta->getId_etiqueta();
            $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
            $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
        }
        return $a_posibles_etiquetas;
    }

    public function getMisEtiquetas(int $id_cargo = null)
    {
        if ($id_cargo === null) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $cEtiquetasPersonales = $this->getEtiquetasPersonales($id_cargo);
        $cEtiquetasOficina = $this->getEtiquetasMiOficina($id_cargo);

        return array_merge($cEtiquetasPersonales, $cEtiquetasOficina);
    }

    public function getEtiquetasPersonales(int $id_cargo = null)
    {
        if ($id_cargo === null) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $aWhere = ['id_cargo' => $id_cargo,
            'oficina' => 'f',
            '_ordre' => 'nom_etiqueta',
        ];
        $aOperador = [];
        return $this->getEtiquetas($aWhere, $aOperador);
    }

    public function getEtiquetasMiOficina(int $id_cargo = null)
    {
        if ($id_cargo === null) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $CargoRepository = new CargoRepository();
            $oCargo = $CargoRepository->findById($id_cargo);
            $id_oficina = $oCargo->getId_oficina();
        } else {
            $id_oficina = Cargo::OFICINA_ESQUEMA;
        }

        if (empty($id_oficina)) {
            return [];
        } else {
            $aWhere = ['id_cargo' => $id_oficina,
                'oficina' => 't',
                '_ordre' => 'nom_etiqueta',
            ];
            $aOperador = [];
            return $this->getEtiquetas($aWhere, $aOperador);
        }
    }

}