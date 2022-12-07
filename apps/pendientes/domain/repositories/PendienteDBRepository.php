<?php

namespace pendientes\domain\repositories;

use PDO;
use pendientes\domain\entity\PendienteDB;
use pendientes\infrastructure\PgPendienteDBRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo PendienteDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PendienteDBRepository implements PendienteDBRepositoryInterface
{

    /**$
     * @var PendienteDBRepositoryInterface
     */
    private PendienteDBRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgPendienteDBRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo PendienteDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo PendienteDB
     */
    public function getPendientesDB(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getPendientesDB($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(PendienteDB $PendienteDB): bool
    {
        return $this->repository->Eliminar($PendienteDB);
    }

    public function Guardar(PendienteDB $PendienteDB): bool
    {
        return $this->repository->Guardar($PendienteDB);
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
     * @param int $id_pendiente
     * @return array|bool
     */
    public function datosById(int $id_pendiente): array|bool
    {
        return $this->repository->datosById($id_pendiente);
    }

    /**
     * Busca la clase con id_pendiente en el repositorio.
     */
    public function findById(int $id_pendiente): ?PendienteDB
    {
        return $this->repository->findById($id_pendiente);
    }

    public function getNewId_pendiente()
    {
        return $this->repository->getNewId_pendiente();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

}