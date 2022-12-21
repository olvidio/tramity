<?php

namespace entradas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\ConverterJson;
use core\Set;
use entradas\domain\entity\EntradaCompartida;
use entradas\domain\repositories\EntradaCompartidaRepositoryInterface;
use JsonException;
use PDO;
use PDOException;
use usuarios\domain\Categoria;
use function core\any_2;
use function core\array_pg2php;
use function core\array_php2pg;


/**
 * Clase que adapta la tabla entradas_compartidas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class PgEntradaCompartidaRepository extends ClaseRepository implements EntradaCompartidaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas_compartidas');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaCompartida
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaCompartida
     * @throws JsonException
     */
    public function getEntradasCompartidas(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaCompartidaSet = new Set();
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
            $sClaveError = 'PgEntradaCompartidaRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEntradaCompartidaRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_documento'] = (new ConverterDate('date', $aDatos['f_documento']))->fromPg();
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            // para los json
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $EntradaCompartida = new EntradaCompartida();
            $EntradaCompartida->setAllAttributes($aDatos);
            $EntradaCompartidaSet->add($EntradaCompartida);
        }
        return $EntradaCompartidaSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaCompartida $EntradaCompartida): bool
    {
        $id_entrada_compartida = $EntradaCompartida->getId_entrada_compartida();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada_compartida = $id_entrada_compartida")) === FALSE) {
            $sClaveError = 'PgEntradaCompartidaRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     * @throws JsonException
     */
    public function Guardar(EntradaCompartida $EntradaCompartida): bool
    {
        $id_entrada_compartida = $EntradaCompartida->getId_entrada_compartida();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_entrada_compartida);

        $aDatos = [];
        $aDatos['descripcion'] = $EntradaCompartida->getDescripcion();
        $aDatos['categoria'] = $EntradaCompartida->getCategoria();
        $aDatos['asunto_entrada'] = $EntradaCompartida->getAsunto_entrada();
        $aDatos['anulado'] = $EntradaCompartida->getAnulado();
        // para los array
        $aDatos['destinos'] = array_php2pg($EntradaCompartida->getDestinos());
        // para las fechas
        $aDatos['f_documento'] = (new ConverterDate('date', $EntradaCompartida->getF_documento()))->toPg();
        $aDatos['f_entrada'] = (new ConverterDate('date', $EntradaCompartida->getF_entrada()))->toPg();
        // para los json
        $aDatos['json_prot_destino'] = (new ConverterJson($EntradaCompartida->getJson_prot_destino()))->toPg();
        $aDatos['json_prot_origen'] = (new ConverterJson($EntradaCompartida->getJson_prot_origen()))->toPg();
        $aDatos['json_prot_ref'] = (new ConverterJson($EntradaCompartida->getJson_prot_ref()))->toPg();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					descripcion              = :descripcion,
					json_prot_destino        = :json_prot_destino,
					destinos                 = :destinos,
					f_documento              = :f_documento,
					json_prot_origen         = :json_prot_origen,
					json_prot_ref            = :json_prot_ref,
					categoria                = :categoria,
					asunto_entrada           = :asunto_entrada,
					f_entrada                = :f_entrada,
					anulado                  = :anulado";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada_compartida = $id_entrada_compartida")) === FALSE) {
                $sClaveError = 'PgEntradaCompartidaRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaCompartidaRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_entrada_compartida'] = $EntradaCompartida->getId_entrada_compartida();
            $campos = "(id_entrada_compartida,descripcion,json_prot_destino,destinos,f_documento,json_prot_origen,json_prot_ref,categoria,asunto_entrada,f_entrada,anulado)";
            $valores = "(:id_entrada_compartida,:descripcion,:json_prot_destino,:destinos,:f_documento,:json_prot_origen,:json_prot_ref,:categoria,:asunto_entrada,:f_entrada,:anulado)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEntradaCompartidaRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaCompartidaRepository.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_entrada_compartida): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada_compartida = $id_entrada_compartida")) === FALSE) {
            $sClaveError = 'PgEntradaCompartidaRepository.isNew';
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
     * @param int $id_entrada_compartida
     * @return array|bool
     * @throws JsonException
     */
    public function datosById(int $id_entrada_compartida): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada_compartida = $id_entrada_compartida")) === FALSE) {
            $sClaveError = 'PgEntradaCompartidaRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los array del postgres
        if ($aDatos !== FALSE) {
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
        }
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_documento'] = (new ConverterDate('date', $aDatos['f_documento']))->fromPg();
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
        }
        // para los json
        if ($aDatos !== FALSE) {
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_entrada_compartida en la base de datos .
     * @throws JsonException
     */
    public function findById(int $id_entrada_compartida): ?EntradaCompartida
    {
        $aDatos = $this->datosById($id_entrada_compartida);
        if (empty($aDatos)) {
            return null;
        }
        return (new EntradaCompartida())->setAllAttributes($aDatos);
    }

    public function getNewId_entrada_compartida()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('entradas_compartidas_id_entrada_compartida_seq'::regclass)";
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
            $sClauError = 'PgEntradaCompartidaRepository.llistar.execute';
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

    public function getEntradasByProtOrigenDestino(array $aProt_origen,
                                                   int   $id_destino,
                                                   array $aWhere = [],
                                                   array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaCompartidaSet = new Set();
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
            if ($camp === 'asunto' || $camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "public.sin_acentos(asunto_entrada::text)  ~* public.sin_acentos('$valor'::text)";

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
        if ($COND_OR !== '') {
            if ($sCondi !== '') {
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
            $sOrdre = " ORDER BY CASE WHEN anulado IS NULL THEN 1 WHEN anulado = '' THEN 1 ELSE 2 END , t.f_entrada DESC";
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

        if (empty($where_condi)) {
            $where_condi = " WHERE '$id_destino' = ANY(destinos) ";
        } else {
            $where_condi = " WHERE '$id_destino' = ANY(destinos) AND " . $where_condi;
        }

        $sQry = "SELECT * FROM $nom_tabla $where_condi " . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEntradaCompartidaRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaCompartidaRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_documento'] = (new ConverterDate('date', $aDatos['f_documento']))->fromPg();
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            // para los json
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $EntradaCompartida = new EntradaCompartida();
            $EntradaCompartida->setAllAttributes($aDatos);
            $EntradaCompartidaSet->add($EntradaCompartida);
        }
        return $EntradaCompartidaSet->getTot();
    }

    public function getEntradasByProtOrigenDB(array $aProt_origen, array $aWhere = [], array $aOperators = []): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaCompartidaSet = new Set();
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
            if ($camp === 'asunto' || $camp === 'asunto_detalle') {
                $valor = $aWhere[$camp];
                $COND_OR = "public.sin_acentos(asunto_entrada::text)  ~* public.sin_acentos('$valor'::text)";

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
        if ($COND_OR !== '') {
            if ($sCondi !== '') {
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
            $sClauError = 'PgEntradaCompartidaRepository.getEntradasByProtOrigenDB.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaCompartidaRepository.getEntradasByProtOrigenDB.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_documento'] = (new ConverterDate('date', $aDatos['f_documento']))->fromPg();
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            // para los json
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $EntradaCompartida = new EntradaCompartida();
            $EntradaCompartida->setAllAttributes($aDatos);
            $EntradaCompartidaSet->add($EntradaCompartida);
        }
        return $EntradaCompartidaSet->getTot();
    }

}