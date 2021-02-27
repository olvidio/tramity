<?php
namespace davical\model;

use davical\model\entity\Collection;
use davical\model\entity\GestorPrincipal;
use davical\model\entity\GestorUserDB;
use davical\model\entity\Grant;
use davical\model\entity\GroupMember;
use davical\model\entity\Principal;
use davical\model\entity\RoleMember;
use DateTimeZone;
use web\DateTimeLocal;

/**
 * Fitxer amb la Classe que accedeix a la taula aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
/**
 * Classe que implementa l'entitat aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
class Davical {
    
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /* METODOS -------------------------------------------------------------- */

    public function cambioNombreUser($user) {
        
    }
    
    /**
     * realmente crea un user en davical, que corresponde a un cargo en tramity.
     * Pongo el mismo password a todos (system) para poder acceder desde el programa.
     * en tramity los cargos no son usuarios (no tienen password).
     * Para dar acceso al davical a un usuario habría que crear un usuario desde la pagina de administración
     *  y añadirlo al grupo de su oficina. 
     *       
     * @param array $aDatosCargo
     * @return boolean
     */
    public function crearUser($aDatosCargo) {
        $cargo = $aDatosCargo['cargo'];
        $descripcion = $aDatosCargo['descripcion'];
        $oficina = $aDatosCargo['oficina'];
        //$password = $aDatosCargo['password'];
        
        $error_txt = '';
        if (($cUsers = $this->existeUser($cargo)) === FALSE) {
            // crear resource tipo persona
            //$aData['active'] = 't';
            //$aData['email_ok'] = null;
            //$aData['updated'] =  $str_ahora;
            //$aData['last_used'] = $str_ahora;
            $aData['username'] = $cargo;
            $aData['password'] = '*5SU3Y5apO*{SSHA}SQ2FNnUuR9vvBASHMJ9NlaxR9dI1U1UzWTVhcE8='; // system
            $aData['fullname'] = $descripcion;
            $aData['email'] = '';
            //$aData['config_data'] = '';
            //$aData['date_format_type'] = 'E';
            //$aData['locale'] = 'es_ES';
            
            
            $a_ids_cargo = $this->crearPersona($aData);
            $principal_id_person = $a_ids_cargo['principal_id'];
        } else {
            $user_no = $cUsers[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo para este cargo (%s) en davical"),$cargo);
            } else {
                $principal_id_person = $cPrincipales[0]->getPrincipal_id();
            }
            
        }
        
        // Añadirlo al grupo de la oficina (si existe)
        // Comprobar si existe la oficina, o crearla.
        if (($cUsersOficina = $this->existeOficina($oficina)) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe esta oficina (%s) en davical"),$oficina);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $this->crearOficina($oficina);
        }
        
        // Comprobar si existe el grupo, o crearlo.
        if (($cGroups = $this->existeGrupo($oficina)) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe un grupo para esta oficina (%s) en davical"),$oficina);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $a_id = $this->crearGrupo($oficina);
            $principal_id_grupo = $a_id['principal_id'];  
        } else {
            $user_no = $cGroups[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= _("No existe un principal del grupo la oficina scdl en davical");
            } else {
                $principal_id_grupo = $cPrincipales[0]->getPrincipal_id();
            }
        }
        $this->addPerson2Group($principal_id_person,$principal_id_grupo); 
        
        
        // return
        if (!empty($error_txt)) {
            echo $error_txt;
        }
    }
    
    /**
     * Añadir una persona al grupo.
     * 
     * @param integer $member_id   principal_id del usuario 
     * @param integer $group_id    principal_id del grupo 
     */
    private function addPerson2Group($member_id,$group_id) {
        $oGroupMember = new GroupMember($member_id);
        $oGroupMember->setGroup_id($group_id);
        $oGroupMember->DBGuardar();
    }
    
    /**
     * Crea un resource para el cargo (como un usuario). Es lo mismo que crearGrupo o crearResource
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     * 
     * @param array $aData Un array con los parametros. Si no existen, se ponen por defecto.
     * @return number[]  array con los valores: 'user_no' y 'principal_id'  
     */
    private function crearPersona($aData) {
        $error_txt = '';
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;
        
        $active = empty($aData['active'])? 't' : $aData['active'];
        $email_ok = empty($aData['email_ok'])? null : $aData['email_ok'];
        $updated = empty($aData['updated'])?  $str_ahora : $aData['updated'];
        $last_used = empty($aData['last_used'])? $str_ahora : $aData['last_used'];
        $username = empty($aData['username'])? '?' : $aData['username'];
        $password = empty($aData['password'])? null : $aData['password'];
        $fullname = empty($aData['fullname'])? $username : $aData['fullname'];
        $email = empty($aData['email'])? '' : $aData['email'];
        $config_data = empty($aData['config_data'])? '' : $aData['config_data'];
        $date_format_type = empty($aData['date_format_type'])? 'E' : $aData['date_format_type'];
        $locale = empty($aData['locale'])? 'es_ES' : $aData['locale'];
        
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
        
        // crear el principal correspondiente:
        $oPrincipal = new Principal();
        $oPrincipal->setUser_no($user_no);
        $default_privileges = "000000001111110000000001";
        $oPrincipal->setType_id(Principal::TYPE_PERSON);
        $oPrincipal->setDisplayname($username);
        $oPrincipal->setDefault_privileges($default_privileges);
        $oPrincipal->DBGuardar();
        
        $principal_id = $oPrincipal->getPrincipal_id();
        
        // crear el role_member
        $oRoleMember = new RoleMember($user_no);
        $oRoleMember->setRole_no(RoleMember::ROLE_PUBLIC);
        $oRoleMember->DBGuardar();
        
        if (!empty($error_txt)) {
            $error_txt .= sprintf(_("Problemas al crear la persona (%s) en Davical"),$username);
            exit($error_txt);
        }

        $a_id = ['user_no' => $user_no,
                'principal_id' => $principal_id,  
                ];
        return $a_id;
    }
    
    
    public function cambioNombreOficina($oficina) {
        
    }
    
    /**
     * Crea una oficina:
     *  1.- un usr + principal con nombre: oficina_xxx
     *  2.- un grupo con nombre: grupo_xxx.
     *  3.- crear dos colecciones (oficin y registro) para el principal: oficina_xxx
     *  4.- Dar permiso a cada coleccion para el grupo_xxx
     *  5.- Añadir permiso en la coleccion 'registro' para el grupo: 'grupo_scl'
     * 
     * @param string $oficina
     * @return boolean
     */
    public function crearOficina($oficina) {
        $error_txt = '';
        // si ya existe, no hacer nada.
        if ($this->existeOficina($oficina) !== FALSE) {
            return TRUE;
        }
        // crear resource tipo: oficina_vsm
        $a_ids_oficina = $this->crearResource($oficina);
        $user_no = $a_ids_oficina['user_no'];
        // crear grupo tipo: vsm
        $a_ids_grupo = $this->crearGrupo($oficina);
        // crear collecciones: registro y oficina
        $aCollection_id = $this->crearColecciones($user_no,$oficina);
        
        // Dar al grupo, permiso distinto para cada coleccion. (el registro no borrar...)
        // Resulta que para los evetos con CLASS= PRIVATE, si no tiene todos los privilegios no va.
        // para registro:
        $aData['by_principal'] = '';
        $aData['to_principal'] = $a_ids_grupo['principal_id'];
        $aData['by_collection'] = $aCollection_id['registro'];
        $aData['privileges'] = "111111111111111111111111";
        $aData['is_group'] = '';
        $this->grant($aData);
        // Añadir permisos para el grupo de secretaria
        if (($principal_id_secretaria = $this->getPincipalSecretaria()) !== FALSE) {
            $aData['by_principal'] = '';
            $aData['to_principal'] = $principal_id_secretaria;
            $aData['by_collection'] = $aCollection_id['registro'];
            $aData['privileges'] = "111111111111111111111111";
            $aData['is_group'] = '';
            $this->grant($aData);
        }
        // Oficina
        $aData['by_principal'] = '';
        $aData['to_principal'] = $a_ids_grupo['principal_id'];
        $aData['by_collection'] = $aCollection_id['oficina'];
        $aData['privileges'] = "111111111111111111111111";
        $aData['is_group'] = '';
        $this->grant($aData);
        
        // return
        if (!empty($error_txt)) {
            echo $error_txt;
        }
    }
    
    /**
     * Recupera el valor del principal_id del grupo de secretaria (scdl).
     * 
     * @return integer
     */
    private function getPincipalSecretaria() {
        // Comprobar si existe el grupo y el usuario.
        $principal_id_secretaria = $this->existeSecretaria();
        
        return $principal_id_secretaria;
    }
    
    private function grant($aData) {
        
        $by_principal = $aData['by_principal'];
        $to_principal = $aData['to_principal'];
        $by_collection = $aData['by_collection'];
        $privileges = $aData['privileges'];
        $is_group = $aData['is_group'];
        
        $pKey = [ 'to_principal' => $to_principal, ];
        $oGrant = new Grant($pKey);
       
        $oGrant->setBy_principal($by_principal);
        $oGrant->setBy_collection($by_collection);
        $oGrant->setPrivileges($privileges);
        $oGrant->setIs_group($is_group);
        $oGrant->DBGuardar();
        
    }
    
    /**
     * Crea dos colecciones: una para el registro y otra para la oficina
     * 
     * @param integer $user_no
     * @param string $oficina
     * @return array con los valores de collection_id para 'registro' y 'oficina'.
     */
    private function crearColecciones($user_no,$oficina) {
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $aData = [];
        
        // registro  
        $aData['user_no'] = $user_no;
        $aData['parent_container'] = "/oficina_".$oficina."/";
        $aData['dav_etag'] = -1; //weak_etag?
        $aData['is_calendar'] = 't';
        $aData['created'] = $str_ahora;
        $aData['modified'] = $str_ahora;
        $aData['public_events_only'] = 'f';
        $aData['publicly_readable'] = 'f';
        $aData['default_privileges'] = null;
        $aData['is_addressbook'] = 'f';
        $aData['resourcetypes'] = '<DAV::collection/><urn:ietf:params:xml:ns:caldav:calendar/>';
        $aData['schedule_transp'] = 'opaque';
        $aData['timezone'] = null;
        
        // registro  
        $aData['dav_name'] = '/oficina_'.$oficina.'/registro/';
        $aData['dav_displayname'] = 'registro';
        $aData['description'] = _("pendientes del registro");
        $aCollection_id['registro'] = $this->crearColeccion($aData);
        
        // oficina  
        $aData['dav_name'] = '/oficina_'.$oficina.'/oficina/';
        $aData['dav_displayname'] = 'oficina';
        $aData['description'] = _("pendientes de la oficina");
        
        $aCollection_id['oficina'] = $this->crearColeccion($aData);
        
        return $aCollection_id;
    }
    private function crearColeccion($aData) {
        
        $oCollection = new Collection();
        $oCollection->setUser_no($aData['user_no']);
        $oCollection->setParent_container($aData['parent_container']);
        $oCollection->setDav_name($aData['dav_name']);
        $oCollection->setDav_etag($aData['dav_etag']);
        $oCollection->setDav_displayname($aData['dav_displayname']);
        $oCollection->setIs_calendar($aData['is_calendar']);
        $oCollection->setCreated($aData['created']);
        $oCollection->setModified($aData['modified']);
        $oCollection->setPublic_events_only($aData['public_events_only']);
        $oCollection->setPublicly_readable($aData['publicly_readable']);
        $oCollection->setDefault_privileges($aData['default_privileges']);
        $oCollection->setIs_addressbook($aData['is_addressbook']);
        $oCollection->setResourcetypes($aData['resourcetypes']);
        $oCollection->setSchedule_transp($aData['schedule_transp']);
        $oCollection->setTimezone($aData['timezone']);
        $oCollection->setDescription($aData['description']);
        $oCollection->DBGuardar();
        
        return $oCollection->getCollection_id();
    }
    
    
    /**
     * Crea un resource para el grupo de la oficina. Es lo mismo que crearPersona o crearResource
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     * 
     * @param string $oficina
     * @return number[]  array con los valores: 'user_no' y 'principal_id'  
     */
    private function crearGrupo($oficina) {
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;
        
        $active = 't';
        $email_ok = null;
        $updated =  $str_ahora;
        $last_used = $str_ahora;
        $username = 'grupo_'.$oficina;
        $password = null;
        $fullname = _("grupo de")." $oficina";
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
        
        // crear el principal correspondiente:
        $oPrincipal = new Principal();
        $oPrincipal->setUser_no($user_no);
        $default_privileges = "000000001111110000000001";
        $oPrincipal->setType_id(Principal::TYPE_GROUP);
        $oPrincipal->setDisplayname($username);
        $oPrincipal->setDefault_privileges($default_privileges);
        $oPrincipal->DBGuardar();
        
        $principal_id = $oPrincipal->getPrincipal_id();
        
        // crear el role_member
        $oRoleMember = new RoleMember($user_no);
        $oRoleMember->setRole_no(RoleMember::ROLE_GROUP);
        $oRoleMember->DBGuardar();
        
        if (!empty($error_txt)) {
            $error_txt .= sprintf(_("Problemas al crear el grupo (%s) en Davical"),$username);
            exit($error_txt);
        }
        
        $a_id = ['user_no' => $user_no,
                'principal_id' => $principal_id,  
                ];
        return $a_id;
    }
    
    /**
     * Crea un resource para la oficina. Es lo mismo que crearPersona o crearGrupo
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     * 
     * @param string $oficina
     * @return number[]  array con los valores: 'user_no' y 'principal_id'  
     */
    private function crearResource($oficina) {
        $error_txt = '';
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;
        
        $active = 't';
        $email_ok = null;
        $updated =  $str_ahora;
        $last_used = $str_ahora;
        $username = 'oficina_'.$oficina;
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
        
        // crear el principal correspondiente:
        $oPrincipal = new Principal();
        $oPrincipal->setUser_no($user_no);
        $default_privileges = "000000001111110000000001";
        $oPrincipal->setType_id(Principal::TYPE_RESOURCE);
        $oPrincipal->setDisplayname($username);
        $oPrincipal->setDefault_privileges($default_privileges);
        $oPrincipal->DBGuardar();
        
        $principal_id = $oPrincipal->getPrincipal_id();
        
        // crear el role_member
        $oRoleMember = new RoleMember($user_no);
        $oRoleMember->setRole_no(RoleMember::ROLE_RESOURCE);
        $oRoleMember->DBGuardar();
        
        if (!empty($error_txt)) {
            $error_txt .= sprintf(_("Problemas al crear el recurso (%s) en Davical"),$username);
            exit($error_txt);
        }

        $a_id = ['user_no' => $user_no,
                'principal_id' => $principal_id,  
                ];
        return $a_id;
    }
    
    /**
     * Mira si existe el resource la oficina en la tabla usr. En caso afirmativo
     * devuelve el array con los objetos User y username='oficina_xxx' (xxx=nombre de la oficina)
     * en caso negativo devuelve FALSE.
     * 
     * @param string $oficina
     * @return boolean|array
     */
    private function existeOficina($oficina) {
        // mirar si existe el resource tipo: oficina_vsm
        $username = 'oficina_'.$oficina;
        $gesUser = new GestorUserDB();
        $cUsers = $gesUser->getUsersDB(['username' => $username]);
        if (empty($cUsers)) {
            return FALSE;
        } else {
            return $cUsers;
        }
    }
    
    /**
     * Mira si existe el grupo para la oficina en la tabla usr. En caso afirmativo
     * devuelve el array con los objetos User y username='grupo_xxx' (xxx=nombre de la oficina)
     * en caso negativo devuelve FALSE.
     * 
     * @param string $oficina
     * @return boolean|array
     */
    private function existeGrupo($oficina) {
        // asegurar que existe el grupo
        // crear grupo tipo: grupo_vsm
        $username = 'grupo_'.$oficina;
        $gesUser = new GestorUserDB();
        $cUsers = $gesUser->getUsersDB(['username' => $username]);
        if (empty($cUsers)) {
            return FALSE;
        } else {
            return $cUsers;
        }
    }
    
    /**
     * Mira si existe el usuario en la tabla usr. En caso afirmativo
     * devuelve el array con los objetos User y username=user.
     * en caso negativo devuelve FALSE.
     * 
     * @param string $user
     * @return boolean|array
     */
    private function existeUser($user) {
        // mirar si existe el user
        $gesUser = new GestorUserDB();
        $cUsers = $gesUser->getUsersDB(['username' => $user]);
        if (empty($cUsers)) {
            return FALSE;
        } else {
            return $cUsers;
        }
    }
    
    private function existeSecretaria() {
        $error_txt = '';
        $user = 'secretaria';
        $descripcion = "usuario y grupo para secretaria. Tiene permiso total para todos los calendarios de registro";
        // mirar si existe el user
        if (($cUsers = $this->existeUser($user)) === FALSE) {
            // crear resource tipo persona
            //$aData['active'] = 't';
            //$aData['email_ok'] = null;
            //$aData['updated'] =  $str_ahora;
            //$aData['last_used'] = $str_ahora;
            $aData['username'] = $user;
            $aData['password'] = '*5SU3Y5apO*{SSHA}SQ2FNnUuR9vvBASHMJ9NlaxR9dI1U1UzWTVhcE8='; // system
            $aData['fullname'] = $descripcion;
            $aData['email'] = '';
            //$aData['config_data'] = '';
            //$aData['date_format_type'] = 'E';
            //$aData['locale'] = 'es_ES';
            
            
            $a_ids_cargo = $this->crearPersona($aData);
            $principal_id_person = $a_ids_cargo['principal_id'];
        } else {
            $user_no = $cUsers[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo para este cargo (%s) en davical"),$cargo);
            } else {
                $principal_id_person = $cPrincipales[0]->getPrincipal_id();
            }
            
        }
        // asegurar que existe el grupo
        // Comprobar si existe el grupo, o crearlo.
        if (($cGroups = $this->existeGrupo($user)) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe un grupo para esta oficina (%s) en davical"),$user);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $a_id = $this->crearGrupo($user);
            $principal_id_grupo = $a_id['principal_id'];  
        } else {
            $user_no = $cGroups[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= _("No existe un principal del grupo la oficina scdl en davical");
            } else {
                $principal_id_grupo = $cPrincipales[0]->getPrincipal_id();
            }
        }
        $this->addPerson2Group($principal_id_person,$principal_id_grupo); 
        
        if (!empty($error_txt)) {
            $error_txt .= _("Problemas al crear secretaria en Davical");
            exit($error_txt);
        }
        return $principal_id_grupo;
    }
    
}