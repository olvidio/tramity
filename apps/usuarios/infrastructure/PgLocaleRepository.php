<?php

namespace usuarios\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\Set;
use PDO;
use PDOException;
use usuarios\domain\entity\Locale;
use usuarios\domain\repositories\LocaleRepositoryInterface;
use web\Desplegable;
use function core\is_true;


/**
 * Clase que adapta la tabla x_locales a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 6/12/2022
 */
class PgLocaleRepository extends ClaseRepository implements LocaleRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBP'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_locales');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Locale
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Locale
     */
    public function getLocales(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $LocaleSet = new Set();
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
            $sClaveError = 'PgLocaleRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgLocaleRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            $Locale = new Locale();
            $Locale->setAllAttributes($aDatos);
            $LocaleSet->add($Locale);
        }
        return $LocaleSet->getTot();
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function Eliminar(Locale $Locale): bool
    {
        $id_locale = $Locale->getId_locale();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_locale = '$id_locale'")) === FALSE) {
            $sClaveError = 'Locale.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Locale $Locale): bool
    {
        $id_locale = $Locale->getId_locale();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_locale);

        $aDatos = [];
        $aDatos['nom_locale'] = $Locale->getNom_locale();
        $aDatos['idioma'] = $Locale->getIdioma();
        $aDatos['nom_idioma'] = $Locale->getNom_idioma();
        $aDatos['activo'] = $Locale->isActivo();
        array_walk($aDatos, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDatos['activo'])) {
            $aDatos['activo'] = 'true';
        } else {
            $aDatos['activo'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nom_locale               = :nom_locale,
					idioma                   = :idioma,
					nom_idioma               = :nom_idioma,
					activo                   = :activo";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_locale = '$id_locale'")) === FALSE) {
                $sClaveError = 'Locale.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Locale.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_locale'] = $Locale->getId_locale();
            $campos = "(id_locale,nom_locale,idioma,nom_idioma,activo)";
            $valores = "(:id_locale,:nom_locale,:idioma,:nom_idioma,:activo)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Locale.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Locale.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(bool $id_locale): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_locale = '$id_locale'")) === FALSE) {
            $sClaveError = 'Locale.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Busca la clase con id_locale en la base de datos .
     */
    public function findById(string $id_locale): ?Locale
    {
        $aDatos = $this->datosById($id_locale);
        if (empty($aDatos)) {
            return null;
        }
        return (new Locale())->setAllAttributes($aDatos);
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     */
    public function datosById(string $id_locale): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_locale = '$id_locale'")) === FALSE) {
            $sClaveError = 'Locale.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        return $aDatos;
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    function getListaIdiomas(string $sWhere = ''): Desplegable|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $sQuery = "SELECT DISTINCT idioma, nom_idioma
				FROM $nom_tabla $sWhere
				ORDER BY nom_idioma";
        if (($oDblSt = $oDbl->query($sQuery)) === false) {
            $sClauError = 'LocaleRepository.listaIdiomas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        return new Desplegable('', $oDblSt, '', true);
    }

    function getListaLocales(string $sWhere = ''): Desplegable|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (empty($sWhere)) $sWhere = "WHERE activo = 't'";
        $sQuery = "SELECT id_locale, nom_locale
				FROM $nom_tabla $sWhere
				ORDER BY nom_locale";
        if (($oDblSt = $oDbl->query($sQuery)) === false) {
            $sClauError = 'LocaleRepository.listaLocales';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        return new Desplegable('', $oDblSt, '', true);
    }

}