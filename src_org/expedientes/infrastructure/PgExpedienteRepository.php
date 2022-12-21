<?php

namespace expedientes\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\ConverterJson;
use core\Set;
use expedientes\domain\entity\Expediente;
use expedientes\domain\repositories\ExpedienteRepositoryInterface;
use JsonException;
use PDO;
use PDOException;
use function core\array_pg2php;
use function core\array_php2pg;


/**
 * Clase que adapta la tabla expedientes a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class PgExpedienteRepository extends ClaseRepository implements ExpedienteRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('expedientes');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Expediente
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Expediente
     * @throws JsonException
     */
    public function getExpedientes(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $ExpedienteDBSet = new Set();
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
            $sClaveError = 'PgExpedienteRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgExpedienteRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            $aDatos['etiquetas'] = array_pg2php($aDatos['etiquetas']);
            $aDatos['firmas_oficina'] = array_pg2php($aDatos['firmas_oficina']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_ini_circulacion'] = (new ConverterDate('date', $aDatos['f_ini_circulacion']))->fromPg();
            $aDatos['f_reunion'] = (new ConverterDate('timestamp', $aDatos['f_reunion']))->fromPg();
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            // para los json
            $aDatos['json_antecedentes'] = (new ConverterJson($aDatos['json_antecedentes']))->fromPg();
            $aDatos['json_acciones'] = (new ConverterJson($aDatos['json_acciones']))->fromPg();
            $aDatos['json_preparar'] = (new ConverterJson($aDatos['json_preparar']))->fromPg();
            $Expediente = new Expediente();
            $Expediente->setAllAttributes($aDatos);
            $ExpedienteDBSet->add($Expediente);
        }
        return $ExpedienteDBSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Expediente $Expediente): bool
    {
        $id_expediente = $Expediente->getId_expediente();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'expedienteRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     * @throws JsonException
     */
    public function Guardar(Expediente $Expediente): bool
    {
        $id_expediente = $Expediente->getId_expediente();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_expediente);

        $aDatos = [];
        $aDatos['id_tramite'] = $Expediente->getId_tramite();
        $aDatos['ponente'] = $Expediente->getPonente();
        $aDatos['asunto'] = $Expediente->getAsunto();
        $aDatos['entradilla'] = $Expediente->getEntradilla();
        $aDatos['comentarios'] = $Expediente->getComentarios();
        $aDatos['prioridad'] = $Expediente->getPrioridad();
        $aDatos['estado'] = $Expediente->getEstado();
        $aDatos['vida'] = $Expediente->getVida();
        $aDatos['visibilidad'] = $Expediente->getVisibilidad();
        // para los array
        $aDatos['resto_oficinas'] = array_php2pg($Expediente->getResto_oficinas());
        $aDatos['etiquetas'] = array_php2pg($Expediente->getEtiquetas());
        $aDatos['firmas_oficina'] = array_php2pg($Expediente->getFirmas_oficina());
        // para las fechas
        $aDatos['f_contestar'] = (new ConverterDate('date', $Expediente->getF_contestar()))->toPg();
        $aDatos['f_ini_circulacion'] = (new ConverterDate('date', $Expediente->getF_ini_circulacion()))->toPg();
        $aDatos['f_reunion'] = (new ConverterDate('timestamp', $Expediente->getF_reunion()))->toPg();
        $aDatos['f_aprobacion'] = (new ConverterDate('date', $Expediente->getF_aprobacion()))->toPg();
        // para los json
        $aDatos['json_antecedentes'] = (new ConverterJson($Expediente->getJson_antecedentes()))->toPg();
        $aDatos['json_acciones'] = (new ConverterJson($Expediente->getJson_acciones()))->toPg();
        $aDatos['json_preparar'] = (new ConverterJson($Expediente->getJson_preparar()))->toPg();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_tramite               = :id_tramite,
					ponente                  = :ponente,
					resto_oficinas           = :resto_oficinas,
					asunto                   = :asunto,
					entradilla               = :entradilla,
					comentarios              = :comentarios,
					prioridad                = :prioridad,
					json_antecedentes        = :json_antecedentes,
					json_acciones            = :json_acciones,
					etiquetas                = :etiquetas,
					f_contestar              = :f_contestar,
					estado                   = :estado,
					f_ini_circulacion        = :f_ini_circulacion,
					f_reunion                = :f_reunion,
					f_aprobacion             = :f_aprobacion,
					vida                     = :vida,
					json_preparar            = :json_preparar,
					firmas_oficina           = :firmas_oficina,
					visibilidad              = :visibilidad";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_expediente = $id_expediente")) === FALSE) {
                $sClaveError = 'expedienteRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'expedienteRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_expediente'] = $Expediente->getId_expediente();
            $campos = "(id_expediente,id_tramite,ponente,resto_oficinas,asunto,entradilla,comentarios,prioridad,json_antecedentes,json_acciones,etiquetas,f_contestar,estado,f_ini_circulacion,f_reunion,f_aprobacion,vida,json_preparar,firmas_oficina,visibilidad)";
            $valores = "(:id_expediente,:id_tramite,:ponente,:resto_oficinas,:asunto,:entradilla,:comentarios,:prioridad,:json_antecedentes,:json_acciones,:etiquetas,:f_contestar,:estado,:f_ini_circulacion,:f_reunion,:f_aprobacion,:vida,:json_preparar,:firmas_oficina,:visibilidad)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'expedienteRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'expedienteRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_expediente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'expedienteRepository.isNew';
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
     * @param int $id_expediente
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_expediente): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_expediente = $id_expediente")) === FALSE) {
            $sClaveError = 'expedienteRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los array del postgres
        if ($aDatos !== FALSE) {
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            $aDatos['etiquetas'] = array_pg2php($aDatos['etiquetas']);
            $aDatos['firmas_oficina'] = array_pg2php($aDatos['firmas_oficina']);
        }
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_ini_circulacion'] = (new ConverterDate('date', $aDatos['f_ini_circulacion']))->fromPg();
            $aDatos['f_reunion'] = (new ConverterDate('timestamp', $aDatos['f_reunion']))->fromPg();
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
        }
        // para los json
        if ($aDatos !== FALSE) {
            $aDatos['json_antecedentes'] = (new ConverterJson($aDatos['json_antecedentes']))->fromPg();
            $aDatos['json_acciones'] = (new ConverterJson($aDatos['json_acciones']))->fromPg();
            $aDatos['json_preparar'] = (new ConverterJson($aDatos['json_preparar']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_expediente en la base de datos .
     * @throws JsonException
     */
    public function findById(int $id_expediente): ?Expediente
    {
        $aDatos = $this->datosById($id_expediente);
        if (empty($aDatos)) {
            return null;
        }
        return (new Expediente())->setAllAttributes($aDatos);
    }

    public function getNewId_expediente()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('expedientes_id_expediente_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getIdExpedientesConAntecedente($id, $tipo)
    {
        $oDbl = $this->getoDbl();

        $json = "\"id\": $id";
        $json .= ", \"tipo\": \"$tipo\"";
        $Where_json = "json_antecedentes @> '[{" . $json . "}]'";

        $sQuery = "SELECT e.id_expediente
                    FROM expedientes e 
                    WHERE $Where_json ";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'PgExpedienteRepository.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_expedientes = [];
        foreach ($oDbl->query($sQuery) as $aDades) {
            $id_expediente = $aDades['id_expediente'];
            $a_expedientes[] = $id_expediente;
        }
        return $a_expedientes;

    }

    public function getIdExpedientesPreparar($id_cargo, $visto = 'no_visto')
    {
        $oDbl = $this->getoDbl();

        $json = "\"id\": $id_cargo";
        switch ($visto) {
            case 'visto':
                $json .= empty($json) ? '' : ',';
                $json .= "\"visto\": true";
                break;
            case 'no_visto':
                $json .= empty($json) ? '' : ',';
                $json .= "\"visto\": false";
                break;
            case 'todos':
            default:
                // No añado nada.
                //$json .= "";
        }
        $Where_json = "json_preparar @> '[{" . $json . "}]'";

        $sQuery = "SELECT e.id_expediente
                    FROM expedientes e WHERE $Where_json ";

        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'PgExpedienteRepository.queryPreparar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_expedientes = [];
        foreach ($oDbl->query($sQuery) as $aDades) {
            $id_expediente = $aDades['id_expediente'];
            $a_expedientes[] = $id_expediente;
        }
        return $a_expedientes;
    }

}