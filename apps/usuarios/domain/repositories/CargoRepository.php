<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Cargo;
use usuarios\infrastructure\PgCargoRepository;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo Cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class CargoRepository implements CargoRepositoryInterface
{

    /**$
     * @var CargoRepositoryInterface
     */
    private CargoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgCargoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Cargo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Cargo
     */
    public function getCargos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getCargos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Cargo $Cargo): bool
    {
        return $this->repository->Eliminar($Cargo);
    }

    public function Guardar(Cargo $Cargo): bool
    {
        return $this->repository->Guardar($Cargo);
    }

    public function getErrorTxt(): string
    {
        return $this->repository->getErrorTxt();
    }

    public function getoDbl(): PDO
    {
        return $this->repository->getoDbl();
    }

    public function setoDbl(PDO $oDbl): void
    {
        $this->repository->setoDbl($oDbl);
    }

    public function getNomTabla(): string
    {
        return $this->repository->getNomTabla();
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_cargo): array|bool
    {
        return $this->repository->datosById($id_cargo);
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function findById(int $id_cargo): ?Cargo
    {
        return $this->repository->findById($id_cargo);
    }

    public function getNewId_cargo()
    {
        return $this->repository->getNewId_cargo();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    /**
     * @param int $id_oficina
     * @return false|integer $id_cargo del director de la oficina
     */
    public function getDirectorOficina(int $id_oficina): bool|int
    {
        return $this->repository->getDirectorOficina($id_oficina);
    }

    public function getArrayUsuariosOficina($id_oficina = '', $sin_cargo = FALSE): bool|array
    {
        return $this->repository->getArrayUsuariosOficina($id_oficina, $sin_cargo);
    }

    public function getArrayCargosOficina($id_oficina = ''): bool|array
    {
        return $this->repository->getArrayCargosOficina($id_oficina);
    }

    /**
     * @param bool $conOficina
     * @return array|false  [id_cargo => cargo]
     */
    public function getArrayCargosConUsuario($conOficina = TRUE): bool|array
    {
        return $this->repository->getArrayCargosConUsuario($conOficina);
    }

    /**
     * @param bool $conOficina
     * @return array|false  [id_cargo => cargo]
     */
    public function getArrayCargos(bool $conOficina = TRUE): array|false
    {
        return $this->repository->getArrayCargos($conOficina);
    }

    public function getArrayCargosRef(): bool|array
    {
        return $this->repository->getArrayCargosRef();
    }

    public function getDesplCargosUsuario($id_usuario): Desplegable|bool
    {
        return $this->repository->getDesplCargosUsuario($id_usuario);
    }

    public function getDesplCargos($id_oficina = '', $bdirector = FALSE): Desplegable|bool
    {
        return $this->repository->getDesplCargos($id_oficina, $bdirector);
    }
}