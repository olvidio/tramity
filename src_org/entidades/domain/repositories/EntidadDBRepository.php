<?php

namespace entidades\domain\repositories;

use entidades\domain\entity\EntidadDB;
use entidades\infrastructure\PgEntidadDBRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntidadDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntidadDBRepository implements EntidadDBRepositoryInterface
{

    /**$
     * @var EntidadDBRepositoryInterface
     */
    private EntidadDBRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntidadDBRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntidadDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntidadDB
     */
    public function getEntidadesDB(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntidadesDB($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntidadDB $EntidadDB): bool
    {
        return $this->repository->Eliminar($EntidadDB);
    }

    public function Guardar(EntidadDB $EntidadDB): bool
    {
        return $this->repository->Guardar($EntidadDB);
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
     * @param int $id_entidad
     * @return array|bool
     */
    public function datosById(int $id_entidad): array|bool
    {
        return $this->repository->datosById($id_entidad);
    }

    /**
     * Busca la clase con id_entidad en el repositorio.
     */
    public function findById(int $id_entidad): ?EntidadDB
    {
        return $this->repository->findById($id_entidad);
    }

    public function getNewId_entidad()
    {
        return $this->repository->getNewId_entidad();
    }
}