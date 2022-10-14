<?php

namespace usuarios\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula x_oficinas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */

/**
 * Classe que implementa l'entitat x_oficinas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */
class Oficina extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Oficina
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Oficina
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Oficina
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Oficina
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
     * Id_schema de Oficina
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_oficina de Oficina
     *
     * @var integer
     */
    private $iid_oficina;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Sigla de Oficina
     *
     * @var string
     */
    private $ssigla;
    /**
     * Orden de Oficina
     *
     * @var integer
     */
    private $iorden;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_oficina
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_oficina') && $val_id !== '') {
                    $this->iid_oficina = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_oficina = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_oficina' => $this->iid_oficina);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_oficinas');
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
        if ($this->DBCarregar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['sigla'] = $this->ssigla;
        $aDades['orden'] = $this->iorden;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					sigla                    = :sigla,
					orden                    = :orden";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_oficina='$this->iid_oficina'")) === FALSE) {
                $sClauError = 'Oficina.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Oficina.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(sigla,orden)";
            $valores = "(:sigla,:orden)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Oficina.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Oficina.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_oficina = $oDbl->lastInsertId('x_oficinas_id_oficina_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCarregar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_oficina)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_oficina='$this->iid_oficina'")) === FALSE) {
                $sClauError = 'Oficina.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->aDades = $aDades;
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
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_oficina('');
        $this->setSigla('');
        $this->setOrden('');
        $this->setPrimary_key($aPK);
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Oficina en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_oficina' => $this->iid_oficina);
        }
        return $this->aPrimary_key;
    }

    /**
     * estableix el valor de l'atribut iid_oficina de Oficina
     *
     * @param integer iid_oficina
     */
    function setId_oficina($iid_oficina)
    {
        $this->iid_oficina = $iid_oficina;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut ssigla de Oficina
     *
     * @param string ssigla='' optional
     */
    function setSigla($ssigla = '')
    {
        $this->ssigla = $ssigla;
    }

    /**
     * estableix el valor de l'atribut iorden de Oficina
     *
     * @param integer iorden='' optional
     */
    function setOrden($iorden = '')
    {
        $this->iorden = $iorden;
    }

    /**
     * Estableix las claus primàries de Oficina en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_oficina') && $val_id !== '') {
                    $this->iid_oficina = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_oficina = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_oficina' => $this->iid_oficina);
            }
        }
    }

    /**
     * Estableix el valor de tots els ATRIBUTOS
     *
     * @param array $aDades
     */
    function setAllAtributes($aDades)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_oficina', $aDades)) {
            $this->setId_oficina($aDades['id_oficina']);
        }
        if (array_key_exists('sigla', $aDades)) {
            $this->setSigla($aDades['sigla']);
        }
        if (array_key_exists('orden', $aDades)) {
            $this->setOrden($aDades['orden']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_oficina='$this->iid_oficina'")) === FALSE) {
            $sClauError = 'Oficina.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_oficina de Oficina
     *
     * @return integer iid_oficina
     */
    function getId_oficina()
    {
        if (!isset($this->iid_oficina) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_oficina;
    }

    /**
     * Recupera l'atribut ssigla de Oficina
     *
     * @return string ssigla
     */
    function getSigla()
    {
        if (!isset($this->ssigla) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->ssigla;
    }

    /**
     * Recupera l'atribut iorden de Oficina
     *
     * @return integer iorden
     */
    function getOrden()
    {
        if (!isset($this->iorden) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iorden;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oOficinaSet = new core\Set();

        $oOficinaSet->add($this->getDatosSigla());
        $oOficinaSet->add($this->getDatosOrden());
        return $oOficinaSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut ssigla de Oficina
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosSigla()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'sigla'));
        $oDatosCampo->setEtiqueta(_("sigla"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iorden de Oficina
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOrden()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'orden'));
        $oDatosCampo->setEtiqueta(_("orden"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Oficina en un array
     *
     * @return array aDades
     */
    function getTot()
    {
        if (!is_array($this->aDades)) {
            $this->DBCarregar('tot');
        }
        return $this->aDades;
    }
}
