<?php

namespace tramites\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula tramite_cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 19/6/2020
 */

/**
 * Classe que implementa l'entitat tramite_cargo
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 19/6/2020
 */
class TramiteCargo extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de TramiteCargo
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de TramiteCargo
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de TramiteCargo
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de TramiteCargo
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
     * Id_schema de TramiteCargo
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_item de TramiteCargo
     *
     * @var integer
     */
    private $iid_item;
    /**
     * Id_tramite de TramiteCargo
     *
     * @var integer
     */
    private $iid_tramite;
    /**
     * Orden_tramite de TramiteCargo
     *
     * @var integer
     */
    private $iorden_tramite;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Id_cargo de TramiteCargo
     *
     * @var integer
     */
    private $iid_cargo;
    /**
     * Multiple de TramiteCargo
     *
     * @var integer
     */
    private $imultiple;
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
        $this->setNomTabla('tramite_cargo');
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
        $aDades['id_tramite'] = $this->iid_tramite;
        $aDades['orden_tramite'] = $this->iorden_tramite;
        $aDades['id_cargo'] = $this->iid_cargo;
        $aDades['multiple'] = $this->imultiple;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_tramite               = :id_tramite,
					orden_tramite            = :orden_tramite,
					id_cargo                 = :id_cargo,
					multiple                 = :multiple";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'TramiteCargo.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'TramiteCargo.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_tramite,orden_tramite,id_cargo,multiple)";
            $valores = "(:id_tramite,:orden_tramite,:id_cargo,:multiple)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'TramiteCargo.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'TramiteCargo.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_item = $oDbl->lastInsertId('tramite_cargo_id_item_seq');
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
                $sClauError = 'TramiteCargo.carregar';
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
        $this->setId_tramite('');
        $this->setOrden_tramite('');
        $this->setId_cargo('');
        $this->setMultiple('');
        $this->setPrimary_key($aPK);
    }

    /* METODES ALTRES  ----------------------------------------------------------*/
    /* METODES PRIVATS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de TramiteCargo en un array
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
     * estableix el valor de l'atribut iid_item de TramiteCargo
     *
     * @param integer iid_item
     */
    function setId_item($iid_item)
    {
        $this->iid_item = $iid_item;
    }

    /* METODES GET i SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_tramite de TramiteCargo
     *
     * @param integer iid_tramite='' optional
     */
    function setId_tramite($iid_tramite = '')
    {
        $this->iid_tramite = $iid_tramite;
    }

    /**
     * estableix el valor de l'atribut iorden_tramite de TramiteCargo
     *
     * @param integer iorden_tramite='' optional
     */
    function setOrden_tramite($iorden_tramite = '')
    {
        $this->iorden_tramite = $iorden_tramite;
    }

    /**
     * estableix el valor de l'atribut iid_cargo de TramiteCargo
     *
     * @param integer iid_cargo='' optional
     */
    function setId_cargo($iid_cargo = '')
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     * estableix el valor de l'atribut imultiple de TramiteCargo
     *
     * @param integer imultiple=1 optional
     */
    function setMultiple($imultiple = 1)
    {
        $this->imultiple = $imultiple;
    }

    /**
     * Estableix las claus primàries de TramiteCargo en un array
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
        if (array_key_exists('id_tramite', $aDades)) {
            $this->setId_tramite($aDades['id_tramite']);
        }
        if (array_key_exists('orden_tramite', $aDades)) {
            $this->setOrden_tramite($aDades['orden_tramite']);
        }
        if (array_key_exists('id_cargo', $aDades)) {
            $this->setId_cargo($aDades['id_cargo']);
        }
        if (array_key_exists('multiple', $aDades)) {
            $this->setMultiple($aDades['multiple']);
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
            $sClauError = 'TramiteCargo.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_item de TramiteCargo
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
     * Recupera l'atribut iid_tramite de TramiteCargo
     *
     * @return integer iid_tramite
     */
    function getId_tramite()
    {
        if (!isset($this->iid_tramite) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_tramite;
    }

    /**
     * Recupera l'atribut iorden_tramite de TramiteCargo
     *
     * @return integer iorden_tramite
     */
    function getOrden_tramite()
    {
        if (!isset($this->iorden_tramite) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iorden_tramite;
    }

    /**
     * Recupera l'atribut iid_cargo de TramiteCargo
     *
     * @return integer iid_cargo
     */
    function getId_cargo()
    {
        if (!isset($this->iid_cargo) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_cargo;
    }

    /**
     * Recupera l'atribut imultiple de TramiteCargo
     *
     * @return integer imultiple
     */
    function getMultiple()
    {
        if (!isset($this->imultiple) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->imultiple;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oTramiteCargoSet = new core\Set();

        $oTramiteCargoSet->add($this->getDatosId_tramite());
        $oTramiteCargoSet->add($this->getDatosOrden_tramite());
        $oTramiteCargoSet->add($this->getDatosId_cargo());
        $oTramiteCargoSet->add($this->getDatosMultiple());
        return $oTramiteCargoSet->getTot();
    }
    /* METODES GET i SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_tramite de TramiteCargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_tramite()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_tramite'));
        $oDatosCampo->setEtiqueta(_("id_tramite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iorden_tramite de TramiteCargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOrden_tramite()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'orden_tramite'));
        $oDatosCampo->setEtiqueta(_("orden_tramite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_cargo de TramiteCargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_cargo()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_cargo'));
        $oDatosCampo->setEtiqueta(_("id_cargo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut imultiple de TramiteCargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosMultiple()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'multiple'));
        $oDatosCampo->setEtiqueta(_("multiple"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de TramiteCargo en un array
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
