<?php

namespace usuarios\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula aux_cargos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 12/11/2020
 */

/**
 * Classe que implementa l'entitat aux_cargos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 12/11/2020
 */
class Cargo extends core\ClasePropiedades
{

    public const AMBITO_CG = 1;
    public const AMBITO_CR = 2;
    public const AMBITO_DL = 3;
    public const AMBITO_CTR = 4;

    public const CARGO_PONENTE = 1;
    public const CARGO_OFICIALES = 2;
    public const CARGO_VARIAS = 3;
    public const CARGO_TODOS_DIR = 4;
    public const CARGO_VB_VCD = 5;
    public const CARGO_DISTRIBUIR = 6;
    public const CARGO_REUNION = 7;

    public const OFICINA_ESQUEMA = -10;

    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * oDbl de Cargo
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Cargo
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Cargo
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Cargo
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Cargo
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Cargo
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_cargo de Cargo
     *
     * @var integer
     */
    private $iid_cargo;
    /**
     * Id_ambito de Cargo
     *
     * @var integer
     */
    private $iid_ambito;
    /**
     * Cargo de Cargo
     *
     * @var string
     */
    private $scargo;
    /**
     * Descripcion de Cargo
     *
     * @var string
     */
    private $sdescripcion;
    /**
     * Id_oficina de Cargo
     *
     * @var integer
     */
    private $iid_oficina;
    /**
     * Director de Cargo
     *
     * @var boolean
     */
    private $bdirector;
    /**
     * Sacd de Cargo
     *
     * @var boolean
     */
    private $bsacd;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Id_usuario de Cargo
     *
     * @var integer
     */
    private $iid_usuario;
    /**
     * Id_suplente de Cargo
     *
     * @var integer
     */
    private $iid_suplente;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_cargo
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_cargo') && $val_id !== '') {
                    $this->iid_cargo = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_cargo = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_cargo' => $this->iid_cargo);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_cargos');
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
        $aDades['id_ambito'] = $this->iid_ambito;
        $aDades['cargo'] = $this->scargo;
        $aDades['descripcion'] = $this->sdescripcion;
        $aDades['id_oficina'] = $this->iid_oficina;
        $aDades['director'] = $this->bdirector;
        $aDades['sacd'] = $this->bsacd;
        $aDades['id_usuario'] = $this->iid_usuario;
        $aDades['id_suplente'] = $this->iid_suplente;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['director'])) {
            $aDades['director'] = 'true';
        } else {
            $aDades['director'] = 'false';
        }
        if (core\is_true($aDades['sacd'])) {
            $aDades['sacd'] = 'true';
        } else {
            $aDades['sacd'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_ambito                = :id_ambito,
					cargo                    = :cargo,
					descripcion              = :descripcion,
					id_oficina               = :id_oficina,
					director                 = :director,
					sacd                 	 = :sacd,
					id_usuario               = :id_usuario,
					id_suplente              = :id_suplente";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
                $sClauError = 'Cargo.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Cargo.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_ambito,cargo,descripcion,id_oficina,director,sacd,id_usuario,id_suplente)";
            $valores = "(:id_ambito,:cargo,:descripcion,:id_oficina,:director,:sacd,:id_usuario,:id_suplente)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Cargo.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Cargo.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_cargo = $oDbl->lastInsertId('aux_cargos_id_cargo_seq');
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
        if (isset($this->iid_cargo)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
                $sClauError = 'Cargo.carregar';
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
    function setAllAtributes($aDades)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_cargo', $aDades)) {
            $this->setId_cargo($aDades['id_cargo']);
        }
        if (array_key_exists('id_ambito', $aDades)) {
            $this->setId_ambito($aDades['id_ambito']);
        }
        if (array_key_exists('cargo', $aDades)) {
            $this->setCargo($aDades['cargo']);
        }
        if (array_key_exists('descripcion', $aDades)) {
            $this->setDescripcion($aDades['descripcion']);
        }
        if (array_key_exists('id_oficina', $aDades)) {
            $this->setId_oficina($aDades['id_oficina']);
        }
        if (array_key_exists('director', $aDades)) {
            $this->setDirector($aDades['director']);
        }
        if (array_key_exists('sacd', $aDades)) {
            $this->setSacd($aDades['sacd']);
        }
        if (array_key_exists('id_usuario', $aDades)) {
            $this->setId_usuario($aDades['id_usuario']);
        }
        if (array_key_exists('id_suplente', $aDades)) {
            $this->setId_suplente($aDades['id_suplente']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_cargo de Cargo
     *
     * @param integer iid_cargo
     */
    function setId_cargo($iid_cargo)
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     * estableix el valor de l'atribut iid_ambito de Cargo
     *
     * @param integer iid_ambito='' optional
     */
    function setId_ambito($iid_ambito = '')
    {
        $this->iid_ambito = $iid_ambito;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut scargo de Cargo
     *
     * @param string scargo='' optional
     */
    function setCargo($scargo = '')
    {
        $this->scargo = $scargo;
    }

    /**
     * estableix el valor de l'atribut sdescripcion de Cargo
     *
     * @param string sdescripcion='' optional
     */
    function setDescripcion($sdescripcion = '')
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     * estableix el valor de l'atribut iid_oficina de Cargo
     *
     * @param integer iid_oficina='' optional
     */
    function setId_oficina($iid_oficina = '')
    {
        $this->iid_oficina = $iid_oficina;
    }

    /**
     * estableix el valor de l'atribut bdirector de Cargo
     *
     * @param boolean bdirector='f' optional
     */
    function setDirector($bdirector = 'f')
    {
        $this->bdirector = $bdirector;
    }

    /**
     * estableix el valor de l'atribut bsacd de Cargo
     *
     * @param boolean bsacd='f' optional
     */
    function setSacd($bsacd = 'f')
    {
        $this->bsacd = $bsacd;
    }

    /**
     * estableix el valor de l'atribut iid_usuario de Cargo
     *
     * @param integer iid_usuario='' optional
     */
    function setId_usuario($iid_usuario = '')
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     * estableix el valor de l'atribut iid_suplente de Cargo
     *
     * @param integer iid_suplente='' optional
     */
    function setId_suplente($iid_suplente = '')
    {
        $this->iid_suplente = $iid_suplente;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_cargo('');
        $this->setId_ambito('');
        $this->setCargo('');
        $this->setDescripcion('');
        $this->setId_oficina('');
        $this->setDirector('');
        $this->setSacd('');
        $this->setId_usuario('');
        $this->setId_suplente('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Cargo en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_cargo' => $this->iid_cargo);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Cargo en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_cargo') && $val_id !== '') {
                    $this->iid_cargo = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_cargo = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_cargo' => $this->iid_cargo);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
            $sClauError = 'Cargo.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_cargo de Cargo
     *
     * @return integer iid_cargo
     */
    function getId_cargo()
    {
        if (!isset($this->iid_cargo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_cargo;
    }

    /**
     * Recupera l'atribut iid_ambito de Cargo
     *
     * @return integer iid_ambito
     */
    function getId_ambito()
    {
        if (!isset($this->iid_ambito) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_ambito;
    }

    /**
     * Recupera l'atribut scargo de Cargo
     *
     * @return string scargo
     */
    function getCargo()
    {
        if (!isset($this->scargo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->scargo;
    }

    /**
     * Recupera l'atribut sdescripcion de Cargo
     *
     * @return string sdescripcion
     */
    function getDescripcion()
    {
        if (!isset($this->sdescripcion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdescripcion;
    }

    /**
     * Recupera l'atribut iid_oficina de Cargo
     *
     * @return integer iid_oficina
     */
    function getId_oficina()
    {
        if (!isset($this->iid_oficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_oficina;
    }

    /**
     * Recupera l'atribut bdirector de Cargo
     *
     * @return boolean bdirector
     */
    function getDirector()
    {
        if (!isset($this->bdirector) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bdirector;
    }

    /**
     * Recupera l'atribut bsacd de Cargo
     *
     * @return boolean bsacd
     */
    function getSacd()
    {
        if (!isset($this->bsacd) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bsacd;
    }

    /**
     * Recupera l'atribut iid_usuario de Cargo
     *
     * @return integer iid_usuario
     */
    function getId_usuario()
    {
        if (!isset($this->iid_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_usuario;
    }

    /**
     * Recupera l'atribut iid_suplente de Cargo
     *
     * @return integer iid_suplente
     */
    function getId_suplente()
    {
        if (!isset($this->iid_suplente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_suplente;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oCargoSet = new core\Set();

        $oCargoSet->add($this->getDatosId_ambito());
        $oCargoSet->add($this->getDatosCargo());
        $oCargoSet->add($this->getDatosDescripcion());
        $oCargoSet->add($this->getDatosId_oficina());
        $oCargoSet->add($this->getDatosDirector());
        $oCargoSet->add($this->getDatosId_usuario());
        $oCargoSet->add($this->getDatosId_suplente());
        return $oCargoSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_ambito de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_ambito()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_ambito'));
        $oDatosCampo->setEtiqueta(_("id_ambito"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut scargo de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCargo()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'cargo'));
        $oDatosCampo->setEtiqueta(_("cargo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescripcion de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDescripcion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'descripcion'));
        $oDatosCampo->setEtiqueta(_("descripcion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_oficina de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_oficina()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_oficina'));
        $oDatosCampo->setEtiqueta(_("id_oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bdirector de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDirector()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'director'));
        $oDatosCampo->setEtiqueta(_("director"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_usuario de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_usuario()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_usuario'));
        $oDatosCampo->setEtiqueta(_("id_usuario"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_suplente de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_suplente()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_suplente'));
        $oDatosCampo->setEtiqueta(_("id_suplente"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Cargo en un array
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
     * Recupera les propietats de l'atribut bsacd de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosSacd()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'sacd'));
        $oDatosCampo->setEtiqueta(_("sacd"));
        return $oDatosCampo;
    }
}
