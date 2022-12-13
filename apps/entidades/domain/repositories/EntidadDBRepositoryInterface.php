<?php

namespace entidades\domain\repositories;

use entidades\domain\entity\EntidadDB;
use PDO;


/**
 * Interfaz de la clase EntidadDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface EntidadDBRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntidadDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntidadDB
     */
    public function getEntidadesDB(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntidadDB $EntidadDB): bool;

    public function Guardar(EntidadDB $EntidadDB): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_entidad
     * @return array|bool
     */
    public function datosById(int $id_entidad): array|bool;

    /**
     * Busca la clase con id_entidad en el repositorio.
     */
    public function findById(int $id_entidad): ?EntidadDB;

    public function getNewId_entidad();
}