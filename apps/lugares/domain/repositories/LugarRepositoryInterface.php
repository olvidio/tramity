<?php

namespace lugares\domain\repositories;

use lugares\domain\entity\Lugar;
use PDO;

/**
 * Interfaz de la clase Lugar y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
interface LugarRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Lugar
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Lugar
     */
    public function getLugares(array $aWhere, array $aOperators): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Lugar $Lugar): bool;

    public function Guardar(Lugar $Lugar): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_lugar
     * @return array|bool
     */
    public function datosById(int $id_lugar): array|bool;

    /**
     * Busca la clase con id_lugar en el repositorio.
     */
    public function findById(int $id_lugar): ?Lugar;

    public function getNewId_lugar();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getPlataformas(bool $propia = FALSE): array;

    public function getId_iese(): ?int;

    public function getId_cr(): int;

    public function getSigla_superior(string $sigla_base, bool $return_id = FALSE): string|int;

    public function getId_sigla_local(): ?int;

    public function getArrayBusquedas(bool $ctr_anulados = FALSE): array;

    public function getArrayLugaresCtr(bool $ctr_anulados = FALSE): bool|array;

    public function getArrayLugaresTipo(string $tipo_ctr, bool $ctr_anulados = FALSE): bool|array;

    public function getArrayLugares(bool $ctr_anulados = FALSE): bool|array;
}