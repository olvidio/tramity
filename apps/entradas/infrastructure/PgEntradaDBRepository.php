<?php

namespace entradas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\ConverterJson;
use core\Set;
use entradas\domain\entity\Entrada;
use entradas\domain\repositories\EntradaDBRepositoryInterface;
use JsonException;
use PDO;
use PDOException;
use usuarios\domain\Categoria;
use usuarios\domain\repositories\CargoRepository;
use function core\any_2;
use function core\array_pg2php;
use function core\array_php2pg;
use function core\is_true;

/**
 * Clase que adapta la tabla entradas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class PgEntradaDBRepository extends ClaseRepository implements EntradaDBRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaDB
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Entrada
     * @throws JsonException
     */
    public function getEntradas(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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
            $sClaveError = 'PgEntradaDBRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEntradaDBRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Entrada $Entrada): bool
    {
        $id_entrada = $Entrada->getId_entrada();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEntradaDBRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     * @throws JsonException
     */
    public function Guardar(Entrada $Entrada): bool
    {
        $id_entrada = $Entrada->getId_entrada();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_entrada);

        $aDatos = [];
        $aDatos['modo_entrada'] = $Entrada->getModo_entrada();
        $aDatos['asunto_entrada'] = $Entrada->getAsunto_entrada();
        $aDatos['ponente'] = $Entrada->getPonente();
        $aDatos['asunto'] = $Entrada->getAsunto();
        $aDatos['detalle'] = $Entrada->getDetalle();
        $aDatos['categoria'] = $Entrada->getCategoria();
        $aDatos['visibilidad'] = $Entrada->getVisibilidad();
        $aDatos['bypass'] = $Entrada->isBypass();
        $aDatos['estado'] = $Entrada->getEstado();
        $aDatos['anulado'] = $Entrada->getAnulado();
        $aDatos['encargado'] = $Entrada->getEncargado();
        $aDatos['id_entrada_compartida'] = $Entrada->getId_entrada_compartida();
        // para los array
        $aDatos['resto_oficinas'] = array_php2pg($Entrada->getResto_oficinas());
        // para las fechas
        $aDatos['f_entrada'] = (new ConverterDate('date', $Entrada->getF_entrada()))->toPg();
        $aDatos['f_contestar'] = (new ConverterDate('date', $Entrada->getF_contestar()))->toPg();
        // para los json
        $aDatos['json_prot_origen'] = (new ConverterJson($Entrada->getJson_prot_origen()))->toPg();
        $aDatos['json_prot_ref'] = (new ConverterJson($Entrada->getJson_prot_ref()))->toPg();
        $aDatos['json_visto'] = (new ConverterJson($Entrada->getJson_visto()))->toPg();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['bypass'])) {
            $aDatos['bypass'] = 'true';
        } else {
            $aDatos['bypass'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					modo_entrada             = :modo_entrada,
					json_prot_origen         = :json_prot_origen,
					asunto_entrada           = :asunto_entrada,
					json_prot_ref            = :json_prot_ref,
					ponente                  = :ponente,
					resto_oficinas           = :resto_oficinas,
					asunto                   = :asunto,
					f_entrada                = :f_entrada,
					detalle                  = :detalle,
					categoria                = :categoria,
					visibilidad              = :visibilidad,
					f_contestar              = :f_contestar,
					bypass                   = :bypass,
					estado                   = :estado,
					anulado                  = :anulado,
					encargado                = :encargado,
					json_visto               = :json_visto,
					id_entrada_compartida    = :id_entrada_compartida";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada = $id_entrada")) === FALSE) {
                $sClaveError = 'PgEntradaDBRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaDBRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_entrada'] = $Entrada->getId_entrada();
            $campos = "(id_entrada,modo_entrada,json_prot_origen,asunto_entrada,json_prot_ref,ponente,resto_oficinas,asunto,f_entrada,detalle,categoria,visibilidad,f_contestar,bypass,estado,anulado,encargado,json_visto,id_entrada_compartida)";
            $valores = "(:id_entrada,:modo_entrada,:json_prot_origen,:asunto_entrada,:json_prot_ref,:ponente,:resto_oficinas,:asunto,:f_entrada,:detalle,:categoria,:visibilidad,:f_contestar,:bypass,:estado,:anulado,:encargado,:json_visto,:id_entrada_compartida)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEntradaDBRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaDBRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_entrada): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEntradaDBRepository.isNew';
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
     * @param int $id_entrada
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada = $id_entrada")) === FALSE) {
            $sClaveError = 'PgEntradaDBRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los array del postgres
        if ($aDatos !== FALSE) {
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
        }
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
        }
        // para los json
        if ($aDatos !== FALSE) {
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_entrada en la base de datos .
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?Entrada
    {
        $aDatos = $this->datosById($id_entrada);
        if (empty($aDatos)) {
            return null;
        }
        return (new Entrada())->setAllAttributes($aDatos);
    }

    public function getNewId_entrada()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('entradas_id_entrada_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function posiblesYear(): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $sql_anys = "SELECT json_prot_origen -> 'any' as a 
                    FROM $nom_tabla
                    WHERE categoria = " . Categoria::CAT_PERMANENTE . "
                    GROUP BY a ORDER BY a";

        if (($oDblSt = $oDbl->Query($sql_anys)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.posiblesYear.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_anys = [];
        foreach ($oDblSt as $a_year) {
            $year = trim($a_year['a'], '"');
            $iyear = (int)$year;
            if ($iyear > 70) {
                $iany = 1900 + $iyear;
            } else {
                $iany = 2000 + $iyear;
            }

            $a_anys[] = $iany;
        }
        sort($a_anys);

        return $a_anys;
    }

    function getEntradasNoVistoDB($oficina, $tipo_oficina, $a_visibilidad = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();

        $estado = Entrada::ESTADO_ACEPTADO;

        $json = '';
        // Quitar las vistas
        $json .= "\"visto\": true";

        // Todas las de la oficina
        switch ($tipo_oficina) {
            case 'ponente':
                // en el caso de la oficina ponente, lo considero visto si está encargado a alguien
                $sCondi = "ponente = $oficina AND estado = $estado AND encargado IS NULL";
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                $json .= empty($json) ? '' : ',';
                $json .= "\"oficina\": $oficina";
                break;
            case 'resto':
                $sCondi = "$oficina = ANY (resto_oficinas) AND estado = $estado";
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                $json .= empty($json) ? '' : ',';
                $json .= "\"oficina\": $oficina";
                break;
            case 'encargado':
                $encargado = $oficina;
                $sCondi = "encargado = $encargado AND estado = $estado";
                $CargoRepository = new CargoRepository();
                $oCargo = $CargoRepository->findById($encargado);
                if ($oCargo !== null) {
                    $id_oficina = $oCargo->getId_oficina();
                    // comprobar visibilidad:
                    if (!empty($a_visibilidad)) {
                        $visibilidad_csv = implode(',', $a_visibilidad);
                        $sCondi .= " AND (visibilidad IN ($visibilidad_csv) OR visibilidad IS NULL)";
                    }
                }
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                // Reescribo toda la condición: hay que cambiar la oficina
                $json = "\"oficina\": $id_oficina";
                $json .= empty($json) ? '' : ',';
                $json .= "\"visto\": true";
                $json .= empty($json) ? '' : ',';
                $json .= "\"cargo\": $encargado";
                break;
            case 'centro':
                // para los ctr
                $estado = Entrada::ESTADO_INGRESADO;
                $sCondi = "estado = $estado";
                // comprobar visibilidad:
                if (!empty($a_visibilidad)) {
                    $visibilidad_csv = implode(',', $a_visibilidad);
                    $sCondi .= " AND (visibilidad IN ($visibilidad_csv) OR visibilidad IS NULL)";
                }
                $select_todas = "SELECT t.* FROM $nom_tabla t WHERE $sCondi";
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }

        if (!empty($json)) {
            $Where_json = "json_visto @> '[{" . $json . "}]'";
        } else {
            $Where_json = '';
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

        $select_vistas = "SELECT * FROM $nom_tabla $where_condi";

        $sQry = "$select_todas EXCEPT $select_vistas";

        if (($oDblSt = $oDbl->query($sQry)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getNoVisto.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    function getEntradasByVistoDB($aVisto = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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

        // Where del visto
        $Where_json = '';
        $json = '';
        if (!empty($aVisto['oficina'])) {
            $oficina = $aVisto['oficina'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"oficina\": $oficina";
        }
        if (!empty($aVisto['visto'])) {
            $visto = $aVisto['visto'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"visto\": $visto";
        }
        if (!empty($aVisto['cargo'])) {
            $cargo = $aVisto['cargo'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"cargo\": $cargo";
        }
        if (!empty($json)) {
            $Where_json = "json_visto @> '[{" . $json . "}]'";
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

        $sQry = "SELECT * FROM $nom_tabla $where_condi" . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getVisto.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getVisto.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    function getEntradasByRefDB($aProt_ref = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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

        // Where del prot_ref
        $Where_json = '';
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

        // pongo tipo 'text' en todos los campos del json, porque si hay algun null devuelve error syntax
        $sQry = "SELECT * FROM $nom_tabla $where_condi" . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getByRef.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getByRef.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    function getEntradasByProtOrigenDB($aProt_origen = [], $aWhere = [], $aOperators = [])
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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
        } else {
            $sOrdre = " ORDER BY CASE WHEN anulado IS NULL THEN 1 WHEN anulado = '' THEN 1 ELSE 2 END , f_entrada DESC";
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

        // Where del prot_origen
        $Where_json = '';
        $json = '';
        if (!empty($aProt_origen['id_lugar'])) {
            $id_lugar = $aProt_origen['id_lugar'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"id_lugar\":$id_lugar";
        }
        if (!empty($aProt_origen['num'])) {
            $num = $aProt_origen['num'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"num\":$num";
        }
        if (!empty($aProt_origen['any'])) {
            $any = $aProt_origen['any'];
            $any_2 = any_2($any);
            $json .= empty($json) ? '' : ',';
            $json .= "\"any\":\"$any_2\"";
        }
        if (!empty($aProt_origen['mas'])) {
            $mas = $aProt_origen['mas'];
            $json .= empty($json) ? '' : ',';
            $json .= "\"mas\":\"$mas\"";
        }
        if (!empty($json)) {
            $Where_json = "json_prot_origen @> '{" . $json . "}'";
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

        $sQry = "SELECT * FROM $nom_tabla $where_condi" . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getByProtOrg.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getByProtOrg.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    function getEntradasNumeradas($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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
            $sCondi = " WHERE NOT (json_prot_origen @> '{\"num\":0}')";
        } else {
            $sCondi = " WHERE NOT (json_prot_origen @> '{\"num\":0}') AND " . $sCondi;
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
            $sClauError = 'PgEntradaDBRepository.getNumeradas.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getNumeradas.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

    function getEntradasByLugarDB($id_lugar, $aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaSet = new Set();
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
            $sCondi = " WHERE json_prot_origen @> '{\"id_lugar\":$id_lugar}'";
        } else {
            $sCondi = " WHERE json_prot_origen @> '{\"id_lugar\":$id_lugar}' AND " . $sCondi;
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
            $sClauError = 'PgEntradaDBRepository.getByLugar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaDBRepository.getByLugar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();
            $Entrada = new Entrada();
            $Entrada->setAllAttributes($aDatos);
            $EntradaSet->add($Entrada);
        }
        return $EntradaSet->getTot();
    }

}