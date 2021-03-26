<?php
namespace expedientes\model;

use etherpad\model\Etherpad;
use expedientes\model\entity\Accion;
use expedientes\model\entity\EscritoAdjunto;
use expedientes\model\entity\EscritoDB;
use expedientes\model\entity\GestorAccion;
use expedientes\model\entity\GestorEscritoAdjunto;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use usuarios\model\PermRegistro;
use web\Protocolo;
use web\ProtocoloArray;



class Escrito Extends EscritoDB {
    /* CONST -------------------------------------------------------------- */
    // categoria, visibilidad, accion, modo_envio.
    
    // = entrada
    // categoria
    const CAT_E12          = 1;
    const CAT_NORMAL       = 2;
    const CAT_PERMANATE    = 3;
    // visibilidad
    // USAR LAS DE ENTRADADB
    
    // modo envio
    const MODO_MANUAL       = 1;
    const MODO_XML          = 2;
    // Accion
    const ACCION_PROPUESTA  = 1;
    const ACCION_ESCRITO    = 2;
    const ACCION_PLANTILLA  = 3;
    
    /* PROPIEDADES -------------------------------------------------------------- */
    /**
     *
     * @var string
     */
    private $destinos_txt;

    
    /* CONSTRUCTOR -------------------------------------------------------------- */
    
    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_escrito
     * 						$a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id='') {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach($a_id as $nom_id=>$val_id) {
                if (($nom_id == 'id_escrito') && $val_id !== '') $this->iid_escrito = (int)$val_id; // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_escrito = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_escrito' => $this->iid_escrito);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('escritos');
    }
    
    /**
     * Recupera l'atribut sasunto de Entrada teniendo en cuenta los permisos
     * Solo si está aprobado
     *
     * @return string sasunto
     */
    function getAsunto() {
        $asunto = '';
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this,'asunto');
        if ($perm > 0) {
            $asunto = $this->getAsuntoDB();
        }
        return $asunto;
    }
    
    /**
     * Recupera l'atribut sdetalle de Escrito teniendo en cuenta los permisos
     * Solo si está aprobado
     *
     * @return string sdetalle
     */
    function getDetalle() {
        $detalle = '';
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this,'detalle');
        if ($perm > 0) {
            $detalle = $this->getDetalleDB();
        }
        return $detalle;
    }
    
    /**
     * añadir el detalle en el asunto.
     * tener en cuenta los permisos...
     * 
     * return string
     */
    public function getAsuntoDetalle() {
        $detalle = $this->getDetalle();
        $asunto_detelle = empty($detalle)? $this->getAsunto() : $this->getAsunto()." [$detalle]";
        
        return $asunto_detelle;
    }
    /**
     * genera el número de protocolo local. y lo guarda.
     */
    public function generarProtocolo($id_lugar='',$id_lugar_cr='') {
        $gesLugares = new GestorLugar();
        if (empty($id_lugar_cr)) {
            $id_lugar_cr = $gesLugares->getId_cr();
        }
        if (empty($id_lugar)) {
            $id_lugar = $gesLugares->getId_sigla_local();
        }
        // segun si el destino es cr o resto:
        $bCr = FALSE;
        $aProtDst = $this->getJson_prot_destino();
        // es un array, pero sólo debería haber uno...
        foreach($aProtDst as $json_prot_destino) {
            if (count(get_object_vars($json_prot_destino)) == 0) {
                exit (_("Error no hay destino"));
            } else {
                $lugar = $json_prot_destino->lugar;
                if ($lugar == $id_lugar_cr) {
                    $bCr = TRUE;
                } else {
                    $bCr = FALSE;
                }
            }
        }
        $prot_num = $_SESSION['oConfig']->getContador($bCr);
        $prot_any = date('y');
        $prot_mas = '';
        
        $oProtLocal = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
        $prot_local = $oProtLocal->getProt();
        
        $this->DBCarregar();
        $this->setJson_prot_local($prot_local);
        $this->DBGuardar();
    }
    
    /**
     * Elimina el escrito, sus adjuntos y el texto (etherpad...)
     */
    public function eliminarTodo() {
        $txt_err = '';
        // Tipo de texto:
        if ($this->getTipo_doc() == self::TIPO_ETHERPAD) {
            $oEtherpad = new Etherpad();
            $oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid_escrito);
            $rta = $oEtherpad->eliminarPad();
            if (!empty($rta)) {
                $txt_err .= $rta;
            }
        }
        // adjuntos:
        $gesAdjuntos = new GestorEscritoAdjunto();
        $cAdjuntos = $gesAdjuntos->getEscritoAdjuntos(['id_escrito' => $this->iid_escrito]);
        foreach($cAdjuntos as $oAdjunto) {
            if ($oAdjunto->DBEliminar() === FALSE) {
                $txt_err .= _("No se ha podido eliminar un adjunto");
                $txt_err .= "<br>";
            }
        }
        // el propio escrito
        if (parent::DBEliminar() === FALSE) {
            $txt_err .= _("No se ha podido eliminar el escrito");
            $txt_err .= "<br>";
        }
        if (empty($txt_err)) {
            return TRUE;
        } else {
            return $txt_err; 
        }
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
    
    public function getArrayModoEnvio() {
        $a_tipos = [
            self::MODO_MANUAL => _("manual"),
            self::MODO_XML => _("xml"),
        ];
        
        return $a_tipos;
    }
    
    public function getArrayAccion() {
        $a_tipos = [
            self::ACCION_PROPUESTA => _("propuesta"),
            self::ACCION_PLANTILLA => _("plantilla"),
            self::ACCION_ESCRITO => _("escrito"),
        ];
        
        return $a_tipos;
    }
    
    public function getArrayIdAdjuntos(){
        $gesEscritoAdjuntos = new GestorEscritoAdjunto();
        return $gesEscritoAdjuntos->getArrayIdAdjuntos($this->iid_escrito);
    }
    
    public function cabeceraIzquierda() {
        // destinos + ref
        // destinos:
        $a_grupos = [];
        $destinos_txt = '';
        $id_dst = '';
        
        $a_grupos = $this->getId_grupos();
        if (!empty($a_grupos)) {
            //(según los grupos seleccionados)
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $descripcion_g = $oGrupo->getDescripcion();
                $destinos_txt .= empty($destinos_txt)? '' : ', ';
                $destinos_txt .= $descripcion_g;
            }
        } else {
            $a_json_prot_dst = $this->getJson_prot_destino();
            if (!empty((array)$a_json_prot_dst)) {
                $json_prot_dst = $a_json_prot_dst[0];
                $id_dst = $json_prot_dst->lugar;
            }
            //(segun individuales)
            $a_json_prot_dst = $this->getJson_prot_destino();
            $oArrayProtDestino = new ProtocoloArray($a_json_prot_dst,'','destinos');
            $destinos_txt = $oArrayProtDestino->ListaTxtBr();
        }
        // Si no hay ni grupos ni json, miro ids
        if (empty($destinos_txt)) {
            $descripcion_g = $this->getDescripcion();
            if (empty($descripcion_g)) {
                $a_id_lugar = $this->getDestinos();
                foreach ($a_id_lugar as $id_lugar) {
                    $oLugar = new Lugar($id_lugar);
                    $destinos_txt .= empty($destinos_txt)? '' : ', ';
                    $destinos_txt .= $oLugar->getSigla();
                }
            } else {
                $destinos_txt .= $descripcion_g;
            }
        }

        // referencias:
        $a_json_prot_ref = $this->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($a_json_prot_ref,'','referencias');
        $oArrayProtRef->setRef(TRUE);
        $aRef = $oArrayProtRef->ArrayListaTxtBr($id_dst);
        
        if (!empty($aRef['dst_org'])) {
            $destinos_txt .= '<br>';
            $destinos_txt .= $aRef['dst_org'];
        }
        return $destinos_txt;
    }
    
    public function getDestinosEscrito() {
        $a_grupos = [];
        $destinos_txt = '';
        
        // destinos individuales
        $json_prot_dst = $this->getJson_prot_destino();
        $oArrayProtDestino = new ProtocoloArray($json_prot_dst,'','destinos');
        $destinos_txt = $oArrayProtDestino->ListaTxtBr();
        // si hay grupos, tienen preferencia
        $a_grupos = $this->getId_grupos();
        if (!empty($a_grupos)) {
            //(segun los grupos seleccionados)
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $descripcion_g = $oGrupo->getDescripcion();
                $destinos_txt .= empty($destinos_txt)? '' : ', ';
                $destinos_txt .= $descripcion_g;
            }
        } else {
            // puede ser un destino personalizado:
            $destinos = $this->getDestinos();
            if (!empty($destinos)) {
                $destinos_txt = $this->getDescripcion();
            }
        }
        
        
        $this->destinos_txt = $destinos_txt;
        return $this->destinos_txt;
    }
    
    public function cabeceraDerecha() {
        // prot local + ref
        $id_dst = '';
        $a_json_prot_dst = $this->getJson_prot_destino();
        if (!empty((array)$a_json_prot_dst)) {
            $json_prot_dst = $a_json_prot_dst[0];
            $id_dst = $json_prot_dst->lugar;
        }
        
        // referencias
        $a_json_prot_ref = $this->getJson_prot_ref();
        $oArrayProtRef = new ProtocoloArray($a_json_prot_ref,'','referencias');
        $oArrayProtRef->setRef(TRUE);
        $aRef = $oArrayProtRef->ArrayListaTxtBr($id_dst);
        
        $json_prot_local = $this->getJson_prot_local();
        if (count(get_object_vars($json_prot_local)) == 0) {
            $err_txt = "No hay protocolo local";
            $_SESSION['oGestorErrores']->addError($err_txt,'generar PDF', __LINE__, __FILE__);
            $_SESSION['oGestorErrores']->recordar($err_txt);

            $origen_txt = $_SESSION['oConfig']->getSigla();
        } else {
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_local->lugar);
            $oProtOrigen->setProt_num($json_prot_local->num);
            $oProtOrigen->setProt_any($json_prot_local->any);
            $oProtOrigen->setMas($json_prot_local->mas);
            
            $origen_txt = $oProtOrigen->ver_txt();
        }
        
        if (!empty($aRef['local'])) {
            $origen_txt .= '<br>';
            $origen_txt .= $aRef['local'];
        }
        
        return $origen_txt;
    }
    
    public function generarPDF() {
        $a_header = [ 'left' => $this->cabeceraIzquierda(),
                    'center' => '',
                    'right' => $this->cabeceraDerecha(),
                  ];
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ESCRITO,$this->iid_escrito);
        
        $f_salida = $this->getF_salida()->getFromLocal('.');
        return $oEtherpad->generarPDF($a_header,$f_salida);
    }
    
    public function generarHtml() {
        $a_header = [ 'left' => $this->cabeceraIzquierda(),
                    'center' => '',
                    'right' => $this->cabeceraDerecha(),
                  ];
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ESCRITO,$this->iid_escrito);
        
        $f_salida = $this->getF_salida()->getFromLocal('.');
        return $oEtherpad->generarHtml($a_header,$f_salida);
    }
    
    public function explotar() {
        $aProtDst = [];
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid_escrito);
        $padID = $oEtherpad->getPadId();
        $txtPad = $oEtherpad->getTexto($padID);
        
        // Si esta marcado como grupo de destinos, o destinos individuales.
        $aMiembros = [];
        $a_grupos = [];
        $a_grupos = $this->getId_grupos();
        if (!empty($a_grupos)) {
            //(segun los grupos seleccionados) Los grupos no tienen número de protocolo
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $a_miembros_g = $oGrupo->getMiembros();
                $aMiembros = array_merge($aMiembros, $a_miembros_g);
            }
            $aMiembros = array_unique($aMiembros);
            
            $aProtDst = [];
            foreach ($aMiembros as $id_lugar) {
                $aProtDst[] = [
                            'lugar' => $id_lugar,
                            'num' => '',
                            'any' => '',
                            'mas' => '',
                        ];
            }
        } else {
            $aProtDst = $this->getJson_prot_destino();
        }
        
        // en el último destino, no lo creo nuevo sino que utilizo el 
        // de referencia. Lo hago con el último, porque si hay algun error,
        // pueda conservar el de referencia.
        $max = count($aProtDst);
        $n = 0;
        foreach($aProtDst as $oProtDst) {
            $n++;
            $aProt_dst = (array) $oProtDst;
            $aProtDestino[0] = [
                'lugar' => $aProt_dst['lugar'],
                'num' => $aProt_dst['num'],
                'any' => $aProt_dst['any'],
                'mas' => $aProt_dst['mas'],
            ];
            
            if ($n < $max) {
                $newEscrito = clone ($this);
                // borrar todos los destinos y poner solo uno:
                $newEscrito->setJson_prot_destino($aProtDestino);
                $newEscrito->setId_grupos();
                $newEscrito->DBGuardar();
                $newId_escrito = $newEscrito->getId_escrito();
                // asociarlo al expediente:
                $gesAcciones = new GestorAccion();
                $cAcciones = $gesAcciones->getAcciones(['id_escrito' => $this->iid_escrito]);
                if (!empty($cAcciones)) {
                    $id_expediente = $cAcciones[0]->getId_expediente();
                    $tipo_accion = $cAcciones[0]->getTipo_accion();
                    $oAccion = new Accion();
                    $oAccion->setId_expediente($id_expediente);
                    $oAccion->setTipo_accion($tipo_accion);
                    $oAccion->setId_escrito($newId_escrito);
                    $oAccion->DBGuardar();
                } else {
                    continue;
                }
                // canviar el id, y clonar el etherpad con el nuevo id
                $oNewEtherpad = new Etherpad();
                $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $newId_escrito);
                $oNewEtherpad->setText($txtPad);
                $oNewEtherpad->getPadId(); // Aqui crea el pad y utiliza el $txtPad
                
                // copiar los adjuntos
                $a_id_adjuntos = $this->getArrayIdAdjuntos();
                foreach (array_keys($a_id_adjuntos) as $id_item) {
                    $Adjunto = new EscritoAdjunto($id_item);
                    $Adjunto->DBCarregar();
                    $newAdjunto = clone ($Adjunto);
                    $newAdjunto->setId_escrito($newId_escrito);
                    $newAdjunto->DBGuardar();
                }
                
            } else {
                // En el último, no clono, aprovecho el escrito y 
                // sólo cambio los destinos:
                if (!empty($a_grupos)) { // si es por grupo
                    $this->setId_grupos();
                    $this->setDestinos();
                }
                $this->setJson_prot_destino($aProtDestino);
                $this->setId_grupos();
                $this->DBGuardar();
            }
        }
        return TRUE;
    }
}