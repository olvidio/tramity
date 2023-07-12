<?php
namespace usuarios\model\entity;

use core;
use PDO;
use PDOException;
use function core\is_true;

/**
 * Fitxer amb la Classe que accedeix a la taula x_locales
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2014
 */

/**
 * Classe que implementa l'entitat x_locales
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2014
 */
class TimeZone extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Locale
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Locale
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Locale
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Locale
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_locale de Locale
     *
     * @var integer
     */
    private $iid_tz;
    /**
     * Nom locale de Locale
     *
     * @var string
     */
    private $stz;

    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_tz
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBP'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_tz') && $val_id !== '') {
                    $this->iid_tz = (string)$val_id;
                } // evitem SQL injection fent cast a string
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_tz = (int)$a_id;
                $this->aPrimary_key = array('id_tz' => $this->iid_tz);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_timezones');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi es fa el update.
     *
     */
    public function DBGuardar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if ($this->DBCargar('guardar') === false) {
            $bInsert = true;
        } else {
            $bInsert = false;
        }
        $aDades = array();
        $aDades['tz'] = $this->stz;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === false) {
            //UPDATE
            $update = "
					tz               = :tz ";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_locale='$this->iid_tz'")) === false) {
                $sClauError = 'Locale.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return false;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Locale.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return false;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->iid_tz);
            $campos = "(id_locale,tz)";
            $valores = "(:id_locale,:tz)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === false) {
                $sClauError = 'Locale.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return false;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Locale.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return false;
                }
            }
        }
        $this->setAllAtributes($aDades);
        return true;
    }

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     */
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_tz)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_locale='$this->iid_tz'")) === false) {
                $sClauError = 'Locale.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return false;
            }
            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->aDades = $aDades;
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) return false;
                    break;
                default:
                    // En el caso de no existir esta fila, $aDades = FALSE:
                    if ($aDades === FALSE) {
                        return FALSE;
                    }
                   $this->setAllAtributes($aDades);
            }
            return true;
        } else {
            return false;
        }
    }

    
    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Locale en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_tz' => $this->iid_tz);
        }
        return $this->aPrimary_key;
    }

    /**
     * @param string iid_tz
     */
    function setId_tz($iid_tz)
    {
        $this->iid_tz = $iid_tz;
    }


    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string stz='' optional
     */
    function setTz($stz = '')
    {
        $this->stz = $stz;
    }

    /**
     * Estableix las claus primàries de Locale en un array
     *
     * @return array aPrimary_key
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_locale') && $val_id !== '') $this->slocale = $val_id;
            }
        }
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     */
    private function setAllAtributes($aDades)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_tz', $aDades)) {
            $this->setId_locale($aDades['id_tz']);
        }
        if (array_key_exists('tz', $aDades)) {
            $this->setNom_locale($aDades['tz']);
        }
    }

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_locale='$this->iid_tz'")) === false) {
            $sClauError = 'Locale.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return false;
        }
        return true;
    }

    /**
     * Recupera l'atribut iid_tz de Locale
     *
     * @return string iid_tz
     */
    function getId_tz()
    {
        if (!isset($this->iid_tz) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_tz;
    }

    /**
     * Recupera l'atribut stz de Locale
     *
     * @return string stz
     */
    function getTz()
    {
        if (!isset($this->stz) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->stz;
    }

}