<?php

namespace davical\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula role_member
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */

/**
 * Classe que implementa l'entitat role_member
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */
class RoleMember extends core\ClasePropiedades
{

    /* CONST -------------------------------------------------------------- */
    // de la tabla roles

    // categoria
    const ROLE_ADMIN = 1;
    const ROLE_GROUP = 2;
    const ROLE_PUBLIC = 3;
    const ROLE_RESOURCE = 4;

    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * oDbl de RoleMember
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de RoleMember
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de RoleMember
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de RoleMember
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de RoleMember
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de RoleMember
     *
     * @var integer
     */
    private $iid_schema;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Role_no de RoleMember
     *
     * @var integer
     */
    private $irole_no;
    /**
     * User_no de RoleMember
     *
     * @var integer
     */
    private $iuser_no;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iuser_no
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'user_no') && $val_id !== '') {
                    $this->iuser_no = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iuser_no = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iuser_no' => $this->iuser_no);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('role_member');
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
        $aDades['role_no'] = $this->irole_no;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					role_no                  = :role_no";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE user_no='$this->iuser_no'")) === FALSE) {
                $sClauError = 'RoleMember.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'RoleMember.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->iuser_no);
            $campos = "(user_no,role_no)";
            $valores = "(:user_no,:role_no)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'RoleMember.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'RoleMember.insertar.execute';
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
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iuser_no)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE user_no='$this->iuser_no'")) === FALSE) {
                $sClauError = 'RoleMember.carregar';
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
        if (array_key_exists('role_no', $aDades)) {
            $this->setRole_no($aDades['role_no']);
        }
        if (array_key_exists('user_no', $aDades)) {
            $this->setUser_no($aDades['user_no']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut irole_no de RoleMember
     *
     * @param integer irole_no='' optional
     */
    function setRole_no($irole_no = '')
    {
        $this->irole_no = $irole_no;
    }

    /**
     * estableix el valor de l'atribut iuser_no de RoleMember
     *
     * @param integer iuser_no
     */
    function setUser_no($iuser_no)
    {
        $this->iuser_no = $iuser_no;
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
        $this->setRole_no('');
        $this->setUser_no('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de RoleMember en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('user_no' => $this->iuser_no);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de RoleMember en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'user_no') && $val_id !== '') {
                    $this->iuser_no = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iuser_no = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iuser_no' => $this->iuser_no);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE user_no='$this->iuser_no'")) === FALSE) {
            $sClauError = 'RoleMember.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut irole_no de RoleMember
     *
     * @return integer irole_no
     */
    function getRole_no()
    {
        if (!isset($this->irole_no) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->irole_no;
    }

    /**
     * Recupera l'atribut iuser_no de RoleMember
     *
     * @return integer iuser_no
     */
    function getUser_no()
    {
        if (!isset($this->iuser_no) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iuser_no;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oRoleMemberSet = new core\Set();

        $oRoleMemberSet->add($this->getDatosRole_no());
        return $oRoleMemberSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut irole_no de RoleMember
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosRole_no()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'role_no'));
        $oDatosCampo->setEtiqueta(_("role_no"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de RoleMember en un array
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
