<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Usuario;
use usuarios\infrastructure\PgUsuarioRepository;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo Usuario
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class UsuarioRepository implements UsuarioRepositoryInterface
{

    /**$
     * @var UsuarioRepositoryInterface
     */
    private UsuarioRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgUsuarioRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Usuario
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Usuario
     */
    public function getUsuarios(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getUsuarios($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Usuario $Usuario): bool
    {
        return $this->repository->Eliminar($Usuario);
    }

    public function Guardar(Usuario $Usuario): bool
    {
        return $this->repository->Guardar($Usuario);
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
    public function datosById(int $id_usuario): array|bool
    {
        return $this->repository->datosById($id_usuario);
    }

    /**
     * Busca la clase con $id_usuario en el repositorio.
     */
    public function findById(int $id_usuario): ?Usuario
    {
        return $this->repository->findById($id_usuario);
    }

    public function getNewId_usuario()
    {
        return $this->repository->getNewId_usuario();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayUsuarios(): array|false
    {
        return $this->repository->getArrayUsuarios();
    }

    public function getDesplUsuarios(): Desplegable|false
    {
        return $this->repository->getDesplUsuarios();
    }
}