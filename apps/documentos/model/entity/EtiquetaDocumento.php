<?php

namespace documentos\model\entity;

use core;
use PDO;
use PDOException;

/**
 * Fitxer amb la Classe que accedeix a la taula etiquetas_documento
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/6/2021
 */

/**
 * Classe que implementa l'entitat etiquetas_documento
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/6/2021
 */
class EtiquetaDocumento extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de EtiquetaDocumento
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EtiquetaDocumento
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de EtiquetaDocumento
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de EtiquetaDocumento
     *
     * @var array
     */
    private $aDades;
    /**
     * bLoaded de EtiquetaDocumento
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de EtiquetaDocumento
     *
     * @var integer
     */
    private $iid_schema;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Id_etiqueta de EtiquetaDocumento
     *
     * @var integer
     */
    private $iid_etiqueta;
    /**
     * Id_doc de EtiquetaDocumento
     *
     * @var integer
     */
    private $iid_doc;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_etiqueta,iid_doc
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_etiqueta') && $val_id !== '') {
                    $this->iid_etiqueta = (int)$val_id;
                }
                if (($nom_id === 'id_doc') && $val_id !== '') {
                    $this->iid_doc = (int)$val_id;
                }
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('etiquetas_documento');
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
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = " ";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_etiqueta='$this->iid_etiqueta' AND id_doc='$this->iid_doc'")) === FALSE) {
                $sClauError = 'EtiquetaDocumento.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EtiquetaDocumento.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            array_unshift($aDades, $this->iid_etiqueta, $this->iid_doc);
            $campos = "(id_etiqueta,id_doc)";
            $valores = "(:id_etiqueta,:id_doc)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EtiquetaDocumento.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EtiquetaDocumento.insertar.execute';
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
        if (isset($this->iid_etiqueta) && isset($this->iid_doc)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_etiqueta='$this->iid_etiqueta' AND id_doc='$this->iid_doc'")) === FALSE) {
                $sClauError = 'EtiquetaDocumento.carregar';
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
        if (array_key_exists('id_doc', $aDades)) {
            $this->setId_doc($aDades['id_doc']);
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
     * @param integer iid_doc
     */
    function setId_doc($iid_doc)
    {
        $this->iid_doc = $iid_doc;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    
    /**
     * Recupera las claus primàries de EtiquetaDocumento en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_etiqueta' => $this->iid_etiqueta, 'id_doc' => $this->iid_doc);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EtiquetaDocumento en un array
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
                if (($nom_id === 'id_doc') && $val_id !== '') {
                    $this->iid_doc = (int)$val_id;
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_etiqueta='$this->iid_etiqueta' AND id_doc='$this->iid_doc'")) === FALSE) {
            $sClauError = 'EtiquetaDocumento.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_etiqueta de EtiquetaDocumento
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
     * Recupera l'atribut iid_doc de EtiquetaDocumento
     *
     * @return integer iid_doc
     */
    function getId_doc()
    {
        if (!isset($this->iid_doc) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_doc;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oEtiquetaDocumentoSet = new core\Set();

        return $oEtiquetaDocumentoSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera tots els ATRIBUTOS de EtiquetaDocumento en un array
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
