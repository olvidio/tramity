<?php

namespace etiquetas\domain\repositories;

use PDO;
use etiquetas\domain\entity\Etiqueta;
use etiquetas\infrastructure\PgEtiquetaRepository;
use web\Desplegable;


use function core\is_true;
/**
 *
 * Clase para gestionar la lista de objetos tipo Etiqueta
 * 
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 13/12/2022
 */
class EtiquetaRepository implements EtiquetaRepositoryInterface
{

    /**$
     * @var EtiquetaRepositoryInterface
     */
    private EtiquetaRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgEtiquetaRepository();
    }

/* -------------------- GESTOR BASE ---------------------------------------- */

	/**
	 * devuelve una colección (array) de objetos de tipo Etiqueta
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo Etiqueta
	
	 */
	public function getEtiquetas(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
	    return $this->repository->getEtiquetas($aWhere, $aOperators);
	}
	
/* -------------------- ENTIDAD --------------------------------------------- */

	public function Eliminar(Etiqueta $Etiqueta): bool
    {
        return $this->repository->Eliminar($Etiqueta);
    }

	public function Guardar(Etiqueta $Etiqueta): bool
    {
        return $this->repository->Guardar($Etiqueta);
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
     * @return array|bool
	
     */
    public function datosById(int $id_etiqueta): array|bool
    {
        return $this->repository->datosById($id_etiqueta);
    }
	
    /**
     * Busca la clase con id_etiqueta en el repositorio.
	
     */
    public function findById(int $id_etiqueta): ?Etiqueta
    {
        return $this->repository->findById($id_etiqueta);
    }
	
    public function getNewId_etiqueta()
    {
        return $this->repository->getNewId_etiqueta();
    }

/* -------------------- GESTOR EXTRA ---------------------------------------- */

	public function getArrayMisEtiquetas($id_cargo = ''): array
	{
		return $this->repository->getArrayMisEtiquetas();
	}

	public function getMisEtiquetas($id_cargo = '')
	{
		return $this->repository->getMisEtiquetas();
	}

	public function getEtiquetasPersonales($id_cargo = '')
	{
		return $this->repository->getEtiquetasPersonales();
	}

	public function getEtiquetasMiOficina($id_cargo = '')
	{
		return $this->repository->getEtiquetasMiOficina($id_cargo = '');
	}
}