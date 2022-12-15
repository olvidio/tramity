<?php

namespace escritos\domain\repositories;

use escritos\domain\entity\EscritoAdjunto;
use escritos\infrastructure\PgEscritoAdjuntoRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EscritoAdjunto
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EscritoAdjuntoRepository implements EscritoAdjuntoRepositoryInterface
{

    /**$
     * @var EscritoAdjuntoRepositoryInterface
     */
    private EscritoAdjuntoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEscritoAdjuntoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EscritoAdjunto
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EscritoAdjunto
     */
    public function getEscritoAdjuntos(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEscritoAdjuntos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EscritoAdjunto $EscritoAdjunto): bool
    {
        return $this->repository->Eliminar($EscritoAdjunto);
    }

    public function Guardar(EscritoAdjunto $EscritoAdjunto): bool
    {
        return $this->repository->Guardar($EscritoAdjunto);
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
    public function findById(int $id_item): ?EscritoAdjunto
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayIdAdjuntos(int $id_escrito, int $tipo_doc = null): bool|array
    {
        return $this->repository->getArrayIdAdjuntos($id_escrito, $tipo_doc);
    }
}