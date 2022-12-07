<?php

namespace tmp\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\Set;
use PDO;
use PDOException;
use tmp\domain\entity\Firma;
use tmp\domain\repositories\FirmaRepositoryInterface;


/**
 * Clase que adapta la tabla expediente_firmas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PgFirmaRepository extends ClaseRepository implements FirmaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('expediente_firmas');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Firma
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Firma
     */
    public function getFirmas(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $FirmaSet = new Set();
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
            $sClaveError = 'PgFirmaRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgFirmaRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para las fechas del postgres (texto iso)
            $aDatos['f_valor'] = (new ConverterDate('timestamp', $aDatos['f_valor']))->fromPg();
            $Firma = new Firma();
            $Firma->setAllAttributes($aDatos);
            $FirmaSet->add($Firma);
        }
        return $FirmaSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Firma $Firma): bool
    {
        $id_item = $Firma->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'Firma.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Firma $Firma): bool
    {
        $id_item = $Firma->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_item);

        $aDatos = [];
        $aDatos['id_expediente'] = $Firma->getId_expediente();
        $aDatos['id_tramite'] = $Firma->getId_tramite();
        $aDatos['id_cargo_creador'] = $Firma->getId_cargo_creador();
        $aDatos['cargo_tipo'] = $Firma->getCargo_tipo();
        $aDatos['id_cargo'] = $Firma->getId_cargo();
        $aDatos['id_usuario'] = $Firma->getId_usuario();
        $aDatos['orden_tramite'] = $Firma->getOrden_tramite();
        $aDatos['orden_oficina'] = $Firma->getOrden_oficina();
        $aDatos['tipo'] = $Firma->getTipo();
        $aDatos['valor'] = $Firma->getValor();
        $aDatos['observ_creador'] = $Firma->getObserv_creador();
        $aDatos['observ'] = $Firma->getObserv();
        // para las fechas
        $aDatos['f_valor'] = (new ConverterDate('timestamp', $Firma->getF_valor()))->toPg();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_expediente            = :id_expediente,
					id_tramite               = :id_tramite,
					id_cargo_creador         = :id_cargo_creador,
					cargo_tipo               = :cargo_tipo,
					id_cargo                 = :id_cargo,
					id_usuario               = :id_usuario,
					orden_tramite            = :orden_tramite,
					orden_oficina            = :orden_oficina,
					tipo                     = :tipo,
					valor                    = :valor,
					observ_creador           = :observ_creador,
					observ                   = :observ,
					f_valor                  = :f_valor";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item = $id_item")) === FALSE) {
                $sClaveError = 'Firma.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Firma.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_item'] = $Firma->getId_item();
            $campos = "(id_item,id_expediente,id_tramite,id_cargo_creador,cargo_tipo,id_cargo,id_usuario,orden_tramite,orden_oficina,tipo,valor,observ_creador,observ,f_valor)";
            $valores = "(:id_item,:id_expediente,:id_tramite,:id_cargo_creador,:cargo_tipo,:id_cargo,:id_usuario,:orden_tramite,:orden_oficina,:tipo,:valor,:observ_creador,:observ,:f_valor)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Firma.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Firma.insertar.execute';
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
            $sClaveError = 'Firma.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Busca la clase con id_item en la base de datos .
     */
    public function findById(int $id_item): ?Firma
    {
        $aDatos = $this->datosById($id_item);
        if (empty($aDatos)) {
            return null;
        }
        return (new Firma())->setAllAttributes($aDatos);
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
            $sClaveError = 'Firma.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_valor'] = (new ConverterDate('timestamp', $aDatos['f_valor']))->fromPg();
        }
        return $aDatos;
    }

    public function getNewId_item()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('expediente_firmas_id_item_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }
}