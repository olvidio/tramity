<?php

namespace usuarios\model\entity;

use core\ClasePropiedades;
use core\DatosCampo;
use core\Set;
use PDO;
use PDOException;
use function core\is_true;

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
class Cargo extends ClasePropiedades
{

    public const AMBITO_CG = 1;
    public const AMBITO_CR = 2;
    public const AMBITO_DL = 3;
    public const AMBITO_CTR = 4;
    public const AMBITO_CTR_CORREO = 5;

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
    private array $aPrimary_key;
    /**
     * bLoaded de Cargo
     *
     * @var boolean
     */
    private bool $bLoaded = FALSE;
    /**
     * Id_cargo de Cargo
     *
     * @var integer
     */
    private int $iid_cargo;
    /**
     * Id_ambito de Cargo
     *
     * @var integer
     */
    private int $iid_ambito;
    /**
     * Cargo de Cargo
     *
     * @var string
     */
    private string $scargo;
    /**
     * Descripción de Cargo
     *
     * @var string|null
     */
    private ?string $sdescripcion;
    /**
     * Id_oficina de Cargo
     *
     * @var integer|null
     */
    private ?int $iid_oficina = null;
    /**
     * Director de Cargo
     *
     * @var boolean
     */
    private bool $bdirector = FALSE;
    /**
     * Sacd de Cargo
     *
     * @var boolean
     */
    private bool $bsacd = FALSE;
    /**
     * Id_usuario de Cargo
     *
     * @var integer|null
     */
    private ?int $iid_usuario;
    /**
     * Id_suplente de Cargo
     *
     * @var integer|null
     */
    private ?int $iid_suplente;
    /**
     * activo de Cargo
     *
     * @var boolean
     */
    private bool $bactivo = TRUE;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array $a_id iid_cargo
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    public function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_cargo') && $val_id !== '') {
                    $this->iid_cargo = (int)$val_id;
                }
            }
        } else if (isset($a_id) && $a_id !== '') {
            $this->iid_cargo = (int)$a_id;
            $this->aPrimary_key = array('iid_cargo' => $this->iid_cargo);
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
    public function DBGuardar(): bool
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
        $aDades['activo'] = $this->bactivo;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDades['director'])) {
            $aDades['director'] = 'true';
        } else {
            $aDades['director'] = 'false';
        }
        if (is_true($aDades['sacd'])) {
            $aDades['sacd'] = 'true';
        } else {
            $aDades['sacd'] = 'false';
        }
        if (is_true($aDades['activo'])) {
            $aDades['activo'] = 'true';
        } else {
            $aDades['activo'] = 'false';
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
					id_suplente              = :id_suplente,
                    activo                   = :activo";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
                $sClauError = 'Cargo.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Cargo.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(id_ambito,cargo,descripcion,id_oficina,director,sacd,id_usuario,id_suplente,activo)";
            $valores = "(:id_ambito,:cargo,:descripcion,:id_oficina,:director,:sacd,:id_usuario,:id_suplente,:activo)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Cargo.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Cargo.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $this->iid_cargo = $oDbl->lastInsertId('aux_cargos_id_cargo_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     */
    public function DBCargar($que = null): bool
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
                    if (!$oDblSt->rowCount()) {
                        return FALSE;
                    }
                    break;
                default:
                    // En el caso de no existir esta fila, $aDades = FALSE:
                    if ($aDades === FALSE) {
                        return FALSE;
                    }

                    $this->setAllAtributes($aDades);
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     */
    private function setAllAtributes(array $aDades): void
    {
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
            $this->setDirector(is_true($aDades['director']));
        }
        if (array_key_exists('sacd', $aDades)) {
            $this->setSacd(is_true($aDades['sacd']));
        }
        if (array_key_exists('id_usuario', $aDades)) {
            $this->setId_usuario($aDades['id_usuario']);
        }
        if (array_key_exists('id_suplente', $aDades)) {
            $this->setId_suplente($aDades['id_suplente']);
        }
        if (array_key_exists('activo', $aDades)) {
            $this->setActivo(is_true($aDades['activo']));
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param integer $iid_cargo
     */
    public function setId_cargo(int $iid_cargo): void
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     * @param integer $iid_ambito
     */
    public function setId_ambito(int $iid_ambito): void
    {
        $this->iid_ambito = $iid_ambito;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string $scargo
     */
    public function setCargo(string $scargo): void
    {
        $this->scargo = $scargo;
    }

    /**
     * @param string|null $sdescripcion
     */
    public function setDescripcion(?string $sdescripcion = ''): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     * @param integer|null $iid_oficina
     */
    public function setId_oficina(int $iid_oficina=null): void
    {
        $this->iid_oficina = $iid_oficina;
    }

    /**
     * @param boolean $bdirector
     */
    public function setDirector(bool $bdirector = FALSE): void
    {
        $this->bdirector = $bdirector;
    }

    /**
     * @param boolean $bsacd ='f'
     */
    public function setSacd(bool $bsacd = FALSE): void
    {
        $this->bsacd = $bsacd;
    }

    /**
     * @param integer|null $iid_usuario optional
     */
    public function setId_usuario(?int $iid_usuario = null): void
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     * @param integer|null $iid_suplente optional
     */
    public function setId_suplente(?int $iid_suplente = null): void
    {
        $this->iid_suplente = $iid_suplente;
    }

    /**
     * @param boolean $bactivo ='t'
     */
    public function setActivo(bool $bactivo = TRUE): void
    {
        $this->bactivo = $bactivo;
    }

    /**
     * Recupera las claus primàries de Cargo en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key(): array
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
    public function setPrimary_key($a_id = null): void
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_cargo') && $val_id !== '') {
                    $this->iid_cargo = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_cargo = (int)$a_id;
                $this->aPrimary_key = array('iid_cargo' => $this->iid_cargo);
            }
        }
    }

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar(): bool
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
     * @return integer
     */
    public function getId_cargo(): int
    {
        if (!isset($this->iid_cargo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_cargo;
    }

    /**
     * Recupera l'atribut iid_ambito de Cargo
     *
     * @return integer
     */
    public function getId_ambito(): int
    {
        if (!isset($this->iid_ambito) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_ambito;
    }

    /**
     * Recupera l'atribut scargo de Cargo
     *
     * @return string
     */
    public function getCargo(): string
    {
        if (!isset($this->scargo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->scargo;
    }

    /**
     * Recupera l'atribut sdescripcion de Cargo
     *
     * @return string|null
     */
    public function getDescripcion(): ?string
    {
        if (!isset($this->sdescripcion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdescripcion;
    }

    /**
     * Recupera l'atribut iid_oficina de Cargo
     *
     * @return integer|null
     */
    public function getId_oficina(): ?int
    {
        if (!isset($this->iid_oficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_oficina;
    }

    /**
     * Recupera l'atribut bdirector de Cargo
     *
     * @return boolean
     */
    public function getDirector(): bool
    {
        if (!$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bdirector;
    }

    /**
     * Recupera l'atribut bsacd de Cargo
     *
     * @return boolean
     */
    public function getSacd(): bool
    {
        if (!isset($this->bsacd) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bsacd;
    }

    /**
     * Recupera l'atribut iid_usuario de Cargo
     *
     * @return integer|null
     */
    public function getId_usuario(): ?int
    {
        if (!isset($this->iid_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_usuario;
    }

    /**
     * Recupera l'atribut iid_suplente de Cargo
     *
     * @return integer|null
     */
    public function getId_suplente(): ?int
    {
        if (!isset($this->iid_suplente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_suplente;
    }

    /**
     * Recupera l'atribut bactivo de Cargo
     *
     * @return boolean
     */
    public function getActivo(): bool
    {
        if (!isset($this->bactivo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bactivo;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    public function getDatosCampos(): array
    {
        $oCargoSet = new Set();

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
     * @return DatosCampo
     */
    function getDatosId_ambito(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_ambito'));
        $oDatosCampo->setEtiqueta(_("id_ambito"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut scargo de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosCargo(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'cargo'));
        $oDatosCampo->setEtiqueta(_("cargo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescripcion de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosDescripcion(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'descripcion'));
        $oDatosCampo->setEtiqueta(_("descripcion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_oficina de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosId_oficina(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_oficina'));
        $oDatosCampo->setEtiqueta(_("id_oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bdirector de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosDirector(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'director'));
        $oDatosCampo->setEtiqueta(_("director"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_usuario de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosId_usuario(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_usuario'));
        $oDatosCampo->setEtiqueta(_("id_usuario"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_suplente de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosId_suplente(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_suplente'));
        $oDatosCampo->setEtiqueta(_("id_suplente"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bsacd de Cargo
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosSacd(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'sacd'));
        $oDatosCampo->setEtiqueta(_("sacd"));
        return $oDatosCampo;
    }
}
