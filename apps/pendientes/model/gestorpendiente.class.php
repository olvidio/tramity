<?php
namespace pendientes\model;

use core\Set;
use davical\model\CalDAVClient;

// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class GestorPendiente {
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
    function __construct($cal_oficina,$calendario,$cargo) {
        $this->cal_oficina = $cal_oficina;
        $this->resource = $calendario;
        $this->cargo = $cargo;
    }
    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function getBaseUrl($cal_oficina='') {
        $server_base =  $_SESSION['oConfig']->getServerDavical();
        $this->server = $server_base.'/caldav.php';
        
        $cal_oficina = empty($cal_oficina)? $this->cal_oficina : $cal_oficina;
        return $this->server."/".$cal_oficina."/".$this->resource."/";
    }
    
    public function getPendientes($aWhere) {
        
        $f_inicio = empty($aWhere['f_inicio'])? '19950101T000000Z' : $aWhere['f_inicio'];
        $f_plazo = empty($aWhere['f_plazo'])? date("Ymd\T230000\Z") : $aWhere['f_plazo'];
        $completed = empty($aWhere['completed'])? 'false' : $aWhere['completed'];
        $cancelled = empty($aWhere['cancelled'])? 'false' : $aWhere['cancelled'];
        
        $base_url = $this->getBaseUrl();
        $cargo = $this->cargo;
        $pass = 'system';
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        $events = $cal->GetTodos($f_inicio,$f_plazo,$completed,$cancelled);
        
        
        $oPendienteSet = new Set();
        $tt=0;
        foreach($events as $a_todo) {
            $tt++;
            $vcalendar[$tt] = new \iCalComponent($a_todo['data']);
        }
        
        for ($t=1;$t<=$tt;$t++) {
            $a_icalComp = $vcalendar[$t]->GetComponents('VTODO');
            if (empty($a_icalComp)) { continue; }
            $icalComp = $a_icalComp[0];  // If you know there's only 1 of them...
            
            $uid=$icalComp->GetPValue("UID");
            $oPendiente= new Pendiente($this->cal_oficina,$this->resource,$this->cargo,$uid);
            
            $asunto=$icalComp->GetPValue("SUMMARY");
            if ($asunto=="Busy") { continue; }
            $status=$icalComp->GetPValue("STATUS");
            $f_cal_acabado=$icalComp->GetPValue("COMPLETED");
            $f_cal_plazo=$icalComp->GetPValue("DUE");
            $f_cal_start=$icalComp->GetPValue("DTSTART");
            $f_cal_end=$icalComp->GetPValue("DTEND");
            $rrule=$icalComp->GetPValue("RRULE");
            $observ=$icalComp->GetPValue("DESCRIPTION");
            $class=$icalComp->GetPValue("CLASS");
            $detalle=$icalComp->GetPValue("COMMENT");
            $categorias=$icalComp->GetPValue("CATEGORIES");
            $encargado=$icalComp->GetPValue("ATTENDEE");
            $ref_prot_mas=$icalComp->GetPValue("X-DLB-REF-MAS");
            $pendiente_con=$icalComp->GetPValue("X-DLB-PENDIENTE-CON");
            $id_reg=$icalComp->GetPValue("X-DLB-ID-REG");
            $location=$icalComp->GetPValue("LOCATION");
            $oficinas=$icalComp->GetPValue("X-DLB-OFICINAS");

            $a_exdates = $vcalendar[$t]->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
            
            $visibilidad = $oPendiente->Class_to_visibilidad($class);
            
            $oPendiente->setAsunto($asunto);
            $oPendiente->setStatus($status);
            $oPendiente->setF_acabado($f_cal_acabado);
            $oPendiente->setF_plazo($f_cal_plazo);
            $oPendiente->setF_inicio($f_cal_start);
            $oPendiente->setF_end($f_cal_end);
            $oPendiente->setRrule($rrule);
            $oPendiente->setObserv($observ);
            $oPendiente->setVisibilidad($visibilidad);
            $oPendiente->setDetalle($detalle);
            $oPendiente->setCategorias($categorias);
            $oPendiente->setEncargado($encargado);
            $oPendiente->setRef_prot_mas($ref_prot_mas);
            $oPendiente->setPendiente_con($pendiente_con);
            $oPendiente->setId_reg($id_reg);
            $oPendiente->setLocation($location);
            $oPendiente->setOficinas($oficinas);
            $oPendiente->setExdates($a_exdates);
            
            // No sirve, mirar aquí la rrule, porque tiene el mismo uid, y por tanto es el mismo pendiente 
            $oPendienteSet->add($oPendiente);
        }
        return $oPendienteSet->getTot();
    }
    

}