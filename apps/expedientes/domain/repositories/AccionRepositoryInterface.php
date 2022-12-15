<?php

namespace expedientes\domain\repositories;

use expedientes\domain\entity\Accion;
use PDO;

/**
 * Interfaz de la clase Accion y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
interface AccionRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Accion
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Accion
     */
    public function getAcciones(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Accion $Accion): bool;

    public function Guardar(Accion $Accion): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_item
     * @return array|bool
     */
    public function datosById(int $id_item): array|bool;

    /**
     * Busca la clase con id_item en el repositorio.
     */
    public function findById(int $id_item): ?Accion;

    public function getNewId_item();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}