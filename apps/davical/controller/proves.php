<?php

use davical\model\Davical;
use davical\model\User;
use davical\model\entity\Collection;
use davical\model\entity\GestorPrincipal;
use davical\model\entity\Principal;
use davical\model\entity\RoleMember;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************
	
$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
    case 'add_user':
        $Qoficina = (string) \filter_input(INPUT_POST, 'oficina');
        $Quser = (string) \filter_input(INPUT_POST, 'user');
        $oDavical = new Davical();
        $oDavical->crearUser($Quser);
        $oDavical->addUserOficina($Qoficina,$Quser);
        
        break;
    case 'crear_oficina':
        $Qoficina = (string) \filter_input(INPUT_POST, 'oficina');
        $oDavical = new Davical();
        $oDavical->crearOficina($Qoficina);
        
        break;
    case 'crear_coleccion':
        $Qcollection_id = (integer) \filter_input(INPUT_POST, 'collection_id');
        $Quser_no = (integer) \filter_input(INPUT_POST, 'user_no');
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        if (!empty($Qcollection_id)) {
            $oCollection = new Collection($Qcollection_id);
            $oCollection->DBCarregar();
        } else {
            $oCollection = new Collection();
        }
        
        $user_no = $Quser_no;
        $parent_container = "/oficina_vsm/";
        $dav_name = '/oficina_vsm/registro/';
        $dav_etag = -1; //weak_etag?
        $dav_displayname = 'registro';
        $is_calendar = 't';
        $created = $str_ahora;
        $modified = $str_ahora;
        $public_events_only = 'f';
        $publicly_readable = 'f';
        $default_privileges = null;
        $is_addressbook = 'f';
        $resourcetypes = '<DAV::collection/><urn:ietf:params:xml:ns:caldav:calendar/>';
        $schedule_transp = 'opaque';
        $timezone = null;
        $description = '';
        

        $oCollection->setUser_no($user_no);
        $oCollection->setParent_container($parent_container);
        $oCollection->setDav_name($dav_name);
        $oCollection->setDav_etag($dav_etag);
        $oCollection->setDav_displayname($dav_displayname);
        $oCollection->setIs_calendar($is_calendar);
        $oCollection->setCreated($created);
        $oCollection->setModified($modified);
        $oCollection->setPublic_events_only($public_events_only);
        $oCollection->setPublicly_readable($publicly_readable);
        $oCollection->setDefault_privileges($default_privileges);
        $oCollection->setIs_addressbook($is_addressbook);
        $oCollection->setResourcetypes($resourcetypes);
        $oCollection->setSchedule_transp($schedule_transp);
        $oCollection->setTimezone($timezone);
        $oCollection->setDescription($description);
        
        
        $oCollection->DBGuardar();
        
        $collection_id = $oCollection->getCollection_id();
        
        echo "<pre>";
        print_r($oCollection);
        echo "</pre>";
        break;
    case 'crear_resource':
        $Quser_no = (integer) \filter_input(INPUT_POST, 'user_no');
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        if (!empty($Quser_no)) {
            $oUserDavical = new User($Quser_no);
            $oUserDavical->DBCarregar();
            $joined = $oUserDavical->getJoined()->getFromLocalHora();
        } else {
            $oUserDavical = new User();
            $joined = $str_ahora;
        }

        
        $active = 't';
        $email_ok = null;
        $updated =  $str_ahora;
        $last_used = $str_ahora;
        $username = 'oficina_vsm';
        $password = null;
        $fullname = $username;
        $email = '';
        $config_data = '';
        $date_format_type = 'E';
        $locale = 'es_ES';
        

        $oUserDavical->setActive($active);
        $oUserDavical->setEmail_ok($email_ok);
        $oUserDavical->setJoined($joined);
        $oUserDavical->setUpdated($updated);
        $oUserDavical->setLast_used($last_used);
        $oUserDavical->setUsername($username);
        $oUserDavical->setPassword($password);
        $oUserDavical->setFullname($fullname);
        $oUserDavical->setEmail($email);
        $oUserDavical->setConfig_data($config_data);
        $oUserDavical->setDate_format_type($date_format_type);
        $oUserDavical->setLocale($locale);
        $oUserDavical->DBGuardar();
        
        $user_no = $oUserDavical->getUser_no();
        
        echo "<pre>";
        print_r($oUserDavical);
        echo "</pre>";
        
        // crear el principal correspondiente:
        $oPrincipal = '';
        if (!empty($Quser_no)) {
            // buscar si ya existe
            $gesPrincipal = new GestorPrincipal();
            $cPrincipal = $gesPrincipal->getPrincipales(['user_no' => $Quser_no]);
            if (!empty($cPrincipal)) {
                $oPrincipal = $cPrincipal[0];
                $default_privileges = $oPrincipal->getDefault_privileges();
            }
        }
        if (empty($oPrincipal)) {
            $oPrincipal = new Principal();
            $oPrincipal->setUser_no($user_no);
            $default_privileges = "000000001111110000000001";
        }
        
        $oPrincipal->setType_id(Principal::TYPE_RESOURCE);
        $oPrincipal->setDisplayname($username);
        $oPrincipal->setDefault_privileges($default_privileges);
        $oPrincipal->DBGuardar();
        
        // crear el role_member
        $oRoleMember = new RoleMember($user_no);
        $oRoleMember->setRole_no(RoleMember::ROLE_RESOURCE);
        $oRoleMember->DBGuardar();
        
        break;
    case 'crear_usuario':
        $Quser_no = (integer) \filter_input(INPUT_POST, 'user_no');
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        if (!empty($Quser_no)) {
            $oUserDavical = new User($Quser_no);
            $oUserDavical->DBCarregar();
            $joined = $oUserDavical->getJoined()->getFromLocal();
        } else {
            $oUserDavical = new User();
            $joined = $str_ahora;
        }

        
        $active = 't';
        $email_ok = $str_ahora;
        $updated =  $str_ahora;
        $last_used = $str_ahora;
        $username = 'prova1';
        $password = '88888888';
        $fullname = 'Prova de crear usuari';
        $email = 'prova@lan.moneders.net';
        $config_data = '';
        $date_format_type = 'E';
        $locale = 'es_ES';
        

        $oUserDavical->setActive($active);
        $oUserDavical->setEmail_ok($email_ok);
        $oUserDavical->setJoined($joined);
        $oUserDavical->setUpdated($updated);
        $oUserDavical->setLast_used($last_used);
        $oUserDavical->setUsername($username);
        $oUserDavical->setPassword($password);
        $oUserDavical->setFullname($fullname);
        $oUserDavical->setEmail($email);
        $oUserDavical->setConfig_data($config_data);
        $oUserDavical->setDate_format_type($date_format_type);
        $oUserDavical->setLocale($locale);
        $oUserDavical->DBGuardar();
        
        $user_no = $oUserDavical->getUser_no();
        
        echo "<pre>";
        print_r($oUserDavical);
        echo "</pre>";
        
        // crear el principal correspondiente:
        $oPrincipal = '';
        if (!empty($Quser_no)) {
            // buscar si ya existe
            $gesPrincipal = new GestorPrincipal();
            $cPrincipal = $gesPrincipal->getPrincipales(['user_no' => $Quser_no]);
            if (!empty($cPrincipal)) {
                $oPrincipal = $cPrincipal[0];
                $default_privileges = $oPrincipal->getDefault_privileges();
            }
        }
        if (empty($oPrincipal)) {
            $oPrincipal = new Principal();
            $oPrincipal->setUser_no($user_no);
            $default_privileges = "111111111111111111111111";
        }
        
        $oPrincipal->setType_id(Principal::TYPE_PERSON);
        $oPrincipal->setDisplayname($username);
        $oPrincipal->setDefault_privileges($default_privileges);
        $oPrincipal->DBGuardar();
        
        // crear el role_member
        $oRoleMember = new RoleMember($user_no);
        $oRoleMember->setRole_no(RoleMember::ROLE_PUBLIC);
        $oRoleMember->DBGuardar();
        
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}