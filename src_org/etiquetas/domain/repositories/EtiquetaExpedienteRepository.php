<?php

namespace etiquetas\domain\repositories;

use etiquetas\domain\entity\EtiquetaExpediente;
use etiquetas\infrastructure\PgEtiquetaExpedienteRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EtiquetaExpediente
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EtiquetaExpedienteRepository implements EtiquetaExpedienteRepositoryInterface
{

    /**$
     * @var EtiquetaExpedienteRepositoryInterface
     */
    private EtiquetaExpedienteRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEtiquetaExpedienteRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaExpediente
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaExpediente
     */
    public function getEtiquetasExpediente(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEtiquetasExpediente($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaExpediente $EtiquetaExpediente): bool
    {
        return $this->repository->Eliminar($EtiquetaExpediente);
    }

    public function Guardar(EtiquetaExpediente $EtiquetaExpediente): bool
    {
        return $this->repository->Guardar($EtiquetaExpediente);
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
     * @param int $id_expediente
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_expediente): array|bool
    {
        return $this->repository->datosById($id_etiqueta, $id_expediente);
    }

    /**
     * Busca la clase con id_expediente en el repositorio.
     */
    public function findById(int $id_etiqueta, int $id_expediente): ?EtiquetaExpediente
    {
        return $this->repository->findById($id_etiqueta, $id_expediente);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayExpedientes(array $a_etiquetas, string $andOr): bool|array
    {
        return $this->repository->getArrayExpedientes($a_etiquetas, $andOr);
    }

    public function deleteEtiquetasExpediente(int $id_expediente): bool
    {
        return $this->repository->deleteEtiquetasExpediente($id_expediente);
    }
}