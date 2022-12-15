<?php

namespace etiquetas\domain\repositories;

use PDO;
use etiquetas\domain\entity\EtiquetaExpediente;
use web\Desplegable;


/**
 * Interfaz de la clase EtiquetaExpediente y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
interface EtiquetaExpedienteRepositoryInterface
{

/* -------------------- GESTOR BASE ---------------------------------------- */

	/**
	 * devuelve una colección (array) de objetos de tipo EtiquetaExpediente
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo EtiquetaExpediente
	
	 */
	public function getEtiquetasExpediente(array $aWhere=[], array $aOperators=[]): array|FALSE;
	
/* -------------------- ENTIDAD --------------------------------------------- */

	public function Eliminar(EtiquetaExpediente $EtiquetaExpediente): bool;

	public function Guardar(EtiquetaExpediente $EtiquetaExpediente): bool;

	public function getErrorTxt(): string;

	public function getoDbl(): PDO;

	public function setoDbl(PDO $oDbl): void;

	public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_etiqueta
     * @param int $id_expediente
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_expediente): array|bool;
	
    /**
     * Busca la clase con id_expediente en el repositorio.
	
     */
    public function findById(int $id_etiqueta, int $id_expediente): ?EtiquetaExpediente;

/* -------------------- GESTOR EXTRA ---------------------------------------- */

	public function getArrayExpedientes(array $a_etiquetas, string $andOr): bool|array;

	public function deleteEtiquetasExpediente(int $id_expediente): bool;
}