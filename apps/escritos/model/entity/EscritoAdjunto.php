<?php

namespace escritos\model\entity;

use core;

/**
 * Fitxer amb la Classe que accedeix a la taula escrito_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 23/7/2020
 */

/**
 * Classe que implementa l'entitat escrito_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 23/7/2020
 */
class EscritoAdjunto extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de EscritoAdjunto
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EscritoAdjunto
     *
     * @var string
     */
    protected $sNomTabla;
    protected $clone = FALSE;
    /**
     * aPrimary_key de EscritoAdjunto
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * aDades de EscritoAdjunto
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
     * Id_schema de EscritoAdjunto
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_item de EscritoAdjunto
     *
     * @var integer
     */
    private $iid_item;
    /**
     * Id_escrito de EscritoAdjunto
     *
     * @var integer
     */
    private $iid_escrito;

    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Nom de EscritoAdjunto
     *
     * @var string
     */
    private $snom;
    /**
     * Adjunto de EscritoAdjunto
     *
     * @var string bytea
     */
    private $adjunto;
    /**
     * tipo_doc de EscritoAdjunto
     *
     * @var integer
     */
    private $itipo_doc;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_item
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('escrito_adjuntos');
    }

    public function __clone()
    {
        $this->clone = TRUE;
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
        if ($this->DBCarregar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['id_escrito'] = $this->iid_escrito;
        $aDades['nom'] = $this->snom;
        $aDades['tipo_doc'] = $this->itipo_doc;
        $aDades['adjunto'] = $this->adjunto;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_escrito               = :id_escrito,
					nom                      = :nom,
					adjunto                  = :adjunto,
                    tipo_doc                 = :tipo_doc";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'EscritoAdjunto.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                $id_escrito = $aDades['id_escrito'];
                $nom = $aDades['nom'];
                $adjunto = $aDades['adjunto'];
                $tipo_doc = $aDades['tipo_doc'];

                $oDblSt->bindParam(1, $id_escrito, \PDO::PARAM_INT);
                $oDblSt->bindParam(2, $nom, \PDO::PARAM_STR);
                $oDblSt->bindParam(3, $adjunto, \PDO::PARAM_STR);
                $oDblSt->bindParam(4, $tipo_doc, \PDO::PARAM_INT);
                try {
                    $oDblSt->execute();
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EscritoAdjunto.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_escrito,nom,adjunto,tipo_doc)";
            $valores = "(:id_escrito,:nom,:adjunto,:tipo_doc)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EscritoAdjunto.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                $id_escrito = $aDades['id_escrito'];
                $nom = $aDades['nom'];
                $adjunto = $aDades['adjunto'];
                $tipo_doc = $aDades['tipo_doc'];

                $oDblSt->bindParam(1, $id_escrito, \PDO::PARAM_INT);
                $oDblSt->bindParam(2, $nom, \PDO::PARAM_STR);
                $oDblSt->bindParam(3, $adjunto, \PDO::PARAM_STR);
                $oDblSt->bindParam(4, $tipo_doc, \PDO::PARAM_INT);
                try {
                    $oDblSt->execute();
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EscritoAdjunto.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_item = $oDbl->lastInsertId('escrito_adjuntos_id_item_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCarregar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $id_escrito = 0;
        $nom = '';
        $adjunto = '';
        $tipo_doc = 1;
        if (isset($this->iid_item) && $this->clone === FALSE) {
            if (($oDblSt = $oDbl->query("SELECT id_escrito, nom, adjunto,tipo_doc FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'EscritoAdjunto.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $id_escrito, \PDO::PARAM_INT);
            $oDblSt->bindColumn(2, $nom, \PDO::PARAM_STR, 256);
            $oDblSt->bindColumn(3, $adjunto, \PDO::PARAM_STR);
            $oDblSt->bindColumn(4, $tipo_doc, \PDO::PARAM_INT);
            $oDblSt->fetch(\PDO::FETCH_BOUND);

            $aDades = ['id_escrito' => $id_escrito,
                'nom' => $nom,
                'adjunto' => $adjunto,
                'tipo_doc' => $tipo_doc,
            ];

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
    function setAllAtributes($aDades, $convert = FALSE)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_item', $aDades)) {
            $this->setId_item($aDades['id_item']);
        }
        if (array_key_exists('id_escrito', $aDades)) {
            $this->setId_escrito($aDades['id_escrito']);
        }
        if (array_key_exists('nom', $aDades)) {
            $this->setNom($aDades['nom']);
        }
        if (array_key_exists('adjunto', $aDades)) {
            $this->setAdjuntoEscaped($aDades['adjunto']);
        }
        if (array_key_exists('tipo_doc', $aDades)) {
            $this->setTipo_doc($aDades['tipo_doc']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_item de EscritoAdjunto
     *
     * @param integer iid_item
     */
    function setId_item($iid_item)
    {
        $this->iid_item = $iid_item;
    }

    /**
     * estableix el valor de l'atribut iid_escrito de EscritoAdjunto
     *
     * @param integer iid_escrito='' optional
     */
    function setId_escrito($iid_escrito = '')
    {
        $this->iid_escrito = $iid_escrito;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut snom de EscritoAdjunto
     *
     * @param string snom='' optional
     */
    function setNom($snom = '')
    {
        $this->snom = $snom;
    }

    /**
     * estableix el valor de l'atribut adjunto de EntradaAdjunto
     * per usar amb els valors directes de la DB.
     *
     * @param string adjunto='' optional (ja convertit a hexadecimal)
     */
    private function setAdjuntoEscaped($adjunto = '')
    {
        $this->adjunto = $adjunto;
    }

    /**
     * estableix el valor de l'atribut itipo_doc de Documento
     *
     * @param integer itipo_doc='' optional
     */
    function setTipo_doc($tipo_doc = '')
    {
        $this->itipo_doc = $tipo_doc;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_item('');
        $this->setId_escrito('');
        $this->setNom('');
        $this->setAdjuntoEscaped('');
        $this->setTipo_doc('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de EscritoAdjunto en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_item' => $this->iid_item);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EscritoAdjunto en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
            $sClauError = 'EscritoAdjunto.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_item de EscritoAdjunto
     *
     * @return integer iid_item
     */
    function getId_item()
    {
        if (!isset($this->iid_item) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_item;
    }

    /**
     * Recupera l'atribut iid_escrito de EscritoAdjunto
     *
     * @return integer iid_escrito
     */
    function getId_escrito()
    {
        if (!isset($this->iid_escrito) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_escrito;
    }

    /**
     * Recupera l'atribut snom de EscritoAdjunto
     *
     * @return string snom
     */
    function getNom()
    {
        if (!isset($this->snom) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->snom;
    }

    /**
     * Recupera l'atribut adjunto de EntradaAdjunto
     *
     * @return string adjunto
     */
    public function getAdjunto()
    {
        if (!isset($this->adjunto) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return hex2bin($this->adjunto);
    }

    /**
     * estableix el valor de l'atribut adjunto de EntradaAdjunto
     *
     * @param string adjunto='' optional
     */
    public function setAdjunto($adjunto = '')
    {
        // Escape the binary data
        $escaped = bin2hex($adjunto);
        $this->adjunto = $escaped;
    }

    /**
     * Recupera l'atribut itipo_doc de Documento
     *
     * @return integer itipo_doc
     */
    function getTipo_doc()
    {
        if (!isset($this->itipo_doc) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->itipo_doc;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oEscritoAdjuntoSet = new core\Set();

        $oEscritoAdjuntoSet->add($this->getDatosId_escrito());
        $oEscritoAdjuntoSet->add($this->getDatosNom());
        $oEscritoAdjuntoSet->add($this->getDatosAdjunto());
        $oEscritoAdjuntoSet->add($this->getDatosTipo_doc());
        return $oEscritoAdjuntoSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_escrito de EscritoAdjunto
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_escrito()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_escrito'));
        $oDatosCampo->setEtiqueta(_("id_escrito"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut snom de EscritoAdjunto
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosNom()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'nom'));
        $oDatosCampo->setEtiqueta(_("nom"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut adjunto de EscritoAdjunto
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosAdjunto()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'adjunto'));
        $oDatosCampo->setEtiqueta(_("adjunto"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut itipo_doc de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo_doc()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo_doc'));
        $oDatosCampo->setEtiqueta(_("tipo_doc"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de EscritoAdjunto en un array
     *
     * @return array aDades
     */
    function getTot()
    {
        if (!is_array($this->aDades)) {
            $this->DBCarregar('tot');
        }
        return $this->aDades;
    }
}
