<?php
namespace pendientes\model;

use core\Set;
use function core\fecha_sin_time;
use function core\recurrencias;
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
        $server =  $_SESSION['oConfig']->getServerDavical();
        $this->server = $server.'/caldav.php';
        
        $cal_oficina = empty($cal_oficina)? $this->cal_oficina : $cal_oficina;
        return 'http://'.$this->server."/".$cal_oficina."/".$this->resource."/";
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
        //print_r($events);
        
        
        $oPendienteSet = new Set();
        $tt=0;
        foreach($events as $k1=>$a_todo) {
            $tt++;
            $vcalendar[$tt] = new \iCalComponent($a_todo['data']);
        }
        
        $recur=$tt;
        for ($t=1;$t<=$tt;$t++) {
            //print_r($vcalendar[$t]);
            
            $icalComp = $vcalendar[$t]->GetComponents('VTODO');
            $icalComp = $icalComp[0];  // If you know there's only 1 of them...
            
            $uid=$icalComp->GetPValue("UID");
            $oPendiente= new Pendiente($this->cal_oficina,$this->resource,$this->cargo,$uid);
            
            $asunto=$icalComp->GetPValue("SUMMARY");
            if ($asunto=="Busy") continue;
            $status=$icalComp->GetPValue("STATUS");
            $f_cal_acabado=$icalComp->GetPValue("COMPLETED");
            $f_cal_plazo=$icalComp->GetPValue("DUE");
            $f_cal_start=$icalComp->GetPValue("DTSTART");
            $f_cal_end=$icalComp->GetPValue("DTEND");
            $rrule=$icalComp->GetPValue("RRULE");
            $observ=$icalComp->GetPValue("DESCRIPTION");
            $visibilidad=$icalComp->GetPValue("CLASS");
            $detalle=$icalComp->GetPValue("COMMENT");
            $categorias=$icalComp->GetPValue("CATEGORIES");
            $encargado=$icalComp->GetPValue("ATTENDEE");
            $ref_prot_mas=$icalComp->GetPValue("X-DLB-REF-MAS");
            $pendiente_con=$icalComp->GetPValue("X-DLB-PENDIENTE-CON");
            $id_reg=$icalComp->GetPValue("X-DLB-ID-REG");
            $ref=$icalComp->GetPValue("LOCATION");
            $oficinas=$icalComp->GetPValue("X-DLB-OFICINAS");

            $a_exdates = $vcalendar[$t]->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
            
            if ($visibilidad=="CONFIDENTIAL") {
                $visibilidad="t";
            } else {
                $visibilidad="f";
            }
            
            $oPendiente->setAsunto($asunto);
            $oPendiente->setStatus($status);
            $oPendiente->setF_acabado($f_cal_acabado);
            $oPendiente->setF_plazo($f_cal_plazo);
            $oPendiente->setF_inicio($f_cal_start);
            //$oPendiente->setF_fin($f_cal_end);
            $oPendiente->setRrule($rrule);
            $oPendiente->setObserv($observ);
            $oPendiente->setVisibilidad($visibilidad);
            $oPendiente->setDetalle($detalle);
            $oPendiente->setCategorias($categorias);
            $oPendiente->setEncargado($encargado);
            $oPendiente->setRef_prot_mas($ref_prot_mas);
            $oPendiente->setPendiente_con($pendiente_con);
            $oPendiente->setId_reg($id_reg);
            //$oPendiente->setRef($ref);
            $oPendiente->setOficinas($oficinas);
            $oPendiente->setExdates($a_exdates);
            
            if (!empty($rrule)) {
                // calcular las recurrencias que tocan.
                $dtstart=$icalComp->GetPValue("DTSTART");
                $dtend=$icalComp->GetPValue("DTEND");
                $a_exdates = $vcalendar[$t]->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
                $f_recurrentes=recurrencias($rrule,$dtstart,$dtend,$f_plazo);
                //print_r($f_recurrentes);
                foreach ($f_recurrentes as $f_recur => $fecha) {
                    $recur++;
                    // Quito las excepciones.
                    if (is_array($a_exdates) ){
                        foreach ($a_exdates as $icalprop) {
                            // si hay más de uno separados por coma
                            $a_fechas=preg_split('/,/',$icalprop->content);
                            foreach ($a_fechas as $f_ex) {
                                fecha_sin_time($f_ex); //quito la THHMMSSZ
                                if ($f_recur==$f_ex)  continue(3);
                            }
                        }
                    }
                    $oPendiente->setF_recur($f_recur);
                    $oPendienteSet->add($oPendiente);
                }
            } else {
                $oPendienteSet->add($oPendiente);
            }
        }
        return $oPendienteSet->getTot();
    }
    

}