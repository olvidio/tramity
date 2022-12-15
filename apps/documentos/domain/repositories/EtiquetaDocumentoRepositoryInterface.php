<?php

namespace documentos\domain\repositories;

use documentos\domain\entity\EtiquetaDocumento;
use PDO;


/**
 * Interfaz de la clase EtiquetaDocumento y su Repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
interface EtiquetaDocumentoRepositoryInterface
{

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EtiquetaDocumento
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EtiquetaDocumento
     */
    public function getEtiquetasDocumento(array $aWhere = [], array $aOperators = []): array|false;

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EtiquetaDocumento $EtiquetaDocumento): bool;

    public function Guardar(EtiquetaDocumento $EtiquetaDocumento): bool;

    public function getErrorTxt(): string;

    public function getoDbl(): PDO;

    public function setoDbl(PDO $oDbl): void;

    public function getNomTabla(): string;

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param $id_etiqueta
     * @param $id_documento
     * @return array|bool
     */
    public function datosById($id_etiqueta, $id_documento): array|bool;

    /**
     * Busca la clase con  en el repositorio.
     */
    public function findById($id_etiqueta, $id_documento): ?EtiquetaDocumento;

/* -------------------- GESTOR EXTRA ---------------------------------------- */

	public function getArrayDocumentosTodos(): bool|array;

	public function getArrayDocumentos(array $a_etiquetas, string $andOr): bool|array;

	public function deleteEtiquetasDocumento(int $id_doc): bool;
}