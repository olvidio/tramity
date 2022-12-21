<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaAdjunto;
use PDO;


/**
 * Interfaz de la clase EntradaAdjunto y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
interface EntradaAdjuntoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaAdjunto
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaAdjunto
     */
    public function getEntradasAdjunto(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaAdjunto $EntradaAdjunto): bool;

    public function Guardar(EntradaAdjunto $EntradaAdjunto): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    public function setNomTabla(string $nom_tabla): void;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_item
     * @return array|bool
     */
    public function datosById(int $id_item): array|bool;

    /**
     * Busca la clase con id_item en el repositorio.
     */
    public function findById(int $id_item): ?EntradaAdjunto;

    public function getNewId_item();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayIdAdjuntos(int $id_entrada): bool|array;
}