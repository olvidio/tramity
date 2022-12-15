<?php

namespace expedientes\domain\repositories;

use expedientes\domain\entity\ExpedienteDB;
use expedientes\domain\entity\Expediente;
use JsonException;
use PDO;


/**
 * Interfaz de la clase ExpedienteDB y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
interface ExpedienteRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo ExpedienteDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo ExpedienteDB
     * @throws JsonException
     */
    public function getExpedientes(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Expediente $Expediente): bool;

    public function Guardar(Expediente $Expediente): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_expediente
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_expediente): array|bool;

    /**
     * Busca la clase con id_expediente en el repositorio.
     * @throws JsonException
     */
    public function findById(int $id_expediente): ?ExpedienteDB;

    public function getNewId_expediente();

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getIdExpedientesConAntecedente($id, $tipo);

    public function getIdExpedientesPreparar($id_cargo, $visto = 'no_visto');
}