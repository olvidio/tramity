<?php

namespace pendientes\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\Set;
use PDO;
use PDOException;
use pendientes\domain\entity\PendienteDB;
use pendientes\domain\repositories\PendienteDBRepositoryInterface;
use function core\is_true;


/**
 * Clase que adapta la tabla pendientes a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PgPendienteDBRepository extends ClaseRepository implements PendienteDBRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('pendientes');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo PendienteDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo PendienteDB
     */
    public function getPendientesDB(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $PendienteDBSet = new Set();
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
            $sClaveError = 'PgPendienteDBRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgPendienteDBRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para las fechas del postgres (texto iso)
            $aDatos['f_acabado'] = (new ConverterDate('date', $aDatos['f_acabado']))->fromPg();
            $aDatos['f_plazo'] = (new ConverterDate('date', $aDatos['f_plazo']))->fromPg();
            $aDatos['f_inicio'] = (new ConverterDate('date', $aDatos['f_inicio']))->fromPg();
            $PendienteDB = new PendienteDB();
            $PendienteDB->setAllAttributes($aDatos);
            $PendienteDBSet->add($PendienteDB);
        }
        return $PendienteDBSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(PendienteDB $PendienteDB): bool
    {
        $id_pendiente = $PendienteDB->getId_pendiente();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_pendiente = $id_pendiente")) === FALSE) {
            $sClaveError = 'PendienteDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(PendienteDB $PendienteDB): bool
    {
        $id_pendiente = $PendienteDB->getId_pendiente();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_pendiente);

        $aDatos = [];
        $aDatos['asunto'] = $PendienteDB->getAsunto();
        $aDatos['status'] = $PendienteDB->getStatus();
        $aDatos['ref_mas'] = $PendienteDB->getRef_mas();
        $aDatos['observ'] = $PendienteDB->getObserv();
        $aDatos['encargado'] = $PendienteDB->getEncargado();
        $aDatos['cancilleria'] = $PendienteDB->isCancilleria();
        $aDatos['visibilidad'] = $PendienteDB->getVisibilidad();
        $aDatos['detalle'] = $PendienteDB->getDetalle();
        $aDatos['pendiente_con'] = $PendienteDB->getPendiente_con();
        $aDatos['etiquetas'] = $PendienteDB->getEtiquetas();
        $aDatos['oficinas'] = $PendienteDB->getOficinas();
        $aDatos['id_oficina'] = $PendienteDB->getId_oficina();
        $aDatos['rrule'] = $PendienteDB->getRrule();
        // para las fechas
        $aDatos['f_inicio'] = (new ConverterDate('date', $PendienteDB->getF_inicio()))->toPg();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['cancilleria'])) {
            $aDatos['cancilleria'] = 'true';
        } else {
            $aDatos['cancilleria'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					asunto                   = :asunto,
					status                   = :status,
					f_acabado                = :f_acabado,
					f_plazo                  = :f_plazo,
					ref_mas                  = :ref_mas,
					observ                   = :observ,
					encargado                = :encargado,
					cancilleria              = :cancilleria,
					visibilidad              = :visibilidad,
					detalle                  = :detalle,
					pendiente_con            = :pendiente_con,
					etiquetas                = :etiquetas,
					oficinas                 = :oficinas,
					id_oficina               = :id_oficina,
					rrule                    = :rrule,
					f_inicio                 = :f_inicio";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_pendiente = $id_pendiente")) === FALSE) {
                $sClaveError = 'PendienteDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PendienteDB.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_pendiente'] = $PendienteDB->getId_pendiente();
            $campos = "(id_pendiente,asunto,status,f_acabado,f_plazo,ref_mas,observ,encargado,cancilleria,visibilidad,detalle,pendiente_con,etiquetas,oficinas,id_oficina,rrule,f_inicio)";
            $valores = "(:id_pendiente,:asunto,:status,:f_acabado,:f_plazo,:ref_mas,:observ,:encargado,:cancilleria,:visibilidad,:detalle,:pendiente_con,:etiquetas,:oficinas,:id_oficina,:rrule,:f_inicio)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PendienteDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PendienteDB.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_pendiente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_pendiente = $id_pendiente")) === FALSE) {
            $sClaveError = 'PendienteDB.isNew';
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
     * @param int $id_pendiente
     * @return array|bool
     */
    public function datosById(int $id_pendiente): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_pendiente = $id_pendiente")) === FALSE) {
            $sClaveError = 'PendienteDB.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_acabado'] = (new ConverterDate('date', $aDatos['f_acabado']))->fromPg();
            $aDatos['f_plazo'] = (new ConverterDate('date', $aDatos['f_plazo']))->fromPg();
            $aDatos['f_inicio'] = (new ConverterDate('date', $aDatos['f_inicio']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_pendiente en la base de datos .
     */
    public function findById(int $id_pendiente): ?PendienteDB
    {
        $aDatos = $this->datosById($id_pendiente);
        if (empty($aDatos)) {
            return null;
        }
        return (new PendienteDB())->setAllAttributes($aDatos);
    }

    public function getNewId_pendiente()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('pendientes_id_pendiente_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

}