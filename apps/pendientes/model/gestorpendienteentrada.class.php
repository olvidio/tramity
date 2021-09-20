<?php
namespace pendientes\model;

use davical\model\entity\GestorCalendarItem;
use entradas\model\GestorEntrada;

// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class GestorPendienteEntrada {
    /**
     * num_periodicos de Pendiente
     *
     * @var integer
     */
    private $num_periodicos;
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
    
    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function getPedientesByProtOrigen($a_prot) {
        $this->num_periodicos = 0;
        $this->num_pendientes = 0;
        $this->a_lista_pendientes = [];
        $this->pendientes_uid = '';
        
        $gesEntradas = new GestorEntrada();
        foreach ($a_prot as $aProt) {
            // buscar la entrada con esta ref. No tengo en cuenta el 'mas' para buscar la entrada.
            unset ($aProt['mas']);
            // No buscar si no hay número de protocoloa (solo nombre)
            if (empty($aProt['num'])) { continue; }
            if (empty($aProt['any'])) { continue; }
            $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt);
            $this->getInfoPendientes($cEntradas);
        }
        
        $a_params =  [
            'num_periodicos' => $this->num_periodicos,
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
                    if (empty($status) || $status == 'COMPLETED' || $status == 'CANCELLED') { continue; }
                    $rrule = $oPendiente->getRrule();
                    $this->num_periodicos += empty($rrule)? 0 : 1;
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