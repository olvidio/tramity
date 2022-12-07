<?php

namespace plantillas\domain\repositories;

use PDO;
use plantillas\domain\entity\Plantilla;
use plantillas\infrastructure\PgPlantillaRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo Plantilla
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PlantillaRepository implements PlantillaRepositoryInterface
{

    /**$
     * @var PlantillaRepositoryInterface
     */
    private PlantillaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgPlantillaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Plantilla
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Plantilla
     */
    public function getPlantillas(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getPlantillas($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Plantilla $Plantilla): bool
    {
        return $this->repository->Eliminar($Plantilla);
    }

    public function Guardar(Plantilla $Plantilla): bool
    {
        return $this->repository->Guardar($Plantilla);
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
     * @param int $id_plantilla
     * @return array|bool
     */
    public function datosById(int $id_plantilla): array|bool
    {
        return $this->repository->datosById($id_plantilla);
    }

    /**
     * Busca la clase con id_plantilla en el repositorio.
     */
    public function findById(int $id_plantilla): ?Plantilla
    {
        return $this->repository->findById($id_plantilla);
    }

    public function getNewId_plantilla()
    {
        return $this->repository->getNewId_plantilla();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}