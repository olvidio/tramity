<?php

namespace lugares\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use lugares\domain\entity\Lugar;
use lugares\domain\repositories\LugarRepositoryInterface;
use PDO;
use PDOException;
use usuarios\domain\entity\Cargo;
use function core\is_true;


/**
 * Clase que adapta la tabla lugares a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class PgLugarRepository extends ClaseRepository implements LugarRepositoryInterface
{
    /* CONSTANTES ----------------------------------------------------------------- */

    private const SEPARADOR = '-------------';


    public function __construct()
    {
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('lugares');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Lugar
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Lugar
     */
    public function getLugares(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $LugarSet = new Set();
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
            $sClaveError = 'PgLugarRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgLugarRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {// para los bytea: (resources)
            $handle = $aDatos['pub_key'];
            if ($handle !== null) {
                $contents = stream_get_contents($handle);
                fclose($handle);
                $pub_key = $contents;
                $aDatos['pub_key'] = $pub_key;
            }
            $Lugar = new Lugar();
            $Lugar->setAllAttributes($aDatos);
            $LugarSet->add($Lugar);
        }
        return $LugarSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Lugar $Lugar): bool
    {
        $id_lugar = $Lugar->getId_lugar();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_lugar = $id_lugar")) === FALSE) {
            $sClaveError = 'Lugar.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Lugar $Lugar): bool
    {
        $id_lugar = $Lugar->getId_lugar();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_lugar);

        $aDatos = [];
        $aDatos['sigla'] = $Lugar->getSigla();
        $aDatos['dl'] = $Lugar->getDl();
        $aDatos['region'] = $Lugar->getRegion();
        $aDatos['nombre'] = $Lugar->getNombre();
        $aDatos['tipo_ctr'] = $Lugar->getTipo_ctr();
        $aDatos['modo_envio'] = $Lugar->getModo_envio();
        $aDatos['pub_key'] = $Lugar->getPub_key();
        $aDatos['e_mail'] = $Lugar->getE_mail();
        $aDatos['anulado'] = $Lugar->isAnulado();
        $aDatos['plataforma'] = $Lugar->getPlataforma();
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
					sigla                    = :sigla,
					dl                       = :dl,
					region                   = :region,
					nombre                   = :nombre,
					tipo_ctr                 = :tipo_ctr,
					modo_envio               = :modo_envio,
					pub_key                  = :pub_key,
					e_mail                   = :e_mail,
					anulado                  = :anulado,
					plataforma               = :plataforma";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_lugar = $id_lugar")) === FALSE) {
                $sClaveError = 'Lugar.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Lugar.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_lugar'] = $Lugar->getId_lugar();
            $campos = "(id_lugar,sigla,dl,region,nombre,tipo_ctr,modo_envio,pub_key,e_mail,anulado,plataforma)";
            $valores = "(:id_lugar,:sigla,:dl,:region,:nombre,:tipo_ctr,:modo_envio,:pub_key,:e_mail,:anulado,:plataforma)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Lugar.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Lugar.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_lugar): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_lugar = $id_lugar")) === FALSE) {
            $sClaveError = 'Lugar.isNew';
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
     * @param int $id_lugar
     * @return array|bool
     */
    public function datosById(int $id_lugar): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_lugar = $id_lugar")) === FALSE) {
            $sClaveError = 'Lugar.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        // para los bytea, sobre escribo los valores:
        $spub_key = '';
        $oDblSt->bindColumn('pub_key', $spub_key, PDO::PARAM_STR);
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        if (!empty($aDatos)) {
            $aDatos['pub_key'] = $spub_key;
        }
        return $aDatos;
    }


    /**
     * Busca la clase con id_lugar en la base de datos .
     */
    public function findById(int $id_lugar): ?Lugar
    {
        $aDatos = $this->datosById($id_lugar);
        if (empty($aDatos)) {
            return null;
        }
        return (new Lugar())->setAllAttributes($aDatos);
    }

    public function getNewId_lugar()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('lugares_id_lugar_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    public function getPlataformas(bool $propia = FALSE): array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $a_plataformas = [];

        $where_propia = '';
        // Quitar la propia:
        if ($propia === FALSE) {
            $plataforma_local = $_SESSION['oConfig']->getNomDock();
            $where_propia = "AND l.plataforma != '$plataforma_local' ";
        }

        $query_plataforma = "SELECT DISTINCT l.plataforma FROM $nom_tabla l
                            WHERE l.anulado = FALSE AND l.plataforma IS NOT NULL $where_propia
							AND modo_envio = " . Lugar::MODO_AS4 . "
                            ORDER BY l.plataforma";
        foreach ($oDbl->query($query_plataforma) as $aClave) {
            $clave = $aClave[0];
            $a_plataformas[$clave] = $clave;
        }

        if (!is_array($a_plataformas)) {
            exit (_("Error al buscar las plataformas posibles"));
        }

        return $a_plataformas;
    }

    public function getId_iese(): ?int
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            exit (_("Error al buscar el id del IESE"));
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $sigla = 'IESE';
            $cLugares = $this->getLugares(['sigla' => $sigla]);
            return $cLugares[0]->getId_lugar();
        }
        return null;
    }

    public function getId_cr(): int
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $mi_ctr = $_SESSION['oConfig']->getSigla();
            $mi_dl = $this->getSigla_superior($mi_ctr);
            $mi_cr = $this->getSigla_superior($mi_dl);
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $mi_dl = $_SESSION['oConfig']->getSigla();
            $mi_cr = $this->getSigla_superior($mi_dl);
        }

        // 0º dlb y cr y el propio ctr
        $lugares = [];
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE sigla='$mi_cr' AND anulado = FALSE
                            ORDER BY sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $lugares[] = $clave;
        }

        if (is_array($lugares) && count($lugares) === 1) {
            return $lugares[0];
        }

        exit (_("Error al buscar el id de cr"));
    }

    public function getSigla_superior(string $sigla_base, bool $return_id = FALSE): string|int
    {
        $rta = '';
        $cLugares = $this->getLugares(['sigla' => $sigla_base]);
        if (!empty($cLugares)) {
            $region = $cLugares[0]->getRegion();
            $dl = $cLugares[0]->getDl();
            $tipo_ctr = $cLugares[0]->getTipo_ctr();
            switch ($tipo_ctr) {
                case 'dl':
                    $tipo_sup = 'cr';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                        'region' => $region,
                        'sigla' => $region, // quitar cancilleria...
                    ];
                    break;
                case 'cr':
                    $tipo_sup = 'cg';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                        'region' => $region,
                        'dl' => $dl,
                        'sigla' => $dl,
                    ];
                    break;
                case 'cg':
                    $tipo_sup = 'vat';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                        'region' => $region,
                        'dl' => $dl
                    ];
                    break;
                default:   // 'ctr', am, nj, igl...
                    $tipo_sup = 'dl';
                    $aWhere = ['tipo_ctr' => $tipo_sup,
                        'region' => $region,
                        'dl' => $dl,
                        'sigla' => $dl, // quitar dlbf, cancilleria...
                    ];
                    break;

            }

            $cLugarSup = $this->getLugares($aWhere);
            if (!empty($cLugarSup)) {
                if ($return_id) {
                    $rta = $cLugarSup[0]->getId_lugar();
                } else {
                    $rta = ($tipo_sup === 'cr') ? $tipo_sup : $cLugarSup[0]->getSigla();
                }
            }

        }

        if (empty($rta)) {
            return '?';
        }

        return $rta;
    }

    public function getId_sigla_local(): ?int
    {
        $id_sigla = null;
        $sigla = $_SESSION['oConfig']->getSigla();
        $cLugares = $this->getLugares(['sigla' => $sigla]);
        if (!empty($cLugares)) {
            $id_sigla = $cLugares[0]->getId_lugar();
        }
        return $id_sigla;
    }

    public function getArrayBusquedas(bool $ctr_anulados = FALSE): array
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            return $this->getArrayBusquedasCtr();
        }

        return $this->getArrayBusquedasDl($ctr_anulados);
    }

    private function getArrayBusquedasCtr(): array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_ctr = $_SESSION['oConfig']->getSigla();
        $mi_dl = $this->getSigla_superior($mi_ctr);
        $mi_cr = $this->getSigla_superior($mi_dl);

        $lugares = [];
        // 0º dlb y cr y el propio ctr
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE (sigla='$mi_cr' OR sigla='$mi_dl' OR sigla='$mi_ctr') AND anulado = FALSE
                            ORDER BY sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }

        return $lugares;
    }

    private function getArrayBusquedasDl(bool $ctr_anulados = FALSE): array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_dl = $_SESSION['oConfig']->getSigla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' AND anulado=FALSE';

        $lugares = [];
        // 0º dlb y cr
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE (sigla='cr' OR sigla='$mi_dl') $Where_anulados
                            ORDER BY sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // separación
        $lugares['separador'] = self::SEPARADOR;
        // 1º ctr de dl
        $query_ctr = "SELECT id_lugar, sigla, nombre, substring(tipo_ctr from 1 for 1) as tipo FROM $nom_tabla
                            WHERE dl='$mi_dl' AND tipo_ctr ~ '^(a|n|s)' $Where_anulados
                            ORDER BY tipo,sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // 2º oc de dlb
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE dl='$mi_dl' AND tipo_ctr ~ 'oc' $Where_anulados
                            ORDER BY tipo_ctr,sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // 3º separación
        $lugares['separador3'] = self::SEPARADOR;
        // 4º dl de H
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='dl' AND region='H'  $Where_anulados
                            ORDER BY tipo_ctr,sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // 5º separación
        $lugares['separador5'] = self::SEPARADOR;
        // 6º cr
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='cr' $Where_anulados
                            ORDER BY tipo_ctr,sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // 7º separación
        $lugares['separador7'] = self::SEPARADOR;
        // 8º dl ex
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE tipo_ctr='dl' AND region != 'H' AND sigla != 'ro'  $Where_anulados
                            ORDER BY tipo_ctr,sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }
        // 9º separación
        $lugares['separador9'] = self::SEPARADOR;
        // 10º cg
        $query_ctr = "SELECT id_lugar, sigla, nombre FROM $nom_tabla
                            WHERE sigla='cg' $Where_anulados
                            ORDER BY sigla";
        foreach ($oDbl->query($query_ctr) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $lugares[$clave] = $val;
        }

        return $lugares;
    }

    public function getArrayLugaresCtr(bool $ctr_anulados = FALSE): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_dl = $_SESSION['oConfig']->getSigla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' AND anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE dl = '$mi_dl' $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'PgLugarRepository.Array';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones = array();
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $aOpciones[$clave] = $val;
        }
        return $aOpciones;
    }

    public function getArrayLugaresTipo(string $tipo_ctr, bool $ctr_anulados = FALSE): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' AND anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE tipo_ctr = '$tipo_ctr' $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'PgLugarRepository.Array';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones = array();
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $aOpciones[$clave] = $val;
        }
        return $aOpciones;
    }

    public function getArrayLugares(bool $ctr_anulados = FALSE): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' WHERE anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'PgLugarRepository.Array';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        $aOpciones = array();
        foreach ($oDbl->query($sQuery) as $aClave) {
            $clave = $aClave[0];
            $val = $aClave[1];
            $aOpciones[$clave] = $val;
        }
        return $aOpciones;
    }

}