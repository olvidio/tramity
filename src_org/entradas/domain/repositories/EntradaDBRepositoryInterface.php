<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\Entrada;
use JsonException;
use PDO;


/**
 * Interfaz de la clase EntradaDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface EntradaDBRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaDB
     * @throws JsonException
     */
    public function getEntradas(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Entrada $Entrada): bool;

    public function Guardar(Entrada $Entrada): bool;

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
     * Busca la clase con id_entrada en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?Entrada;

    public function getNewId_entrada();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function posiblesYear(): bool|array;

    public function getEntradasNoVistoDB($oficina, $tipo_oficina, $a_visibilidad = []);

    public function getEntradasByVistoDB($aVisto = [], $aWhere = [], $aOperators = []);

    public function getEntradasByRefDB($aProt_ref = [], $aWhere = [], $aOperators = []);

    public function getEntradasByProtOrigenDB($aProt_origen = [], $aWhere = [], $aOperators = []);

    public function getEntradasNumeradas($aWhere = array(), $aOperators = array());

    public function getEntradasByLugarDB($id_lugar, $aWhere = array(), $aOperators = array());

}