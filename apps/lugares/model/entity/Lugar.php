<?php

namespace lugares\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula lugares
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */

/**
 * Classe que implementa l'entitat lugares
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 16/6/2020
 */
class Lugar extends core\ClasePropiedades
{

    /* CONST -------------------------------------------------------------- */
    // modo envio
    const MODO_PDF = 1;
    const MODO_XML = 2;
    const MODO_AS4 = 3;

    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * oDbl de Lugar
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Lugar
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Lugar
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Lugar
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
     * Id_schema de Lugar
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_lugar de Lugar
     *
     * @var integer
     */
    private $iid_lugar;
    /**
     * Sigla de Lugar
     *
     * @var string
     */
    private $ssigla;
    /**
     * Dl de Lugar
     *
     * @var string
     */
    private $sdl;
    /**
     * Region de Lugar
     *
     * @var string
     */
    private $sregion;
    /**
     * Nombre de Lugar
     *
     * @var string
     */
    private $snombre;
    /**
     * Tipo_ctr de Lugar
     *
     * @var string
     */
    private $stipo_ctr;
    /**
     * Modo_envio de Lugar
     *
     * @var integer
     */
    private $imodo_envio;
    /**
     * plataforma de Lugar
     *
     * @var string
     */
    private $splataforma;
    /**
     * Pub_key de Lugar
     *
     * @var integer
     */
    private $ipub_key;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * e_mail de Lugar
     *
     * @var string
     */
    private $se_mail;
    /**
     * Anulado de Lugar
     *
     * @var boolean
     */
    private $banulado;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_lugar
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBP'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_lugar') && $val_id !== '') {
                    $this->iid_lugar = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_lugar = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_lugar' => $this->iid_lugar);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('lugares');
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
        $aDades['sigla'] = $this->ssigla;
        $aDades['dl'] = $this->sdl;
        $aDades['region'] = $this->sregion;
        $aDades['nombre'] = $this->snombre;
        $aDades['tipo_ctr'] = $this->stipo_ctr;
        $aDades['modo_envio'] = $this->imodo_envio;
        $aDades['plataforma'] = $this->splataforma;
        $aDades['pub_key'] = $this->ipub_key;
        $aDades['e_mail'] = $this->se_mail;
        $aDades['anulado'] = $this->banulado;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['anulado'])) {
            $aDades['anulado'] = 'true';
        } else {
            $aDades['anulado'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					sigla                    = :sigla,
					dl                       = :dl,
					region                   = :region,
					nombre                   = :nombre,
					tipo_ctr                 = :tipo_ctr,
					modo_envio               = :modo_envio,
					plataforma               = :plataforma,
					pub_key                  = :pub_key,
					e_mail                   = :e_mail,
					anulado                  = :anulado";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_lugar='$this->iid_lugar'")) === FALSE) {
                $sClauError = 'Lugar.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Lugar.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(sigla,dl,region,nombre,tipo_ctr,modo_envio,plataforma,pub_key,e_mail,anulado)";
            $valores = "(:sigla,:dl,:region,:nombre,:tipo_ctr,:modo_envio,:plataforma,:pub_key,:e_mail,:anulado)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Lugar.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Lugar.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_lugar = $oDbl->lastInsertId('lugares_id_lugar_seq');
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
        if (isset($this->iid_lugar)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_lugar='$this->iid_lugar'")) === FALSE) {
                $sClauError = 'Lugar.carregar';
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
        $this->setId_lugar('');
        $this->setSigla('');
        $this->setDl('');
        $this->setRegion('');
        $this->setNombre('');
        $this->setTipo_ctr('');
        $this->setModo_envio('');
        $this->setPlataforma('');
        $this->setPub_key('');
        $this->setE_mail('');
        $this->setAnulado('');
        $this->setPrimary_key($aPK);
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Lugar en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_lugar' => $this->iid_lugar);
        }
        return $this->aPrimary_key;
    }

    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_lugar de Lugar
     *
     * @param integer iid_lugar
     */
    function setId_lugar($iid_lugar)
    {
        $this->iid_lugar = $iid_lugar;
    }

    /**
     * estableix el valor de l'atribut ssigla de Lugar
     *
     * @param string ssigla='' optional
     */
    function setSigla($ssigla = '')
    {
        $this->ssigla = $ssigla;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut sdl de Lugar
     *
     * @param string sdl='' optional
     */
    function setDl($sdl = '')
    {
        $this->sdl = $sdl;
    }

    /**
     * estableix el valor de l'atribut sregion de Lugar
     *
     * @param string sregion='' optional
     */
    function setRegion($sregion = '')
    {
        $this->sregion = $sregion;
    }

    /**
     * estableix el valor de l'atribut snombre de Lugar
     *
     * @param string snombre='' optional
     */
    function setNombre($snombre = '')
    {
        $this->snombre = $snombre;
    }

    /**
     * estableix el valor de l'atribut stipo_ctr de Lugar
     *
     * @param string stipo_ctr='' optional
     */
    function setTipo_ctr($stipo_ctr = '')
    {
        $this->stipo_ctr = $stipo_ctr;
    }

    /**
     * estableix el valor de l'atribut imodo_envio de Lugar
     *
     * @param integer imodo_envio='' optional
     */
    function setModo_envio($imodo_envio = '')
    {
        $this->imodo_envio = $imodo_envio;
    }

    /**
     * estableix el valor de l'atribut splataforma de Lugar
     *
     * @param string splataforma='' optional
     */
    function setPlataforma($splataforma = '')
    {
        $this->splataforma = $splataforma;
    }

    /**
     * estableix el valor de l'atribut ipub_key de Lugar
     *
     * @param integer ipub_key='' optional
     */
    function setPub_key($ipub_key = '')
    {
        $this->ipub_key = $ipub_key;
    }

    /**
     * estableix el valor de l'atribut se_mail de Lugar
     *
     * @param string se_mail='' optional
     */
    function setE_mail($se_mail = '')
    {
        $this->se_mail = $se_mail;
    }

    /**
     * estableix el valor de l'atribut banulado de Lugar
     *
     * @param boolean banulado='f' optional
     */
    function setAnulado($banulado = 'f')
    {
        $this->banulado = $banulado;
    }

    /**
     * Estableix las claus primàries de Lugar en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_lugar') && $val_id !== '') {
                    $this->iid_lugar = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_lugar = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_lugar' => $this->iid_lugar);
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
        if (array_key_exists('id_lugar', $aDades)) {
            $this->setId_lugar($aDades['id_lugar']);
        }
        if (array_key_exists('sigla', $aDades)) {
            $this->setSigla($aDades['sigla']);
        }
        if (array_key_exists('dl', $aDades)) {
            $this->setDl($aDades['dl']);
        }
        if (array_key_exists('region', $aDades)) {
            $this->setRegion($aDades['region']);
        }
        if (array_key_exists('nombre', $aDades)) {
            $this->setNombre($aDades['nombre']);
        }
        if (array_key_exists('tipo_ctr', $aDades)) {
            $this->setTipo_ctr($aDades['tipo_ctr']);
        }
        if (array_key_exists('modo_envio', $aDades)) {
            $this->setModo_envio($aDades['modo_envio']);
        }
        if (array_key_exists('plataforma', $aDades)) {
            $this->setPlataforma($aDades['plataforma']);
        }
        if (array_key_exists('pub_key', $aDades)) {
            $this->setPub_key($aDades['pub_key']);
        }
        if (array_key_exists('e_mail', $aDades)) {
            $this->setE_mail($aDades['e_mail']);
        }
        if (array_key_exists('anulado', $aDades)) {
            $this->setAnulado($aDades['anulado']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_lugar='$this->iid_lugar'")) === FALSE) {
            $sClauError = 'Lugar.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    public function getArrayModoEnvio()
    {
        $a_tipos = [
            self::MODO_AS4 => _("as4"),
            self::MODO_PDF => _("pdf"),
            self::MODO_XML => _("xml"),
        ];

        return $a_tipos;
    }

    /**
     * Recupera l'atribut iid_lugar de Lugar
     *
     * @return integer iid_lugar
     */
    function getId_lugar()
    {
        if (!isset($this->iid_lugar) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_lugar;
    }

    /**
     * Recupera l'atribut ssigla de Lugar
     *
     * @return string ssigla
     */
    function getSigla()
    {
        if (!isset($this->ssigla) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ssigla;
    }

    /**
     * Recupera l'atribut sdl de Lugar
     *
     * @return string sdl
     */
    function getDl()
    {
        if (!isset($this->sdl) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdl;
    }

    /**
     * Recupera l'atribut sregion de Lugar
     *
     * @return string sregion
     */
    function getRegion()
    {
        if (!isset($this->sregion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sregion;
    }

    /**
     * Recupera l'atribut snombre de Lugar
     *
     * @return string snombre
     */
    function getNombre()
    {
        if (!isset($this->snombre) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snombre;
    }

    /**
     * Recupera l'atribut stipo_ctr de Lugar
     *
     * @return string stipo_ctr
     */
    function getTipo_ctr()
    {
        if (!isset($this->stipo_ctr) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->stipo_ctr;
    }

    /**
     * Recupera l'atribut imodo_envio de Lugar
     *
     * @return integer imodo_envio
     */
    function getModo_envio()
    {
        if (!isset($this->imodo_envio) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->imodo_envio;
    }

    /**
     * Recupera l'atribut splataforma de Lugar
     *
     * @return string splataforma
     */
    function getPlataforma()
    {
        if (!isset($this->splataforma) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->splataforma;
    }

    /**
     * Recupera l'atribut ipub_key de Lugar
     *
     * @return integer ipub_key
     */
    function getPub_key()
    {
        if (!isset($this->ipub_key) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ipub_key;
    }

    /**
     * Recupera l'atribut se_mail de Lugar
     *
     * @return string se_mail
     */
    function getE_mail()
    {
        if (!isset($this->se_mail) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->se_mail;
    }

    /**
     * Recupera l'atribut banulado de Lugar
     *
     * @return boolean banulado
     */
    function getAnulado()
    {
        if (!isset($this->banulado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->banulado;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oLugarSet = new core\Set();

        $oLugarSet->add($this->getDatosSigla());
        $oLugarSet->add($this->getDatosDl());
        $oLugarSet->add($this->getDatosRegion());
        $oLugarSet->add($this->getDatosNombre());
        $oLugarSet->add($this->getDatosTipo_ctr());
        $oLugarSet->add($this->getDatosModo_envio());
        $oLugarSet->add($this->getDatosPub_key());
        $oLugarSet->add($this->getDatosE_mail());
        $oLugarSet->add($this->getDatosAnulado());
        return $oLugarSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut ssigla de Lugar
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
     * Recupera les propietats de l'atribut sdl de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDl()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'dl'));
        $oDatosCampo->setEtiqueta(_("dl"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sregion de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosRegion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'region'));
        $oDatosCampo->setEtiqueta(_("region"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut snombre de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosNombre()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'nombre'));
        $oDatosCampo->setEtiqueta(_("nombre"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut stipo_ctr de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo_ctr()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo_ctr'));
        $oDatosCampo->setEtiqueta(_("tipo_ctr"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut imodo_envio de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosModo_envio()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'modo_envio'));
        $oDatosCampo->setEtiqueta(_("modo_envio"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ipub_key de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPub_key()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'pub_key'));
        $oDatosCampo->setEtiqueta(_("pub_key"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut se_mail de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosE_mail()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'e_mail'));
        $oDatosCampo->setEtiqueta(_("e_mail"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut banulado de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosAnulado()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'anulado'));
        $oDatosCampo->setEtiqueta(_("anulado"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Lugar en un array
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

    /**
     * Recupera les propietats de l'atribut splataforma de Lugar
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosPlataforma()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'plataforma'));
        $oDatosCampo->setEtiqueta(_("plataforma"));
        return $oDatosCampo;
    }
}
