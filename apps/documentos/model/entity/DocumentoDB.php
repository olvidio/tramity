<?php

namespace documentos\model\entity;

use core;
use PDO;
use PDOException;
use web;

/**
 * Fitxer amb la Classe que accedeix a la taula documentos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/6/2021
 */

/**
 * Classe que implementa l'entitat documentos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 9/6/2021
 */
class DocumentoDB extends core\ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de Documento
     *
     * @var array
     */
    protected $aPrimary_key;

    /**
     * aDades de Documento
     *
     * @var array
     */
    protected $aDades;

    /**
     * bLoaded de Documento
     *
     * @var boolean
     */
    protected $bLoaded = FALSE;

    /**
     * Id_schema de Documento
     *
     * @var integer
     */
    protected $iid_schema;

    /**
     * Id_doc de Documento
     *
     * @var integer
     */
    protected $iid_doc;
    /**
     * Nom de Documento
     *
     * @var string
     */
    protected $snom;
    /**
     * Nom de Documento
     *
     * @var string
     */
    protected $snombre_fichero;
    /**
     * Creador de Documento
     *
     * @var integer
     */
    protected $icreador;
    /**
     * Visibilidad de Documento
     *
     * @var integer
     */
    protected $ivisibilidad;
    /**
     * Tipo_doc de Documento
     *
     * @var integer
     */
    protected $itipo_doc;
    /**
     * F_upload de Documento
     *
     * @var web\DateTimeLocal
     */
    protected $df_upload;
    /**
     * Documento de Documento
     *
     * @var string bytea
     *
     */
    protected $documento;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de Documento
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Documento
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
     * @param integer|array iid_doc
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_doc') && $val_id !== '') {
                    $this->iid_doc = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_doc = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_doc' => $this->iid_doc);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('documentos');
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
        $aDades['nom'] = $this->snom;
        $aDades['nombre_fichero'] = $this->snombre_fichero;
        $aDades['creador'] = $this->icreador;
        $aDades['visibilidad'] = $this->ivisibilidad;
        $aDades['tipo_doc'] = $this->itipo_doc;
        $aDades['f_upload'] = $this->df_upload;
        $aDades['documento'] = $this->documento;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					nom                      = :nom,
					nombre_fichero           = :nombre_fichero,
					creador                  = :creador,
					visibilidad              = :visibilidad,
					tipo_doc                 = :tipo_doc,
					f_upload                 = :f_upload,
					documento                = :documento ";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_doc='$this->iid_doc'")) === FALSE) {
                $sClauError = 'Documento.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                $nom = $aDades['nom'];
                $nombre_fichero = $aDades['nombre_fichero'];
                $creador = $aDades['creador'];
                $visibilidad = $aDades['visibilidad'];
                $tipo_doc = $aDades['tipo_doc'];
                $f_upload = $aDades['f_upload'];
                $documento = $aDades['documento'];

                $oDblSt->bindParam(1, $nom, PDO::PARAM_STR);
                $oDblSt->bindParam(2, $nombre_fichero, PDO::PARAM_STR);
                $oDblSt->bindParam(3, $creador, PDO::PARAM_INT);
                $oDblSt->bindParam(4, $visibilidad, PDO::PARAM_INT);
                $oDblSt->bindParam(5, $tipo_doc, PDO::PARAM_INT);
                $oDblSt->bindParam(6, $f_upload, PDO::PARAM_STR);
                $oDblSt->bindParam(7, $documento, PDO::PARAM_STR);
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Documento.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(nom,nombre_fichero,creador,visibilidad,tipo_doc,f_upload,documento)";
            $valores = "(:nom,:nombre_fichero,:creador,:visibilidad,:tipo_doc,:f_upload, :documento )";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Documento.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                $nom = $aDades['nom'];
                $nombre_fichero = $aDades['nombre_fichero'];
                $creador = $aDades['creador'];
                $visibilidad = $aDades['visibilidad'];
                $tipo_doc = $aDades['tipo_doc'];
                $f_upload = $aDades['f_upload'];
                $documento = $aDades['documento'];

                $oDblSt->bindParam(1, $nom, PDO::PARAM_STR);
                $oDblSt->bindParam(2, $nombre_fichero, PDO::PARAM_STR);
                $oDblSt->bindParam(3, $creador, PDO::PARAM_INT);
                $oDblSt->bindParam(4, $visibilidad, PDO::PARAM_INT);
                $oDblSt->bindParam(5, $tipo_doc, PDO::PARAM_INT);
                $oDblSt->bindParam(6, $f_upload, PDO::PARAM_STR);
                $oDblSt->bindParam(7, $documento, PDO::PARAM_STR);
                try {
                    $oDblSt->execute($aDades);
                } catch (PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'Documento.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            $this->id_doc = $oDbl->lastInsertId('documentos_id_doc_seq');
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
        $nom = '';
        $nombre_fichero = '';
        $creador = '';
        $visibilidad = '';
        $tipo_doc = '';
        $f_upload = '';
        $documento = '';

        if (isset($this->iid_doc)) {
            if (($oDblSt = $oDbl->query("SELECT nom,nombre_fichero,creador,visibilidad,tipo_doc,f_upload,
					documento FROM $nom_tabla WHERE id_doc='$this->iid_doc'")) === FALSE) {
                $sClauError = 'Documento.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $oDblSt->execute();
            $oDblSt->bindColumn(1, $nom, PDO::PARAM_STR);
            $oDblSt->bindColumn(2, $nombre_fichero, PDO::PARAM_STR);
            $oDblSt->bindColumn(3, $creador, PDO::PARAM_INT);
            $oDblSt->bindColumn(4, $visibilidad, PDO::PARAM_INT);
            $oDblSt->bindColumn(5, $tipo_doc, PDO::PARAM_INT);
            $oDblSt->bindColumn(6, $f_upload, PDO::PARAM_STR);
            $oDblSt->bindColumn(7, $documento, PDO::PARAM_STR);
            $oDblSt->fetch(PDO::FETCH_BOUND);

            $aDades = [
                'nom' => $nom,
                'nombre_fichero' => $nombre_fichero,
                'creador' => $creador,
                'visibilidad' => $visibilidad,
                'tipo_doc' => $tipo_doc,
                'f_upload' => $f_upload,
                'documento' => $documento,
            ];

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
    function setAllAtributes($aDades, $convert = FALSE)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_doc', $aDades)) {
            $this->setId_doc($aDades['id_doc']);
        }
        if (array_key_exists('nom', $aDades)) {
            $this->setNom($aDades['nom']);
        }
        if (array_key_exists('nombre_fichero', $aDades)) {
            $this->setNombre_fichero($aDades['nombre_fichero']);
        }
        if (array_key_exists('creador', $aDades)) {
            $this->setCreador($aDades['creador']);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
        if (array_key_exists('tipo_doc', $aDades)) {
            $this->setTipo_doc($aDades['tipo_doc']);
        }
        if (array_key_exists('f_upload', $aDades)) {
            $this->setF_upload($aDades['f_upload'], $convert);
        }
        if (array_key_exists('documento', $aDades)) {
            $this->setDocumentoEscaped($aDades['documento']);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_doc de Documento
     *
     * @param integer iid_doc
     */
    function setId_doc($iid_doc)
    {
        $this->iid_doc = $iid_doc;
    }

    /**
     * estableix el valor de l'atribut snom de Documento
     *
     * @param string snom='' optional
     */
    function setNom($snom = '')
    {
        $this->snom = $snom;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut snombre_fichero de Documento
     *
     * @param string snombre_fichero='' optional
     */
    function setNombre_fichero($snombre_fichero = '')
    {
        $this->snombre_fichero = $snombre_fichero;
    }

    /**
     * estableix el valor de l'atribut icreador de Documento
     *
     * @param integer icreador='' optional
     */
    function setCreador($icreador = '')
    {
        $this->icreador = $icreador;
    }

    /**
     * estableix el valor de l'atribut ivisibilidad de Documento
     *
     * @param integer ivisibilidad='' optional
     */
    function setVisibilidad($ivisibilidad = '')
    {
        $this->ivisibilidad = $ivisibilidad;
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
     * estableix el valor de l'atribut df_upload de Documento
     * Si df_upload es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_upload debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_upload='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_upload($df_upload = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_upload)) {
            $oConverter = new core\Converter('date', $df_upload);
            $this->df_upload = $oConverter->toPg();
        } else {
            $this->df_upload = $df_upload;
        }
    }

    /**
     * estableix el valor de l'atribut documento de Documento
     * per usar amb els valors directes de la DB.
     *
     * @param string documento='' optional (ja convertit a hexadecimal)
     */
    private function setDocumentoEscaped($documento = '')
    {
        $this->documento = $documento;
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_doc('');
        $this->setNom('');
        $this->setNombre_fichero('');
        $this->setCreador('');
        $this->setVisibilidad('');
        $this->setTipo_doc('');
        $this->setF_upload('');
        $this->setDocumentoEscaped('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Documento en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_doc' => $this->iid_doc);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Documento en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_doc') && $val_id !== '') {
                    $this->iid_doc = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_doc = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_doc' => $this->iid_doc);
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
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_doc='$this->iid_doc'")) === FALSE) {
            $sClauError = 'Documento.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_doc de Documento
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
     * Recupera l'atribut snom de Documento
     *
     * @return string snom
     */
    function getNom()
    {
        if (!isset($this->snom) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snom;
    }

    /**
     * Recupera l'atribut snombre_fichero de Documento
     *
     * @return string snombre_fichero
     */
    function getNombre_fichero()
    {
        if (!isset($this->snombre_fichero) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->snombre_fichero;
    }

    /**
     * Recupera l'atribut icreador de Documento
     *
     * @return integer icreador
     */
    function getCreador()
    {
        if (!isset($this->icreador) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icreador;
    }

    /**
     * Recupera l'atribut ivisibilidad de Documento
     *
     * @return integer ivisibilidad
     */
    function getVisibilidad()
    {
        if (!isset($this->ivisibilidad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivisibilidad;
    }

    /**
     * Recupera l'atribut itipo_doc de Documento
     *
     * @return integer itipo_doc
     */
    function getTipo_doc()
    {
        if (!isset($this->itipo_doc) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->itipo_doc;
    }

    /**
     * Recupera l'atribut df_upload de Documento
     *
     * @return web\DateTimeLocal df_upload
     */
    function getF_upload()
    {
        if (!isset($this->df_upload) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_upload)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_upload);
        return $oConverter->fromPg();
    }

    public function getDocumento()
    {
        if (!isset($this->documento) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return hex2bin($this->documento);
    }

    /**
     * estableix el valor de l'atribut documento de Documento
     *
     * @param string documento='' optional
     */
    public function setDocumento($documento = '')
    {
        // Escape the binary data
        $escaped = bin2hex($documento);
        $this->documento = $escaped;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oDocumentoSet = new core\Set();

        $oDocumentoSet->add($this->getDatosNom());
        $oDocumentoSet->add($this->getDatosCreador());
        $oDocumentoSet->add($this->getDatosVisibilidad());
        $oDocumentoSet->add($this->getDatosTipo_doc());
        $oDocumentoSet->add($this->getDatosF_upload());
        $oDocumentoSet->add($this->getDatosDocumento());
        return $oDocumentoSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut snom de Documento
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
     * Recupera les propietats de l'atribut icreador de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCreador()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'creador'));
        $oDatosCampo->setEtiqueta(_("creador"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivisibilidad de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosVisibilidad()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'visibilidad'));
        $oDatosCampo->setEtiqueta(_("visibilidad"));
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
     * Recupera les propietats de l'atribut df_upload de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_upload()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_upload'));
        $oDatosCampo->setEtiqueta(_("f_upload"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut documento de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDocumento()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'documento'));
        $oDatosCampo->setEtiqueta(_("documento"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de Documento en un array
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
     * Recupera les propietats de l'atribut snombre_fichero de Documento
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosNombre_fichero()
    {
        $nombre_fichero_tabla = $this->getNombre_ficheroTabla();
        $oDatosCampo = new core\DatosCampo(array('nombre_fichero_tabla' => $nombre_fichero_tabla, 'nombre_fichero_camp' => 'nombre_fichero'));
        $oDatosCampo->setEtiqueta(_("nombre_fichero"));
        return $oDatosCampo;
    }
}
