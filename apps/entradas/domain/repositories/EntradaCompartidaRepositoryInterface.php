<?php

namespace entradas\domain\repositories;

use entradas\domain\entity\EntradaCompartida;
use JsonException;
use PDO;


/**
 * Interfaz de la clase EntradaCompartida y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
interface EntradaCompartidaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaCompartida
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaCompartida
     * @throws JsonException
     */
    public function getEntradasCompartidas(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaCompartida $EntradaCompartida): bool;

    public function Guardar(EntradaCompartida $EntradaCompartida): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_entrada_compartida
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada_compartida): array|bool;

    /**
     * Busca la clase con id_entrada_compartida en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_entrada_compartida): ?EntradaCompartida;

    public function getNewId_entrada_compartida();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function posiblesYear(): bool|array;

    public function getEntradasByProtOrigenDestino(array $aProt_origen,
                                                   int   $id_destino,
                                                   array $aWhere = [],
                                                   array $aOperators = []): bool|array;

    public function getEntradasByProtOrigenDB(array $aProt_origen, array $aWhere = [], array $aOperators = []): bool|array;
}