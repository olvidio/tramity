<?php

namespace plantillas\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula plantillas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/4/2021
 */

/**
 * Classe que implementa l'entitat plantillas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/4/2021
 */
class Plantilla extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Plantilla
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Plantilla
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Plantilla
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Plantilla
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Plantilla
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Plantilla
     *
     * @var integer
     */
    private $iid_schema;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Id_plantilla de Plantilla
     *
     * @var integer
     */
    private $iid_plantilla;
    /**
     * Nombre de Plantilla
     *
     * @var string
     */
    private $snombre;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_plantilla
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_plantilla') && $val_id !== '') {
                    $this->iid_plantilla = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_plantilla = (int)$a_id;
                $this->aPrimary_key = array('iid_plantilla' => $this->iid_plantilla);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('plantillas');
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
        if ($this->DBCargar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['nombre'] = $this->snombre;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nombre                   = :nombre";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_plantilla='$this->iid_plantilla'")) === FALSE) {
                $sClauError = 'Plantilla.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Plantilla.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(nombre)";
            $valores = "(:nombre)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Plantilla.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Plantilla.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_plantilla = $oDbl->lastInsertId('plantillas_id_plantilla_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_plantilla)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_plantilla='$this->iid_plantilla'")) === FALSE) {
                $sClauError = 'Plantilla.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->setAllAtributes($aDades);
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) return FALSE;
                    break;
                default:
                    // En el caso de no existir esta fila, $aDades = FALSE:
                    if ($aDades === FALSE) {
                        $this->setNullAllAtributes();
                    } else {
                        $this->setAllAtributes($aDades);
                    }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Estableix el valor de tots els ATRIBUTOS
     *
     * @param array $aDades
     */
    private function setAllAtributes($aDades)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_plantilla', $aDades)) {
            $this->setId_plantilla($aDades['id_plantilla']);
        }
        if (array_key_exists('nombre', $aDades)) {
            $this->setNombre($aDades['nombre']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_plantilla de Plantilla
     *
     * @param integer iid_plantilla
     */
    function setId_plantilla($iid_plantilla)
    {
        $this->iid_plantilla = $iid_plantilla;
    }

    /**
     * estableix el valor de l'atribut snombre de Plantilla
     *
     * @param string snombre='' optional
     */
    function setNombre($snombre = '')
    {
        $this->snombre = $snombre;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_plantilla('');
        $this->setNombre('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Plantilla en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_plantilla' => $this->iid_plantilla);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Plantilla en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_plantilla') && $val_id !== '') {
                    $this->iid_plantilla = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_plantilla = (int)$a_id;
                $this->aPrimary_key = array('iid_plantilla' => $this->iid_plantilla);
            }
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_plantilla='$this->iid_plantilla'")) === FALSE) {
            $sClauError = 'Plantilla.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_plantilla de Plantilla
     *
     * @return integer iid_plantilla
     */
    function getId_plantilla()
    {
        if (!isset($this->iid_plantilla) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_plantilla;
    }

    /**
     * Recupera l'atribut snombre de Plantilla
     *
     * @return string snombre
     */
    function getNombre()
    {
        if (!isset($this->snombre) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snombre;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oPlantillaSet = new core\Set();

        $oPlantillaSet->add($this->getDatosNombre());
        return $oPlantillaSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut snombre de Plantilla
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosNombre()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'nombre'));
        $oDatosCampo->setEtiqueta(_("nombre"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Plantilla en un array
     *
     * @return array aDades
     */
    function getTot()
    {
        if (!is_array($this->aDades)) {
            $this->DBCargar('tot');
        }
        return $this->aDades;
    }
}
