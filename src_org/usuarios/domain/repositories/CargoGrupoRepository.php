<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\CargoGrupo;
use usuarios\infrastructure\PgCargoGrupoRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo CargoGrupo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class CargoGrupoRepository implements CargoGrupoRepositoryInterface
{

    /**$
     * @var CargoGrupoRepositoryInterface
     */
    private CargoGrupoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgCargoGrupoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo CargoGrupo
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo CargoGrupo
     */
    public function getCargoGrupos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getCargoGrupos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(CargoGrupo $CargoGrupo): bool
    {
        return $this->repository->Eliminar($CargoGrupo);
    }

    public function Guardar(CargoGrupo $CargoGrupo): bool
    {
        return $this->repository->Guardar($CargoGrupo);
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
     * @param int $id_grupo
     * @return array|bool
     */
    public function datosById(int $id_grupo): array|bool
    {
        return $this->repository->datosById($id_grupo);
    }

    /**
     * Busca la clase con id_grupo en el repositorio.
     */
    public function findById(int $id_grupo): ?CargoGrupo
    {
        return $this->repository->findById($id_grupo);
    }

    public function getNewId_grupo()
    {
        return $this->repository->getNewId_grupo();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}