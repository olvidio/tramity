<?php

namespace entradas\model\entity;

use core;
use entradas\model\Entrada;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use stdClass;
use web;

/**
 * Fitxer amb la Classe que accedeix a la taula entradas_bypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat entradas_bypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class EntradaBypass extends Entrada
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de EntradaBypass
     *
     * @var array
     */
    //private $aPrimary_key;

    /**
     * aDades de EntradaBypass
     *
     * @var array
     */
    //private $aDades;

    /**
     * bLoaded de EntradaBypass
     *
     * @var boolean
     */
    //private $bLoaded = FALSE;

    /**
     * Id_schema de EntradaBypass
     *
     * @var integer
     */
    //private $iid_schema;

    /**
     * Id_item de EntradaBypass
     *
     * @var integer
     */
    //private $iid_item;
    /**
     * Id_entrada de EntradaBypass
     *
     * @var integer
     */
    //private $iid_entrada;
    /**
     * oDbl de EntradaBypass
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EntradaBypass
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * Descripcion de EntradaBypass
     *
     * @var string
     */
    private $sdescripcion;
    /**
     * Json_prot_destino de EntradaBypass
     *
     * @var object JSON
     */
    private $json_prot_destino;
    /**
     * Id_grupos de EntradaBypass
     *
     * @var array
     */
    private $a_id_grupos;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Destinos de EntradaBypass
     *
     * @var array
     */
    private $a_destinos;
    /**
     * F_salida de EntradaBypass
     *
     * @var web\DateTimeLocal
     */
    private $df_salida;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_entrada
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_entrada') && $val_id !== '') {
                    $this->iid_entrada = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas_bypass');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
            $sClauError = 'EntradaBypass.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_entrada de EntradaBypass
     *
     * @return integer iid_entrada
     */
    function getId_entrada()
    {
        if (!isset($this->iid_entrada) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_entrada;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCarregar($que = null)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_entrada)) {
            if (($oDblSt = $oDbl->query("SELECT * 
				FROM $nom_tabla JOIN entradas USING (id_entrada) WHERE id_entrada='$this->iid_entrada'"))
                === FALSE) {
                $sClauError = 'EntradaBypass.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
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

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

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
        //if (array_key_exists('id_item',$aDades)) { $this->setId_item($aDades['id_item']); }
        if (array_key_exists('id_entrada', $aDades)) {
            $this->setId_entrada($aDades['id_entrada']);
        }
        if (array_key_exists('descripcion', $aDades)) {
            $this->setDescripcion($aDades['descripcion']);
        }
        if (array_key_exists('json_prot_destino', $aDades)) {
            $this->setJson_prot_destino($aDades['json_prot_destino'], TRUE);
        }
        if (array_key_exists('id_grupos', $aDades)) {
            $this->setId_grupos($aDades['id_grupos'], TRUE);
        }
        if (array_key_exists('destinos', $aDades)) {
            $this->setDestinos($aDades['destinos'], TRUE);
        }
        if (array_key_exists('f_salida', $aDades)) {
            $this->setF_salida($aDades['f_salida'], $convert);
        }
        // añado los de entradas
        if (array_key_exists('modo_entrada', $aDades)) {
            $this->setModo_entrada($aDades['modo_entrada']);
        }
        if (array_key_exists('json_prot_origen', $aDades)) {
            $this->setJson_prot_origen($aDades['json_prot_origen'], TRUE);
        }
        if (array_key_exists('asunto_entrada', $aDades)) {
            $this->setAsunto_entrada($aDades['asunto_entrada']);
        }
        if (array_key_exists('json_prot_ref', $aDades)) {
            $this->setJson_prot_ref($aDades['json_prot_ref'], TRUE);
        }
        if (array_key_exists('ponente', $aDades)) {
            $this->setPonente($aDades['ponente']);
        }
        if (array_key_exists('resto_oficinas', $aDades)) {
            $this->setResto_oficinas($aDades['resto_oficinas'], TRUE);
        }
        if (array_key_exists('asunto', $aDades)) {
            $this->setAsunto($aDades['asunto']);
        }
        if (array_key_exists('f_entrada', $aDades)) {
            $this->setF_entrada($aDades['f_entrada'], $convert);
        }
        if (array_key_exists('detalle', $aDades)) {
            $this->setDetalle($aDades['detalle']);
        }
        if (array_key_exists('categoria', $aDades)) {
            $this->setCategoria($aDades['categoria']);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
        if (array_key_exists('f_contestar', $aDades)) {
            $this->setF_contestar($aDades['f_contestar'], $convert);
        }
        if (array_key_exists('bypass', $aDades)) {
            $this->setBypass($aDades['bypass']);
        }
        if (array_key_exists('estado', $aDades)) {
            $this->setEstado($aDades['estado']);
        }
        if (array_key_exists('anulado', $aDades)) {
            $this->setAnulado($aDades['anulado']);
        }
        if (array_key_exists('encargado', $aDades)) {
            $this->setEncargado($aDades['encargado']);
        }
        if (array_key_exists('json_visto', $aDades)) {
            $this->setJson_visto($aDades['json_visto'], TRUE);
        }
    }

    /**
     * estableix el valor de l'atribut iid_entrada de EntradaBypass
     *
     * @param integer iid_entrada='' optional
     */
    function setId_entrada($iid_entrada = '')
    {
        $this->iid_entrada = $iid_entrada;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut sdescripcion de EntradaBypass
     *
     * @param string sdescripcion='' optional
     */
    function setDescripcion($sdescripcion = '')
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     * estableix el valor de l'atribut a_id_grupos de EntradaBypass
     *
     * @param array a_id_grupos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    function setId_grupos($a_id_grupos = [], $db = FALSE)
    {
        if ($db === FALSE) {
            $postgresArray = core\array_php2pg($a_id_grupos);
        } else {
            $postgresArray = $a_id_grupos;
        }
        $this->a_id_grupos = $postgresArray;
    }

    /**
     * estableix el valor de l'atribut a_destinos de EntradaBypass
     *
     * @param array a_destinos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    function setDestinos($a_destinos = '', $db = FALSE)
    {
        if ($db === FALSE) {
            $postgresArray = core\array_php2pg($a_destinos);
        } else {
            $postgresArray = $a_destinos;
        }
        $this->a_destinos = $postgresArray;
    }


    /**
     * Recupera l'atribut iid_item de EntradaBypass
     *
     * @return integer iid_item
     */
    /*
    function getId_item() {
        if (!isset($this->iid_item) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->iid_item;
    }
    */
    /**
     * estableix el valor de l'atribut iid_item de EntradaBypass
     *
     * @param integer iid_item
     */
    /*
    function setId_item($iid_item) {
        $this->iid_item = $iid_item;
    }
    */

    /**
     * estableix el valor de l'atribut df_salida de EntradaBypass
     * Si df_salida es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_salida debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param web\DateTimeLocal|string df_salida='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    function setF_salida($df_salida = '', $convert = TRUE)
    {
        if ($convert === TRUE && !empty($df_salida)) {
            $oConverter = new core\Converter('date', $df_salida);
            $this->df_salida = $oConverter->toPg();
        } else {
            $this->df_salida = $df_salida;
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
        //$this->setId_item('');
        $this->setId_entrada('');
        $this->setDescripcion('');
        $this->setJson_prot_destino('');
        $this->setId_grupos();
        $this->setDestinos('');
        $this->setF_salida('');
        $this->setPrimary_key($aPK);
        // añado los de entradas
        $this->setModo_entrada('');
        $this->setJson_prot_origen('');
        $this->setAsunto_entrada('');
        $this->setJson_prot_ref('');
        $this->setPonente('');
        $this->setResto_oficinas('');
        $this->setAsunto('');
        $this->setF_entrada('');
        $this->setDetalle('');
        $this->setCategoria('');
        $this->setVisibilidad('');
        $this->setF_contestar('');
        $this->setBypass('');
        $this->setEstado('');
        $this->setAnulado('');
        $this->setEncargado('');
        $this->setJson_visto('');
    }

    /**
     * Recupera las claus primàries de EntradaBypass en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_entrada' => $this->iid_entrada);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EntradaBypass en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id == 'id_entrada') && $val_id !== '') {
                    $this->iid_entrada = (int)$val_id;
                } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
            }
        }
    }

    /**
     * Recupera l'atribut a_destinos de EntradaBypass
     *
     * @return array a_destinos
     */
    function getDestinos()
    {
        if (!isset($this->a_destinos) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return core\array_pg2php($this->a_destinos);
    }

    /**
     * Recupera l'atribut df_salida de EntradaBypass
     *
     * @return web\DateTimeLocal df_salida
     */
    function getF_salida()
    {
        if (!isset($this->df_salida) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        if (empty($this->df_salida)) {
            return new web\NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_salida);
        return $oConverter->fromPg();
    }

    public function getDestinosByPass()
    {
        $a_grupos = $this->getId_grupos();

        $aMiembros = [];
        $destinos_txt = '';

        if (!empty($a_grupos)) {
            $destinos_txt = $this->getDescripcion();
            //(segun los grupos seleccionados)
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $a_miembros_g = $oGrupo->getMiembros();
                $aMiembros = array_merge($aMiembros, $a_miembros_g);
            }
            $aMiembros = array_unique($aMiembros);
            $this->setDestinos($aMiembros);
            if ($this->DBGuardar() === FALSE) {
                $error_txt = $this->getErrorTxt();
                exit ($error_txt);
            }
        } else {
            //(segun individuales)
            $a_json_prot_dst = $this->getJson_prot_destino();
            foreach ($a_json_prot_dst as $json_prot_dst) {
                $aMiembros[] = $json_prot_dst->id_lugar;
                $oLugar = new Lugar($json_prot_dst->id_lugar);
                $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                $destinos_txt .= $oLugar->getNombre();
            }
        }

        return ['miembros' => $aMiembros, 'txt' => $destinos_txt];
    }

    /**
     * Recupera l'atribut a_id_grupos de EntradaBypass
     *
     * @return array a_id_grupos
     */
    function getId_grupos()
    {
        if (!isset($this->a_id_grupos) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return core\array_pg2php($this->a_id_grupos);
    }

    /**
     * Recupera l'atribut sdescripcion de EntradaBypass
     *
     * @return string sdescripcion
     */
    function getDescripcion()
    {
        if (!isset($this->sdescripcion) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        return $this->sdescripcion;
    }

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi és fa el update.
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
        $aDades['id_entrada'] = $this->iid_entrada;
        $aDades['descripcion'] = $this->sdescripcion;
        $aDades['json_prot_destino'] = $this->json_prot_destino;
        $aDades['id_grupos'] = $this->a_id_grupos;
        $aDades['destinos'] = $this->a_destinos;
        $aDades['f_salida'] = $this->df_salida;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_entrada               = :id_entrada,
					descripcion              = :descripcion,
					json_prot_destino        = :json_prot_destino,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					f_salida                 = :f_salida";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClauError = 'EntradaBypass.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EntradaBypass.update.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
        } else {
            // INSERT
            $campos = "(id_entrada,descripcion,json_prot_destino,id_grupos,destinos,f_salida)";
            $valores = "(:id_entrada,:descripcion,:json_prot_destino,:id_grupos,:destinos,:f_salida)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EntradaBypass.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            } else {
                try {
                    $oDblSt->execute($aDades);
                } catch (\PDOException $e) {
                    $err_txt = $e->errorInfo[2];
                    $this->setErrorTxt($err_txt);
                    $sClauError = 'EntradaBypass.insertar.execute';
                    $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                    return FALSE;
                }
            }
            //$this->id_item = $oDbl->lastInsertId('entradas_bypass_id_item_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Recupera l'atribut json_prot_destino de EntradaBypass
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return object JSON json_prot_destino
     */
    function getJson_prot_destino($bArray = FALSE)
    {
        if (!isset($this->json_prot_destino) && !$this->bLoaded) {
            $this->DBCarregar();
        }
        $oJSON = json_decode($this->json_prot_destino, $bArray);
        if (empty($oJSON) || $oJSON == '[]') {
            if ($bArray) {
                $oJSON = [];
            } else {
                $oJSON = new stdClass;
            }
        }
        //$this->json_prot_destino = $oJSON;
        return $oJSON;
    }

    /**
     * estableix el valor de l'atribut json_prot_destino de EntradaBypass
     *
     * @param object JSON json_prot_destino
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     */
    function setJson_prot_destino($oJSON, $db = FALSE)
    {
        if ($db === FALSE) {
            $json = json_encode($oJSON);
        } else {
            $json = $oJSON;
        }
        $this->json_prot_destino = $json;
    }

    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos()
    {
        $oEntradaBypassSet = new core\Set();

        $oEntradaBypassSet->add($this->getDatosId_entrada());
        $oEntradaBypassSet->add($this->getDatosDescripcion());
        $oEntradaBypassSet->add($this->getDatosJson_prot_destino());
        $oEntradaBypassSet->add($this->getDatosId_grupos());
        $oEntradaBypassSet->add($this->getDatosDestinos());
        $oEntradaBypassSet->add($this->getDatosF_salida());
        return $oEntradaBypassSet->getTot();
    }

    /**
     * Recupera les propietats de l'atribut iid_entrada de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_entrada()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_entrada'));
        $oDatosCampo->setEtiqueta(_("id_entrada"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescripcion de EntradaBypass
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
     * Recupera les propietats de l'atribut json_prot_destino de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosJson_prot_destino()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_destino'));
        $oDatosCampo->setEtiqueta(_("json_prot_destino"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_id_grupos de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_grupos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_grupos'));
        $oDatosCampo->setEtiqueta(_("id_grupos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_destinos de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosDestinos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'destinos'));
        $oDatosCampo->setEtiqueta(_("destinos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_salida de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_salida()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_salida'));
        $oDatosCampo->setEtiqueta(_("f_salida"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de EntradaBypass en un array
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
