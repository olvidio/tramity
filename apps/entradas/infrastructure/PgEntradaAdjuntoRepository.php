<?php

namespace entradas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use entradas\domain\entity\EntradaAdjunto;
use entradas\domain\repositories\EntradaAdjuntoRepositoryInterface;
use PDO;
use PDOException;


/**
 * Clase que adapta la tabla entrada_adjuntos a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class PgEntradaAdjuntoRepository extends ClaseRepository implements EntradaAdjuntoRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('entrada_adjuntos');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaAdjunto
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaAdjunto
     */
    public function getEntradasAdjunto(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaAdjuntoSet = new Set();
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
            $sClaveError = 'PgEntradaAdjuntoRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEntradaAdjuntoRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {// para los bytea: (resources)
            $handle = $aDatos['adjunto'];
            if ($handle !== null) {
                $contents = stream_get_contents($handle);
                fclose($handle);
                $adjunto = $contents;
                $aDatos['adjunto'] = $adjunto;
            }
            $EntradaAdjunto = new EntradaAdjunto();
            $EntradaAdjunto->setAllAttributes($aDatos);
            $EntradaAdjuntoSet->add($EntradaAdjunto);
        }
        return $EntradaAdjuntoSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaAdjunto $EntradaAdjunto): bool
    {
        $id_item = $EntradaAdjunto->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'PgEntradaAdjuntoRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(EntradaAdjunto $EntradaAdjunto): bool
    {
        $id_item = $EntradaAdjunto->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_item);

        $aDatos = [];
        $aDatos['id_entrada'] = $EntradaAdjunto->getId_entrada();
        $aDatos['nom'] = $EntradaAdjunto->getNom();
        $aDatos['adjunto'] = $EntradaAdjunto->getAdjunto();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_entrada               = :id_entrada,
					nom                      = :nom,
					adjunto                  = :adjunto";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item = $id_item")) === FALSE) {
                $sClaveError = 'PgEntradaAdjuntoRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaAdjuntoRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_item'] = $EntradaAdjunto->getId_item();
            $campos = "(id_item,id_entrada,nom,adjunto)";
            $valores = "(:id_item,:id_entrada,:nom,:adjunto)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEntradaAdjuntoRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaAdjuntoRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_item): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'PgEntradaAdjuntoRepository.isNew';
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
     * @param int $id_item
     * @return array|bool
     */
    public function datosById(int $id_item): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'PgEntradaAdjuntoRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        // para los bytea, sobre escribo los valores:
        $sadjunto = '';
        $oDblSt->bindColumn('adjunto', $sadjunto, PDO::PARAM_STR);
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        if (!empty($aDatos)) {
            $aDatos['adjunto'] = $sadjunto;
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_item en la base de datos .
     */
    public function findById(int $id_item): ?EntradaAdjunto
    {
        $aDatos = $this->datosById($id_item);
        if (empty($aDatos)) {
            return null;
        }
        return (new EntradaAdjunto())->setAllAttributes($aDatos);
    }

    public function getNewId_item()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('entrada_adjuntos_id_item_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getArrayIdAdjuntos(int $id_entrada): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $aAdjuntos = [];

        $sQry = "SELECT * FROM $nom_tabla WHERE id_entrada = $id_entrada ";
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'EntradaAdjuntoRepository.getArrayIdAdjuntos.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute()) === FALSE) {
            $sClauError = 'EntradaAdjuntoRepository.getArrayIdAdjuntos.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $id_item = $aDades['id_item'];
            $nom = $aDades['nom'];
            $aAdjuntos[$id_item] = $nom;
        }
        return $aAdjuntos;

    }

}