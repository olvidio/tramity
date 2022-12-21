<?php

namespace etiquetas\domain\repositories;

use etiquetas\domain\entity\EtiquetaEntrada;
use PDO;


/**
 * Interfaz de la clase EtiquetaEntrada y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
interface EtiquetaEntradaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaEntrada
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaEntrada
     */
    public function getEtiquetasEntrada(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaEntrada $EtiquetaEntrada): bool;

    public function Guardar(EtiquetaEntrada $EtiquetaEntrada): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_etiqueta
     * @param int $id_entrada
     * @return array|bool
     */
    public function datosById(int $id_etiqueta, int $id_entrada): array|bool;

    /**
     * Busca la clase con id_entrada en el repositorio.
     */
    public function findById(int $id_etiqueta, int $id_entrada): ?EtiquetaEntrada;

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getArrayEntradas(array $a_etiquetas, string $andOr): bool|array;

    public function deleteEtiquetasEntrada(int $id_entrada): bool;
}