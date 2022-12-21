<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\Entrada;
use entradas\infrastructure\PgEntradaDBRepository;
use JsonException;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EntradaDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaDBRepository implements EntradaDBRepositoryInterface
{

    /**$
     * @var EntradaDBRepositoryInterface
     */
    protected EntradaDBRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEntradaDBRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaDB
     * @throws JsonException
     */
    public function getEntradas(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEntradas($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Entrada $Entrada): bool
    {
        return $this->repository->Eliminar($Entrada);
    }

    public function Guardar(Entrada $Entrada): bool
    {
        return $this->repository->Guardar($Entrada);
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
     * Busca la clase con id_entrada en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?Entrada
    {
        return $this->repository->findById($id_entrada);
    }

    public function getNewId_entrada()
    {
        return $this->repository->getNewId_entrada();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function posiblesYear(): bool|array
    {
        return $this->repository->posiblesYear();
    }

    public function getEntradasNoVistoDB($oficina, $tipo_oficina, $a_visibilidad = [])
    {
        return $this->repository->getEntradasNoVistoDB($oficina, $tipo_oficina, $a_visibilidad = []);
    }

    public function getEntradasByVistoDB($aVisto = [], $aWhere = [], $aOperators = [])
    {
        return $this->repository->getEntradasByVistoDB($aVisto = [], $aWhere = [], $aOperators = []);
    }

    public function getEntradasByRefDB($aProt_ref = [], $aWhere = [], $aOperators = [])
    {
        return $this->repository->getEntradasByRefDB($aProt_ref = [], $aWhere = [], $aOperators = []);
    }

    public function getEntradasByProtOrigenDB($aProt_origen = [], $aWhere = [], $aOperators = [])
    {
        return $this->repository->getEntradasByProtOrigenDB($aProt_origen = [], $aWhere = [], $aOperators = []);
    }

    public function getEntradasNumeradas($aWhere = array(), $aOperators = array())
    {
        return $this->repository->getEntradasNumeradas($aWhere = array(), $aOperators = array());
    }

    public function getEntradasByLugarDB($id_lugar, $aWhere = array(), $aOperators = array())
    {
        return $this->repository->getEntradasByLugarDB($id_lugar, $aWhere = array(), $aOperators = array());
    }

}