<?php

namespace tmp\domain\repositories;

use PDO;
use tmp\domain\entity\Firma;
use tmp\infrastructure\PgFirmaRepository;
use web\Desplegable;

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
	public function getFirmas(array $aWhere=[], array $aOperators=[]): array|FALSE
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
}