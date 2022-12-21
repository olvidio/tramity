<?php

namespace usuarios\domain\repositories;

use PDO;
use usuarios\domain\entity\Oficina;
use web\Desplegable;

/**
 * Interfaz de la clase Oficina y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
interface OficinaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Oficina
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Oficina
     */
    public function getOficinas(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Oficina $Oficina): bool;

    public function Guardar(Oficina $Oficina): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(int $id_oficina): array|bool;

    /**
     * Busca la clase con id_oficina en el repositorio.
     */
    public function findById(int $id_oficina): ?Oficina;

    public function getNewId_oficina();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayOficinas(): array|false;

    public function getListaOficinas(): Desplegable|false;
}