<?php

namespace config\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula x_config_schema
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */

/**
 * Classe que implementa l'entitat x_config_schema
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */
class ConfigSchema extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de ConfigSchema
     *
     * @var array
     */
    protected $aPrimary_key;

    /**
     * aDades de ConfigSchema
     *
     * @var array
     */
    protected $aDades;

    /**
     * bLoaded
     *
     * @var boolean
     */
    protected $bLoaded = FALSE;

    /**
     * Parametro de ConfigSchema
     *
     * @var string
     */
    protected $sparametro;
    /**
     * Valor de ConfigSchema
     *
     * @var string
     */
    protected $svalor;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de ConfigSchema
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de ConfigSchema
     *
     * @var string
     */
    protected $sNomTabla;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array sparametro
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'parametro') && $val_id !== '') {
                    $this->sparametro = (string)$val_id; // evitem SQL injection fent cast a string
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->sparametro = $a_id;
                $this->aPrimary_key = array('parametro' => $this->sparametro);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_config');
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
        $aDades = [];
        $aDades['valor'] = $this->svalor;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					valor                    = :valor";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE parametro='$this->sparametro'")) === FALSE) {
                $sClauError = 'ConfigSchema.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'ConfigSchema.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->sparametro);
            $campos = "(parametro,valor)";
            $valores = "(:parametro,:valor)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'ConfigSchema.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'ConfigSchema.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
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
        if (isset($this->sparametro)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE parametro='$this->sparametro'")) === FALSE) {
                $sClauError = 'ConfigSchema.carregar';
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
                    if (!$oDblSt->rowCount()) {
                        return FALSE;
                    }
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
        $this->setParametro('');
        $this->setValor('');
        $this->setPrimary_key($aPK);
    }

    /* METODES ALTRES  ----------------------------------------------------------*/
    /* METODES PRIVATS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de ConfigSchema en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('parametro' => $this->sparametro);
        }
        return $this->aPrimary_key;
    }

    /**
     * estableix el valor de l'atribut sparametro de ConfigSchema
     *
     * @param string sparametro
     */
    function setParametro($sparametro)
    {
        $this->sparametro = $sparametro;
    }


    /* METODES GET i SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut svalor de ConfigSchema
     *
     * @param string svalor='' optional
     */
    function setValor($svalor = '')
    {
        $this->svalor = $svalor;
    }

    /**
     * Estableix las claus primàries de ConfigSchema en un array
     *
     * @return array aPrimary_key
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'parametro') && $val_id !== '') {
                    $this->sparametro = $val_id;
                }
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
        if (array_key_exists('parametro', $aDades)) {
            $this->setParametro($aDades['parametro']);
        }
        if (array_key_exists('valor', $aDades)) {
            $this->setValor($aDades['valor']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE parametro='$this->sparametro'")) === FALSE) {
            $sClauError = 'ConfigSchema.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut sparametro de ConfigSchema
     *
     * @return string sparametro
     */
    function getParametro()
    {
        if (!isset($this->sparametro) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->sparametro;
    }

    /**
     * Recupera l'atribut svalor de ConfigSchema
     *
     * @return string svalor
     */
    function getValor()
    {
        if (!isset($this->svalor) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->svalor;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oConfigSchemaSet = new core\Set();

        $oConfigSchemaSet->add($this->getDatosValor());
        return $oConfigSchemaSet->getTot();
    }
    /* METODES GET i SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut svalor de ConfigSchema
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosValor()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'valor'));
        $oDatosCampo->setEtiqueta(_("valor"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de ConfigSchema en un array
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
