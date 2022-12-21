<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaDocDB;
use entradas\infrastructure\PgEntradaDocDBRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaDocDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaDocDBRepository implements EntradaDocDBRepositoryInterface
{

    /**$
     * @var EntradaDocDBRepositoryInterface
     */
    private EntradaDocDBRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaDocDBRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaDocDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaDocDB
     */
    public function getEntradasDocsDB(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradasDocsDB($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaDocDB $EntradaDocDB): bool
    {
        return $this->repository->Eliminar($EntradaDocDB);
    }

    public function Guardar(EntradaDocDB $EntradaDocDB): bool
    {
        return $this->repository->Guardar($EntradaDocDB);
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

    public function setNomTabla(string $nom_tabla): void
    {
        $this->repository->setNomTabla($nom_tabla);
    }

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_entrada
     * @return array|bool
     */
    public function datosById(int $id_entrada): array|bool
    {
        return $this->repository->datosById($id_entrada);
    }

    /**
     * Busca la clase con id_entrada en el repositorio.
     */
    public function findById(int $id_entrada): ?EntradaDocDB
    {
        return $this->repository->findById($id_entrada);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}