<?php

namespace expedientes\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula acciones
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 23/7/2020
 */

/**
 * Classe que implementa l'entitat acciones
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 23/7/2020
 */
class Accion extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Accion
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Accion
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Accion
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Accion
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
     * Id_schema de Accion
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_item de Accion
     *
     * @var integer
     */
    private $iid_item;
    /**
     * Id_expediente de Accion
     *
     * @var integer
     */
    private $iid_expediente;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Tipo_accion de Accion
     *
     * @var integer
     */
    private $itipo_accion;
    /**
     * Id_escrito de Accion
     *
     * @var integer
     */
    private $iid_escrito;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_item
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('acciones');
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
        $aDades['id_expediente'] = $this->iid_expediente;
        $aDades['tipo_accion'] = $this->itipo_accion;
        $aDades['id_escrito'] = $this->iid_escrito;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_expediente            = :id_expediente,
					tipo_accion              = :tipo_accion,
					id_escrito               = :id_escrito";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'Accion.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Accion.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_expediente,tipo_accion,id_escrito)";
            $valores = "(:id_expediente,:tipo_accion,:id_escrito)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Accion.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Accion.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_item = $oDbl->lastInsertId('acciones_id_item_seq');
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
        if (isset($this->iid_item)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'Accion.carregar';
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
        $this->setId_item('');
        $this->setId_expediente('');
        $this->setTipo_accion('');
        $this->setId_escrito('');
        $this->setPrimary_key($aPK);
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Accion en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_item' => $this->iid_item);
        }
        return $this->aPrimary_key;
    }

    /**
     * estableix el valor de l'atribut iid_item de Accion
     *
     * @param integer iid_item
     */
    function setId_item($iid_item)
    {
        $this->iid_item = $iid_item;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_expediente de Accion
     *
     * @param integer iid_expediente='' optional
     */
    function setId_expediente($iid_expediente = '')
    {
        $this->iid_expediente = $iid_expediente;
    }

    /**
     * estableix el valor de l'atribut itipo_accion de Accion
     *
     * @param integer itipo_accion='' optional
     */
    function setTipo_accion($itipo_accion = '')
    {
        $this->itipo_accion = $itipo_accion;
    }

    /**
     * estableix el valor de l'atribut iid_escrito de Accion
     *
     * @param integer iid_escrito='' optional
     */
    function setId_escrito($iid_escrito = '')
    {
        $this->iid_escrito = $iid_escrito;
    }

    /**
     * Estableix las claus primàries de Accion en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
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
        if (array_key_exists('id_item', $aDades)) {
            $this->setId_item($aDades['id_item']);
        }
        if (array_key_exists('id_expediente', $aDades)) {
            $this->setId_expediente($aDades['id_expediente']);
        }
        if (array_key_exists('tipo_accion', $aDades)) {
            $this->setTipo_accion($aDades['tipo_accion']);
        }
        if (array_key_exists('id_escrito', $aDades)) {
            $this->setId_escrito($aDades['id_escrito']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
            $sClauError = 'Accion.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_item de Accion
     *
     * @return integer iid_item
     */
    function getId_item()
    {
        if (!isset($this->iid_item) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_item;
    }

    /**
     * Recupera l'atribut iid_expediente de Accion
     *
     * @return integer iid_expediente
     */
    function getId_expediente()
    {
        if (!isset($this->iid_expediente) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_expediente;
    }

    /**
     * Recupera l'atribut itipo_accion de Accion
     *
     * @return integer itipo_accion
     */
    function getTipo_accion()
    {
        if (!isset($this->itipo_accion) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->itipo_accion;
    }

    /**
     * Recupera l'atribut iid_escrito de Accion
     *
     * @return integer iid_escrito
     */
    function getId_escrito()
    {
        if (!isset($this->iid_escrito) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_escrito;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oAccionSet = new core\Set();

        $oAccionSet->add($this->getDatosId_expediente());
        $oAccionSet->add($this->getDatosTipo_accion());
        $oAccionSet->add($this->getDatosId_escrito());
        return $oAccionSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_expediente de Accion
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_expediente()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_expediente'));
        $oDatosCampo->setEtiqueta(_("id_expediente"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut itipo_accion de Accion
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo_accion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo_accion'));
        $oDatosCampo->setEtiqueta(_("tipo_accion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_escrito de Accion
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_escrito()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_escrito'));
        $oDatosCampo->setEtiqueta(_("id_escrito"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Accion en un array
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
