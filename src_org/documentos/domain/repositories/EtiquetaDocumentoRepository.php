<?php

namespace documentos\domain\repositories;

use documentos\domain\entity\EtiquetaDocumento;
use documentos\infrastructure\PgEtiquetaDocumentoRepository;
use PDO;


/**
 *
 * Clase para gestionar la lista de objetos tipo EtiquetaDocumento
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EtiquetaDocumentoRepository implements EtiquetaDocumentoRepositoryInterface
{

    /**$
     * @var EtiquetaDocumentoRepositoryInterface
     */
    private EtiquetaDocumentoRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEtiquetaDocumentoRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaDocumento
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaDocumento
     */
    public function getEtiquetasDocumento(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getEtiquetasDocumento($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaDocumento $EtiquetaDocumento): bool
    {
        return $this->repository->Eliminar($EtiquetaDocumento);
    }

    public function Guardar(EtiquetaDocumento $EtiquetaDocumento): bool
    {
        return $this->repository->Guardar($EtiquetaDocumento);
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
     * @param $id_etiqueta
     * @param $id_documento
     * @return array|bool
     */
    public function datosById($id_etiqueta, $id_documento): array|bool
    {
        return $this->repository->datosById($id_etiqueta, $id_documento);
    }

    /**
     * Busca la clase con  en el repositorio.
     */
    public function findById($id_etiqueta, $id_documento): ?EtiquetaDocumento
    {
        return $this->repository->findById($id_etiqueta, $id_documento);
    }

/* -------------------- GESTOR EXTRA ---------------------------------------- */

	public function getArrayDocumentosTodos(): bool|array
	{
		return $this->repository->getArrayDocumentosTodos();
	}

	public function getArrayDocumentos(array $a_etiquetas, string $andOr): bool|array
	{
		return $this->repository->getArrayDocumentos($a_etiquetas, $andOr);
	}

	public function deleteEtiquetasDocumento(int $id_doc): bool
	{
		return $this->repository->deleteEtiquetasDocumento($id_doc);
	}
}