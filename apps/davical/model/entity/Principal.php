<?php

namespace davical\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula principal
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */

/**
 * Classe que implementa l'entitat principal
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */
class Principal extends core\ClasePropiedades
{

    /* CONST -------------------------------------------------------------- */
    // de la tabla principal_type

    // categoria
    const TYPE_PERSON = 1;
    const TYPE_RESOURCE = 2;
    const TYPE_GROUP = 3;

    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * oDbl de Principal
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Principal
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Principal
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Principal
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Principal
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Principal
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Principal_id de Principal
     *
     * @var integer
     */
    private $iprincipal_id;
    /**
     * Type_id de Principal
     *
     * @var integer
     */
    private $itype_id;
    /**
     * User_no de Principal
     *
     * @var integer
     */
    private $iuser_no;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Displayname de Principal
     *
     * @var string
     */
    private $sdisplayname;
    /**
     * Default_privileges de Principal
     *
     * @var string
     */
    private $sdefault_privileges;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iprincipal_id
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'principal_id') && $val_id !== '') {
                    $this->iprincipal_id = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iprincipal_id = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iprincipal_id' => $this->iprincipal_id);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('principal');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function cambiarNombre($user_no, $displayname_new)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $sQry = "UPDATE $nom_tabla SET displayname='$displayname_new' WHERE user_no='$user_no'; ";

        if (($oDbl->query($sQry)) === FALSE) {
            $sClauError = 'DavicalUser.cambioNombre';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

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
        $aDades['type_id'] = $this->itype_id;
        $aDades['user_no'] = $this->iuser_no;
        $aDades['displayname'] = $this->sdisplayname;
        $aDades['default_privileges'] = $this->sdefault_privileges;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					type_id                  = :type_id,
					user_no                  = :user_no,
					displayname              = :displayname,
					default_privileges       = :default_privileges";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE principal_id='$this->iprincipal_id'")) === FALSE) {
                $sClauError = 'Principal.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Principal.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(type_id,user_no,displayname,default_privileges)";
            $valores = "(:type_id,:user_no,:displayname,:default_privileges)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Principal.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Principal.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->principal_id = $oDbl->lastInsertId('dav_id_seq');
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
        if (isset($this->iprincipal_id)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE principal_id='$this->iprincipal_id'")) === FALSE) {
                $sClauError = 'Principal.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
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
    function setAllAtributes($aDades)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('principal_id', $aDades)) {
            $this->setPrincipal_id($aDades['principal_id']);
        }
        if (array_key_exists('type_id', $aDades)) {
            $this->setType_id($aDades['type_id']);
        }
        if (array_key_exists('user_no', $aDades)) {
            $this->setUser_no($aDades['user_no']);
        }
        if (array_key_exists('displayname', $aDades)) {
            $this->setDisplayname($aDades['displayname']);
        }
        if (array_key_exists('default_privileges', $aDades)) {
            $this->setDefault_privileges($aDades['default_privileges']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iprincipal_id de Principal
     *
     * @param integer iprincipal_id
     */
    function setPrincipal_id($iprincipal_id)
    {
        $this->iprincipal_id = $iprincipal_id;
    }

    /**
     * estableix el valor de l'atribut itype_id de Principal
     *
     * @param integer itype_id='' optional
     */
    function setType_id($itype_id = '')
    {
        $this->itype_id = $itype_id;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iuser_no de Principal
     *
     * @param integer iuser_no='' optional
     */
    function setUser_no($iuser_no = '')
    {
        $this->iuser_no = $iuser_no;
    }

    /**
     * estableix el valor de l'atribut sdisplayname de Principal
     *
     * @param string sdisplayname='' optional
     */
    function setDisplayname($sdisplayname = '')
    {
        $this->sdisplayname = $sdisplayname;
    }

    /**
     * estableix el valor de l'atribut sdefault_privileges de Principal
     *
     * @param string sdefault_privileges='' optional
     */
    function setDefault_privileges($sdefault_privileges = '')
    {
        $this->sdefault_privileges = $sdefault_privileges;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setPrincipal_id('');
        $this->setType_id('');
        $this->setUser_no('');
        $this->setDisplayname('');
        $this->setDefault_privileges('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Principal en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('principal_id' => $this->iprincipal_id);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Principal en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'principal_id') && $val_id !== '') {
                    $this->iprincipal_id = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iprincipal_id = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iprincipal_id' => $this->iprincipal_id);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE principal_id='$this->iprincipal_id'")) === FALSE) {
            $sClauError = 'Principal.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iprincipal_id de Principal
     *
     * @return integer iprincipal_id
     */
    function getPrincipal_id()
    {
        if (!isset($this->iprincipal_id) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iprincipal_id;
    }

    /**
     * Recupera l'atribut itype_id de Principal
     *
     * @return integer itype_id
     */
    function getType_id()
    {
        if (!isset($this->itype_id) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->itype_id;
    }

    /**
     * Recupera l'atribut iuser_no de Principal
     *
     * @return integer iuser_no
     */
    function getUser_no()
    {
        if (!isset($this->iuser_no) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iuser_no;
    }

    /**
     * Recupera l'atribut sdisplayname de Principal
     *
     * @return string sdisplayname
     */
    function getDisplayname()
    {
        if (!isset($this->sdisplayname) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->sdisplayname;
    }

    /**
     * Recupera l'atribut sdefault_privileges de Principal
     *
     * @return string sdefault_privileges
     */
    function getDefault_privileges()
    {
        if (!isset($this->sdefault_privileges) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->sdefault_privileges;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oPrincipalSet = new core\Set();

        $oPrincipalSet->add($this->getDatosType_id());
        $oPrincipalSet->add($this->getDatosUser_no());
        $oPrincipalSet->add($this->getDatosDisplayname());
        $oPrincipalSet->add($this->getDatosDefault_privileges());
        return $oPrincipalSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut itype_id de Principal
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosType_id()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'type_id'));
        $oDatosCampo->setEtiqueta(_("type_id"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iuser_no de Principal
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUser_no()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'user_no'));
        $oDatosCampo->setEtiqueta(_("user_no"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdisplayname de Principal
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDisplayname()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'displayname'));
        $oDatosCampo->setEtiqueta(_("displayname"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdefault_privileges de Principal
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDefault_privileges()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'default_privileges'));
        $oDatosCampo->setEtiqueta(_("privilegios por defecto"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Principal en un array
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
