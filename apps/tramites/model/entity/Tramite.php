<?php

namespace tramites\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula x_tramites
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 19/6/2020
 */

/**
 * Classe que implementa l'entitat x_tramites
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 19/6/2020
 */
class Tramite extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Tramite
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Tramite
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Tramite
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Tramite
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
     * Id_schema de Tramite
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_tramite de Tramite
     *
     * @var integer
     */
    private $iid_tramite;
    /**
     * Tramite de Tramite
     *
     * @var string
     */
    private $stramite;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Orden de Tramite
     *
     * @var integer
     */
    private $iorden;
    /**
     * Breve de Tramite
     *
     * @var string
     */
    private $sbreve;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_tramite
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_tramite') && $val_id !== '') {
                    $this->iid_tramite = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_tramite = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_tramite' => $this->iid_tramite);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('x_tramites');
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
        $aDades['tramite'] = $this->stramite;
        $aDades['orden'] = $this->iorden;
        $aDades['breve'] = $this->sbreve;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					tramite                  = :tramite,
					orden                    = :orden,
					breve                    = :breve";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_tramite='$this->iid_tramite'")) === FALSE) {
                $sClauError = 'Tramite.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Tramite.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(tramite,orden,breve)";
            $valores = "(:tramite,:orden,:breve)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Tramite.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Tramite.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_tramite = $oDbl->lastInsertId('x_tramites_id_tramite_seq');
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
        if (isset($this->iid_tramite)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_tramite='$this->iid_tramite'")) === FALSE) {
                $sClauError = 'Tramite.carregar';
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
        $this->setId_tramite('');
        $this->setTramite('');
        $this->setOrden('');
        $this->setBreve('');
        $this->setPrimary_key($aPK);
    }

    /* METODES ALTRES  ----------------------------------------------------------*/
    /* METODES PRIVATS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Tramite en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_tramite' => $this->iid_tramite);
        }
        return $this->aPrimary_key;
    }

    /**
     * estableix el valor de l'atribut iid_tramite de Tramite
     *
     * @param integer iid_tramite
     */
    function setId_tramite($iid_tramite)
    {
        $this->iid_tramite = $iid_tramite;
    }

    /* METODES GET i SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut stramite de Tramite
     *
     * @param string stramite='' optional
     */
    function setTramite($stramite = '')
    {
        $this->stramite = $stramite;
    }

    /**
     * estableix el valor de l'atribut iorden de Tramite
     *
     * @param integer iorden='' optional
     */
    function setOrden($iorden = '')
    {
        $this->iorden = $iorden;
    }

    /**
     * estableix el valor de l'atribut sbreve de Tramite
     *
     * @param string sbreve='' optional
     */
    function setBreve($sbreve = '')
    {
        $this->sbreve = $sbreve;
    }

    /**
     * Estableix las claus primàries de Tramite en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_tramite') && $val_id !== '') {
                    $this->iid_tramite = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_tramite = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_tramite' => $this->iid_tramite);
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
        if (array_key_exists('id_tramite', $aDades)) {
            $this->setId_tramite($aDades['id_tramite']);
        }
        if (array_key_exists('tramite', $aDades)) {
            $this->setTramite($aDades['tramite']);
        }
        if (array_key_exists('orden', $aDades)) {
            $this->setOrden($aDades['orden']);
        }
        if (array_key_exists('breve', $aDades)) {
            $this->setBreve($aDades['breve']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_tramite='$this->iid_tramite'")) === FALSE) {
            $sClauError = 'Tramite.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_tramite de Tramite
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
     * Recupera l'atribut stramite de Tramite
     *
     * @return string stramite
     */
    function getTramite()
    {
        if (!isset($this->stramite) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->stramite;
    }

    /**
     * Recupera l'atribut iorden de Tramite
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
     * Recupera l'atribut sbreve de Tramite
     *
     * @return string sbreve
     */
    function getBreve()
    {
        if (!isset($this->sbreve) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->sbreve;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oTramiteSet = new core\Set();

        $oTramiteSet->add($this->getDatosTramite());
        $oTramiteSet->add($this->getDatosOrden());
        $oTramiteSet->add($this->getDatosBerve());
        return $oTramiteSet->getTot();
    }
    /* METODES GET i SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut stramite de Tramite
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTramite()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tramite'));
        $oDatosCampo->setEtiqueta(_("trámite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iorden de Tramite
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
     * Recupera tots els ATRIBUTOS de Tramite en un array
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

    /**
     * Recupera les propietats de l'atribut sbreve de Tramite
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosBreve()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'breve'));
        $oDatosCampo->setEtiqueta(_("breve"));
        return $oDatosCampo;
    }
}
