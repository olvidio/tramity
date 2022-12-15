<?php

namespace entradas\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConverterDate;
use core\ConverterJson;
use core\Set;
use entradas\domain\entity\EntradaBypass;
use entradas\domain\repositories\EntradaBypassRepositoryInterface;
use JsonException;
use PDO;
use PDOException;
use function core\array_pg2php;
use function core\array_php2pg;


/**
 * Clase que adapta la tabla entradas_bypass a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class PgEntradaBypassRepository extends ClaseRepository implements EntradaBypassRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas_bypass');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo EntradaBypass
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo EntradaBypass
     * @throws JsonException
     */
    public function getEntradasBypass(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaBypassSet = new Set();
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
        $sQry = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) " . $sCondicion . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClaveError = 'PgEntradaBypassRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgEntradaBypassRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();

            // de la entrada normal:
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();

            $EntradaBypass = new EntradaBypass();
            $EntradaBypass->setAllAttributes($aDatos);
            $EntradaBypassSet->add($EntradaBypass);
        }
        return $EntradaBypassSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(EntradaBypass $EntradaBypass): bool
    {
        $id_item = $EntradaBypass->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'PgEntradaBypassRepository.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     * @throws JsonException
     */
    public function Guardar(EntradaBypass $EntradaBypass): bool
    {
        $id_item = $EntradaBypass->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_item);

        $aDatos = [];
        $aDatos['id_entrada'] = $EntradaBypass->getId_entrada();
        $aDatos['descripcion'] = $EntradaBypass->getDescripcion();
        // para los array
        $aDatos['id_grupos'] = array_php2pg($EntradaBypass->getId_grupos());
        $aDatos['destinos'] = array_php2pg($EntradaBypass->getDestinos());
        // para las fechas
        $aDatos['f_salida'] = (new ConverterDate('date', $EntradaBypass->getF_salida()))->toPg();
        // para los json
        $aDatos['json_prot_destino'] = (new ConverterJson($EntradaBypass->getJson_prot_destino()))->toPg();
        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_entrada               = :id_entrada,
					descripcion              = :descripcion,
					json_prot_destino        = :json_prot_destino,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					f_salida                 = :f_salida";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item = $id_item")) === FALSE) {
                $sClaveError = 'PgEntradaBypassRepository.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaBypassRepository.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_item'] = $EntradaBypass->getId_item();
            $campos = "(id_item,id_entrada,descripcion,json_prot_destino,id_grupos,destinos,f_salida)";
            $valores = "(:id_item,:id_entrada,:descripcion,:json_prot_destino,:id_grupos,:destinos,:f_salida)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'PgEntradaBypassRepository.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'PgEntradaBypassRepository.insertar.execute';
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
            $sClaveError = 'PgEntradaBypassRepository.isNew';
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
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) WHERE id_entrada=$id_entrada")) === FALSE) {
            $sClaveError = 'PgEntradaBypassRepository.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para los array del postgres
        if ($aDatos !== FALSE) {
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
        }
        // para las fechas del postgres (texto iso)
        if ($aDatos !== FALSE) {
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
        }
        // para los json
        if ($aDatos !== FALSE) {
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_item en la base de datos .
     * @throws JsonException
     */
    public function findById(int $id_entrada): ?EntradaBypass
    {
        $aDatos = $this->datosById($id_entrada);
        if (empty($aDatos)) {
            return null;
        }
        return (new EntradaBypass())->setAllAttributes($aDatos);
    }

    public function getNewId_item()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('entradas_bypass_id_item_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */
    public function getEntradasBypassByDestino($id_lugar, $aWhere = array(), $aOperators = array()): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $EntradaBypassSet = new Set();
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
                $valor = $val;
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
        // Buscar en prot_destino
        if (empty($sCondi)) {
            $sCondi1 = " WHERE json_prot_destino @> '{\"id_lugar\":$id_lugar}'";
        } else {
            $sCondi1 = " WHERE json_prot_destino @> '{\"id_lugar\":$id_lugar}' AND " . $sCondi;
        }
        if ($COND_OR !== '') {
            if ($sCondi1 !== '') {
                $sCondi1 .= " AND " . $COND_OR;
            } else {
                $sCondi1 .= " WHERE " . $COND_OR;
            }
        }
        $sQry1 = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) " . $sCondi1;
        // buscar en a_destinos
        if (empty($sCondi)) {
            $sCondi2 = " WHERE $id_lugar = ANY(destinos)";
        } else {
            $sCondi2 = " WHERE $id_lugar = ANY(destinos) AND " . $sCondi;
        }
        if ($COND_OR !== '') {
            if ($sCondi2 !== '') {
                $sCondi2 .= " AND " . $COND_OR;
            } else {
                $sCondi2 .= " WHERE " . $COND_OR;
            }
        }
        $sQry2 = "SELECT * FROM $nom_tabla JOIN entradas USING (id_entrada) " . $sCondi2;

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

        $sQry = "$sQry1 UNION $sQry2 " . $sOrdre . $sLimit;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'PgEntradaBypassRepository.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'PgEntradaBypassRepository.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para los array del postgres
            $aDatos['id_grupos'] = array_pg2php($aDatos['id_grupos']);
            $aDatos['destinos'] = array_pg2php($aDatos['destinos']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_salida'] = (new ConverterDate('date', $aDatos['f_salida']))->fromPg();
            // para los json
            $aDatos['json_prot_destino'] = (new ConverterJson($aDatos['json_prot_destino']))->fromPg();

            // de la entrada normal:
            $aDatos['resto_oficinas'] = array_pg2php($aDatos['resto_oficinas']);
            // para las fechas del postgres (texto iso)
            $aDatos['f_entrada'] = (new ConverterDate('date', $aDatos['f_entrada']))->fromPg();
            $aDatos['f_contestar'] = (new ConverterDate('date', $aDatos['f_contestar']))->fromPg();
            // para los json
            $aDatos['json_prot_origen'] = (new ConverterJson($aDatos['json_prot_origen']))->fromPg();
            $aDatos['json_prot_ref'] = (new ConverterJson($aDatos['json_prot_ref']))->fromPg();
            $aDatos['json_visto'] = (new ConverterJson($aDatos['json_visto']))->fromPg();

            $EntradaBypass = new EntradaBypass();
            $EntradaBypass->setAllAttributes($aDatos);
            $EntradaBypassSet->add($EntradaBypass);
        }
        return $EntradaBypassSet->getTot();
    }

}