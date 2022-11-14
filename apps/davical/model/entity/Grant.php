<?php

namespace davical\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula grants
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 2/2/2021
 */

/**
 * Classe que implementa l'entitat grants
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 2/2/2021
 */
class Grant extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Grant
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Grant
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Grant
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Grant
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Grant
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Grant
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * By_principal de Grant
     *
     * @var integer
     */
    private $iby_principal;
    /**
     * By_collection de Grant
     *
     * @var integer
     */
    private $iby_collection;
    /**
     * To_principal de Grant
     *
     * @var integer
     */
    private $ito_principal;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Privileges de Grant
     *
     * @var integer
     */
    private $iprivileges;
    /**
     * Is_group de Grant
     *
     * @var boolean
     */
    private $bis_group;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array ito_principal
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                $this->ito_principal = (int)$val_id;
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('grants');
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
        $aDades['by_principal'] = $this->iby_principal;
        $aDades['by_collection'] = $this->iby_collection;
        $aDades['privileges'] = $this->iprivileges;
        $aDades['is_group'] = $this->bis_group;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['is_group'])) {
            $aDades['is_group'] = 'true';
        } else {
            $aDades['is_group'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					by_principal            = :by_principal,
					by_collection            = :by_collection,
					privileges               = :privileges,
					is_group                 = :is_group";

            if (!empty($this->iby_principal)) {
                $sUpdate = "UPDATE $nom_tabla SET $update WHERE by_principal='$this->iby_principal' AND to_principal='$this->ito_principal'";
            }
            if (!empty($this->iby_collection)) {
                $sUpdate = "UPDATE $nom_tabla SET $update WHERE by_collection='$this->iby_collection' AND to_principal='$this->ito_principal'";
            }
            if (($oDblSt = $oDbl->prepare($sUpdate)) === FALSE) {
                $sClauError = 'Grant.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Grant.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->ito_principal);
            $aDades1 = array_values($aDades);
            //array_walk($aDades1, 'core\poner_null');
            $campos = "(to_principal,by_principal,by_collection,privileges,is_group)";
            $valores = "(?,?,?,?,?)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Grant.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades1);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Grant.insertar.execute';
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
        if (isset($this->ito_principal)) {
            if (!empty($this->iby_principal)) {
                $sQuery = "SELECT * FROM $nom_tabla WHERE by_principal='$this->iby_principal' AND to_principal='$this->ito_principal'";
            }
            if (!empty($this->iby_collection)) {
                $sQuery = "SELECT * FROM $nom_tabla WHERE by_collection='$this->iby_collection' AND to_principal='$this->ito_principal'";
            }
            if (($oDblSt = $oDbl->query($sQuery)) === FALSE) {
                $sClauError = 'Grant.carregar';
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
        if (array_key_exists('by_principal', $aDades)) {
            $this->setBy_principal($aDades['by_principal']);
        }
        if (array_key_exists('by_collection', $aDades)) {
            $this->setBy_collection($aDades['by_collection']);
        }
        if (array_key_exists('to_principal', $aDades)) {
            $this->setTo_principal($aDades['to_principal']);
        }
        if (array_key_exists('privileges', $aDades)) {
            $this->setPrivileges($aDades['privileges']);
        }
        if (array_key_exists('is_group', $aDades)) {
            $this->setIs_group($aDades['is_group']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iby_principal de Grant
     *
     * @param integer iby_principal
     */
    function setBy_principal($iby_principal)
    {
        $this->iby_principal = $iby_principal;
    }

    /**
     * estableix el valor de l'atribut iby_collection de Grant
     *
     * @param integer iby_collection='' optional
     */
    function setBy_collection($iby_collection = '')
    {
        $this->iby_collection = $iby_collection;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut ito_principal de Grant
     *
     * @param integer ito_principal
     */
    function setTo_principal($ito_principal)
    {
        $this->ito_principal = $ito_principal;
    }

    /**
     * estableix el valor de l'atribut iprivileges de Grant
     *
     * @param integer iprivileges='' optional
     */
    function setPrivileges($iprivileges = '')
    {
        $this->iprivileges = $iprivileges;
    }

    /**
     * estableix el valor de l'atribut bis_group de Grant
     *
     * @param boolean bis_group='f' optional
     */
    function setIs_group($bis_group = 'f')
    {
        $this->bis_group = $bis_group;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setBy_principal('');
        $this->setBy_collection('');
        $this->setTo_principal('');
        $this->setPrivileges('');
        $this->setIs_group('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Grant en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('by_principal' => $this->iby_principal, 'to_principal' => $this->ito_principal);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Grant en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'by_principal') && $val_id !== '') {
                    $this->iby_principal = (int)$val_id;
                }
                if (($nom_id === 'to_principal') && $val_id !== '') {
                    $this->ito_principal = (int)$val_id;
                }
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
        if (!empty($this->iby_principal)) {
            $sDelete = "DELETE FROM $nom_tabla WHERE by_principal='$this->iby_principal' AND to_principal='$this->ito_principal'";
        }
        if (!empty($this->iby_collection)) {
            $sDelete = "DELETE FROM $nom_tabla WHERE by_collection='$this->iby_collection' AND to_principal='$this->ito_principal'";
        }
        if (($oDbl->exec($sDelete)) === FALSE) {
            $sClauError = 'Grant.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iby_principal de Grant
     *
     * @return integer iby_principal
     */
    function getBy_principal()
    {
        if (!isset($this->iby_principal) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iby_principal;
    }

    /**
     * Recupera l'atribut iby_collection de Grant
     *
     * @return integer iby_collection
     */
    function getBy_collection()
    {
        if (!isset($this->iby_collection) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iby_collection;
    }

    /**
     * Recupera l'atribut ito_principal de Grant
     *
     * @return integer ito_principal
     */
    function getTo_principal()
    {
        if (!isset($this->ito_principal) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ito_principal;
    }

    /**
     * Recupera l'atribut iprivileges de Grant
     *
     * @return integer iprivileges
     */
    function getPrivileges()
    {
        if (!isset($this->iprivileges) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iprivileges;
    }

    /**
     * Recupera l'atribut bis_group de Grant
     *
     * @return boolean bis_group
     */
    function getIs_group()
    {
        if (!isset($this->bis_group) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bis_group;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oGrantSet = new core\Set();

        $oGrantSet->add($this->getDatosBy_collection());
        $oGrantSet->add($this->getDatosPrivileges());
        $oGrantSet->add($this->getDatosIs_group());
        return $oGrantSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iby_collection de Grant
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosBy_collection()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'by_collection'));
        $oDatosCampo->setEtiqueta(_("by_collection"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iprivileges de Grant
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPrivileges()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'privileges'));
        $oDatosCampo->setEtiqueta(_("privileges"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bis_group de Grant
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosIs_group()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'is_group'));
        $oDatosCampo->setEtiqueta(_("is_group"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Grant en un array
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
