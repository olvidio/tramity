<?php

namespace documentos\domain\repositories;

use documentos\domain\entity\Documento;
use documentos\infrastructure\PgDocumentoRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo DocumentoDB
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class DocumentoRepository implements DocumentoRepositoryInterface
{

    /**$
     * @var DocumentoRepositoryInterface
     */
    private DocumentoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgDocumentoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo DocumentoDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo DocumentoDB
     */
    public function getDocumentos(array $aWhere, array $aOperators): array|false
    {
        return $this->repository->getDocumentos($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Documento $Documento): bool
    {
        return $this->repository->Eliminar($Documento);
    }

    public function Guardar(Documento $Documento): bool
    {
        return $this->repository->Guardar($Documento);
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
     * @param int $id_doc
     * @return array|bool
     */
    public function datosById(int $id_doc): array|bool
    {
        return $this->repository->datosById($id_doc);
    }

    /**
     * Busca la clase con id_doc en el repositorio.
     */
    public function findById(int $id_doc): ?Documento
    {
        return $this->repository->findById($id_doc);
    }

    public function getNewId_doc(): int
    {
        return $this->repository->getNewId_doc();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}