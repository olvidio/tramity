<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Preferencia;
use usuarios\infrastructure\PgPreferenciaRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo Preferencia
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class PreferenciaRepository implements PreferenciaRepositoryInterface
{

    /**$
     * @var PreferenciaRepositoryInterface
     */
    private PreferenciaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgPreferenciaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Preferencia
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Preferencia
     */
    public function getPreferencias(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getPreferencias($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Preferencia $Preferencia): bool
    {
        return $this->repository->Eliminar($Preferencia);
    }

    public function Guardar(Preferencia $Preferencia): bool
    {
        return $this->repository->Guardar($Preferencia);
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
    public function datosById(int $id_item): array|bool
    {
        return $this->repository->datosById($id_item);
    }

    /**
     * Busca la clase con id_item en el repositorio.
     */
    public function findById(int $id_item): ?Preferencia
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getMiPreferencia($tipo)
    {
        return $this->repository->getMiPreferencia($tipo);
    }

    public function getPreferenciaUsuario($id_usuario, $tipo)
    {
        return $this->repository->getPreferenciaUsuario($id_usuario, $tipo);
    }
}