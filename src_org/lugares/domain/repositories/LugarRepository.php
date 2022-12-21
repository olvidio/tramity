<?php

namespace lugares\domain\repositories;

use lugares\domain\entity\Lugar;
use lugares\infrastructure\PgLugarRepository;
use PDO;

/**
 *
 * Clase para gestionar la lista de objetos tipo Lugar
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class LugarRepository implements LugarRepositoryInterface
{

    /**$
     * @var LugarRepositoryInterface
     */
    private LugarRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgLugarRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Lugar
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Lugar
     */
    public function getLugares(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getLugares($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Lugar $Lugar): bool
    {
        return $this->repository->Eliminar($Lugar);
    }

    public function Guardar(Lugar $Lugar): bool
    {
        return $this->repository->Guardar($Lugar);
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
     * @param int $id_lugar
     * @return array|bool
     */
    public function datosById(int $id_lugar): array|bool
    {
        return $this->repository->datosById($id_lugar);
    }

    /**
     * Busca la clase con id_lugar en el repositorio.
     */
    public function findById(int $id_lugar): ?Lugar
    {
        return $this->repository->findById($id_lugar);
    }

    public function getNewId_lugar()
    {
        return $this->repository->getNewId_lugar();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getPlataformas(bool $propia = FALSE): array
    {
        return $this->repository->getPlataformas();
    }

    public function getId_iese(): int
    {
        return $this->repository->getId_iese();
	}

    public function getId_cr(): int
    {
        return $this->repository->getId_cr();
	}

    public function getSigla_superior(string $sigla_base, bool $return_id = FALSE): string|int
    {
        return $this->repository->getSigla_superior($sigla_base);
    }

    public function getId_sigla_local(): ?int
    {
        return $this->repository->getId_sigla_local();
    }

    public function getArrayBusquedas(bool $ctr_anulados = FALSE): array
    {
        return $this->repository->getArrayBusquedas();
    }

    public function getArrayLugaresCtr(bool $ctr_anulados = FALSE): bool|array
    {
        return $this->repository->getArrayLugaresCtr();
    }

    public function getArrayLugaresTipo(string $tipo_ctr, bool $ctr_anulados = FALSE): bool|array
    {
        return $this->repository->getArrayLugaresTipo($tipo_ctr);
    }

    public function getArrayLugares(bool $ctr_anulados = FALSE): bool|array
    {
        return $this->repository->getArrayLugares();
    }
}