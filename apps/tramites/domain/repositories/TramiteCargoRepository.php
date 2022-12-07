<?php

namespace tramites\domain\repositories;

use PDO;
use tramites\domain\entity\TramiteCargo;
use tramites\infrastructure\PgTramiteCargoRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo TramiteCargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class TramiteCargoRepository implements TramiteCargoRepositoryInterface
{

    /**$
     * @var TramiteCargoRepositoryInterface
     */
    private TramiteCargoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgTramiteCargoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo TramiteCargo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo TramiteCargo
     */
    public function getTramiteCargos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getTramiteCargos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(TramiteCargo $TramiteCargo): bool
    {
        return $this->repository->Eliminar($TramiteCargo);
    }

    public function Guardar(TramiteCargo $TramiteCargo): bool
    {
        return $this->repository->Guardar($TramiteCargo);
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
    public function findById(int $id_item): ?TramiteCargo
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}