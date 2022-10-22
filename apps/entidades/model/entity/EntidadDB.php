<?php

namespace entidades\model\entity;

use core;
use PDO;
use PDOException;

class EntidadDB extends core\ClasePropiedades
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de EntidadDB
     *
     * @var array
     */
    protected $aPrimary_key;

    /**
     * aDades de EntidadDB
     *
     * @var array
     */
    protected $aDades;

    /**
     * bLoaded de EntidadDB
     *
     * @var boolean
     */
    protected $bLoaded = FALSE;

    /**
     * Id_schema de EntidadDB
     *
     * @var integer
     */
    protected $iid_schema;

    /**
     * Id_entidad de EntidadDB
     *
     * @var integer
     */
    protected $iid_entidad;
    /**
     * Nombre de EntidadDB
     *
     * @var string
     */
    protected $snombre;
    /**
     * Schema de EntidadDB
     *
     * @var string
     */
    protected $sschema;
    /**
     * Tipo de EntidadDB
     *
     * @var integer
     */
    protected $itipo;
    /**
     * Anulado de EntidadDB
     *
     * @var boolean
     */
    protected $banulado;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de EntidadDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EntidadDB
     *
     * @var string
     */
    protected $sNomTabla;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_entidad
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    public function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBP'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_entidad') && $val_id !== '') {
                    $this->iid_entidad = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entidad = (int) $a_id;
                $this->aPrimary_key = array('iid_entidad' => $this->iid_entidad);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('entidades');
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
        $aDades['nombre'] = $this->snombre;
        $aDades['schema'] = $this->sschema;
        $aDades['tipo'] = $this->itipo;
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
					nombre                   = :nombre,
					schema                   = :schema,
					tipo                     = :tipo,
					anulado                  = :anulado";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entidad='$this->iid_entidad'")) === FALSE) {
                $sClauError = 'EntidadDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EntidadDB.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(nombre,schema,tipo,anulado)";
            $valores = "(:nombre,:schema,:tipo,:anulado)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EntidadDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EntidadDB.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_entidad = $oDbl->lastInsertId('entidades_id_entidad_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCargar($que = null): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_entidad)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entidad='$this->iid_entidad'")) === FALSE) {
                $sClauError = 'EntidadDB.carregar';
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
        if (array_key_exists('id_entidad', $aDades)) {
            $this->setId_entidad($aDades['id_entidad']);
        }
        if (array_key_exists('nombre', $aDades)) {
            $this->setNombre($aDades['nombre']);
        }
        if (array_key_exists('schema', $aDades)) {
            $this->setSchema($aDades['schema']);
        }
        if (array_key_exists('tipo', $aDades)) {
            $this->setTipo($aDades['tipo']);
        }
        if (array_key_exists('anulado', $aDades)) {
            $this->setAnulado($aDades['anulado']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_entidad de EntidadDB
     *
     * @param integer iid_entidad
     */
    function setId_entidad($iid_entidad)
    {
        $this->iid_entidad = $iid_entidad;
    }

    /**
     * estableix el valor de l'atribut snombre de EntidadDB
     *
     * @param string snombre='' optional
     */
    function setNombre($snombre = '')
    {
        $this->snombre = $snombre;
    }

    /**
     * estableix el valor de l'atribut sschema de EntidadDB
     *
     * @param string sschema='' optional
     */
    public function setSchema($sschema = ''): void
    {
        $this->sschema = $sschema;
    }

    /**
     * estableix el valor de l'atribut itipo de EntidadDB
     *
     * @param integer itipo='' optional
     */
    public function setTipo($itipo = ''): void
    {
        $this->itipo = $itipo;
    }

    /**
     * estableix el valor de l'atribut banulado de EntidadDB
     *
     * @param boolean banulado='f' optional
     */
    public function setAnulado($banulado = 'f'): void
    {
        $this->banulado = $banulado;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    private function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_entidad('');
        $this->setNombre('');
        $this->setSchema('');
        $this->setTipo('');
        $this->setAnulado('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de EntidadDB en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_entidad' => $this->iid_entidad);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EntidadDB en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_entidad') && $val_id !== '') {
                    $this->iid_entidad = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entidad = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_entidad' => $this->iid_entidad);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entidad='$this->iid_entidad'")) === FALSE) {
            $sClauError = 'EntidadDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_entidad de EntidadDB
     *
     * @return integer iid_entidad
     */
    public function getId_entidad()
    {
        if (!isset($this->iid_entidad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_entidad;
    }

    /**
     * Recupera l'atribut snombre de EntidadDB
     *
     * @return string snombre
     */
    public function getNombre(): string
    {
        if (!isset($this->snombre) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snombre;
    }

    /**
     * Recupera l'atribut sschema de EntidadDB
     *
     * @return string sschema
     */
    public function getSchema(): string
    {
        if (!isset($this->sschema) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sschema;
    }

    /**
     * Recupera l'atribut itipo de EntidadDB
     *
     * @return integer itipo
     */
    public function getTipo(): int
    {
        if (!isset($this->itipo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->itipo;
    }

    /**
     * Recupera l'atribut banulado de EntidadDB
     *
     * @return boolean banulado
     */
    public function isAnulado(): bool
    {
        if (!isset($this->banulado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->banulado;
    }

    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    public function getDatosCampos()
    {
        $oEntidadDBesSet = new core\Set();

        $oEntidadDBesSet->add($this->getDatosNombre());
        $oEntidadDBesSet->add($this->getDatosSchema());
        $oEntidadDBesSet->add($this->getDatosTipo());
        $oEntidadDBesSet->add($this->getDatosAnulado());
        return $oEntidadDBesSet->getTot();
    }

    /**
     * Recupera les propietats de l'atribut snombre de EntidadDB
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
     * Recupera les propietats de l'atribut sschema de EntidadDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosSchema()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'schema'));
        $oDatosCampo->setEtiqueta(_("schema"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut itipo de EntidadDB
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo'));
        $oDatosCampo->setEtiqueta(_("tipo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut banulado de EntidadDB
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
     * Recupera tots els ATRIBUTOS de EntidadDB en un array
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
