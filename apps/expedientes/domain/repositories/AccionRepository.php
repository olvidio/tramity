<?php

namespace expedientes\domain\repositories;

use expedientes\domain\entity\Accion;
use expedientes\infrastructure\PgAccionRepository;
use PDO;

/**
 *
 * Clase para gestionar la lista de objetos tipo Accion
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class AccionRepository implements AccionRepositoryInterface
{

    /**$
     * @var AccionRepositoryInterface
     */
    private AccionRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgAccionRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Accion
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Accion
     */
    public function getAcciones(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getAcciones($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Accion $Accion): bool
    {
        return $this->repository->Eliminar($Accion);
    }

    public function Guardar(Accion $Accion): bool
    {
        return $this->repository->Guardar($Accion);
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
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_item
     * @return array|bool
     */
    public function datosById(int $id_item): array|bool
    {
        return $this->repository->datosById($id_item);
    }

    /**
     * Busca la clase con id_item en el repositorio.
     */
    public function findById(int $id_item): ?Accion
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}