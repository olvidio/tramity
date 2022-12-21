<?php

namespace etiquetas\domain\repositories;

use etiquetas\domain\entity\EtiquetaEntrada;
use etiquetas\infrastructure\PgEtiquetaEntradaRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EtiquetaEntrada
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EtiquetaEntradaRepository implements EtiquetaEntradaRepositoryInterface
{

    /**$
     * @var EtiquetaEntradaRepositoryInterface
     */
    private EtiquetaEntradaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEtiquetaEntradaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaEntrada
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaEntrada
     */
    public function getEtiquetasEntrada(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEtiquetasEntrada($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaEntrada $EtiquetaEntrada): bool
    {
        return $this->repository->Eliminar($EtiquetaEntrada);
    }

    public function Guardar(EtiquetaEntrada $EtiquetaEntrada): bool
    {
        return $this->repository->Guardar($EtiquetaEntrada);
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
     * @param int $id_etiqueta
     * @param int $id_entrada
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_entrada): array|bool
    {
        return $this->repository->datosById($id_etiqueta, $id_entrada);
    }

    /**
     * Busca la clase con id_entrada en el repositorio.
     */
    public function findById(int $id_etiqueta, int $id_entrada): ?EtiquetaEntrada
    {
        return $this->repository->findById($id_etiqueta, $id_entrada);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayEntradas(array $a_etiquetas, string $andOr): bool|array
    {
        return $this->repository->getArrayEntradas($a_etiquetas, $andOr);
    }

    public function deleteEtiquetasEntrada(int $id_entrada):bool
    {
        return $this->repository->deleteEtiquetasEntrada($id_entrada);
    }
}