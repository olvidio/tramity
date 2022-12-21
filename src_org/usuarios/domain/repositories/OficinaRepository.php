<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Oficina;
use usuarios\infrastructure\PgOficinaRepository;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo Oficina
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class OficinaRepository implements OficinaRepositoryInterface
{

    /**$
     * @var OficinaRepositoryInterface
     */
    private OficinaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgOficinaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Oficina
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Oficina
     */
    public function getOficinas(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getOficinas($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Oficina $Oficina): bool
    {
        return $this->repository->Eliminar($Oficina);
    }

    public function Guardar(Oficina $Oficina): bool
    {
        return $this->repository->Guardar($Oficina);
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
    public function datosById(int $id_oficina): array|bool
    {
        return $this->repository->datosById($id_oficina);
    }

    /**
     * Busca la clase con id_oficina en el repositorio.
     */
    public function findById(int $id_oficina): ?Oficina
    {
        return $this->repository->findById($id_oficina);
    }

    public function getNewId_oficina()
    {
        return $this->repository->getNewId_oficina();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayOficinas(): array|false
    {
        return $this->repository->getArrayOficinas();
    }

    public function getListaOficinas(): Desplegable|false
    {
        return $this->repository->getListaOficinas();
    }
}