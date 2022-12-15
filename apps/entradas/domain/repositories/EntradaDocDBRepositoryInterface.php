<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaDocDB;
use PDO;


/**
 * Interfaz de la clase EntradaDocDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface EntradaDocDBRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaDocDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaDocDB
     */
    public function getEntradasDocsDB(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaDocDB $EntradaDocDB): bool;

    public function Guardar(EntradaDocDB $EntradaDocDB): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    public function setNomTabla(string $nom_tabla): void;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_entrada
     * @return array|bool
     */
    public function datosById(int $id_entrada): array|bool;

    /**
     * Busca la clase con id_entrada en el repositorio.
     */
    public function findById(int $id_entrada): ?EntradaDocDB;

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}