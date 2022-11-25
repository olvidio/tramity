<?php

namespace etiquetas\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula etiquetas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 10/11/2020
 */

/**
 * Classe que implementa l'entitat etiquetas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 10/11/2020
 */
class Etiqueta extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de Etiqueta
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Etiqueta
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Etiqueta
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de Etiqueta
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de Etiqueta
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Etiqueta
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_etiqueta de Etiqueta
     *
     * @var integer
     */
    private $iid_etiqueta;
    /**
     * Nom_etiqueta de Etiqueta
     *
     * @var string
     */
    private $snom_etiqueta;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Id_cargo de Etiqueta
     *
     * @var integer
     */
    private $iid_cargo;
    /**
     * Oficina de Etiqueta
     *
     * @var boolean
     */
    private $boficina;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|null iid_etiqueta
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($iid_etiqueta = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if ($iid_etiqueta !== NULL || $iid_etiqueta === 0) {
            $this->iid_etiqueta = $iid_etiqueta;
            $this->aPrimary_key = array('iid_etiqueta' => $this->iid_etiqueta);
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas');
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
        $aDades['nom_etiqueta'] = $this->snom_etiqueta;
        $aDades['id_cargo'] = $this->iid_cargo;
        $aDades['oficina'] = $this->boficina;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (core\is_true($aDades['oficina'])) {
            $aDades['oficina'] = 'true';
        } else {
            $aDades['oficina'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nom_etiqueta             = :nom_etiqueta,
					id_cargo                 = :id_cargo,
					oficina                  = :oficina";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_etiqueta='$this->iid_etiqueta'")) === FALSE) {
                $sClauError = 'Etiqueta.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Etiqueta.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(nom_etiqueta,id_cargo,oficina)";
            $valores = "(:nom_etiqueta,:id_cargo,:oficina)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Etiqueta.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Etiqueta.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->iid_etiqueta = $oDbl->lastInsertId('etiquetas_id_etiqueta_seq');
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
        if (isset($this->iid_etiqueta)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta='$this->iid_etiqueta'")) === FALSE) {
                $sClauError = 'Etiqueta.carregar';
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
        if (array_key_exists('id_etiqueta', $aDades)) {
            $this->setId_etiqueta($aDades['id_etiqueta']);
        }
        if (array_key_exists('nom_etiqueta', $aDades)) {
            $this->setNom_etiqueta($aDades['nom_etiqueta']);
        }
        if (array_key_exists('id_cargo', $aDades)) {
            $this->setId_cargo($aDades['id_cargo']);
        }
        if (array_key_exists('oficina', $aDades)) {
            $this->setOficina($aDades['oficina']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * @param integer iid_etiqueta
     */
    function setId_etiqueta($iid_etiqueta)
    {
        $this->iid_etiqueta = $iid_etiqueta;
    }

    /**
     * @param string snom_etiqueta='' optional
     */
    function setNom_etiqueta($snom_etiqueta = '')
    {
        $this->snom_etiqueta = $snom_etiqueta;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param integer iid_cargo='' optional
     */
    function setId_cargo($iid_cargo = '')
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     * @param boolean boficina='f' optional
     */
    function setOficina($boficina = 'f')
    {
        $this->boficina = $boficina;
    }

    
    /**
     * Recupera las claus primàries de Etiqueta en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_etiqueta' => $this->iid_etiqueta);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Etiqueta en un array
     *
     */
    public function setPrimary_key($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_etiqueta') && $val_id !== '') {
                    $this->iid_etiqueta = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_etiqueta = (int)$a_id;
                $this->aPrimary_key = array('iid_etiqueta' => $this->iid_etiqueta);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_etiqueta='$this->iid_etiqueta'")) === FALSE) {
            $sClauError = 'Etiqueta.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_etiqueta de Etiqueta
     *
     * @return integer iid_etiqueta
     */
    function getId_etiqueta()
    {
        if (!isset($this->iid_etiqueta) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_etiqueta;
    }

    /**
     * Recupera l'atribut snom_etiqueta de Etiqueta
     *
     * @return string snom_etiqueta
     */
    function getNom_etiqueta()
    {
        if (!isset($this->snom_etiqueta) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snom_etiqueta;
    }

    /**
     * Recupera l'atribut iid_cargo de Etiqueta
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
     * Recupera l'atribut boficina de Etiqueta
     *
     * @return boolean boficina
     */
    function getOficina()
    {
        if (!isset($this->boficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->boficina;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oEtiquetaSet = new core\Set();

        $oEtiquetaSet->add($this->getDatosNom_etiqueta());
        $oEtiquetaSet->add($this->getDatosId_cargo());
        $oEtiquetaSet->add($this->getDatosOficina());
        return $oEtiquetaSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut snom_etiqueta de Etiqueta
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosNom_etiqueta()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'nom_etiqueta'));
        $oDatosCampo->setEtiqueta(_("nom_etiqueta"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_cargo de Etiqueta
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_cargo()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_cargo'));
        $oDatosCampo->setEtiqueta(_("id_cargo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut boficina de Etiqueta
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOficina()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'oficina'));
        $oDatosCampo->setEtiqueta(_("oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Etiqueta en un array
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
