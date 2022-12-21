<?php

namespace tramites\domain\repositories;

use PDO;
use tramites\domain\entity\Tramite;
use web\Desplegable;

/**
 * Interfaz de la clase Tramite y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
interface TramiteRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Tramite
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Tramite
     */
    public function getTramites(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Tramite $Tramite): bool;

    public function Guardar(Tramite $Tramite): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_tramite
     * @return array|bool
     */
    public function datosById(int $id_tramite): array|bool;

    /**
     * Busca la clase con id_tramite en el repositorio.
     */
    public function findById(int $id_tramite): ?Tramite;

    public function getNewId_tramite();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayAbrevTramites(): array|false;

    public function getListaTramites(): Desplegable|false;
}