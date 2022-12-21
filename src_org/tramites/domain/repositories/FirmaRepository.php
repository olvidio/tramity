<?php

namespace tramites\domain\repositories;

use PDO;
use tramites\domain\entity\Firma;
use tramites\infrastructure\PgFirmaRepository;

/**
 *
 * Clase para gestionar la lista de objetos tipo Firma
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class FirmaRepository implements FirmaRepositoryInterface
{

    /**$
     * @var FirmaRepositoryInterface
     */
    private FirmaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgFirmaRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Firma
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Firma
     */
    public function getFirmas(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getFirmas($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Firma $Firma): bool
    {
        return $this->repository->Eliminar($Firma);
    }

    public function Guardar(Firma $Firma): bool
    {
        return $this->repository->Guardar($Firma);
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
    public function findById(int $id_item): ?Firma
    {
        return $this->repository->findById($id_item);
    }

    public function getNewId_item()
    {
        return $this->repository->getNewId_item();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function faltaFirmarReunionExpediente(int $id_expediente): bool|array
    {
        return $this->repository->faltaFirmarReunionExpediente($id_expediente);
    }

    public function getFirmasReunion(int $id_cargo): bool|array
    {
        return $this->repository->getFirmasReunion($id_cargo);
    }

    public function faltaFirmarReunion(): bool|array
    {
        return $this->repository->faltaFirmarReunion();
    }

    public function getOrdenCargo(int $id_expediente, int $cargo_tipo): int|false
    {
        return $this->repository->getOrdenCargo($id_expediente, $cargo_tipo);
    }

    public function getFirmasConforme(int $id_expediente): array
    {
        return $this->repository->getFirmasConforme($id_expediente);
    }

    public function getRecorrido(int $id_expediente): array
    {
        return $this->repository->getRecorrido($id_expediente);
    }

    public function isAnteriorOK(int $id_expediente, int $orden_tramite_ref): bool
    {
        return $this->repository->isAnteriorOK($id_expediente, $orden_tramite_ref);
    }

    public function isParaReunion(int $id_expediente): bool
    {
        return $this->repository->isParaReunion($id_expediente);
    }

    public function getUltimaOk(int $id_expediente): bool|Firma|null
    {
        return $this->repository->getUltimaOk($id_expediente);
    }

    public function hasTodasLasFirmas(int $id_expediente): bool
    {
        return $this->repository->hasTodasLasFirmas($id_expediente);
    }

    public function isParaDistribuir(int $id_expediente): bool
    {
        return $this->repository->isParaDistribuir($id_expediente);
    }

    public function getPrimeraFirma(int $id_expediente): bool|Firma|null
    {
        return $this->repository->getPrimeraFirma($id_expediente);
    }

    public function copiarFirmas(int $id_expediente, int $id_tramite, int $id_tramite_old): bool
    {
        return $this->repository->copiarFirmas($id_expediente, $id_tramite, $id_tramite_old);
    }

    public function borrarFirmas(int $id_expediente, int $id_tramite): bool
    {
        return $this->repository->borrarFirmas($id_expediente, $id_tramite);
    }
}