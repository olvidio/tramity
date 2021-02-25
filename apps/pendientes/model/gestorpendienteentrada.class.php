<?php
namespace pendientes\model;

use core\Set;
use davical\model\CalDAVClient;
use entradas\model\Entrada;
use core\ConfigGlobal;
use usuarios\model\entity\Oficina;
use davical\model\entity\GestorCalendarItem;

// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class GestorPendienteEntrada {
    /**
     * server de Pendiente
     *
     * @var string
     */
    private $server;
    /**
     * resource de Pendiente
     *
     * @var string
     */
    private $resource;
    /**
     * cargo de Pendiente
     *
     * @var string
     */
    private $cargo;
    /**
     * cal_oficina de Pendiente
     *
     * @var string
     */
    private $cal_oficina;
    
    //$f_inicio,$f_plazo,$completed,$cancelled
    
    
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