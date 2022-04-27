<?php
namespace entradas\model;

use core\ConfigGlobal;
use function core\is_true;
use entradas\model\entity\EntradaDB;
use entradas\model\entity\EntradaDocDB;
use entradas\model\entity\GestorEntradaAdjunto;
use entradas\model\entity\GestorEntradaBypass;
use etiquetas\model\entity\Etiqueta;
use etiquetas\model\entity\EtiquetaEntrada;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaEntrada;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use web\Protocolo;
use web\ProtocoloArray;


class Entrada Extends EntradaDB {
    
    /* CONST -------------------------------------------------------------- */
	
	// modo entrada
	const MODO_MANUAL       = 1;
	const MODO_XML          = 2;
	
    // estado
    /*
     - Ingresa (secretaría introduce los datos de la entrada)
     - Admitir (vcd los mira y da el ok)
     - Asignar (secretaría añade datos tipo: ponente... Puede que no se haya hecho el paso de ingresar)
     - Aceptar (scdl ok)
     - Oficinas (Las oficinas puede ver lo suyo)
     - Archivado (Ya no sale en las listas de la oficina)
     - Enviado cr (Cuando se han enviado los bypass)
     */
    const ESTADO_INGRESADO          = 1;
    const ESTADO_ADMITIDO           = 2;
    const ESTADO_ASIGNADO           = 3;
    const ESTADO_ACEPTADO           = 4;
    //const ESTADO_OFICINAS           = 5;
    const ESTADO_ARCHIVADO          = 6;
    const ESTADO_ENVIADO_CR         = 10;
    
    /* PROPIEDADES -------------------------------------------------------------- */

    protected $df_doc;
    protected $convert;
    protected $itipo_doc;

    protected $nombre_escrito;
    
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
                if (($nom_id == 'id_entrada') && $val_id !== '') { $this->iid_entrada = (int)$val_id; } // evitem SQL injection fent cast a integer
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
    
    
    
    public function cabeceraDistribucion_cr() {
        // a ver si ya está
        $gesEntradasBypass = new GestorEntradaBypass();
        $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $this->iid_entrada]);
        if (!empty($cEntradasBypass)) {
            // solo debería haber una:
            $oEntradaBypass = $cEntradasBypass[0];
            
            // poner los destinos
            $a_grupos = $oEntradaBypass->getId_grupos();
            $descripcion = $oEntradaBypass->getDescripcion();
            
            if (!empty($a_grupos)) {
                //(segun los grupos seleccionados)
                $destinos_txt = $descripcion; 
            } else {
                //(segun individuales)
                $destinos_txt = '';
                if (!empty($descripcion)) {
                   $destinos_txt = $descripcion; 
                } else {
                    $a_json_prot_dst = $oEntradaBypass->getJson_prot_destino();
                    foreach ($a_json_prot_dst as $json_prot_dst) {
                        $oLugar = new Lugar($json_prot_dst->lugar);
                        $destinos_txt .= empty($destinos_txt)? '' : ', ';
                        $destinos_txt .= $oLugar->getNombre();
                    }
                }
            }
            
            $destinos_txt;
        } else {
            // No hay destinos definidos.
            $destinos_txt = _("No hay destinos");
        }
        
        return $destinos_txt;
    }
    
    public function cabeceraIzquierda() {
        // sigla +(visibilidad) + ref
    	$oVisibilidad = new Visibilidad();
    	$a_visibilidad_dst = $oVisibilidad->getArrayVisibilidadCtr();
        
        $sigla = $_SESSION['oConfig']->getSigla();
        $destinos_txt = $sigla;
        // excepción para bypass
        if (!is_true($this->getBypass())) {
			$visibilidad = $this->getVisibilidad();
			if (!empty($visibilidad) && $visibilidad != Visibilidad::V_CTR_TODOS) {
				$visibilidad_txt = $a_visibilidad_dst[$visibilidad];
				$destinos_txt .= " ($visibilidad_txt)";
			}
        }
        
        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        if (!empty($cLugares)) {
            $id_sigla = $cLugares[0]->getId_lugar();

            // referencias
            $a_json_prot_ref = $this->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($a_json_prot_ref,'','referencias');
            $oArrayProtRef->setRef(TRUE);
            $aRef = $oArrayProtRef->ArrayListaTxtBr($id_sigla);
        } else {
            $aRef['dst_org'] = '??';
        }
        
        if (!empty($aRef['dst_org'])) {
            $destinos_txt .= '<br>';
            $destinos_txt .= $aRef['dst_org'];
        }
        return $destinos_txt;
    }
    
    public function cabeceraDerecha() {
        // origen + ref
        $id_org = '';
        $json_prot_origen = $this->getJson_prot_origen();
        if (!empty((array)$json_prot_origen)) {
            $id_org = $json_prot_origen->lugar;
            
            // referencias
            $a_json_prot_ref = $this->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($a_json_prot_ref,'','referencias');
            $oArrayProtRef->setRef(TRUE);
            $aRef = $oArrayProtRef->ArrayListaTxtBr($id_org);
            
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_origen->lugar);
            $oProtOrigen->setProt_num($json_prot_origen->num);
            $oProtOrigen->setProt_any($json_prot_origen->any);
            $oProtOrigen->setMas($json_prot_origen->mas);
            
            $origen_txt = $oProtOrigen->ver_txt();
        } else {
            $origen_txt = '??';
        }
        
        if (!empty($aRef['dst_org'])) {
            $origen_txt .= '<br>';
            $origen_txt .= $aRef['dst_org'];
        }
        
        return $origen_txt;
    }
    
    /**
     * Recupera l'atribut sasunto de Entrada teniendo en cuenta los permisos
     *
     * @return string sasunto
     */
    function getAsunto() {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this,'asunto');
            
        $asunto = _("reservado");
        if ($perm > 0) {
            $asunto = '';
            $anulado = $this->getAnulado();
            if (!empty($anulado)) {
                $asunto = _("ANULADO") . "($anulado) ";
            }
            $asunto .= $this->getAsuntoDB();
        } 
        return $asunto;
    }

    /**
     * Recupera l'atribut sdetalle de Entrada teniendo en cuenta los permisos
     *
     * @return string sdetalle
     */
    function getDetalle() {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this,'detalle');
            
        $detalle = _("reservado");
        if ($perm > 0) {
            $detalle = $this->getDetalleDB();
        } 
        return $detalle;
    }

    /**
     * añadir el detalle en el asunto.
     * también el grupo de destinos (si es distrbución cr)
     * tener en cuenta los permisos...
     *
     * return string
     */
    public function getAsuntoDetalle() {
        // 
        $txt_grupos = '';
        if ($this->getBypass()) {
            $lista_grupos = $this->cabeceraDistribucion_cr();
            $lista_grupos = empty($lista_grupos)? _("No hay destinos") : $lista_grupos;
            $txt_grupos = "<span class=\"text-success\"> ($lista_grupos)</span>";
        }
        $asunto = $this->getAsunto();
        $detalle = $this->getDetalle();
        $asunto_detelle = empty($detalle)? $asunto : $asunto." [$detalle]";
        
        $asunto_detelle .= $txt_grupos;
        
        return $asunto_detelle;
    }
    
    /**
     * Hay que gauradar dos objetos.
     * {@inheritDoc}
     * @see \entradas\model\entity\EntradaDB::DBGuardar()
     */
    public function DBGuardar() {
        // El tipo y fecha documento: (excepto si es nuevo)
        if (!empty($this->iid_entrada)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $oEntradaDocDB->setF_doc($this->df_doc,TRUE);
            $oEntradaDocDB->setTipo_doc($this->itipo_doc);
            $oEntradaDocDB->DBGuardar();
        }
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
        if (!empty($this->iid_entrada)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $this->df_doc = $oEntradaDocDB->getF_doc();
            $this->itipo_doc = $oEntradaDocDB->getTipo_doc();
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut df_doc de Entrada
     * de EntradaDocDB
     *
     * @return DateTimeLocal df_doc
     */
    function getF_documento() {
        if (!isset($this->df_doc) && !empty($this->iid_entrada)) {
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
     * Si df_doc es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
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
        if (!isset($this->itipo_doc) && !empty($this->iid_entrada)) {
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
    
    public function getNombreEscrito() {
    	$json_prot_local = $this->getJson_prot_origen();
    	// nombre del archivo
    	if (empty((array)$json_prot_local)) {
    		// genero un id: fecha
    		$f_hoy = date('Y-m-d');
    		$hora = date('His');
    		$this->nombre_escrito = $f_hoy.'_'._("E12")."($hora)";
    	} else {
    		$oProtOrigen = new Protocolo();
    		$oProtOrigen->setLugar($json_prot_local->lugar);
    		$oProtOrigen->setProt_num($json_prot_local->num);
    		$oProtOrigen->setProt_any($json_prot_local->any);
    		$oProtOrigen->setMas($json_prot_local->mas);
    		$this->nombre_escrito = $this->renombrar($oProtOrigen->ver_txt());
    	}
    	return $this->nombre_escrito;
    }
    
    public function getEtiquetasVisiblesArray($id_cargo='') {
    	$cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
    	$a_etiquetas = [];
    	foreach ($cEtiquetas as $oEtiqueta) {
    		$a_etiquetas[] = $oEtiqueta->getId_etiqueta();
    	}
    	return $a_etiquetas;
    }
    
    public function getEtiquetasVisiblesTxt($id_cargo='') {
    	$cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
    	$str_etiquetas = '';
    	foreach ($cEtiquetas as $oEtiqueta) {
    		$str_etiquetas .= empty($str_etiquetas)? '' : ', ';
    		$str_etiquetas .= $oEtiqueta->getNom_etiqueta();
    	}
    	return $str_etiquetas;
    }
    
    public function getEtiquetasVisibles($id_cargo='') {
    	if (empty($id_cargo)) {
    		$id_cargo = ConfigGlobal::role_id_cargo();
    	}
    	$gesEtiquetas = new GestorEtiqueta();
    	$cMisEtiquetas = $gesEtiquetas->getMisEtiquetas($id_cargo);
    	$a_mis_etiquetas = [];
    	foreach($cMisEtiquetas as $oEtiqueta) {
    		$a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
    	}
    	$gesEtiquetasEntrada = new GestorEtiquetaEntrada();
    	$aWhere = [ 'id_entrada' => $this->iid_entrada ];
    	$cEtiquetasEnt = $gesEtiquetasEntrada->getEtiquetasEntrada($aWhere);
    	$cEtiquetas = [];
    	foreach ($cEtiquetasEnt as $oEtiquetaEnt) {
    		$id_etiqueta = $oEtiquetaEnt->getId_etiqueta();
    		if (in_array($id_etiqueta, $a_mis_etiquetas)) {
    			$cEtiquetas[] = new Etiqueta($id_etiqueta);
    		}
    	}
    	
    	return $cEtiquetas;
    }
    
    public function getEtiquetas() {
    	$gesEtiquetasEntrada = new GestorEtiquetaEntrada();
    	$aWhere = [ 'id_entrada' => $this->iid_entrada ];
    	$cEtiquetasExp = $gesEtiquetasEntrada->getEtiquetasEntrada($aWhere);
    	$cEtiquetas = [];
    	foreach ($cEtiquetasExp as $oEtiquetaExp) {
    		$id_etiqueta = $oEtiquetaExp->getId_etiqueta();
    		$cEtiquetas[] = new Etiqueta($id_etiqueta);
    	}
    	
    	return $cEtiquetas;
    }
    
    public function setEtiquetas($aEtiquetas){
    	$this->delEtiquetas();
    	$a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
    	foreach ($a_filter_etiquetas as $id_etiqueta) {
    		$EtiquetaEntrada = new EtiquetaEntrada(['id_entrada' => $this->iid_entrada, 'id_etiqueta' => $id_etiqueta]);
    		$EtiquetaEntrada->DBGuardar();
    	}
    }
    
    public function delEtiquetas(){
    	$gesEtiquetasEntrada = new GestorEtiquetaEntrada();
    	if ($gesEtiquetasEntrada->deleteEtiquetasEntrada($this->iid_entrada) === FALSE) {
    		return FALSE;
    	}
    	return TRUE;
    }
    private function renombrar($string) {
    	//cambiar ' ' por '_':
    	$string1 = str_replace(' ', '_', $string);
    	//cambiar '/' por '_':
    	return str_replace('/', '_', $string1);
    }
    
    
}

