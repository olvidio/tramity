<?php

namespace lugares\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula lugares_grupos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat lugares_grupos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class Grupo extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Grupo
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Grupo
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Grupo
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Grupo
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Grupo
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Grupo
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_grupo de Grupo
     *
     * @var integer
     */
    private $iid_grupo;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Descripcion de Grupo
     *
     * @var string
     */
    private $sdescripcion;
    /**
     * Miembros de Grupo
     *
     * @var array
     */
    private $a_miembros;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_grupo
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_grupo') && $val_id !== '') {
                    $this->iid_grupo = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_grupo = (int)$a_id;
                $this->aPrimary_key = array('iid_grupo' => $this->iid_grupo);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('lugares_grupos');
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
        $aDades['descripcion'] = $this->sdescripcion;
        $aDades['miembros'] = $this->a_miembros;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					descripcion              = :descripcion,
					miembros                 = :miembros";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_grupo='$this->iid_grupo'")) === FALSE) {
                $sClauError = 'Grupo.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Grupo.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(descripcion,miembros)";
            $valores = "(:descripcion,:miembros)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Grupo.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Grupo.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_grupo = $oDbl->lastInsertId('lugares_grupos_id_grupo_seq');
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
        if (isset($this->iid_grupo)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_grupo='$this->iid_grupo'")) === FALSE) {
                $sClauError = 'Grupo.carregar';
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
        if (array_key_exists('id_grupo', $aDades)) {
            $this->setId_grupo($aDades['id_grupo']);
        }
        if (array_key_exists('descripcion', $aDades)) {
            $this->setDescripcion($aDades['descripcion']);
        }
        if (array_key_exists('miembros', $aDades)) {
            $this->setMiembros($aDades['miembros'], TRUE);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_grupo de Grupo
     *
     * @param integer iid_grupo
     */
    function setId_grupo($iid_grupo)
    {
        $this->iid_grupo = $iid_grupo;
    }

    /**
     * estableix el valor de l'atribut sdescripcion de Grupo
     *
     * @param string sdescripcion='' optional
     */
    function setDescripcion($sdescripcion = '')
    {
        $this->sdescripcion = $sdescripcion;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut a_miembros de Grupo
     *
     * @param array a_miembros
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    function setMiembros($a_miembros = '', $db = FALSE)
    {
        if ($db === FALSE) {
            $postgresArray = core\array_php2pg($a_miembros);
        } else {
            $postgresArray = $a_miembros;
        }
        $this->a_miembros = $postgresArray;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_grupo('');
        $this->setDescripcion('');
        $this->setMiembros('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Grupo en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_grupo' => $this->iid_grupo);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Grupo en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_grupo') && $val_id !== '') {
                    $this->iid_grupo = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_grupo = (int)$a_id;
                $this->aPrimary_key = array('iid_grupo' => $this->iid_grupo);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_grupo='$this->iid_grupo'")) === FALSE) {
            $sClauError = 'Grupo.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_grupo de Grupo
     *
     * @return integer iid_grupo
     */
    function getId_grupo()
    {
        if (!isset($this->iid_grupo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_grupo;
    }

    /**
     * Recupera l'atribut sdescripcion de Grupo
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
     * Recupera l'atribut a_miembros de Grupo
     *
     * @return array a_miembros
     */
    function getMiembros()
    {
        if (!isset($this->a_miembros) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return core\array_pg2php($this->a_miembros);
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oGrupoSet = new core\Set();

        $oGrupoSet->add($this->getDatosDescripcion());
        $oGrupoSet->add($this->getDatosMiembros());
        return $oGrupoSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut sdescripcion de Grupo
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
     * Recupera les propietats de l'atribut a_miembros de Grupo
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosMiembros()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'miembros'));
        $oDatosCampo->setEtiqueta(_("miembros"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Grupo en un array
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
