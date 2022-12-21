<?php

namespace documentos\domain\repositories;

use documentos\domain\entity\Documento;
use PDO;


/**
 * Interfaz de la clase DocumentoDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface DocumentoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo DocumentoDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo DocumentoDB
     */
    public function getDocumentos(array $aWhere, array $aOperators): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Documento $Documento): bool;

    public function Guardar(Documento $Documento): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_doc
     * @return array|bool
     */
    public function datosById(int $id_doc): array|bool;

    /**
     * Busca la clase con id_doc en el repositorio.
     */
    public function findById(int $id_doc): ?Documento;

    public function getNewId_doc(): int;

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}