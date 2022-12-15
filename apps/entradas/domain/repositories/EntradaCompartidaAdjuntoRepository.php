<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\EntradaCompartidaAdjunto;
use usuarios\infrastructure\PgEntradaCompartidaAdjuntoRepository;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaCompartidaAdjunto
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EntradaCompartidaAdjuntoRepository implements EntradaCompartidaAdjuntoRepositoryInterface
{

    /**$
     * @var EntradaCompartidaAdjuntoRepositoryInterface
     */
    private EntradaCompartidaAdjuntoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaCompartidaAdjuntoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaCompartidaAdjunto
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaCompartidaAdjunto
     */
    public function getEntradaCompartidaAdjuntos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradaCompartidaAdjuntos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaCompartidaAdjunto $EntradaCompartidaAdjunto): bool
    {
        return $this->repository->Eliminar($EntradaCompartidaAdjunto);
    }

    public function Guardar(EntradaCompartidaAdjunto $EntradaCompartidaAdjunto): bool
    {
        return $this->repository->Guardar($EntradaCompartidaAdjunto);
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
    public function findById(int $id_item): ?EntradaCompartidaAdjunto
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    public function getArrayIdAdjuntos(int $id_entrada_compartida): bool|array
    {
        return $this->repository->getArrayIdAdjuntos($id_entrada_compartida);
    }

}