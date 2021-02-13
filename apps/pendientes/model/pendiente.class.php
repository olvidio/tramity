<?php
namespace pendientes\model;

use core\Converter;
use function core\any_2;
use function core\fecha_sin_time;
use davical\model\CalDAVClient;
use entradas\model\GestorEntrada;
use pendientes\model\entity\PendienteDB;
use usuarios\model\PermRegistro;
use usuarios\model\entity\Cargo;
use web\NullDateTimeLocal;
use web\Protocolo;
use entradas\model\Entrada;
use Twig\TokenParser\EmbedTokenParser;
use usuarios\model\entity\GestorOficina;

// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class Pendiente { 

    /* CONST -------------------------------------------------------------- */
    
    // status No hace falta definir constantes, porque el valor es el mismo nombre.
    /*
    "NEEDS-ACTION"
    "COMPLETED"
    "IN-PROCESS"
    "CANCELLED"
    */
    
    /* PROPIEDADES -------------------------------------------------------------- */
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
     * passwd de Pendiente
     *
     * @var string
     */
    private $passwd;
    
    /**
     * parent_container de Pendiente
     *
     * @var string
     */
    private $parent_container;
    
    /**
     * bLoaded de PendienteDB
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    
    
    /* ATRIBUTS ----------------------------------------------------------------- */
    
    private $uid;

    private $id_reg;
    private $asunto;
    private $status;
    private $f_inicio;
    private $f_acabado;
    private $f_plazo;
    private $ref_prot_mas;
    private $location;
    private $observ;
    private $visibilidad;
    private $detalle;
    private $categorias;
    private $encargado;
    private $pendiente_con;
    private $oficinas;
    private $id_oficina;
    private $rrule;
    private $exdates;

    private $f_recur;

    
/* CONSTRUCTOR -------------------------------------------------------------- */
    
    /**
     * Constructor de la classe.
     */
    function __construct($parent_container,$resource,$cargo,$uid) {
        
        $this->setParent_container($parent_container);
        $this->setResource($resource);
        $this->setCargo($cargo);
        $this->setUid($uid);
        $this->setPasswd('system');
    }
    
    /* METODES PUBLICS ----------------------------------------------------------*/

    static public function getArrayStatus() {
        $a_tipos = [
            "NEEDS-ACTION" => _("iniciado"),
            "COMPLETED" =>  _("acabado"),
            "IN-PROCESS" =>  _("en proceso"),
            "CANCELLED" =>  _("cancelado"),
        ];
        
        return $a_tipos;
    }

    public function getBaseUrl($parent_container='') {
        $this->server = 'davical.local/caldav.php';
        
        $parent_container = empty($parent_container)? $this->parent_container : $parent_container;
        return 'http://'.$this->server."/".$parent_container."/".$this->resource."/";
    }
    
    public function getProtocoloOrigen() {
        $uid = $this->getUid();
        $ref=$this->buscar_ref_uid($uid,"array");
        /*
        $ref_mas=$this->getRef_prot_mas();
        if (!empty($ref_mas)) $ref.=", ".$ref_mas;
        $pendiente_con=$this->getPendiente_con();
        if (!empty($pendiente_con)) {
            
            //$ref=$a_lugares[$pendiente_con]." ($ref)";
        }
        */
        return $ref;
    }
    
    public function getReferencias() {
        $uid = $this->getUid();
        $ref=$this->buscar_ref_uid($uid,"txt");
        $ref_mas=$this->getRef_prot_mas();
        if (!empty($ref_mas)) $ref.=", ".$ref_mas;
        $pendiente_con=$this->getPendiente_con();
        if (!empty($pendiente_con)) {
            
            //$ref=$a_lugares[$pendiente_con]." ($ref)";
        }
        return $ref;
    }
    
    
    public function buscar_ref_uid($uid,$formato) {
        if (($pos_ini = strpos($uid, 'REN')) !== FALSE && $pos_ini == 0) { //  Registro entradas
            $pos = strpos($uid, '-') - 3;
            $id_reg=substr($uid,3,$pos);
            //echo "ref: $id_reg<br>";
            // Buscar en entradas
            $oProtOrigen = new Protocolo();
            $gesEntradas = new GestorEntrada();
            $aWhere = ['id_entrada' => $id_reg];
            $cEntradas = $gesEntradas->getEntradas($aWhere);
            if (!empty($cEntradas)) {
                $oEntrada = $cEntradas[0];
                $oProtOrigen->setJson($oEntrada->getJson_prot_origen());

                if($formato=="txt") {
                    $ref = $oProtOrigen->ver_txt();
                }
                if($formato=="object"){
                    $ref = $oProtOrigen->getProt();
                }
                if($formato=="array"){
                    $ref = (array) $oProtOrigen->getProt();
                }
                return $ref;
            }
        }
            
        if (($pos_ini = strpos($uid, 'RES')) !== FALSE && $pos_ini == 0) { //  Registro escritos
            /*
            } else {
                // No es una entrada, será una aprobación.
                $sql_ref="SELECT prot_num ,prot_any
					FROM escritos
					WHERE id_reg=$id_reg
					";
                $oDBRSt_q2=$oDBR->query($sql_ref);
                if ($oDBRSt_q2->rowCount()) {
                    $referencias="";
                    $row=$oDBRSt_q2->fetch(\PDO::FETCH_ASSOC);
                    extract($row);
                    $prot_any=any_2($prot_any);
                    if($formato=="txt") { $ref=ConfigGlobal::$dele." ".$prot_num."/".$prot_any; }
                    if($formato=="array"){ $ref['id_lugar']=$id_lugar; $ref['sigla']=ConfigGlobal::$dele; $ref['num']=$prot_num; $ref['any']=$prot_any; }
                    return $ref;
                } else {
                    return _("referencia a un escrito eliminado");
                }
            }
            */
            
        }

        if (($pos_ini = strpos($uid, 'RC')) !== FALSE && $pos_ini == 0) { //  Registro cancillería
            $pos = strpos($uid, '-') - 2;
            $id_reg=substr($uid,2,$pos);
            //echo "ref: $id_reg<br>";
            $sql_ref="SELECT origen, origen_num ,origen_any
				FROM cancilleria_escritos
				WHERE id_reg=$id_reg
				";
            //echo "sql: $sql_ref<br>";
            $oDBRSt_q2=$oDBR->query($sql_ref);
            if ($oDBRSt_q2->rowCount()) {
                $referencias="";
                $row=$oDBRSt_q2->fetch(\PDO::FETCH_ASSOC);
                extract($row);
                $origen_any=any_2($origen_any);
                if($formato=="txt") { $ref=$origen." ".$origen_num."/".$origen_any; }
                if($formato=="array"){ $ref['id_lugar']=$origen; $ref['sigla']=$origen; $ref['num']=$origen_num; $ref['any']=$origen_any; }
                return $ref;
            } else {
                return _("referencia a un escrito eliminado");
            }
            
        }
        // en cualquier otro caso.
        return;
    }
    
    
    
    
    
    /**
     * crea un pendiente en davical con  los datos existentes en la tabla (PendienteDB)
     * y despues lo elimina de la DB.
     * 
     * @param string $id_reg
     * @param integer $id_pendiente
     */
    public function crear_de_pendienteDB($id_reg,$id_pendiente) {

        $oPendienteDB = new PendienteDB($id_pendiente);	

        $aDades['asunto'] = $oPendienteDB->getAsunto();
        $aDades['status'] = $oPendienteDB->getStatus();
        $aDades['f_acabado'] = $oPendienteDB->getF_acabado();
        $aDades['f_plazo'] = $oPendienteDB->getF_plazo();
        $aDades['ref_mas'] = $oPendienteDB->getRef_mas();
        $aDades['observ'] = $oPendienteDB->getObserv();
        $aDades['visibilidad'] = $oPendienteDB->getVisibilidad();
        $aDades['detalle'] = $oPendienteDB->getDetalle();
        $aDades['categorias'] = $oPendienteDB->getEtiquetas();
        $aDades['encargado'] = $oPendienteDB->getEncargado();
        $aDades['pendiente_con'] = $oPendienteDB->getPendiente_con();
        $aDades['oficinas'] = $oPendienteDB->getOficinas();
        //$aDades['id_oficina'] = $oPendienteDB->setId_oficina();
        $aDades['rrule'] = $oPendienteDB->getRrule();
        $aDades['f_inicio'] = $oPendienteDB->getF_inicio();
        
        $aDades['id_reg'] = $id_reg;
        
        $this->ins_pendiente($aDades);
        
        $oPendienteDB->DBEliminar();
    }
   

    private function ins_pendiente($aDades) {
        $id_reg = empty($aDades['id_reg'])? '' :$aDades['id_reg'];
        $asunto = empty($aDades['asunto'])? '' :$aDades['asunto'];
        $status = empty($aDades['status'])? '' :$aDades['status'];
        $f_inicio = empty($aDades['f_inicio'])? '' :$aDades['f_inicio'];
        $f_acabado = empty($aDades['f_acabado'])? '' :$aDades['f_acabado'];
        $f_plazo = empty($aDades['f_plazo'])? '' :$aDades['f_plazo'];
        $ref_prot_mas = empty($aDades['ref_prot_mas'])? '' :$aDades['ref_prot_mas'];
        $location = empty($aDades['location'])? '' :$aDades['location'];
        $observ = empty($aDades['observ'])? '' :$aDades['observ'];
        $visibilidad = empty($aDades['visibilidad'])? '' :$aDades['visibilidad'];
        $detalle = empty($aDades['detalle'])? '' :$aDades['detalle'];
        $categorias = empty($aDades['categorias'])? '' :$aDades['categorias'];
        $encargado = empty($aDades['encargado'])? '' :$aDades['encargado'];
        $pendiente_con = empty($aDades['pendiente_con'])? '' :$aDades['pendiente_con'];
        $oficinas = empty($aDades['oficinas'])? '' :$aDades['oficinas'];
        $rrule = empty($aDades['rrule'])? '' :$aDades['rrule'];
        $exdates = empty($aDades['exdates'])? '' :$aDades['exdates'];
        
        $base_url = $this->getBaseUrl();
        $cargo = $this->getCargo();
        $pass = $this->getPasswd();
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        if (!empty($f_acabado)) {
            $oConverter = new Converter('date', $f_acabado);
            $f_cal_acabado = $oConverter->toCal();
        }

        if (!empty($rrule)) {
            if (empty($f_inicio)) { $f_inicio=date("d/m/Y"); }
            $f_plazo=$f_inicio;
        } else {
            $f_inicio=$f_plazo;
        }
        
        if (!empty($f_inicio)) {
            $oConverter = new Converter('date', $f_inicio);
            $f_cal_inicio = $oConverter->toCal();
        }
        if (!empty($f_plazo)) {
            $oConverter = new Converter('date', $f_plazo);
            $f_cal_plazo = $oConverter->toCal();
        }

        $ahora=date("Ymd\THis");
        // Generar el uid en función de si tiene id_reg o no:
        if (!empty($id_reg)) {
            // si vengo de entradas ya he puesto 'REN' al inicio
            if (($pos_ini = strpos($id_reg, 'REN')) !== FALSE && $pos_ini == 0) { //  Registro entradas
                $uid = "$id_reg-$ahora";
            } else {
                $uid = "R$id_reg-$ahora";
            }
        } else {
            $uid="$ahora";
        }
        $uid.="@registro_$this->parent_container";
        // Amb caldav.
        $args['UID']="$uid";
        $args['SUMMARY']="$asunto";
        $args['STATUS']="$status";
        if (!empty($f_cal_acabado)) {
            $args['COMPLETED']=$f_cal_acabado;
            $args['STATUS']="COMPLETED";
        }
        $args['CREATED']=$ahora;
        if (!empty($f_plazo)) $args['DUE']=$f_cal_plazo;
        if (!empty($f_cal_inicio))  { $args['DTSTART']=$f_cal_inicio; } else { $args['DTSTART']=$f_cal_plazo; }
        if (!empty($rrule)) $args['RRULE']="$rrule";
        if (!empty($observ)) $args['DESCRIPTION']="$observ";
        if (!empty($visibilidad)) $args['CLASS']="$visibilidad"; // property name - case independt
        if (!empty($detalle)) $args['COMMENT']="$detalle";
        if (!empty($categorias)) $args['CATEGORIES']="$categorias";
        if (!empty($encargado)) $args['ATTENDEE']="$encargado";
        if (!empty($ref_prot_mas)) $args['X-DLB-REF-MAS']="$ref_prot_mas";
        if (!empty($location)) $args['LOCATION']="$location";
        if (!empty($pendiente_con)) $args['X-DLB-PENDIENTE-CON']="$pendiente_con";
        if (!empty($id_reg)) {
            $args['X-DLB-ID-REG']="$id_reg";
            
            $ref=$this->buscar_ref_uid($uid,"txt");
            if (!empty($ref_prot_mas)) $ref.=", ".$ref_prot_mas;
            $args['LOCATION']="$ref";
        }
        
        if (!empty($oficinas)){
            $args['X-DLB-OFICINAS']="$oficinas";
        }

        $icalComp=new \iCalComponent();
        $icalComp->SetType('VTODO');
        if (is_array($exdates)) {
            foreach($exdates as $f_exdate) {
                if (!empty($f_exdate)) {
                    $new_prop = new \iCalProp();
                    $new_prop->Name('EXDATE');
                    $new_prop->Value($f_exdate);
                    $a_exdates[]=$new_prop;
                }
            }
            $icalComp->SetProperties($a_exdates,'EXDATE');
        } else {
            $args['EXDATE']="$exdates";
        }

        foreach($args as $new_property => $value) {
          $new_prop = new \iCalProp();
          $new_prop->Name($new_property);
          $new_prop->Value($value);
          $a_properties[]=$new_prop;
        }

        $icalComp->SetProperties($a_properties);

        $vcalendar=new \iCalComponent();
        $vcalendar->VCalendar();
        $vcalendar->AddComponent($icalComp);

        $icalendar=$vcalendar->Render();
        
        $uid2=strtok($uid,'@');
        $nom_fichero="$uid2.ics";
        $rta=$cal->DoPUTRequest( $base_url.$nom_fichero,$icalendar,'*');
        if (strlen($rta) > 32 ) print_r($rta);

    }

    /**
     * cambia la propiedad de STATUS en Davical en función del parámetro que se le pasa:
     *  'contestado' => COMPLETED
     *  'cancelar'  => CANCELLED
     *  'eliminar'  => De hecho lo elimina del davical.
     * 
     * @param string $que contestado|cancelar|eliminar
     */
    public function marcar_contestado($que) {
        $todo = $this->getTodoByUid();
        $etag = $todo[0]['etag'];
        
        // OJO! El nombre no puede contener la '@'.
        $uid = $this->getUid();
        $uid2=strtok($uid,'@');
        $nom_fichero="$uid2.ics";

        $base_url = $this->getBaseUrl();
        $cargo = $this->getCargo();
        $pass = $this->getPasswd();
        $cal = new CalDAVClient($base_url, $cargo, $pass);

        // cambio el status y completed.
        $vcalendar = new \iCalComponent($todo[0]['data']);
        $icalComp = $vcalendar->GetComponents('VTODO');
        switch ($que) {
            case "contestado":
                $ahora=date("Ymd\THis");
                $new_prop = new \iCalProp();
                $new_prop->Name('COMPLETED');
                $new_prop->Value($ahora);
                $icalComp[0]->SetProperties(array($new_prop),'COMPLETED');
                $new_prop = new \iCalProp();
                $new_prop->Name('STATUS');
                $new_prop->Value('COMPLETED');
                $icalComp[0]->SetProperties(array($new_prop),'STATUS');
            break;
            case "cancelar":
                $new_prop = new \iCalProp();
                $new_prop->Name('STATUS');
                $new_prop->Value('CANCELLED');
                $icalComp[0]->SetProperties(array($new_prop),'STATUS');
            break;
            case "eliminar":
                $cal->DoDELETERequest( $base_url.$nom_fichero, $etag );
            break;
        }
        if ($que != "eliminar") {
            $vcalendar->SetComponents($icalComp); // OJO, le paso el array de objetos.
            $icalendar=$vcalendar->Render();
            //print_r($icalendar);
            //echo "<br>nom: $nom_fichero<br>cal: $icalendar<br>tag: $etag<br>";
            $rta = $cal->DoPUTRequest( $base_url.$nom_fichero,$icalendar,$etag);
            if (strlen($rta) > 32 ) print_r($rta);
        }
    }

    public function marcar_excepcion($f_recur) {
        $todo = $this->getTodoByUid();
        $etag = $todo[0]['etag'];
        
        // OJO! El nombre no puede contener la '@'.
        $uid = $this->getUid();
        $uid2=strtok($uid,'@');
        $nom_fichero="$uid2.ics";
        
        $base_url = $this->getBaseUrl();
        $cargo = $this->getCargo();
        $pass = $this->getPasswd();
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        $vcalendar = new \iCalComponent($todo[0]['data']);
        $icalComp = $vcalendar->GetComponents('VTODO');
        $exdates = $vcalendar->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
        if (is_array($exdates)) {
            // me aseguro que no está repetida
            $repe=0;
            foreach ($exdates as $iCalProp) {
                // si hay más de uno separados por coma
                $a_fechas=preg_split('/,/',$iCalProp->content);
                foreach ($a_fechas as $f_ex) {
                    fecha_sin_time($f_ex); //quito la THHMMSSZ
                    if ($f_recur==$f_ex) $repe=1;
                }
            }
            if (empty($repe)) {
                $icalComp[0]->AddProperty('EXDATE',$f_recur);
            }
        }

        $vcalendar->SetComponents($icalComp); // OJO, le paso el array de objetos.
        $icalendar=$vcalendar->Render();
        //print_r($icalendar);
        //echo "<br>nom: $nom_fichero<br>cal: $icalendar<br>tag: $etag<br>";
        $rta=$cal->DoPUTRequest( $base_url.$nom_fichero,$icalendar,$etag);
        if (strlen($rta) > 32 ) print_r($rta);
    }

    private function update_pendiente($uid,$aDades) {
        $id_reg = empty($aDades['id_reg'])? '' :$aDades['id_reg'];
        $asunto = empty($aDades['asunto'])? '' :$aDades['asunto'];
        $status = empty($aDades['status'])? '' :$aDades['status'];
        $f_inicio = empty($aDades['f_inicio'])? '' :$aDades['f_inicio'];
        $f_acabado = empty($aDades['f_acabado'])? '' :$aDades['f_acabado'];
        $f_plazo = empty($aDades['f_plazo'])? '' :$aDades['f_plazo'];
        $ref_prot_mas = empty($aDades['ref_prot_mas'])? '' :$aDades['ref_prot_mas'];
        $location = empty($aDades['location'])? '' :$aDades['location'];
        $observ = empty($aDades['observ'])? '' :$aDades['observ'];
        $visibilidad = empty($aDades['visibilidad'])? '' :$aDades['visibilidad'];
        $detalle = empty($aDades['detalle'])? '' :$aDades['detalle'];
        $categorias = empty($aDades['categorias'])? '' :$aDades['categorias'];
        $encargado = empty($aDades['encargado'])? '' :$aDades['encargado'];
        $pendiente_con = empty($aDades['pendiente_con'])? '' :$aDades['pendiente_con'];
        $oficinas = empty($aDades['oficinas'])? '' :$aDades['oficinas'];
        $rrule = empty($aDades['rrule'])? '' :$aDades['rrule'];
        $exdates = empty($aDades['exdates'])? '' :$aDades['exdates'];
        
        $base_url = $this->getBaseUrl();
        $cargo = $this->getCargo();
        $pass = $this->getPasswd();
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        $todo = $this->getTodoByUid();
        $etag=$todo[0]['etag'];

        if (!empty($f_acabado)) {
            $oConverter = new Converter('date', $f_acabado);
            $f_cal_acabado = $oConverter->toCal();
        }

        if (!empty($rrule)) {
            if (empty($f_inicio)) { $f_inicio=date("d/m/Y"); }
            $f_plazo=$f_inicio;
        } else {
            $f_inicio=$f_plazo;
        }
        
        if (!empty($f_inicio)) {
            $oConverter = new Converter('date', $f_inicio);
            $f_cal_inicio = $oConverter->toCal();
        }
        if (!empty($f_plazo)) {
            $oConverter = new Converter('date', $f_plazo);
            $f_cal_plazo = $oConverter->toCal();
        }
        $args['SUMMARY']="$asunto";
        $args['STATUS']="$status";
        if ($status=="COMPLETED") {
            $args['STATUS']="COMPLETED";
            $args['COMPLETED']=$f_cal_acabado;
        }
        
        $args['DUE']=$f_cal_plazo;
        if (!empty($f_cal_inicio))  { $args['DTSTART']=$f_cal_inicio; } else { $args['DTSTART']=$f_cal_plazo; }
        $args['RRULE']="$rrule";
        $args['DESCRIPTION']="$observ";
        $args['CLASS']="$visibilidad"; // property name - case independt
        $args['COMMENT']="$detalle";
        $args['CATEGORIES']="$categorias";
        $args['ATTENDEE']="$encargado";
        $args['X-DLB-REF-MAS']="$ref_prot_mas";
        $args['X-DLB-PENDIENTE-CON']="$pendiente_con";
        
        if (!empty($id_reg)) {
            $args['X-DLB-ID-REG']="$id_reg";
            
            $ref=$this->buscar_ref_uid($uid,"txt");
            if (!empty($ref_prot_mas)) $ref.=", ".$ref_prot_mas;
            $args['LOCATION']="$ref";
        } else {
            $args['X-DLB-ID-REG']="";
            $args['LOCATION']= empty($location)? '' : "**$location"; //by the moment
        }
        if (!empty($oficinas)){
            $args['X-DLB-OFICINAS']="$oficinas";
        } else {
            $args['X-DLB-OFICINAS']="";
        }
        
        // Sólo cambio las propiedades que vienen del formulario, el resto no las toco.
        $vcalendar = new \iCalComponent($todo[0]['data']);
        $icalComp = $vcalendar->GetComponents('VTODO');
        if (is_array($exdates)) {
            foreach($exdates as $f_exdate) {
                if (!empty($f_exdate)) {
                    $new_prop = new \iCalProp();
                    $new_prop->Name('EXDATE');
                    $new_prop->Value($f_exdate);
                    $a_exdates[]=$new_prop;
                }
            }
            if (!empty($a_exdates)) {
                $icalComp[0]->SetProperties($a_exdates,'EXDATE');
            } else {
                $icalComp[0]->ClearProperties('EXDATE');
            }
        } else {
            $args['EXDATE']="$exdates";
        }
        foreach($args as $new_property => $value) {
            $new_prop = new \iCalProp();
            $new_prop->Name($new_property);
            $new_prop->Value($value);
            if (!empty($value)) {
                $icalComp[0]->SetProperties(array($new_prop),"$new_property");
            }else {
                $icalComp[0]->ClearProperties("$new_property");
            }
        }
         $vcalendar->SetComponents($icalComp); // OJO, le paso el array de objetos.
         $icalendar=$vcalendar->Render();
        //print_r($cal);

        // OJO! El nombre no puede contener la '@'.
        $uid2=strtok($uid,'@');
        $nom_fichero="$uid2.ics";
        //echo "DoPUTRequest( $nom_fichero,$icalendar,$etag)<br>";
        $rta=$cal->DoPUTRequest( $base_url.$nom_fichero,$icalendar,$etag);
        if (strlen($rta) > 32 ) print_r($rta);
    }

    
    
    /**
     * Desa els atributs de l'objecte 
     *
     */
    public function Guardar() {
        if (($uid=$this->getUid()) === FALSE) { $bInsert=TRUE; } else { $bInsert=FALSE; }
        $aDades = [];
        
        $aDades['id_reg'] = $this->id_reg;
        $aDades['asunto'] = $this->asunto;
        $aDades['status'] = $this->status;
        $aDades['f_inicio'] = $this->f_inicio;
        $aDades['f_acabado'] = $this->f_acabado;
        $aDades['f_plazo'] = $this->f_plazo;
        $aDades['ref_prot_mas'] = $this->ref_prot_mas;
        $aDades['location'] = $this->location;
        $aDades['observ'] = $this->observ;
        $aDades['visibilidad'] = $this->visibilidad;
        $aDades['detalle'] = $this->detalle;
        $aDades['categorias'] = $this->categorias;
        $aDades['encargado'] = $this->encargado;
        $aDades['pendiente_con'] = $this->pendiente_con;
        $aDades['oficinas'] = $this->oficinas;
        $aDades['rrule'] = $this->rrule;
        $aDades['exdates'] = $this->exdates;


        if ($bInsert === FALSE) {
            //UPDATE
            $this->update_pendiente($uid,$aDades);
        } else {
            // INSERT
            $this->ins_pendiente($aDades);
        }
    
    }
    
    public function getTodoByUid() {
        $base_url = $this->getBaseUrl();
        $cargo = $this->getCargo();
        $pass = $this->getPasswd();
        $cal = new CalDAVClient($base_url, $cargo, $pass);
        
        $uid = $this->getUid();
        $todo = $cal->GetEntryByUid($uid);
        
        return $todo;
    }
        
        
    public function Carregar() {
        // Para evitar posteriores cargas
        $this->bLoaded = TRUE;
        $aDades = [];
        $todo = $this->getTodoByUid();
        if (empty($todo)) {
            exit (_("No encuentro el todo"));
        }
        $vcalendar = new \iCalComponent($todo[0]['data']);
        $icalComp = $vcalendar->GetComponents('VTODO');
        $icalComp = $icalComp[0];  // If you know there's only 1 of them...
        
        $aDades['asunto'] = $icalComp->GetPValue("SUMMARY");
        $aDades['status'] = $icalComp->GetPValue("STATUS");
        $aDades['f_cal_acabado'] = $icalComp->GetPValue("COMPLETED");
        $aDades['f_cal_plazo'] = $icalComp->GetPValue("DUE");
        $aDades['f_cal_start'] = $icalComp->GetPValue("DTSTART");
        $aDades['f_cal_end'] = $icalComp->GetPValue("DTEND");
        $aDades['rrule'] = $icalComp->GetPValue("RRULE");
        $aDades['observ'] = $icalComp->GetPValue("DESCRIPTION");
        $aDades['visibilidad'] = $icalComp->GetPValue("CLASS");
        $aDades['detalle'] = $icalComp->GetPValue("COMMENT");
        $aDades['categorias'] = $icalComp->GetPValue("CATEGORIES");
        $aDades['encargado'] = $icalComp->GetPValue("ATTENDEE");
        $aDades['ref_prot_mas'] = $icalComp->GetPValue("X-DLB-REF-MAS");
        $aDades['location'] = $icalComp->GetPValue("LOCATION");
        $aDades['pendiente_con'] = $icalComp->GetPValue("X-DLB-PENDIENTE-CON");
        $aDades['id_reg'] = $icalComp->GetPValue("X-DLB-ID-REG");
        $aDades['oficinas'] = $icalComp->GetPValue("X-DLB-OFICINAS");
        
        $aDades['exdates'] = $vcalendar->GetPropertiesByPath('/VCALENDAR/VTODO/EXDATE');
        /*
        if ($visibilidad=="CONFIDENTIAL") {
            $visibilidad="t";
        } else {
            $visibilidad="f";
        }
        */
        $this->setAllAtributes($aDades);
    }
    
    /**
     * Estableix el valor de tots els atributs
     *
     * @param array $aDades
     */
    function setAllAtributes($aDades) {
        if (!is_array($aDades)) return;
        if (array_key_exists('id_pendiente',$aDades)) $this->setId_pendiente($aDades['id_pendiente']);
        if (array_key_exists('asunto',$aDades)) $this->setAsunto($aDades['asunto']);
        if (array_key_exists('status',$aDades)) $this->setStatus($aDades['status']);
        
        if (array_key_exists('asunto',$aDades)) $this->setAsunto($aDades['asunto']);
        if (array_key_exists('status',$aDades)) $this->setStatus($aDades['status']);
        if (array_key_exists('f_cal_acabado',$aDades)) $this->setF_acabado($aDades['f_cal_acabado']);
        if (array_key_exists('f_cal_plazo',$aDades)) $this->setF_plazo($aDades['f_cal_plazo']);
        if (array_key_exists('f_cal_start',$aDades)) $this->setF_inicio($aDades['f_cal_start']);
        //if (array_key_exists('f_cal_end',$aDades)) $this->setF_end($aDades['f_cal_end']);
        if (array_key_exists('rrule',$aDades)) $this->setRrule($aDades['rrule']);
        if (array_key_exists('observ',$aDades)) $this->setObserv($aDades['observ']);
        if (array_key_exists('visibilidad',$aDades)) $this->setvisibilidad($aDades['visibilidad']);
        if (array_key_exists('detalle',$aDades)) $this->setDetalle($aDades['detalle']);
        if (array_key_exists('categorias',$aDades)) $this->setCategorias($aDades['categorias']);
        if (array_key_exists('encargado',$aDades)) $this->setEncargado($aDades['encargado']);
        if (array_key_exists('ref_prot_mas',$aDades)) $this->setRef_prot_mas($aDades['ref_prot_mas']);
        if (array_key_exists('location',$aDades)) $this->setLocation($aDades['location']);
        if (array_key_exists('pendiente_con',$aDades)) $this->setPendiente_con($aDades['pendiente_con']);
        if (array_key_exists('id_reg',$aDades)) $this->setId_reg($aDades['id_reg']);
        if (array_key_exists('oficinas',$aDades)) $this->setOficinas($aDades['oficinas']);
        
        if (array_key_exists('exdates',$aDades)) $this->setExdates($aDades['exdates']);
    }
    /* METODES GET SET ----------------------------------------------------------------- */
    /**
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $sresource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * @param string $cargo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    }

    /**
     * @return string
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * @param string $cargo
     */
    public function setPasswd($passwd)
    {
        $this->passwd = $passwd;
    }

    /**
     * @return string
     */
    public function getParent_container()
    {
        return $this->parent_container;
    }

    /**
     * @param string $parent_container
     */
    public function setParent_container($parent_container)
    {
        $this->parent_container = $parent_container;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        if (!isset($this->uid) || empty($this->uid)) {
            return FALSE;
        }
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return mixed
     */
    public function getId_reg()
    {
        if (!isset($this->asunto) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->id_reg;
    }

    /**
     * @param mixed $id_reg
     */
    public function setId_reg($id_reg)
    {
        $this->id_reg = $id_reg;
    }

    /**
     * para compatibilidad con los permisos
     * 
     * @return mixed
     */
    public function getPonente()
    {
       $parent_container = $this->getParent_container();
       $a_container = explode('_', $parent_container);
       $nom_oficina = $a_container[1];
       $gesOficinas = new GestorOficina();
       $cOficinas = $gesOficinas->getOficinas(['sigla' => $nom_oficina]);
       $id_oficina = $cOficinas[0]->getId_oficina();
       return $id_oficina;
    }

    /**
     * para compatibilidad con los permisos
     * 
     * @return mixed
     */
    public function getResto_oficinas()
    {
       return $this->getOficinasArray();
    }

    /**
     * para compatibilidad con los permisos
     * 
     * @return mixed
     */
    public function getAsunto()
    {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this,'asunto');
        
        $oEntrada = new Entrada();
        $a_visibilidad = $oEntrada->getArrayVisibilidad();
        $asunto = $a_visibilidad[Entrada::V_RESERVADO];
        if ($perm > 0) {
            $asunto = $this->getAsuntoDV();
        }
        return $asunto;
    }


    /**
     * @return mixed
     */
    public function getAsuntoDV()
    {
        if (!isset($this->asunto) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->asunto;
    }

    /**
     * @param mixed $asunto
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if (!isset($this->status) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getF_inicio()
    {
        if (!isset($this->f_inicio) && !$this->bLoaded) {
            $this->Carregar();
        }
        if (empty($this->f_inicio)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new Converter('date', $this->f_inicio);
        return $oConverter->fromPg();
    }

    /**
     * @param mixed $f_inicio
     */
    public function setF_inicio($f_inicio)
    {
        $this->f_inicio = $f_inicio;
    }

    /**
     * @return mixed
     */
    public function getF_acabado()
    {
        if (!isset($this->f_acabado) && !$this->bLoaded) {
            $this->Carregar();
        }
        if (empty($this->f_acabado)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new Converter('date', $this->f_acabado);
        return $oConverter->fromPg();
    }

    /**
     * @param mixed $f_acabado
     */
    public function setF_acabado($f_acabado)
    {
        $this->f_acabado = $f_acabado;
    }
    
    /**
     * @return mixed
     */
    public function getF_plazo()
    {
        if (!isset($this->f_plazo) && !$this->bLoaded) {
            $this->Carregar();
        }
        if (empty($this->f_plazo)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new Converter('date', $this->f_plazo);
        return $oConverter->fromPg();
    }

    /**
     * @param mixed $f_plazo
     */
    public function setF_plazo($f_plazo)
    {
        $this->f_plazo = $f_plazo;
    }

    /**
     * @return mixed
     */
    public function getRef_prot_mas()
    {
        if (!isset($this->ref_prot_mas) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->ref_prot_mas;
    }

    /**
     * @param mixed $ref_prot_mas
     */
    public function setRef_prot_mas($ref_prot_mas)
    {
        $this->ref_prot_mas = $ref_prot_mas;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        if (!isset($this->location) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getObserv()
    {
        if (!isset($this->observ) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->observ;
    }

    /**
     * @param mixed $observ
     */
    public function setObserv($observ)
    {
        $this->observ = $observ;
    }

    /**
     * @return mixed
     */
    public function getVisibilidad()
    {
        if (!isset($this->visibilidad) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->visibilidad;
    }

    /**
     * @param mixed $visibilidad
     */
    public function setVisibilidad($visibilidad)
    {
        $this->visibilidad = $visibilidad;
    }

    /**
     * @return mixed
     */
    public function getDetalle()
    {
        if (!isset($this->detalle) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->detalle;
    }

    /**
     * @param mixed $detalle
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    }

    /**
     * @return mixed
     */
    public function getCategorias()
    {
        if (!isset($this->categorias) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->categorias;
    }

    /**
     * @param mixed $categorias
     */
    public function setCategorias($categorias)
    {
        $this->categorias = $categorias;
    }

    /**
     * @return mixed
     */
    public function getEncargado()
    {
        if (!isset($this->encargado) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->encargado;
    }

    /**
     * @param mixed $encargado
     */
    public function setEncargado($encargado)
    {
        $this->encargado = $encargado;
    }

    /**
     * @return mixed
     */
    public function getPendiente_con()
    {
        if (!isset($this->pendiente_con) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->pendiente_con;
    }

    /**
     * @param mixed $pendiente_con
     */
    public function setPendiente_con($pendiente_con)
    {
        $this->pendiente_con = $pendiente_con;
    }

    /**
     * @return mixed
     */
    public function getOficinas()
    {
        if (!isset($this->oficinas) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->oficinas;
    }

    /**
     * @param mixed $oficinas
     */
    public function setOficinas($oficinas)
    {
        $this->oficinas = $oficinas;
    }

    /**
     * @return mixed
     */
    public function getId_oficina()
    {
        if (!isset($this->id_oficina) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->id_oficina;
    }

    /**
     * @param mixed $id_oficina
     */
    public function setId_oficina($id_oficina)
    {
        $this->id_oficina = $id_oficina;
    }

    /**
     * @return mixed
     */
    public function getRrule()
    {
        if (!isset($this->rrule) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->rrule;
    }

    /**
     * @param mixed $rrule
     */
    public function setRrule($rrule)
    {
        $this->rrule = $rrule;
    }

    /**
     * @return mixed
     */
    public function getExdates()
    {
        if (!isset($this->exdates) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->exdates;
    }

    /**
     * @param mixed $exdates
     */
    public function setExdates($exdates)
    {
        $this->exdates = $exdates;
    }

    /**
     * @return mixed
     */
    public function getF_recur()
    {
        if (!isset($this->f_recur) && !$this->bLoaded) {
            $this->Carregar();
        }
        return $this->f_recur;
    }

    /**
     * @param mixed $f_recur
     */
    public function setF_recur($f_recur)
    {
        $this->f_recur = $f_recur;
    }

    public function setEtiquetasArray($aEtiquetas){
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        $etiquetas_csv = implode(",", $a_filter_etiquetas);
        
        $this->categorias = $etiquetas_csv;
    }
    
    public function getEtiquetasArray() {
        if (!isset($this->categorias) && !$this->bLoaded) {
            $this->Carregar();
        }
        if (empty($this->categorias)) {
            $aEtiquetas = [];   
        } else {
            $aEtiquetas = explode(",", $this->categorias);
        }
        
        return $aEtiquetas;
    }

    public function setOficinasFromCargos($aCargos){
        $a_oficinas = [];
        foreach ($aCargos as $id_cargo) {
            $oCargo = new Cargo($id_cargo);
            $a_oficinas[] = $oCargo->getId_oficina();
        }
        $a_filter_oficinas = array_filter($a_oficinas); // Quita los elementos vacíos y nulos.
        $oficinas_csv = implode(",", $a_filter_oficinas);
        
        $this->oficinas = $oficinas_csv;
    }
    
    public function setOficinasArray($aOficinas){
        $a_filter_oficinas = array_filter($aOficinas); // Quita los elementos vacíos y nulos.
        $oficinas_csv = implode(",", $a_filter_oficinas);
        
        $this->oficinas = $oficinas_csv;
    }
    
    public function getOficinasArray() {
        if (!isset($this->oficinas) && !$this->bLoaded) {
            $this->Carregar();
        }
        if (empty($this->oficinas)) {
            $aOficinas = [];   
        } else {
            $aOficinas = explode(",", $this->oficinas);
        }
        
        return $aOficinas;
    }
}