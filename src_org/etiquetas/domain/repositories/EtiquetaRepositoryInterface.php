<?php

namespace etiquetas\domain\repositories;

use PDO;
use etiquetas\domain\entity\Etiqueta;
use web\Desplegable;


use function core\is_true;
/**
 * Interfaz de la clase Etiqueta y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 13/12/2022
 */
interface EtiquetaRepositoryInterface
{

/* -------------------- GESTOR BASE ---------------------------------------- */

	/**
	 * devuelve una colección (array) de objetos de tipo Etiqueta
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo Etiqueta
	
	 */
	public function getEtiquetas(array $aWhere=[], array $aOperators=[]): array|FALSE;
	
/* -------------------- ENTIDAD --------------------------------------------- */

	public function Eliminar(Etiqueta $Etiqueta): bool;

	public function Guardar(Etiqueta $Etiqueta): bool;

	public function getErrorTxt(): string;

	public function getoDbl(): PDO;

	public function setoDbl(PDO $oDbl): void;

	public function getNomTabla(): string;
	
    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     * 
     * @param int $id_etiqueta
     * @return array|bool
	
     */
    public function datosById(int $id_etiqueta): array|bool;
	
    /**
     * Busca la clase con id_etiqueta en el repositorio.
	
     */
    public function findById(int $id_etiqueta): ?Etiqueta;
	
    public function getNewId_etiqueta();

/* -------------------- GESTOR EXTRA ---------------------------------------- */

	public function getArrayMisEtiquetas(int $id_cargo = null): array;

	public function getMisEtiquetas(int $id_cargo = null);

	public function getEtiquetasPersonales(int $id_cargo = null);

	public function getEtiquetasMiOficina(int $id_cargo = null);
}