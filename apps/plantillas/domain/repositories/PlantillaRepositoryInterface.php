<?php

namespace plantillas\domain\repositories;

use PDO;
use plantillas\domain\entity\Plantilla;

/**
 * Interfaz de la clase Plantilla y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
interface PlantillaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Plantilla
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Plantilla
     */
    public function getPlantillas(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Plantilla $Plantilla): bool;

    public function Guardar(Plantilla $Plantilla): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_plantilla
     * @return array|bool
     */
    public function datosById(int $id_plantilla): array|bool;

    /**
     * Busca la clase con id_plantilla en el repositorio.
     */
    public function findById(int $id_plantilla): ?Plantilla;

    public function getNewId_plantilla();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}