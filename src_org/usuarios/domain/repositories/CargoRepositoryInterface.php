<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Cargo;

/**
 * Interfaz de la clase Cargo y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
interface CargoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Cargo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Cargo
     */
    public function getCargos(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Cargo $Cargo): bool;

    public function Guardar(Cargo $Cargo): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_cargo): array|bool;

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function findById(int $id_cargo): ?Cargo;

    public function getNewId_cargo();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    /**
     * @param int $id_oficina
     * @return false|integer $id_cargo del director de la oficina
     */
    public function getDirectorOficina(int $id_oficina): bool|int;

    public function getArrayUsuariosOficina($id_oficina = '', $sin_cargo = FALSE);

    public function getArrayCargosOficina($id_oficina = '');

    public function getArrayCargosConUsuario($conOficina = TRUE);

    /**
     * @param bool $conOficina
     * @return array|false  [id_cargo => cargo]
     */
    public function getArrayCargos(bool $conOficina = TRUE): bool|array;

    public function getArrayCargosRef();

    public function getDesplCargosUsuario($id_usuario);

    public function getDesplCargos($id_oficina = '', $bdirector = FALSE);
}