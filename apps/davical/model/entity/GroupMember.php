<?php

namespace davical\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula group_member
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 3/2/2021
 */

/**
 * Classe que implementa l'entitat group_member
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 3/2/2021
 */
class GroupMember extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de GroupMember
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de GroupMember
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de GroupMember
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de GroupMember
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de GroupMember
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de GroupMember
     *
     * @var integer
     */
    private $iid_schema;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Group_id de GroupMember
     *
     * @var integer
     */
    private $igroup_id;
    /**
     * Member_id de GroupMember
     *
     * @var integer
     */
    private $imember_id;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array imember_id
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'member_id') && $val_id !== '') {
                    $this->imember_id = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->imember_id = (int)$a_id;
                $this->aPrimary_key = array('imember_id' => $this->imember_id);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('group_member');
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
        $aDades['group_id'] = $this->igroup_id;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					group_id                 = :group_id";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE member_id='$this->imember_id'")) === FALSE) {
                $sClauError = 'GroupMember.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'GroupMember.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->imember_id);
            $aDades1 = array_values($aDades);
            $campos = "(member_id,group_id)";
            $valores = "(?,?)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'GroupMember.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades1);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'GroupMember.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     */
    public function DBCargar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->imember_id)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE member_id='$this->imember_id'")) === FALSE) {
                $sClauError = 'GroupMember.carregar';
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
     * Establece el valor de todos los atributos
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
        if (array_key_exists('group_id', $aDades)) {
            $this->setGroup_id($aDades['group_id']);
        }
        if (array_key_exists('member_id', $aDades)) {
            $this->setMember_id($aDades['member_id']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param integer igroup_id='' optional
     */
    function setGroup_id($igroup_id = '')
    {
        $this->igroup_id = $igroup_id;
    }

    /**
     * @param integer imember_id
     */
    function setMember_id($imember_id)
    {
        $this->imember_id = $imember_id;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * Establece a empty el valor de todos los atributos de la clase
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setGroup_id('');
        $this->setMember_id('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de GroupMember en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('member_id' => $this->imember_id);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de GroupMember en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'member_id') && $val_id !== '') {
                    $this->imember_id = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->imember_id = (int)$a_id;
                $this->aPrimary_key = array('imember_id' => $this->imember_id);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE member_id='$this->imember_id'")) === FALSE) {
            $sClauError = 'GroupMember.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut igroup_id de GroupMember
     *
     * @return integer igroup_id
     */
    function getGroup_id()
    {
        if (!isset($this->igroup_id) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->igroup_id;
    }

    /**
     * Recupera l'atribut imember_id de GroupMember
     *
     * @return integer imember_id
     */
    function getMember_id()
    {
        if (!isset($this->imember_id) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->imember_id;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oGroupMemberSet = new core\Set();

        $oGroupMemberSet->add($this->getDatosGroup_id());
        return $oGroupMemberSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut igroup_id de GroupMember
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosGroup_id()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'group_id'));
        $oDatosCampo->setEtiqueta(_("group_id"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de GroupMember en un array
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
