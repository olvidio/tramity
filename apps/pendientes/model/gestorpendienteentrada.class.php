<?php
namespace pendientes\model;

use davical\model\entity\GestorCalendarItem;
use entradas\model\GestorEntrada;

// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class GestorPendienteEntrada {
    /**
     * num_pendientes de Pendiente
     *
     * @var integer
     */
    private $num_pendientes;
    /**
     * a_lista_pendientes de Pendiente
     *
     * @var array
     */
    private $a_lista_pendientes;
    /**
     * pendientes_uid de Pendiente
     *
     * @var string
     */
    private $pendientes_uid;
    
    
    /* CONSTRUCTOR -------------------------------------------------------------- */
    
    /**
     * Constructor de la classe.
     */
    /*
    function __construct($cal_oficina,$calendario,$cargo) {
        $this->cal_oficina = $cal_oficina;
        $this->resource = $calendario;
        $this->cargo = $cargo;
    }
    */
    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function getPedientesByRef($a_prot) {
        $this->num_pendientes = 0;
        $this->a_lista_pendientes = [];
        $this->pendientes_uid = '';
        
        $gesEntradas = new GestorEntrada();
        foreach ($a_prot as $aProt) {
            // buscar la entrada con esta ref.
            $cEntradas = $gesEntradas->getEntradasByRefDB($aProt);
            $this->getInfoPendientes($cEntradas);
        }
        
        $a_params =  [
            'num_pendientes' => $this->num_pendientes,
            'a_lista_pendientes' => $this->a_lista_pendientes,
            'pendientes_uid' => $this->pendientes_uid,
        ];
        
        return $a_params;
    }
    
    public function getPedientesByProtOrigen($a_prot) {
        $this->num_pendientes = 0;
        $this->a_lista_pendientes = [];
        $this->pendientes_uid = '';
        
        $gesEntradas = new GestorEntrada();
        foreach ($a_prot as $aProt) {
            // buscar la entrada con esta ref.
            $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt);
            $this->getInfoPendientes($cEntradas);
        }
        
        $a_params =  [
            'num_pendientes' => $this->num_pendientes,
            'a_lista_pendientes' => $this->a_lista_pendientes,
            'pendientes_uid' => $this->pendientes_uid,
        ];
        
        return $a_params;
    }
    
    /**
     * No devuelve nada porque actua directamente en las propiedades
     * 
     * @param array $cEntradas
     */
    private function getInfoPendientes($cEntradas) {
        foreach ($cEntradas as $oEntrada) {
            $id_entrada = $oEntrada->getId_entrada();
            $gesPendientes = new GestorPendienteEntrada();
            $cUids = $gesPendientes->getArrayUidById_entrada($id_entrada);
            if (!empty($cUids)) {
                $resource = 'registro';
                $cargo = 'secretaria';
                foreach ($cUids as $uid => $parent_container) {
                    $uid_container = "$uid#$parent_container";
                    $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
                    $status = $oPendiente->getStatus();
                    if ($status == 'COMPLETED' OR $status == 'CANCELLED') continue;
                    $this->num_pendientes++;
                    $this->a_lista_pendientes[] = $oPendiente->getAsunto();
                    $this->pendientes_uid .= empty($this->pendientes_uid)? $uid_container : ','.$uid_container;
                }
            }
        }
    }
    
    public function getArrayUidById_entrada($id_entrada) {
        $id_reg = 'REN'.$id_entrada; // REN = Regitro Entrada
        
        $gesCalendarItems = new GestorCalendarItem();
        $cCalItems = $gesCalendarItems->getCalendarItemsById_reg($id_reg);
        $a_uid = [];
        foreach ($cCalItems as $oCalendarItem) {
            $uid = $oCalendarItem->getUid();
            $dav_name = $oCalendarItem->getDav_name();
            // "/oficina_agd/registro/REN20-20210225T124453.ics"
            $pos = strpos($dav_name, '/', 1);
            $parent_container = substr($dav_name, 1, $pos - 1);
            $a_uid[$uid] = $parent_container;
        }
        return $a_uid;
    }
     
}