<?php
namespace expedientes\model;

use core\ConfigGlobal;
use etherpad\model\Etherpad;
use expedientes\model\entity\EscritoDB;
use expedientes\model\entity\GestorEscritoAdjunto;
use web\Protocolo;
use expedientes\model\entity\EscritoAdjunto;
use lugares\model\entity\GestorLugar;



class Escrito Extends EscritoDB {
    
    /* CONST -------------------------------------------------------------- */
    // categoria, visibilidad, accion, modo_envio.
    
    // = entrada
    // categoria
    const CAT_E12          = 1;
    const CAT_NORMAL       = 2;
    const CAT_PERMANATE    = 3;
    // visibilidad
    const V_TODOS           = 1;  // cualquiera
    const V_PERSONAL        = 2;  // oficina y directores
    const V_RESERVADO       = 3;  // sólo directores
    const V_RESERVADO_VCD   = 4;  // sólo vcd + quien señale
    
    // modo envio
    const MODO_MANUAL       = 1;
    const MODO_XML          = 2;
    // Accion
    const ACCION_PROPUESTA  = 1;
    const ACCION_ESCRITO    = 2;
    const ACCION_PLANTILLA  = 3;
    
    /* PROPIEDADES -------------------------------------------------------------- */

    
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
     * genera el número de protocolo local. y lo guarda.
     */
    public function generarProtocolo($id_lugar='',$id_lugar_cr) {
        if (empty($id_lugar_cr)) {
            $sigla = 'cr';
            $gesLugares = new GestorLugar();
            $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
            $oLugar = $cLugares[0];
            $id_lugar_cr = $oLugar->getId_lugar();
        }
        if (empty($id_lugar)) {
            $sigla = $_SESSION['oConfig']->getSigla();
            $gesLugares = new GestorLugar();
            $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
            $oLugar = $cLugares[0];
            $id_lugar = $oLugar->getId_lugar();
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
    
    public function getArrayVisibilidad() {
        $a_tipos = [
            self::V_TODOS => _("todos"),
            self::V_PERSONAL => _("personal"),
            self::V_RESERVADO => _("reservado"),
            self::V_RESERVADO_VCD => _("vcd"),
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
    
    public function generarPDF() {
        
        $oProtDestino = new Protocolo();
        $oProtDestino->setNombre('destino');
        $oProtLocal = new Protocolo();
        $oProtLocal->setNombre('local');
        
        $json_prot_destino = $this->getJson_prot_destino();
        if (count(get_object_vars($json_prot_destino)) == 0) {
            //exit (_("Error no hay destino"));
        } else {
            $oProtDestino->setLugar($json_prot_destino->lugar);
            $oProtDestino->setProt_num($json_prot_destino->num);
            $oProtDestino->setProt_any($json_prot_destino->any);
            if (property_exists($json_prot_destino, 'mas')) {
                $oProtDestino->setMas($json_prot_destino->mas);
            }
        }
        
        $json_prot_local = $this->getJson_prot_local();
        if (count(get_object_vars($json_prot_local)) == 0) {
            // Todavía no está definido el protocolo local;
            $prot_local_txt = _("revisar");
        } else {
            $oProtLocal->setLugar($json_prot_local->lugar);
            $oProtLocal->setProt_num($json_prot_local->num);
            $oProtLocal->setProt_any($json_prot_local->any);
            if (property_exists($json_prot_local, 'mas')) {
                $oProtLocal->setMas($json_prot_local->mas);
            }
            $prot_local_txt = $oProtLocal->ver_txt();
        }
        
        $a_header = [ 'left' => $oProtDestino->ver_txt(),
                    'center' => '',
                    'right' => $prot_local_txt,
                  ];
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ESCRITO,$this->iid_escrito);
        
        $f_salida = $this->getF_salida()->getFromLocal('.');
        return $oEtherpad->generarPDF($a_header,$f_salida);
    }
    
    public function generarHtml() {
        
        $oProtDestino = new Protocolo();
        $oProtDestino->setNombre('destino');
        $oProtLocal = new Protocolo();
        $oProtLocal->setNombre('local');
        
        $json_prot_destino = $this->getJson_prot_destino();
        if (count(get_object_vars($json_prot_destino)) == 0) {
            //exit (_("Error no hay destino"));
        } else {
            $oProtDestino->setLugar($json_prot_destino->lugar);
            $oProtDestino->setProt_num($json_prot_destino->num);
            $oProtDestino->setProt_any($json_prot_destino->any);
            if (property_exists($json_prot_destino, 'mas')) {
                $oProtDestino->setMas($json_prot_destino->mas);
            }
        }
        
        $json_prot_local = $this->getJson_prot_local();
        if (count(get_object_vars($json_prot_local)) == 0) {
            // Todavía no está definido el protocolo local;
            $prot_local_txt = _("revisar");
        } else {
            $oProtLocal->setLugar($json_prot_local->lugar);
            $oProtLocal->setProt_num($json_prot_local->num);
            $oProtLocal->setProt_any($json_prot_local->any);
            if (property_exists($json_prot_local, 'mas')) {
                $oProtLocal->setMas($json_prot_local->mas);
            }
            $prot_local_txt = $oProtLocal->ver_txt();
        }
        
        $a_header = [ 'left' => $oProtDestino->ver_txt(),
                    'center' => '',
                    'right' => $prot_local_txt,
                  ];
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ESCRITO,$this->iid_escrito);
        
        $f_salida = $this->getF_salida()->getFromLocal('.');
        return $oEtherpad->generarHtml($a_header,$f_salida);
    }
}