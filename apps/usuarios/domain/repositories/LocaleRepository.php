<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Locale;
use usuarios\infrastructure\PgLocaleRepository;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo Locale
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class LocaleRepository implements LocaleRepositoryInterface
{

    /**$
     * @var LocaleRepositoryInterface
     */
    private LocaleRepositoryInterface $repository;

    public function __construct()
    {
        $this->repository = new PgLocaleRepository();
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Locale
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Locale
     */
    public function getLocales(array $aWhere = [], array $aOperators = []): array|false
    {
        return $this->repository->getLocales($aWhere, $aOperators);
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Locale $Locale): bool
    {
        return $this->repository->Eliminar($Locale);
    }

    public function Guardar(Locale $Locale): bool
    {
        return $this->repository->Guardar($Locale);
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
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(string $id_locale): array|bool
    {
        return $this->repository->datosById($id_locale);
    }

    /**
     * Busca la clase con id_locale en el repositorio.
     */
    public function findById(string $id_locale): ?Locale
    {
        return $this->repository->findById($id_locale);
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getListaIdiomas(string $sWhere = ''): Desplegable|false
    {
        return $this->repository->getListaIdiomas($sWhere);
    }

    public function getListaLocales(string $sWhere = ''): Desplegable|false
    {
        return $this->repository->getListaLocales($sWhere);
    }
}