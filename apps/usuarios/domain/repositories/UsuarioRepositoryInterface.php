<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Usuario;

/**
 * Interfaz de la clase Usuario y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
interface UsuarioRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Usuario
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Usuario
     */
    public function getUsuarios(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Usuario $Usuario): bool;

    public function Guardar(Usuario $Usuario): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_usuario): array|bool;

    /**
     * Busca la clase con $id_usuario en el repositorio.
     */
    public function findById(int $id_usuario): ?Usuario;

    public function getNewId_usuario();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayUsuarios();

    public function getDesplUsuarios();
}