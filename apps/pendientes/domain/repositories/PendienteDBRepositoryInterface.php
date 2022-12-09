<?php

namespace pendientes\domain\repositories;

use PDO;
use pendientes\domain\entity\PendienteDB;

/**
 * Interfaz de la clase PendienteDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
interface PendienteDBRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo PendienteDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo PendienteDB
     */
    public function getPendientesDB(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(PendienteDB $PendienteDB): bool;

    public function Guardar(PendienteDB $PendienteDB): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_pendiente
     * @return array|bool
     */
    public function datosById(int $id_pendiente): array|bool;

    /**
     * Busca la clase con id_pendiente en el repositorio.
     */
    public function findById(int $id_pendiente): ?PendienteDB;

    public function getNewId_pendiente();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

}