<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaBypass;
use JsonException;
use PDO;


/**
 * Interfaz de la clase EntradaBypass y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface EntradaBypassRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaBypass
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaBypass
     * @throws JsonException
     */
    public function getEntradasBypass(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaBypass $EntradaBypass): bool;

    public function Guardar(EntradaBypass $EntradaBypass): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_entrada
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada): array|bool;

    /**
     * Busca la clase con id_item en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?EntradaBypass;

    public function getNewId_item();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getEntradasBypassByDestino($id_lugar, $aWhere = array(), $aOperators = array()): bool|array;
}