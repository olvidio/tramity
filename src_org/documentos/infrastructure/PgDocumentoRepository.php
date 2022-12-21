<?php

namespace documentos\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\Set;
use documentos\domain\entity\Documento;
use documentos\domain\repositories\DocumentoRepositoryInterface;
use PDO;
use PDOException;


/**
 * Clase que adapta la tabla documentos a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class PgDocumentoRepository extends ClaseRepository implements DocumentoRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('documentos');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo DocumentoDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo DocumentoDB
     */
    public function getDocumentos(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $DocumentoSet = new Set();
        $oCondicion = new Condicion();
        $aCondicion = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondicion[] = $a;
            }
            // operadores que no requieren valores
            if ($sOperador === 'BETWEEN' || $sOperador === 'IS NULL' || $sOperador === 'IS NOT NULL' || $sOperador === 'OR') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'IN' || $sOperador === 'NOT IN') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'TXT') {
                unset($aWhere[$camp]);
            }
        }
        $sCondicion = implode(' AND ', $aCondicion);
        if ($sCondicion !== '') {
            $sCondicion = " WHERE " . $sCondicion;
        }
        $sOrdre = '';
        $sLimit = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        $sQry = "SELECT * FROM $nom_tabla " . $sCondicion . $sOrdre . $sLimit;
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClaveError = 'PgDocumentoRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgDocumentoRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {// para los bytea: (resources)
            $handle = $aDatos['documento'];
            if ($handle !== null) {
                $contents = stream_get_contents($handle);
                fclose($handle);
                $documento = $contents;
                $aDatos['documento'] = $documento;
            }
            // para las fechas del postgres (texto iso)
            $aDatos['f_upload'] = (new ConverterDate('date', $aDatos['f_upload']))->fromPg();
            $Documento = new Documento();
            $Documento->setAllAttributes($aDatos);
            $DocumentoSet->add($Documento);
        }
        return $DocumentoSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Documento $Documento): bool
    {
        $id_doc = $Documento->getId_doc();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_doc = $id_doc")) === FALSE) {
            $sClaveError = 'PgDocumentoRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Documento $Documento): bool
    {
        $id_doc = $Documento->getId_doc();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_doc);

        $aDatos = [];
        $aDatos['nom'] = $Documento->getNom();
        $aDatos['nombre_fichero'] = $Documento->getNombre_fichero();
        $aDatos['creador'] = $Documento->getCreador();
        $aDatos['visibilidad'] = $Documento->getVisibilidad();
        $aDatos['tipo_doc'] = $Documento->getTipo_doc();
        // para los ficheros
        $aDatos['documento'] =  bin2hex($Documento->getDocumento());
        // para las fechas
        $aDatos['f_upload'] = (new ConverterDate('date', $Documento->getF_upload()))->toPg();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nom                      = :nom,
					nombre_fichero           = :nombre_fichero,
					creador                  = :creador,
					visibilidad              = :visibilidad,
					f_upload                 = :f_upload,
					tipo_doc                 = :tipo_doc,
					documento                = :documento";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_doc = $id_doc")) === FALSE) {
                $sClaveError = 'PgDocumentoRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgDocumentoRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_doc'] = $Documento->getId_doc();
            $campos = "(id_doc,nom,nombre_fichero,creador,visibilidad,f_upload,tipo_doc,documento)";
            $valores = "(:id_doc,:nom,:nombre_fichero,:creador,:visibilidad,:f_upload,:tipo_doc,:documento)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgDocumentoRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgDocumentoRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_doc): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_doc = $id_doc")) === FALSE) {
            $sClaveError = 'PgDocumentoRepository.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_doc
     * @return array|bool
     */
    public function datosById(int $id_doc): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_doc = $id_doc")) === FALSE) {
            $sClaveError = 'PgDocumentoRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        // para los bytea, sobre escribo los valores:
        $sdocumento = '';
        $oDblSt->bindColumn('documento', $sdocumento);
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        if (!empty($aDatos)) {
            $aDatos['documento'] = hex2bin($sdocumento);
            // para las fechas del postgres (texto iso)
            $aDatos['f_upload'] = (new ConverterDate('date', $aDatos['f_upload']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_doc en la base de datos .
     */
    public function findById(int $id_doc): ?Documento
    {
        $aDatos = $this->datosById($id_doc);
        if (empty($aDatos)) {
            return null;
        }
        return (new Documento())->setAllAttributes($aDatos);
    }

    public function getNewId_doc(): int
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('documentos_id_doc_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }
    /* -------------------- GESTOR EXTRA ---------------------------------------- */
}