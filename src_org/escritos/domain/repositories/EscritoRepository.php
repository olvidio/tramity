<?php

namespace escritos\domain\repositories;

use escritos\domain\entity\Escrito;
use escritos\infrastructure\PgEscritoRepository;
use JsonException;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo Escrito
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EscritoRepository implements EscritoRepositoryInterface
{

    /**$
     * @var EscritoRepositoryInterface
     */
    private EscritoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEscritoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Escrito
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Escrito
     * @throws JsonException
     */
    public function getEscritos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEscritos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Escrito $Escrito): bool
    {
        return $this->repository->Eliminar($Escrito);
    }

    public function Guardar(Escrito $Escrito): bool
    {
        return $this->repository->Guardar($Escrito);
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
     * @param int $id_escrito
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_escrito): array|bool
    {
        return $this->repository->datosById($id_escrito);
    }

    /**
     * Busca la clase con id_escrito en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_escrito): ?Escrito
    {
        return $this->repository->findById($id_escrito);
    }

    public function getNewId_escrito()
    {
        return $this->repository->getNewId_escrito();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getEscritosByRef(array $aProt_ref = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEscritosByRef($aProt_ref, $aWhere, $aOperators);
    }

    public function getEscritosByProtLocal(array $aProt_local = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEscritosByProtLocal($aProt_local, $aWhere, $aOperators);
    }

    public function getEscritosByProtDestino(array $aProt_destino = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEscritosByProtDestino($aProt_destino, $aWhere, $aOperators);
    }

    public function getEscritosByLugarDeGrupo(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEscritosByLugarDeGrupo($id_lugar, $aWhere, $aOperators);
    }

    public function getEscritosByLugar(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array
    {
        return $this->repository->getEscritosByLugar($id_lugar, $aWhere, $aOperators);
    }

    public function getEscritosNumerados(array $aWhere = [], array $aOperators = []): array
    {
        return $this->repository->getEscritosNumerados($aWhere, $aOperators);
    }

    public function getEscritosByLocal(int $id_lugar, array $aWhere = [], array $aOperators = []): array
    {
        return $this->repository->getEscritosByLocal($id_lugar, $aWhere, $aOperators);
    }
}