<?php

namespace escritos\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConfigGlobal;
use core\ConverterDate;
use core\ConverterJson;
use core\Set;
use escritos\domain\entity\Escrito;
use escritos\domain\repositories\EscritoRepositoryInterface;
use Exception;
use JsonException;
use PDO;
use PDOException;
use function core\any_2;
use function core\array_pg2php;
use function core\array_php2pg;
use function core\is_true;


/**
 * Clase que adapta la tabla escritos a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class PgEscritoRepository extends ClaseRepository implements EscritoRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('escritos');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Escrito
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Escrito
     * @throws JsonException
     */
    public function getEscritos(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();
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
            $sClaveError = 'PgEscritoRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEscritoRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Escrito $Escrito): bool
    {
        $id_escrito = $Escrito->getId_escrito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_escrito = $id_escrito")) === FALSE) {
            $sClaveError = 'PgEscritoRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     * @throws JsonException
     */
    public function Guardar(Escrito $Escrito): bool
    {
        $id_escrito = $Escrito->getId_escrito();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_escrito);

        $aDatos = [];
        $aDatos['asunto'] = $Escrito->getAsunto();
        $aDatos['detalle'] = $Escrito->getDetalle();
        $aDatos['creador'] = $Escrito->getCreador();
        $aDatos['comentarios'] = $Escrito->getComentarios();
        $aDatos['categoria'] = $Escrito->getCategoria();
        $aDatos['visibilidad'] = $Escrito->getVisibilidad();
        $aDatos['accion'] = $Escrito->getAccion();
        $aDatos['modo_envio'] = $Escrito->getModo_envio();
        $aDatos['ok'] = $Escrito->getOk();
        $aDatos['tipo_doc'] = $Escrito->getTipo_doc();
        $aDatos['anulado'] = $Escrito->isAnulado();
        $aDatos['descripcion'] = $Escrito->getDescripcion();
        $aDatos['visibilidad_dst'] = $Escrito->getVisibilidad_dst();
        // para los array
        $aDatos['id_grupos'] = array_php2pg($Escrito->getId_grupos());
        $aDatos['destinos'] = array_php2pg($Escrito->getDestinos());
        $aDatos['resto_oficinas'] = array_php2pg($Escrito->getResto_oficinas());
        // para las fechas
        $aDatos['f_aprobacion'] = (new ConverterDate('date', $Escrito->getF_aprobacion()))->toPg();
        $aDatos['f_escrito'] = (new ConverterDate('date', $Escrito->getF_escrito()))->toPg();
        $aDatos['f_contestar'] = (new ConverterDate('date', $Escrito->getF_contestar()))->toPg();
        $aDatos['f_salida'] = (new ConverterDate('date', $Escrito->getF_salida()))->toPg();
        // para los json
        $aDatos['json_prot_local'] = (new ConverterJson($Escrito->getJson_prot_local()))->toPg();
        $aDatos['json_prot_destino'] = (new ConverterJson($Escrito->getJson_prot_destino()))->toPg();
        $aDatos['json_prot_ref'] = (new ConverterJson($Escrito->getJson_prot_ref()))->toPg();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['anulado'])) {
            $aDatos['anulado'] = 'true';
        } else {
            $aDatos['anulado'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					json_prot_local          = :json_prot_local,
					json_prot_destino        = :json_prot_destino,
					json_prot_ref            = :json_prot_ref,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					asunto                   = :asunto,
					detalle                  = :detalle,
					creador                  = :creador,
					resto_oficinas           = :resto_oficinas,
					comentarios              = :comentarios,
					f_aprobacion             = :f_aprobacion,
					f_escrito                = :f_escrito,
					f_contestar              = :f_contestar,
					categoria                = :categoria,
					visibilidad              = :visibilidad,
					accion                   = :accion,
					modo_envio               = :modo_envio,
					f_salida                 = :f_salida,
					ok                       = :ok,
					tipo_doc                 = :tipo_doc,
					anulado                  = :anulado,
					descripcion              = :descripcion,
					visibilidad_dst          = :visibilidad_dst";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_escrito = $id_escrito")) === FALSE) {
                $sClaveError = 'PgEscritoRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEscritoRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_escrito'] = $Escrito->getId_escrito();
            $campos = "(id_escrito,json_prot_local,json_prot_destino,json_prot_ref,id_grupos,destinos,asunto,detalle,creador,resto_oficinas,comentarios,f_aprobacion,f_escrito,f_contestar,categoria,visibilidad,accion,modo_envio,f_salida,ok,tipo_doc,anulado,descripcion,visibilidad_dst)";
            $valores = "(:id_escrito,:json_prot_local,:json_prot_destino,:json_prot_ref,:id_grupos,:destinos,:asunto,:detalle,:creador,:resto_oficinas,:comentarios,:f_aprobacion,:f_escrito,:f_contestar,:categoria,:visibilidad,:accion,:modo_envio,:f_salida,:ok,:tipo_doc,:anulado,:descripcion,:visibilidad_dst)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEscritoRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEscritoRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_escrito): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_escrito = $id_escrito")) === FALSE) {
            $sClaveError = 'PgEscritoRepository.isNew';
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
     * @param int $id_escrito
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_escrito): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_escrito = $id_escrito")) === FALSE) {
            $sClaveError = 'PgEscritoRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los array del postgres
        if ($aDatos !== FALSE) {
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
        }
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
        }
        // para los json
        if ($aDatos !== FALSE) {
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_escrito en la base de datos .
     * @throws JsonException
     */
    public function findById(int $id_escrito): ?Escrito
    {
        $aDatos = $this->datosById($id_escrito);
        if (empty($aDatos)) {
            return null;
        }
        return (new Escrito())->setAllAttributes($aDatos);
    }

    public function getNewId_escrito()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('escritos_id_escrito_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getEscritosByRef(array $aProt_ref = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();

        $oCondicion = new Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        // Where del prot_ref
        $json = '';
        if (!empty($aProt_ref['id_lugar'])) {
            $id_lugar = $aProt_ref['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";

        }
        if (!empty($aProt_ref['num'])) {
            $num = $aProt_ref['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_ref['any'])) {
            $any = $aProt_ref['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_ref['mas'])) {
            $mas = $aProt_ref['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }

        if (!empty($json)) {
            $Where_json = "json_prot_ref @> '[{" . $json . "}]'";
        }

        if (empty($sCondi)) {
            if (empty($json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

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

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre $sLimit ";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();
    }

    public function getEscritosByProtLocal(array $aProt_local = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        // Where del prot_destino
        // pongo tipo 'text' en todos los campos, porque si hay algun null devuelve error syntax
        $Where_json = '';
        $json = '';
        if (!empty($aProt_local['id_lugar'])) {
            $id_lugar = $aProt_local['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_local['num'])) {
            $num = $aProt_local['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_local['any'])) {
            $any = $aProt_local['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_local['mas'])) {
            $mas = $aProt_local['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }
        if (!empty($json)) {
            $Where_json = "json_prot_local @> '{" . $json . "}'";
        }
        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

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

        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre $sLimit";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();
    }

    public function getEscritosByProtDestino(array $aProt_destino = [], array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        // Where del prot_destino
        $Where_json = '';
        $json = '';
        if (!empty($aProt_destino['id_lugar'])) {
            $id_lugar = $aProt_destino['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_destino['num'])) {
            $num = $aProt_destino['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_destino['any'])) {
            $any = $aProt_destino['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_destino['mas'])) {
            $mas = $aProt_destino['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }

        if (!empty($json)) {
            $Where_json = "json_prot_ref @> '[{" . $json . "}]'";
        }

        if (empty($sCondi)) {
            if (empty($Where_json)) {
                $where_condi = '';
            } else {
                $where_condi = $Where_json;
            }
        } else {
            if (!empty($Where_json)) {
                $where_condi = $Where_json . " AND " . $sCondi;
            } else {
                $where_condi = $sCondi;
            }
        }
        $where_condi = empty($where_condi) ? '' : "WHERE " . $where_condi;

        $sOrdre = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }

        $sQry = "SELECT * FROM $nom_tabla $where_condi $sOrdre";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();
    }

    public function getEscritosByLugarDeGrupo(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();

        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);
        if (empty($sCondi)) {
            $sCondi = " WHERE '$id_lugar' = ANY(destinos) ";
        } else {
            $sCondi = " WHERE '$id_lugar' = ANY(destinos) AND " . $sCondi;
        }
        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND " . $COND_OR;
            } else {
                $sCondi .= " WHERE " . $COND_OR;
            }
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

        // pongo tipo 'text' en todos los campos del json, porque si hay algun null devuelve error syntax
        $sQry = "SELECT * FROM $nom_tabla $sCondi $sOrdre $sLimit ";

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();

    }

    public function getEscritosByLugar(int $id_lugar, array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EscritoSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        if (empty($sCondi)) {
            $sCondi = " WHERE json_prot_destino @> '[{\"id_lugar\":$id_lugar}]'";
        } else {
            $sCondi = " WHERE json_prot_destino @> '[{\"id_lugar\":$id_lugar}]' AND " . $sCondi;
        }

        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND " . $COND_OR;
            } else {
                $sCondi .= " WHERE " . $COND_OR;
            }
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

        $sQry = "SELECT * FROM $nom_tabla " . $sCondi . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEscritoRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_aprobacion'] = (new ConverterDate('date', $aDatos['f_aprobacion']))->fromPg();
            $aDatos['f_escrito'] = (new ConverterDate('date', $aDatos['f_escrito']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_local'] = (new ConverterJson($aDatos['json_prot_local']))->fromPg();
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $Escrito = new Escrito();
            $Escrito->setAllAttributes($aDatos);
            $EscritoSet->add($Escrito);
        }
        return $EscritoSet->getTot();
    }

    public function getEscritosNumerados(array $aWhere = [], array $aOperators = []): array
    {
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        if (empty($sCondi)) {
            $sCondi = " WHERE NOT (json_prot_local @> '{\"num\":0}')";
        } else {
            $sCondi = " WHERE NOT (json_prot_local @> '{\"num\":0}') AND " . $sCondi;
        }

        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND " . $COND_OR;
            } else {
                $sCondi .= " WHERE " . $COND_OR;
            }
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

        $sQry = "SELECT * FROM $nom_tabla " . $sCondi . $sOrdre . $sLimit;

        // Se usa la utilidad CURSOR del Postgresql para evitar colapsar la memoria del servidor
        // cuando se busca un número muy grande de registros (más de 20.000)
        foreach ($this->fetchCursor($sQry, $aWhere) as $row) {
            $a_pkey = array('id_escrito' => $row['id_escrito']);
            $oEscritoDB = new Escrito($a_pkey);
            $oEscritoDBSet->add($oEscritoDB);

        }

        return $oEscritoDBSet->getTot();
    }

     private function fetchCursor($sql, $aWhere, $idCol = false)
    {
        $pdo = $this->getoDbl();
        /*
         nextCursorId() is an undefined function, but
         the objective of it is to create a unique Id for each cursor.
         */
        try {
            $cursorID = 'cursor_' . ConfigGlobal::mi_id_usuario();
            $pdo->beginTransaction();
            //$stm0 = $pdo->exec("DECLARE $cursorID CURSOR FOR $sql ");
            $stm0 = $pdo->prepare("DECLARE $cursorID CURSOR FOR $sql ");
            $stm0->execute($aWhere);

            $stm = $pdo->prepare("FETCH NEXT FROM $cursorID");
            $stm->execute();
            if ($stm) {
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    if (is_string($idCol) && array_key_exists($idCol, $row)) {
                        yield $row[$idCol] => $row;
                    } else {
                        yield $row;
                    }
                    $stm->execute();
                }
            }
        } catch (Exception $e) {
            // Anything you want [*Parece que no hace nada!!]
            echo _("Demasiados registros");
            echo sprintf(_("Excepción capturada: %s"), $e->getMessage());
            echo "\n";
        } finally {
            /*
             Do some clean up after the loop is done.
             This is in a "finally" block because if you break the parent loop, it still gets called.
             */
            $pdo->exec("CLOSE $cursorID");
            $pdo->commit();
            return;
        }
    }

    public function getEscritosByLocal(int $id_lugar, array $aWhere = [], array $aOperators = []): array
    {
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new Set();
        $oCondicion = new Condicion();
        $aCondi = array();
        $COND_OR = '';
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            if ($camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "(public.sin_acentos(asunto::text)  ~* public.sin_acentos('$valor'::text)";
                $COND_OR .= " OR ";
                $COND_OR .= "public.sin_acentos(detalle::text)  ~* public.sin_acentos('$valor'::text) )";

                unset($aWhere[$camp]);
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondi[] = $a;
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
        $sCondi = implode(' AND ', $aCondi);

        if (empty($sCondi)) {
            $sCondi = " WHERE json_prot_local @> '{\"id_lugar\":$id_lugar}'";
        } else {
            $sCondi = " WHERE json_prot_local @> '{\"id_lugar\":$id_lugar}' AND " . $sCondi;
        }

        if ($COND_OR != '') {
            if ($sCondi != '') {
                $sCondi .= " AND " . $COND_OR;
            } else {
                $sCondi .= " WHERE " . $COND_OR;
            }
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

        $sQry = "SELECT * FROM $nom_tabla " . $sCondi . $sOrdre . $sLimit;

        // Se usa la utilidad CURSOR del Postgresql para evitar colapsar la memoria del servidor
        // cuando se busca un número muy grande de registros (más de 20.000)
        foreach ($this->fetchCursor($sQry, $aWhere) as $row) {
            $a_pkey = array('id_escrito' => $row['id_escrito']);
            $oEscritoDB = new Escrito($a_pkey);
            $oEscritoDBSet->add($oEscritoDB);

        }

        return $oEscritoDBSet->getTot();
    }

}