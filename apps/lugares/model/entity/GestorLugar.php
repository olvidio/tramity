<?php

namespace lugares\model\entity;

use config\model\entity\ConfigSchema;
use core;
use usuarios\model\entity\Cargo;
use function core\is_true;

/**
 * GestorLugar
 *
 * Classe per gestionar la llista d'objectes de la clase Lugar
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */
class GestorLugar extends core\ClaseGestor
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    const SEPARADOR = '-------------';

    /* CONSTRUCTOR -------------------------------------------------------------- */


    public function __construct()
    {
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('lugares');
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * Devuelve el nombre de las posibles plataformas as4
     *
     * @param boolean propia. Si debe aparecer la propia plataforma en la lista o no.
     * @return array []
     */
    public function getPlataformas($propia = FALSE)
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

        if (is_array($a_plataformas)) {
            return $a_plataformas;
        } else {
            exit (_("Error al buscar las plataformas posibles"));
        }

        return $a_plataformas;
    }

    /**
     * devuelve el id de Cancillería
     */
    public function getId_cancilleria(): int
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            exit (_("Error al buscar el id del Cancillería"));
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $oConfigSchema = new ConfigSchema('id_lugar_cancilleria');
            $id_cancilleria = $oConfigSchema->getValor();
            return $id_cancilleria;
        }
        return 0;
    }

    /**
     * devuelve el id del IESE/UDEN
     */
    public function getId_uden(): int
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            exit (_("Error al buscar el id del IESE/UDEN"));
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $oConfigSchema = new ConfigSchema('id_lugar_uden');
            $id_uden = $oConfigSchema->getValor();
            return $id_uden;
        }
        return 0;
    }

    function getLugares($aWhere = array(), $aOperators = array())
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_dl = $_SESSION['oConfig']->getSigla();

        $oLugarSet = new core\Set();
        $oCondicion = new core\Condicion();
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
        if ($sCondi != '') {
            $sCondi = " WHERE " . $sCondi;
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
            $sClauError = 'GestorLugar.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClauError = 'GestorLugar.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oLugar = new Lugar($aDades['id_lugar']);
            $oLugarSet->add($oLugar);
        }
        return $oLugarSet->getTot();
    }

    /**
     * devuelve el id de la cr (cr)
     */
    public function getId_cr(): int
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $mi_ctr = $_SESSION['oConfig']->getSigla();
            $mi_dl = $this->getSigla_superior($mi_ctr);
            $mi_cr = $this->getSigla_superior($mi_dl);
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $mi_dl = $_SESSION['oConfig']->getSigla();
            $mi_cr = $this->getSigla_superior($mi_dl);
        }

        // 0º dlb y cr y el propio ctr
        // OJO apóstrofes
        // escapar los apóstrofes con doble ('') cosas del postgresql.
        $mi_cr = str_replace("'", "''", $mi_cr);
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
        } else {
            exit (_("Error al buscar el id de cr"));
        }
    }

    /**
     * devuelve la sigla (o el id) de la nombre_entidad superior (dl para los centros, cr para las dl)
     *
     * @param boolean $id Si quero el id o la sigla.
     * @return string|integer
     */
    public function getSigla_superior($sigla_base, $id = FALSE)
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
                if ($id) {
                    $rta = $cLugarSup[0]->getId_lugar();
                } else {
                    $rta = ($tipo_sup === 'cr') ? $tipo_sup : $cLugarSup[0]->getSigla();
                }
            }

        }

        if (empty($rta)) {
            return '?';
        } else {
            return $rta;
        }
    }

    /**
     * devuelve el id de la sigla (dlb)
     */
    public function getId_sigla_local()
    {
        $sigla = $_SESSION['oConfig']->getSigla();
        $cLugares = $this->getLugares(['sigla' => $sigla]);
        if (!empty($cLugares)) {
            $id_sigla = $cLugares[0]->getId_lugar();
        }
        return $id_sigla;
    }

    /**
     * retorna un array
     * Els posibles llocs per buscar: també els anulados
     *
     * @param boolean $ctr_anulados
     * @return array   id_lugar => sigla
     */
    function getArrayBusquedas($ctr_anulados = FALSE)
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            return $this->getArrayBusquedasCtr();
        } else {
            return $this->getArrayBusquedasDl($ctr_anulados);
        }
    }

    /**
     * retorna un array
     * Els posibles llocs per buscar en el cas del ctr
     *
     * @return array   id_lugar => sigla
     */
    function getArrayBusquedasCtr()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_ctr = $_SESSION['oConfig']->getSigla();
        $mi_dl = $this->getSigla_superior($mi_ctr);
        $mi_cr = $this->getSigla_superior($mi_dl);

        $lugares = [];
        // 0º dlb y cr y el propio ctr
         // OJO apóstrofes
        // escapar los apóstrofes con doble ('') cosas del postgresql.
        $mi_ctr = str_replace("'", "''", $mi_ctr);
        $mi_dl = str_replace("'", "''", $mi_dl);
        $mi_cr = str_replace("'", "''", $mi_cr);
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

    /**
     * retorna un array
     * Els posibles llocs per buscar: també els anulados
     *
     * @param boolean $ctr_anulados
     * @return array   id_lugar => sigla
     */
    function getArrayBusquedasDl($ctr_anulados = FALSE)
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

    /**
     * retorna un array
     * Els posibles ctr de la dl
     *
     * @param boolean $ctr_anulados
     * @return array   id_lugar => sigla
     */
    function getArrayLugaresCtr($ctr_anulados = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $mi_dl = $_SESSION['oConfig']->getSigla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' AND anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE dl = '$mi_dl' $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLugares.Array';
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

    /**
     * retorna un array
     * Els posibles dl
     *
     * @param string $tipo_ctr ('dl', 'cr')
     * @param boolean $ctr_anulados
     * @return array   id_lugar => sigla
     */
    function getArrayLugaresTipo($tipo_ctr, $ctr_anulados = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $Where_anulados = is_true($ctr_anulados) ? '' : ' AND anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 WHERE tipo_ctr = '$tipo_ctr' $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLugares.Array';
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

    /**
     * Devuelve un array con los lugares
     *
     * @param boolean $ctr_anulados
     * @return array   id_lugar => sigla
     */
    function getArrayLugares($ctr_anulados = FALSE)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $Where_anulados = is_true($ctr_anulados) ? '' : ' WHERE anulado=FALSE';

        $sQuery = "SELECT id_lugar, sigla FROM $nom_tabla
                 $Where_anulados
                 ORDER BY sigla";
        if (($oDbl->query($sQuery)) === false) {
            $sClauError = 'GestorLugares.Array';
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


    /* MÉTODOS PROTECTED --------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/
}
