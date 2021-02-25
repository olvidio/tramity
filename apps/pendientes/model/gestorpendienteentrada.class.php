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
            $a_uid[] = $oCalendarItem->getUid();
        }
        return $a_uid;
    }
        
        
     /*   
        $this->resource = 'registro';
        $this->cargo = ConfigGlobal::role_actual();
        $oEntrada = new Entrada($id_entrada);
        $id_of_ponente = $oEntrada->getPonente();
        $oOficina = new Oficina($id_of_ponente);
        $oficina = $oOficina->getSigla();
        
        $this->cal_oficina="oficina_$oficina";
        $cargo = ConfigGlobal::role_id_cargo();
        
        $base_url = $this->getBaseUrl();
        $cargo = $this->cargo;
        $pass = 'system';
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        $uid = $this->getUid();
        $todo = $cal->GetEntryByUid($uid);
        
        
    }
    
    public function getBaseUrl($cal_oficina='') {
        $server =  $_SESSION['oConfig']->getServerDavical();
        $this->server = $server.'/caldav.php';
        
        $cal_oficina = empty($cal_oficina)? $this->cal_oficina : $cal_oficina;
        return 'http://'.$this->server."/".$cal_oficina."/".$this->resource."/";
    }
    */
}