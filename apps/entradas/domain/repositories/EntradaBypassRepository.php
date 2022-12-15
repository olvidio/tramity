<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaBypass;
use entradas\infrastructure\PgEntradaBypassRepository;
use JsonException;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaBypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaBypassRepository implements EntradaBypassRepositoryInterface
{

    /**$
     * @var EntradaBypassRepositoryInterface
     */
    private EntradaBypassRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaBypassRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaBypass
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaBypass
     * @throws JsonException
     */
    public function getEntradasBypass(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradasBypass($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaBypass $EntradaBypass): bool
    {
        return $this->repository->Eliminar($EntradaBypass);
    }

    public function Guardar(EntradaBypass $EntradaBypass): bool
    {
        return $this->repository->Guardar($EntradaBypass);
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
     * @param int $id_entrada
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada): array|bool
    {
        return $this->repository->datosById($id_entrada);
    }

    /**
     * Busca la clase con id_item en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?EntradaBypass
    {
        return $this->repository->findById($id_entrada);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getEntradasBypassByDestino($id_lugar, $aWhere = [], $aOperators = []): bool|array
    {
        return $this->repository->getEntradasBypassByDestino($id_lugar, $aWhere, $aOperators);
	}
}