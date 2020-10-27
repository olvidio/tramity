<?php
namespace entradas\model;

use core\Converter;
use entradas\model\entity\EntradaDB;
use entradas\model\entity\EntradaDocDB;
use entradas\model\entity\GestorEntradaAdjunto;
use web\DateTimeLocal;
use web\NullDateTimeLocal;


class Entrada Extends EntradaDB {
    
    /* CONST -------------------------------------------------------------- */
    
    // modo entrada
    const MODO_MANUAL       = 1;
    const MODO_XML          = 2;
    // categoria
    const CAT_E12          = 1;
    const CAT_NORMAL       = 2;
    const CAT_PERMANATE    = 3;
    // visibilidad
    const V_TODOS           = 1;  // cualquiera
    const V_PERSONAL        = 2;  // oficina y directores
    const V_RESERVADO       = 3;  // sólo directores
    const V_RESERVADO_VCD   = 4;  // sólo vcd + quien señale
    
    /* PROPIEDADES -------------------------------------------------------------- */

    private $escrito;
    
    private $df_doc;
    private $convert;
    private $itipo_doc;
    
    /* CONSTRUCTOR -------------------------------------------------------------- */
    
    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_entrada
     * 						$a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id='') {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach($a_id as $nom_id=>$val_id) {
                if (($nom_id == 'id_entrada') && $val_id !== '') $this->iid_entrada = (int)$val_id; // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entrada = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas');
    }
    
    /* METODES PUBLICS ----------------------------------------------------------*/

    public function getArrayCategoria() {
        $a_tipos = [
            self::CAT_NORMAL => _("normal"),
            self::CAT_E12 => _("sin numerar"),
            self::CAT_PERMANATE => _("permanente"),
        ];
        
        return $a_tipos;
    }
    
    public function getArrayVisibilidad() {
        $a_tipos = [
            self::V_TODOS => _("todos"),
            self::V_PERSONAL => _("personal"),
            self::V_RESERVADO => _("reservado"),
            self::V_RESERVADO_VCD => _("vcd"),
        ];
        
        return $a_tipos;
    }
    
    /**
     * Hay que gauradar dos objetos.
     * {@inheritDoc}
     * @see \entradas\model\entity\EntradaDB::DBGuardar()
     */
    public function DBGuardar() {
        // El tipo y fecha documento:
        $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
        $oEntradaDocDB->setF_doc($this->df_doc,$this->convert);
        $oEntradaDocDB->setTipo_doc($this->itipo_doc);
        $oEntradaDocDB->DBGuardar();
        // El objeto padre:
        parent::DBGuardar();
    }
    
    /**
     * Hay que gauradar dos objetos.
     * {@inheritDoc}
     * @see \entradas\model\entity\EntradaDB::DBGuardar()
     */
    public function DBCarregar($que=NULL) {
        // El objeto padre:
        if (parent::DBCarregar($que) === FALSE) {
            return FALSE;
        }
        // El tipo y fecha documento:
        $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
        $this->df_doc = $oEntradaDocDB->getF_doc();
        $this->itipo_doc = $oEntradaDocDB->getTipo_doc();
        return TRUE;
    }

    /**
     * Recupera l'atribut df_doc de Entrada
     * de EntradaDocDB
     *
     * @return DateTimeLocal df_doc
     */
    function getF_documento() {
        if (!isset($this->df_doc)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $oFdoc = $oEntradaDocDB->getF_doc();
            $this->df_doc = $oFdoc;
        }
        if (empty($this->df_doc)) {
            return new NullDateTimeLocal();
        }
        return $this->df_doc;
    }
    /**
     * estableix el valor de l'atribut df_doc de EntradaDB
     * Si df_doc es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getForamat().
     * Si convert es FALSE, df_entrada debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_doc='' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_documento($df_doc='',$convert=TRUE) {
        $this->convert = $convert;
        $this->df_doc = $df_doc;
    }
    
    public function getTipo_documento(){
        if (!isset($this->itipo_doc)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $this->itipo_doc = $oEntradaDocDB->getTipo_doc();
        }
        return $this->itipo_doc;
    }
    
    public function setTipo_documento($itipo_doc) {
        $this->itipo_doc = $itipo_doc;
    }
    
    public function getArrayIdAdjuntos(){
        
        $gesEntradaAdjuntos = new GestorEntradaAdjunto();
        return $gesEntradaAdjuntos->getArrayIdAdjuntos($this->iid_entrada);
    }
    
    
}

