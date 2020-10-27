<?php
namespace expedientes\model;

use core\ConfigGlobal;
use etherpad\model\Etherpad;
use expedientes\model\entity\EscritoDB;
use expedientes\model\entity\GestorEscritoAdjunto;
use web\Protocolo;



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