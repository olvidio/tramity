<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Locale;
use web\Desplegable;

/**
 * Interfaz de la clase Locale y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
interface LocaleRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Locale
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Locale
     */
    public function getLocales(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Locale $Locale): bool;

    public function Guardar(Locale $Locale): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(string $id_locale): array|bool;

    /**
     * Busca la clase con id_locale en el repositorio.
     */
    public function findById(string $id_locale): ?Locale;

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getListaIdiomas(string $sWhere = ''): Desplegable|false;

    public function getListaLocales(string $sWhere = ''): Desplegable|false;
}