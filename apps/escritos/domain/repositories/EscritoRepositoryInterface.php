<?php

namespace escritos\domain\repositories;

use escritos\domain\entity\Escrito;
use JsonException;
use PDO;


/**
 * Interfaz de la clase EscritoDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
interface EscritoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EscritoDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EscritoDB
     * @throws JsonException
     */
    public function getEscritos(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Escrito $Escrito): bool;

    public function Guardar(Escrito $Escrito): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_escrito
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_escrito): array|bool;

    /**
     * Busca la clase con id_escrito en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_escrito): ?Escrito;

    public function getNewId_escrito();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getEscritosByRef(array $aProt_ref = [], array $aWhere = [], array $aOperators = []): bool|array;

    public function getEscritosByProtLocal(array $aProt_local = [], array $aWhere = [], array $aOperators = []): bool|array;

    public function getEscritosByProtDestino(array $aProt_destino = [], array $aWhere = [], array $aOperators = []): bool|array;

    public function getEscritosByLugarDeGrupo(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array;

    public function getEscritosByLugar(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array;

    public function getEscritosNumerados(array $aWhere = [], array $aOperators = []): array;

    public function getEscritosByLocal(int $id_lugar, array $aWhere = [], array $aOperators = []): array;
}