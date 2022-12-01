<?php

namespace usuarios\model\entity;

use core\ClasePropiedades;
use core\DatosCampo;
use core\Set;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */

/**
 * Classe que implementa l'entitat aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
class zUsuario extends ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Usuario
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Usuario
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Usuario
     *
     * @var array
     */
    private array $aPrimary_key;
    /**
     * aDades de Usuario
     *
     * @var array
     */
    private array $aDades;
    /**
     * bLoaded
     *
     * @var boolean
     */
    private bool $bLoaded = FALSE;
    /**
     * Id_usuario de Usuario
     *
     * @var integer
     */
    private int $iid_usuario;
    /**
     * Usuario de Usuario
     *
     * @var string
     */
    private string $susuario;
    /**
     * Id_cargo_preferido de Usuario
     *
     * @var integer
     */
    private int $iid_cargo_preferido;
    /**
     * Password de Usuario
     *
     * @var string|null
     */
    private ?string $spassword = null;
    /**
     * Email de Usuario
     *
     * @var string|null
     */
    private ?string $semail = null;
    /**
     * Nom_usuario de Usuario
     *
     * @var string|null
     */
    private ?string $snom_usuario = null;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|null $iid_usuario
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    public function __construct(int $iid_usuario = null)
    {
         $oDbl = $GLOBALS['oDBT'];
        if ($iid_usuario !== null) {
            $this->iid_usuario = $iid_usuario;
            $this->aPrimary_key = array('iid_usuario' => $this->iid_usuario);
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_usuarios');
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
        $aDades['usuario'] = $this->susuario;
        $aDades['id_cargo_preferido'] = $this->iid_cargo_preferido;
        $aDades['password'] = $this->spassword;
        $aDades['email'] = $this->semail;
        $aDades['nom_usuario'] = $this->snom_usuario;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					usuario                  = :usuario,
					id_cargo_preferido       = :id_cargo_preferido,
					password                 = :password,
					email                    = :email,
					nom_usuario              = :nom_usuario";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
                $sClauError = 'Usuario.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Usuario.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(usuario,id_cargo_preferido,password,email,nom_usuario)";
            $valores = "(:usuario,:id_cargo_preferido,:password,:email,:nom_usuario)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Usuario.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Usuario.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $this->iid_usuario = $oDbl->lastInsertId('aux_usuarios_id_usuario_seq');
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
        if (isset($this->iid_usuario)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
                $sClauError = 'Usuario.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            // para los bytea:
            $sPasswd = '';
            $oDblSt->bindColumn('password', $sPasswd, PDO::PARAM_STR);

            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            $aDades['password'] = $sPasswd;
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->aDades = $aDades;
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

    
    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Recupera las claus primàries de Usuario en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key(): array
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_usuario' => $this->iid_usuario);
        }
        return $this->aPrimary_key;
    }

    /**
     * @param integer $iid_usuario
     */
    public function setId_usuario(int $iid_usuario): void
    {
        $this->iid_usuario = $iid_usuario;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string $susuario
     */
    public function setUsuario(string $susuario): void
    {
        $this->susuario = $susuario;
    }

    /**
     * @param integer $iid_cargo_preferido
     */
    public function setId_cargo_preferido(int $iid_cargo_preferido): void
    {
        $this->iid_cargo_preferido = $iid_cargo_preferido;
    }

    /**
     * @param string|null $spassword
     */
    public function setPassword(string $spassword = null): void
    {
        $this->spassword = $spassword;
    }

    /**
     * @param string|null $semail
     */
    public function setEmail(string $semail = null): void
    {
        $this->semail = $semail;
    }

    /**
     * @param string|null $snom_usuario
     */
    public function setNom_usuario(string $snom_usuario = null): void
    {
        $this->snom_usuario = $snom_usuario;
    }

    /**
     * Estableix las claus primàries de Usuario en un array
     *
     */
    public function setPrimary_key(int $iid_usuario): void
    {
        $this->iid_usuario = $iid_usuario;
        $this->aPrimary_key = array('iid_usuario' => $this->iid_usuario);
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     */
    private function setAllAtributes(array $aDades): void
    {
        if (array_key_exists('id_usuario', $aDades)) {
            $this->setId_usuario($aDades['id_usuario']);
        }
        if (array_key_exists('usuario', $aDades)) {
            $this->setUsuario($aDades['usuario']);
        }
        if (array_key_exists('id_cargo_preferido', $aDades)) {
            $this->setId_cargo_preferido($aDades['id_cargo_preferido']);
        }
        if (array_key_exists('password', $aDades)) {
            $this->setPassword($aDades['password']);
        }
        if (array_key_exists('email', $aDades)) {
            $this->setEmail($aDades['email']);
        }
        if (array_key_exists('nom_usuario', $aDades)) {
            $this->setNom_usuario($aDades['nom_usuario']);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_usuario='$this->iid_usuario'")) === FALSE) {
            $sClauError = 'Usuario.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_usuario de Usuario
     *
     * @return integer $iid_usuario
     */
    public function getId_usuario(): int
    {
        if (!isset($this->iid_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_usuario;
    }

    /**
     * Recupera l'atribut susuario de Usuario
     *
     * @return string $susuario
     */
    public function getUsuario(): string
    {
        if (!isset($this->susuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->susuario;
    }

    /**
     * Recupera l'atribut iid_cargo_preferido de Usuario
     *
     * @return integer $iid_cargo_preferido
     */
    public function getId_cargo_preferido(): int
    {
        if (!isset($this->iid_cargo_preferido) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_cargo_preferido;
    }

    /**
     * Recupera l'atribut spassword de Usuario
     *
     * @return string|null $spassword
     */
    public function getPassword(): ?string
    {
        if (!isset($this->spassword) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->spassword;
    }

    /**
     * Recupera l'atribut semail de Usuario
     *
     * @return string|null $semail
     */
    public function getEmail(): ?string
    {
        if (!isset($this->semail) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->semail;
    }

    /**
     * Recupera l'atribut snom_usuario de Usuario
     *
     * @return string|null $snom_usuario
     */
    public function getNom_usuario(): ?string
    {
        if (!isset($this->snom_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snom_usuario;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    public function getDatosCampos(): array
    {
        $oUsuarioSet = new Set();

        $oUsuarioSet->add($this->getDatosUsuario());
        $oUsuarioSet->add($this->getDatosId_cargo_preferido());
        $oUsuarioSet->add($this->getDatosPassword());
        $oUsuarioSet->add($this->getDatosEmail());
        $oUsuarioSet->add($this->getDatosNom_usuario());
        return $oUsuarioSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut susuario de Usuario
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosUsuario(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'usuario'));
        $oDatosCampo->setEtiqueta(_("usuario"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_cargo_preferido de Usuario
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosId_cargo_preferido(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_cargo_preferido'));
        $oDatosCampo->setEtiqueta(_("id_cargo_preferido"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut spassword de Usuario
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosPassword(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'password'));
        $oDatosCampo->setEtiqueta(_("password"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut semail de Usuario
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosEmail(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'email'));
        $oDatosCampo->setEtiqueta(_("email"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut snom_usuario de Usuario
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosNom_usuario(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'nom_usuario'));
        $oDatosCampo->setEtiqueta(_("nom_usuario"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Usuario en un array
     *
     * @return array $aDades
     */
    public function getTot(): array
    {
        return $this->aDades;
    }
}
