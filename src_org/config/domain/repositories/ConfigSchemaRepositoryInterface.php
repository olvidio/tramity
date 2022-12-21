<?php

namespace config\domain\repositories;

use config\domain\entity\ConfigSchema;
use PDO;


/**
 * Interfaz de la clase ConfigSchema y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface ConfigSchemaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo ConfigSchema
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo ConfigSchema
     */
    public function getConfigsSchema(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(ConfigSchema $ConfigSchema): bool;

    public function Guardar(ConfigSchema $ConfigSchema): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param string $parametro
     * @return array|bool
     */
    public function datosById(string $parametro): array|bool;

    /**
     * Busca la clase con parametro en el repositorio.
     */
    public function findById(string $parametro): ?ConfigSchema;

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}