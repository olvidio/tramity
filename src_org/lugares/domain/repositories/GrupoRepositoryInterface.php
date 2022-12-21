<?php

namespace lugares\domain\repositories;

use lugares\domain\entity\Grupo;
use PDO;

/**
 * Interfaz de la clase Grupo y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
interface GrupoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Grupo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Grupo
     */
    public function getGrupos(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Grupo $Grupo): bool;

    public function Guardar(Grupo $Grupo): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_grupo
     * @return array|bool
     */
    public function datosById(int $id_grupo): array|bool;

    /**
     * Busca la clase con id_grupo en el repositorio.
     */
    public function findById(int $id_grupo): ?Grupo;

    public function getNewId_grupo(): int;

    public function getArrayGrupos(): bool|array;

}