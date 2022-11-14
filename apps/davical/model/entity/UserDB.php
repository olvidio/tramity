<?php

namespace davical\model\entity;

use core;
use PDO;
use PDOException;
use web;

/**
 * Fitxer amb la Classe que accedeix a la taula usr
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */

/**
 * Classe que implementa l'entitat usr
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 1/2/2021
 */
class UserDB extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de UserDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de UserDB
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de UserDB
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de UserDB
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de UserDB
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de UserDB
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * User_no de UserDB
     *
     * @var integer
     */
    private $iuser_no;
    /**
     * Active de UserDB
     *
     * @var boolean
     */
    private $bactive;
    /**
     * Email_ok de UserDB
     *
     * @var web\DateTimeLocal
     */
    private $demail_ok;
    /**
     * Joined de UserDB
     *
     * @var web\DateTimeLocal
     */
    private $djoined;
    /**
     * Updated de UserDB
     *
     * @var web\DateTimeLocal
     */
    private $dupdated;
    /**
     * Last_used de UserDB
     *
     * @var web\DateTimeLocal
     */
    private $dlast_used;
    /**
     * Username de UserDB
     *
     * @var string
     */
    private $susername;
    /**
     * Password de UserDB
     *
     * @var string
     */
    private $spassword;
    /**
     * Fullname de UserDB
     *
     * @var string
     */
    private $sfullname;
    /**
     * Email de UserDB
     *
     * @var string
     */
    private $semail;
    /**
     * Config_data de UserDB
     *
     * @var string
     */
    private $sconfig_data;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Date_format_type de UserDB
     *
     * @var string
     */
    private $sdate_format_type;
    /**
     * Locale de UserDB
     *
     * @var string
     */
    private $slocale;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iuser_no
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBDavical'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'user_no') && $val_id !== '') {
                    $this->iuser_no = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iuser_no = (int)$a_id;
                $this->aPrimary_key = array('iuser_no' => $this->iuser_no);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('usr');
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
        $aDades['active'] = $this->bactive;
        $aDades['email_ok'] = $this->demail_ok;
        $aDades['joined'] = $this->djoined;
        $aDades['updated'] = $this->dupdated;
        $aDades['last_used'] = $this->dlast_used;
        $aDades['username'] = $this->susername;
        $aDades['password'] = $this->spassword;
        $aDades['fullname'] = $this->sfullname;
        $aDades['email'] = $this->semail;
        $aDades['config_data'] = $this->sconfig_data;
        $aDades['date_format_type'] = $this->sdate_format_type;
        $aDades['locale'] = $this->slocale;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['active'])) {
            $aDades['active'] = 'true';
        } else {
            $aDades['active'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					active                   = :active,
					email_ok                 = :email_ok,
					joined                   = :joined,
					updated                  = :updated,
					last_used                = :last_used,
					username                 = :username,
					password                 = :password,
					fullname                 = :fullname,
					email                    = :email,
					config_data              = :config_data,
					date_format_type         = :date_format_type,
					locale                   = :locale";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE user_no='$this->iuser_no'")) === FALSE) {
                $sClauError = 'UserDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'UserDB.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(active,email_ok,joined,updated,last_used,username,password,fullname,email,config_data,date_format_type,locale)";
            $valores = "(:active,:email_ok,:joined,:updated,:last_used,:username,:password,:fullname,:email,:config_data,:date_format_type,:locale)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'UserDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'UserDB.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iuser_no = $oDbl->lastInsertId('usr_user_no_seq');
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
                $sClauError = 'UserDB.carregar';
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
    private function setAllAtributes($aDades, $convert = FALSE)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('user_no', $aDades)) {
            $this->setUser_no($aDades['user_no']);
        }
        if (array_key_exists('active', $aDades)) {
            $this->setActive($aDades['active']);
        }
        if (array_key_exists('email_ok', $aDades)) {
            $this->setEmail_ok($aDades['email_ok'], $convert);
        }
        if (array_key_exists('joined', $aDades)) {
            $this->setJoined($aDades['joined'], $convert);
        }
        if (array_key_exists('updated', $aDades)) {
            $this->setUpdated($aDades['updated'], $convert);
        }
        if (array_key_exists('last_used', $aDades)) {
            $this->setLast_used($aDades['last_used'], $convert);
        }
        if (array_key_exists('username', $aDades)) {
            $this->setUsername($aDades['username']);
        }
        if (array_key_exists('password', $aDades)) {
            $this->setPassword($aDades['password']);
        }
        if (array_key_exists('fullname', $aDades)) {
            $this->setFullname($aDades['fullname']);
        }
        if (array_key_exists('email', $aDades)) {
            $this->setEmail($aDades['email']);
        }
        if (array_key_exists('config_data', $aDades)) {
            $this->setConfig_data($aDades['config_data']);
        }
        if (array_key_exists('date_format_type', $aDades)) {
            $this->setDate_format_type($aDades['date_format_type']);
        }
        if (array_key_exists('locale', $aDades)) {
            $this->setLocale($aDades['locale']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iuser_no de UserDB
     *
     * @param integer iuser_no
     */
    function setUser_no($iuser_no)
    {
        $this->iuser_no = $iuser_no;
    }

    /**
     * estableix el valor de l'atribut bactive de UserDB
     *
     * @param boolean bactive='f' optional
     */
    function setActive($bactive = 'f')
    {
        $this->bactive = $bactive;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut demail_ok de UserDB
     * Si demail_ok es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, demail_ok debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string demail_ok='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setEmail_ok($demail_ok = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($demail_ok)) {
            $oConverter = new core\Converter('timestamptz', $demail_ok);
            $this->demail_ok = $oConverter->toPg();
        } else {
            $this->demail_ok = $demail_ok;
        }
    }

    /**
     * estableix el valor de l'atribut djoined de UserDB
     * Si djoined es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, djoined debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string djoined='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setJoined($djoined = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($djoined)) {
            $oConverter = new core\Converter('timestamptz', $djoined);
            $this->djoined = $oConverter->toPg();
        } else {
            $this->djoined = $djoined;
        }
    }

    /**
     * estableix el valor de l'atribut dupdated de UserDB
     * Si dupdated es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dupdated debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dupdated='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setUpdated($dupdated = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dupdated)) {
            $oConverter = new core\Converter('timestamptz', $dupdated);
            $this->dupdated = $oConverter->toPg();
        } else {
            $this->dupdated = $dupdated;
        }
    }

    /**
     * estableix el valor de l'atribut dlast_used de UserDB
     * Si dlast_used es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, dlast_used debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string dlast_used='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setLast_used($dlast_used = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($dlast_used)) {
            $oConverter = new core\Converter('timestamptz', $dlast_used);
            $this->dlast_used = $oConverter->toPg();
        } else {
            $this->dlast_used = $dlast_used;
        }
    }

    /**
     * estableix el valor de l'atribut susername de UserDB
     *
     * @param string susername='' optional
     */
    function setUsername($susername = '')
    {
        $this->susername = $susername;
    }

    /**
     * estableix el valor de l'atribut spassword de UserDB
     *
     * @param string spassword='' optional
     */
    function setPassword($spassword = '')
    {
        $this->spassword = $spassword;
    }

    /**
     * estableix el valor de l'atribut sfullname de UserDB
     *
     * @param string sfullname='' optional
     */
    function setFullname($sfullname = '')
    {
        $this->sfullname = $sfullname;
    }

    /**
     * estableix el valor de l'atribut semail de UserDB
     *
     * @param string semail='' optional
     */
    function setEmail($semail = '')
    {
        $this->semail = $semail;
    }

    /**
     * estableix el valor de l'atribut sconfig_data de UserDB
     *
     * @param string sconfig_data='' optional
     */
    function setConfig_data($sconfig_data = '')
    {
        $this->sconfig_data = $sconfig_data;
    }

    /**
     * estableix el valor de l'atribut sdate_format_type de UserDB
     *
     * @param string sdate_format_type='' optional
     */
    function setDate_format_type($sdate_format_type = '')
    {
        $this->sdate_format_type = $sdate_format_type;
    }

    /**
     * estableix el valor de l'atribut slocale de UserDB
     *
     * @param string slocale='' optional
     */
    function setLocale($slocale = '')
    {
        $this->slocale = $slocale;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setUser_no('');
        $this->setActive('');
        $this->setEmail_ok('');
        $this->setJoined('');
        $this->setUpdated('');
        $this->setLast_used('');
        $this->setUsername('');
        $this->setPassword('');
        $this->setFullname('');
        $this->setEmail('');
        $this->setConfig_data('');
        $this->setDate_format_type('');
        $this->setLocale('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de UserDB en un array
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
     * Estableix las claus primàries de UserDB en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'user_no') && $val_id !== '') {
                    $this->iuser_no = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iuser_no = (int)$a_id;
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
            $sClauError = 'UserDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iuser_no de UserDB
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
     * Recupera l'atribut bactive de UserDB
     *
     * @return boolean bactive
     */
    function getActive()
    {
        if (!isset($this->bactive) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bactive;
    }

    /**
     * Recupera l'atribut demail_ok de UserDB
     *
     * @return web\DateTimeLocal|web\NullDateTimeLocal demail_ok
     */
    function getEmail_ok()
    {
        if (!isset($this->demail_ok) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->demail_ok)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('timestamptz', $this->demail_ok);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut djoined de UserDB
     *
     * @return web\DateTimeLocal djoined
     */
    function getJoined()
    {
        if (!isset($this->djoined) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->djoined)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('timestamptz', $this->djoined);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut dupdated de UserDB
     *
     * @return web\DateTimeLocal dupdated
     */
    function getUpdated()
    {
        if (!isset($this->dupdated) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dupdated)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('timestamptz', $this->dupdated);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut dlast_used de UserDB
     *
     * @return web\DateTimeLocal dlast_used
     */
    function getLast_used()
    {
        if (!isset($this->dlast_used) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->dlast_used)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('timestamptz', $this->dlast_used);
        return $oConverter->fromPg();
    }

    /**
     * Recupera l'atribut susername de UserDB
     *
     * @return string susername
     */
    function getUsername()
    {
        if (!isset($this->susername) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->susername;
    }

    /**
     * Recupera l'atribut spassword de UserDB
     *
     * @return string spassword
     */
    function getPassword()
    {
        if (!isset($this->spassword) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->spassword;
    }

    /**
     * Recupera l'atribut sfullname de UserDB
     *
     * @return string sfullname
     */
    function getFullname()
    {
        if (!isset($this->sfullname) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sfullname;
    }

    /**
     * Recupera l'atribut semail de UserDB
     *
     * @return string semail
     */
    function getEmail()
    {
        if (!isset($this->semail) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->semail;
    }

    /**
     * Recupera l'atribut sconfig_data de UserDB
     *
     * @return string sconfig_data
     */
    function getConfig_data()
    {
        if (!isset($this->sconfig_data) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sconfig_data;
    }

    /**
     * Recupera l'atribut sdate_format_type de UserDB
     *
     * @return string sdate_format_type
     */
    function getDate_format_type()
    {
        if (!isset($this->sdate_format_type) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdate_format_type;
    }

    /**
     * Recupera l'atribut slocale de UserDB
     *
     * @return string slocale
     */
    function getLocale()
    {
        if (!isset($this->slocale) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->slocale;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oUserDBSet = new core\Set();

        $oUserDBSet->add($this->getDatosActive());
        $oUserDBSet->add($this->getDatosEmail_ok());
        $oUserDBSet->add($this->getDatosJoined());
        $oUserDBSet->add($this->getDatosUpdated());
        $oUserDBSet->add($this->getDatosLast_used());
        $oUserDBSet->add($this->getDatosUsername());
        $oUserDBSet->add($this->getDatosPassword());
        $oUserDBSet->add($this->getDatosFullname());
        $oUserDBSet->add($this->getDatosEmail());
        $oUserDBSet->add($this->getDatosConfig_data());
        $oUserDBSet->add($this->getDatosDate_format_type());
        $oUserDBSet->add($this->getDatosLocale());
        return $oUserDBSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut bactive de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosActive()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'active'));
        $oDatosCampo->setEtiqueta(_("active"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut demail_ok de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEmail_ok()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'email_ok'));
        $oDatosCampo->setEtiqueta(_("email_ok"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut djoined de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJoined()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'joined'));
        $oDatosCampo->setEtiqueta(_("joined"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dupdated de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUpdated()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'updated'));
        $oDatosCampo->setEtiqueta(_("updated"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut dlast_used de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosLast_used()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'last_used'));
        $oDatosCampo->setEtiqueta(_("last_used"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut susername de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosUsername()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'username'));
        $oDatosCampo->setEtiqueta(_("username"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut spassword de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPassword()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'password'));
        $oDatosCampo->setEtiqueta(_("password"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sfullname de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosFullname()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'fullname'));
        $oDatosCampo->setEtiqueta(_("fullname"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut semail de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosEmail()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'email'));
        $oDatosCampo->setEtiqueta(_("email"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sconfig_data de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosConfig_data()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'config_data'));
        $oDatosCampo->setEtiqueta(_("config_data"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdate_format_type de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDate_format_type()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'date_format_type'));
        $oDatosCampo->setEtiqueta(_("date_format_type"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut slocale de UserDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosLocale()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'locale'));
        $oDatosCampo->setEtiqueta(_("locale"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de UserDB en un array
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
