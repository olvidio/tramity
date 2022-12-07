<?php

namespace tramites\domain\repositories;

use PDO;
use tramites\domain\entity\Firma;

/**
 * Interfaz de la clase Firma y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
interface FirmaRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Firma
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Firma
     */
    public function getFirmas(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Firma $Firma): bool;

    public function Guardar(Firma $Firma): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

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
    public function findById(int $id_item): ?Firma;

    public function getNewId_item();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function faltaFirmarReunionExpediente(int $id_expediente): bool|array;

    public function getFirmasReunion(int $id_cargo): bool|array;

    public function faltaFirmarReunion(): bool|array;

    public function getOrdenCargo(int $id_expediente, int $cargo_tipo): int|false;

    public function getFirmasConforme(int $id_expediente): array;

    public function getRecorrido(int $id_expediente): array;

    public function isAnteriorOK(int $id_expediente, int $orden_tramite_ref): bool;

    public function isParaReunion(int $id_expediente): bool;

    public function getUltimaOk(int $id_expediente): bool|Firma|null;

    public function hasTodasLasFirmas(int $id_expediente): bool;

    public function isParaDistribuir(int $id_expediente): bool;

    public function getPrimeraFirma(int $id_expediente): bool|Firma|null;

    public function copiarFirmas(int $id_expediente, int $id_tramite, int $id_tramite_old): bool;

    public function borrarFirmas(int $id_expediente, int $id_tramite): bool;
}