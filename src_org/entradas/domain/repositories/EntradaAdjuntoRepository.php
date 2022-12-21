<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaAdjunto;
use entradas\infrastructure\PgEntradaAdjuntoRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaAdjunto
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EntradaAdjuntoRepository implements EntradaAdjuntoRepositoryInterface
{

    /**$
     * @var EntradaAdjuntoRepositoryInterface
     */
    private EntradaAdjuntoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaAdjuntoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaAdjunto
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaAdjunto
     */
    public function getEntradasAdjunto(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradasAdjunto($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaAdjunto $EntradaAdjunto): bool
    {
        return $this->repository->Eliminar($EntradaAdjunto);
    }

    public function Guardar(EntradaAdjunto $EntradaAdjunto): bool
    {
        return $this->repository->Guardar($EntradaAdjunto);
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
    public function findById(int $id_item): ?EntradaAdjunto
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayIdAdjuntos(int $id_entrada): bool|array
    {
        return $this->repository->getArrayIdAdjuntos($id_entrada);
    }
}