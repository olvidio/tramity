<?php

namespace expedientes\domain\repositories;

use expedientes\domain\entity\Expediente;
use expedientes\infrastructure\PgExpedienteRepository;
use JsonException;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo ExpedienteDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class ExpedienteRepository implements ExpedienteRepositoryInterface
{

    /**$
     * @var ExpedienteRepositoryInterface
     */
    private ExpedienteRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgExpedienteRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo ExpedienteDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo ExpedienteDB
     * @throws JsonException
     */
    public function getExpedientes(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getExpedientes($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Expediente $Expediente): bool
    {
        return $this->repository->Eliminar($Expediente);
    }

    public function Guardar(Expediente $Expediente): bool
    {
        return $this->repository->Guardar($Expediente);
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
     * @param int $id_expediente
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_expediente): array|bool
    {
        return $this->repository->datosById($id_expediente);
    }

    /**
     * Busca la clase con id_expediente en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_expediente): ?Expediente
    {
        return $this->repository->findById($id_expediente);
    }

    public function getNewId_expediente()
    {
        return $this->repository->getNewId_expediente();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getIdExpedientesConAntecedente($id, $tipo)
    {
        return $this->repository->getIdExpedientesConAntecedente($id, $tipo);
    }

    public function getIdExpedientesPreparar($id_cargo, $visto = 'no_visto')
    {
        return $this->repository->getIdExpedientesPreparar($id_cargo, $visto = 'no_visto');
    }
}