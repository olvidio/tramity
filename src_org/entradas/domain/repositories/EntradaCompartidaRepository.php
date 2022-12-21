<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaCompartida;
use entradas\infrastructure\PgEntradaCompartidaRepository;
use JsonException;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaCompartida
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EntradaCompartidaRepository implements EntradaCompartidaRepositoryInterface
{

    /**$
     * @var EntradaCompartidaRepositoryInterface
     */
    private EntradaCompartidaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaCompartidaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaCompartida
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaCompartida
     * @throws JsonException
     */
    public function getEntradasCompartidas(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradasCompartidas($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaCompartida $EntradaCompartida): bool
    {
        return $this->repository->Eliminar($EntradaCompartida);
    }

    public function Guardar(EntradaCompartida $EntradaCompartida): bool
    {
        return $this->repository->Guardar($EntradaCompartida);
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
     * @param int $id_entrada_compartida
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada_compartida): array|bool
    {
        return $this->repository->datosById($id_entrada_compartida);
    }

    /**
     * Busca la clase con id_entrada_compartida en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada_compartida): ?EntradaCompartida
    {
        return $this->repository->findById($id_entrada_compartida);
    }

    public function getNewId_entrada_compartida()
    {
        return $this->repository->getNewId_entrada_compartida();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function posiblesYear(): bool|array
    {
        return $this->repository->posiblesYear();
    }

    public function getEntradasByProtOrigenDestino(array $aProt_origen, int $id_destino, array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEntradasByProtOrigenDestino($aProt_origen, $id_destino, $aWhere, $aOperators);
    }

    public function getEntradasByProtOrigenDB(array $aProt_origen, array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEntradasByProtOrigenDB($aProt_origen, $aWhere, $aOperators);
    }
}