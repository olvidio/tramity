<?php

namespace tramites\domain\repositories;

use PDO;
use tramites\domain\entity\Tramite;
use tramites\infrastructure\PgTramiteRepository;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo Tramite
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class TramiteRepository implements TramiteRepositoryInterface
{

    /**$
     * @var TramiteRepositoryInterface
     */
    private TramiteRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgTramiteRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Tramite
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Tramite
     */
    public function getTramites(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getTramites($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Tramite $Tramite): bool
    {
        return $this->repository->Eliminar($Tramite);
    }

    public function Guardar(Tramite $Tramite): bool
    {
        return $this->repository->Guardar($Tramite);
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
     * @param int $id_tramite
     * @return array|bool
     */
    public function datosById(int $id_tramite): array|bool
    {
        return $this->repository->datosById($id_tramite);
    }

    /**
     * Busca la clase con id_tramite en el repositorio.
     */
    public function findById(int $id_tramite): ?Tramite
    {
        return $this->repository->findById($id_tramite);
    }

    public function getNewId_tramite()
    {
        return $this->repository->getNewId_tramite();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayAbrevTramites(): array|false
    {
        return $this->repository->getArrayAbrevTramites();
    }

    public function getListaTramites(): Desplegable|false
    {
        return $this->repository->getListaTramites();
    }
}