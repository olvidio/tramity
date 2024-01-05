<?php

namespace davical\model;

use DateTimeZone;
use davical\model\entity\Collection;
use davical\model\entity\GestorCalendarItem;
use davical\model\entity\GestorCollection;
use davical\model\entity\GestorPrincipal;
use davical\model\entity\GestorUserDB;
use davical\model\entity\Grant;
use davical\model\entity\GroupMember;
use davical\model\entity\Principal;
use davical\model\entity\RoleMember;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\Oficina;
use web\DateTimeLocal;
use web\StringLocal;

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
class Davical
{

    /**
     *  En la clase Cargo:
     *  const AMBITO_CG  = 1;
     *  const AMBITO_CR  = 2;
     *  const AMBITO_DL  = 3;  //"dl"
     *  const AMBITO_CTR = 4;
     *  const AMBITO_CTR_CORREO = 5;
     *
     * @var integer
     */
    private $ambito;

    /* CONSTRUCTOR -------------------------------------------------------------- */

    public function __construct(int $ambito)
    {

        $this->setAmbito($ambito);
    }
    /* METODOS -------------------------------------------------------------- */
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
    public function crearUser($aDatosCargo)
    {
        $cargo = $aDatosCargo['cargo'];
        $descripcion = $aDatosCargo['descripcion'];
        $id_oficina = $aDatosCargo['id_oficina'];
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $oficina = $_SESSION['oConfig']->getSigla();
            $oficina_mod = $this->getNombreRecursoPorNombreOficina($oficina);
        } else {
            $oOficina = new Oficina($id_oficina);
            $oficina = $oOficina->getSigla();
            $oficina_mod = $this->getNombreRecursoPorIdOficina($id_oficina);
        }
        $nom_grupo = $this->getNombreGrupo($oficina);
        $username = $this->getNombreUsuario($cargo);

        $error_txt = '';
        if (($cUsers = $this->existeUser($username)) === FALSE) {
            // crear resource tipo persona
            //$aData['active'] = 't';
            //$aData['email_ok'] = null;
            //$aData['updated'] =  $str_ahora;
            //$aData['last_used'] = $str_ahora;
            //$aData['config_data'] = '';
            //$aData['date_format_type'] = 'E';
            //$aData['locale'] = 'es_ES';
            $aData['username'] = $username;
            $aData['password'] = '*5SU3Y5apO*{SSHA}SQ2FNnUuR9vvBASHMJ9NlaxR9dI1U1UzWTVhcE8='; // system
            $aData['fullname'] = $descripcion;
            $aData['email'] = '';

            $a_ids_cargo = $this->crearPersona($aData);
            $principal_id_person = $a_ids_cargo['principal_id'];
        } else {
            $user_no = $cUsers[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo para este cargo (%s) en davical"), $username);
            } else {
                $principal_id_person = $cPrincipales[0]->getPrincipal_id();
            }
        }

        // Añadirlo al grupo de la oficina (si existe)
        // Comprobar si existe la oficina, o crearla.
        if ($this->existeUser($oficina_mod) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe esta oficina (%s) en davical"), $oficina);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $this->crearOficina($oficina);
        }

        // Comprobar si existe el grupo, o crearlo.
        if (($cGroups = $this->existeUser($nom_grupo)) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe un grupo para esta oficina (%s) en davical"), $oficina);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $a_id = $this->crearGrupo($nom_grupo);
            $principal_id_grupo = $a_id['principal_id'];
        } else {
            $user_no = $cGroups[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo la oficina %s en davical"), $oficina);
            } else {
                $principal_id_grupo = $cPrincipales[0]->getPrincipal_id();
            }
        }
        $this->addPerson2Group($principal_id_person, $principal_id_grupo);

        return $error_txt;
    }

    public function getNombreRecursoPorIdOficina($id_oficina = '')
    {
        $sigla = $_SESSION['oConfig']->getSigla();
        $sigla_norm = StringLocal::lowerNormalized($sigla);
        // para los ctr, id_oficina = -10. No la pongo en el nombre
        if (!empty($id_oficina) && $id_oficina > 0) {
            $oOficina = new Oficina($id_oficina);
            $oficina = $oOficina->getSigla();
            $nom_recurso = $this->getNombreRecursoPorNombreOficina($oficina);
        } else {
            $nom_recurso = $sigla_norm . "_oficina";
        }
        return $nom_recurso;
    }

    public function getNombreRecursoPorNombreOficina($oficina)
    {
        $sigla = $_SESSION['oConfig']->getSigla();
        $sigla_norm = StringLocal::lowerNormalized($sigla);
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $oficina_norm = '';
        } else {
            if (empty($oficina)) {
                $msg = _("No se puede determinar la ruta del calendario para añadir el pendiente");
                exit($msg);
            }
            $oficina_norm = '_' . StringLocal::lowerNormalized($oficina);
        }

        return $sigla_norm . "_oficina" . $oficina_norm;
    }

    private function getNombreGrupo($oficina = '')
    {
        $sigla = $_SESSION['oConfig']->getSigla();
        $sigla_norm = StringLocal::lowerNormalized($sigla);
        if ($this->ambito == Cargo::AMBITO_DL) {
            $oficina_norm = StringLocal::lowerNormalized($oficina);
            $nom_grupo = $sigla_norm . "_grupo_" . $oficina_norm;
        } else {
            $nom_grupo = $sigla_norm . "_grupo";
        }
        return $nom_grupo;
    }

    private function getNombreUsuario($cargo)
    {
        // devuelve: dlb_of2sm o agdmontagut_sd
        $sigla = $_SESSION['oConfig']->getSigla();
        $sigla_norm = StringLocal::lowerNormalized($sigla);
        return $sigla_norm . '_' . $cargo;
    }

    /**
     * Es valido para usuarios (vsm), Grupos (dlb_grupo_vsm) y Oficinas (dlb_oficina_vsm)
     * Mira si existe el usuario en la tabla usr. En caso afirmativo
     * devuelve el array con los objetos User y username=user.
     * en caso negativo devuelve FALSE.
     *
     * @param string $user
     * @return boolean|array
     */
    private function existeUser($username)
    {
        // mirar si existe el user
        $gesUser = new GestorUserDB();
        $cUsers = $gesUser->getUsersDB(['username' => $username]);
        if (empty($cUsers)) {
            return FALSE;
        } else {
            return $cUsers;
        }
    }

    /**
     * Crea un resource para el cargo (como un usuario). Es lo mismo que crearGrupo o crearResource
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     *
     * @param array $aData Un array con los parametros. Si no existen, se ponen por defecto.
     * @return number[]  array con los valores: 'user_no' y 'principal_id'
     */
    private function crearPersona($aData)
    {
        $error_txt = '';
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;

        $active = empty($aData['active']) ? 't' : $aData['active'];
        $email_ok = empty($aData['email_ok']) ? NULL : $aData['email_ok'];
        $updated = empty($aData['updated']) ? $str_ahora : $aData['updated'];
        $last_used = empty($aData['last_used']) ? $str_ahora : $aData['last_used'];
        $username = empty($aData['username']) ? '?' : $aData['username'];
        $password = empty($aData['password']) ? NULL : $aData['password'];
        $fullname = empty($aData['fullname']) ? $username : $aData['fullname'];
        $email = empty($aData['email']) ? '' : $aData['email'];
        $config_data = empty($aData['config_data']) ? '' : $aData['config_data'];
        $date_format_type = empty($aData['date_format_type']) ? 'E' : $aData['date_format_type'];
        $locale = empty($aData['locale']) ? 'es_ES' : $aData['locale'];

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
        $default_privileges = "111111111111111111111111";
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
            $error_txt .= sprintf(_("Problemas al crear la persona (%s) en Davical"), $username);
            exit($error_txt);
        }

        return ['user_no' => $user_no,
            'principal_id' => $principal_id,
        ];
    }

    /**
     * Crea una oficina:
     *  1.- un usr + principal con nombre: oficina_xxx
     *  2.- un grupo con nombre: grupo_xxx.
     *  3.- crear dos colecciones (oficina y registro) para el principal: oficina_xxx
     *  4.- Dar permiso a cada coleccion para el grupo_xxx
     *  5.- Añadir permiso en la coleccion 'registro' para el grupo: 'grupo_scl'
     *
     * @param string $oficina
     * @return boolean
     */
    public function crearOficina($oficina)
    {
        $error_txt = '';
        $nom_oficina = $this->getNombreRecursoPorNombreOficina($oficina);
        $nom_grupo = $this->getNombreGrupo($oficina);

        // si ya existe, no hacer nada.
        if ($this->existeUser($nom_oficina) !== FALSE) {
            return '';
        }
        // crear resource tipo: oficina_vsm
        $a_ids_oficina = $this->crearResource($nom_oficina);
        $user_no = $a_ids_oficina['user_no'];
        // crear grupo tipo: grupo_vsm
        $a_ids_grupo = $this->crearGrupo($nom_grupo);
        // crear colecciones: registro y oficina
        $aCollection_id = $this->crearColecciones($user_no, $nom_oficina);

        // Dar al grupo, permiso distinto para cada colección. (el registro no borrar...)
        // Resulta que para los eventos con CLASS= PRIVATE, si no tiene todos los privilegios no va.
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            // para registro:
            $aData['by_principal'] = '';
            $aData['to_principal'] = $a_ids_grupo['principal_id'];
            $aData['by_collection'] = $aCollection_id['registro'];
            $aData['privileges'] = "111111111111111111111111";
            $aData['is_group'] = '';
            $this->grant($aData);
            // Añadir permisos para el grupo de secretaria
            if (($principal_id_secretaria = $this->getPincipalSecretaria()) !== FALSE) {
                $aData['to_principal'] = $principal_id_secretaria;
                $aData['by_collection'] = $aCollection_id['registro'];
                $aData['privileges'] = "111111111111111111111111";
                $aData['is_group'] = '';
                $this->grant($aData);
            }
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
     * Crea un resource para la oficina. Es lo mismo que crearPersona o crearGrupo
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     *
     * @param string $oficina
     * @return number[]  array con los valores: 'user_no' y 'principal_id'
     */
    private function crearResource($username)
    {
        $error_txt = '';
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;

        $active = 't';
        $email_ok = NULL;
        $updated = $str_ahora;
        $last_used = $str_ahora;
        $password = NULL;
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
        $default_privileges = "111111111111111111111111";
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
            $error_txt .= sprintf(_("Problemas al crear el recurso (%s) en Davical"), $username);
            exit($error_txt);
        }

        return ['user_no' => $user_no,
            'principal_id' => $principal_id,
        ];
    }

    /**
     * Crea un resource para el grupo de la oficina. Es lo mismo que crearPersona o crearResource
     * lo que cambia es el tipo (type_id) y los valores por defecto.
     * Modifica la tabla 'usr' + 'principal' + 'role_member'
     *
     * @param string $oficina
     * @return number[]  array con los valores: 'user_no' y 'principal_id'
     */
    private function crearGrupo($username)
    {
        $error_txt = '';
        // Hay que crear un usuario
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $oUserDavical = new User();
        $joined = $str_ahora;

        $active = 't';
        $email_ok = NULL;
        $updated = $str_ahora;
        $last_used = $str_ahora;
        $password = NULL;
        $fullname = sprintf(_("grupo de %s"), $username);
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
        $default_privileges = "111111111111111111111111";
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
            $error_txt .= sprintf(_("Problemas al crear el grupo (%s) en Davical"), $username);
            exit($error_txt);
        }

        return ['user_no' => $user_no,
            'principal_id' => $principal_id,
        ];
    }

    /**
     * Crea dos colecciones: una para el registro y otra para la oficina
     *
     * @param integer $user_no
     * @param string $oficina
     * @return array con los valores de collection_id para 'registro' y 'oficina'.
     */
    private function crearColecciones($user_no, $nom_oficina)
    {
        $oAhora = new DateTimeLocal('', new DateTimeZone('Europe/Madrid'));
        $str_ahora = $oAhora->getFromLocalHora();
        $aData = [];

        $parent_container = "/" . $nom_oficina . "/";

        // registro
        $aData['user_no'] = $user_no;
        $aData['parent_container'] = $parent_container;
        $aData['dav_etag'] = -1; //weak_etag?
        $aData['is_calendar'] = 't';
        $aData['created'] = $str_ahora;
        $aData['modified'] = $str_ahora;
        $aData['public_events_only'] = 'f';
        $aData['publicly_readable'] = 'f';
        $aData['default_privileges'] = NULL;
        $aData['is_addressbook'] = 'f';
        $aData['resourcetypes'] = '<DAV::collection/><urn:ietf:params:xml:ns:caldav:calendar/>';
        $aData['schedule_transp'] = 'opaque';
        $aData['timezone'] = NULL;

        // registro
        if ($this->ambito == Cargo::AMBITO_DL) {
            $aData['dav_name'] = $parent_container . 'registro/';
            $aData['dav_displayname'] = 'registro';
            $aData['description'] = _("pendientes del registro");
            $aCollection_id['registro'] = $this->crearColeccion($aData);
        }
        // oficina
        $aData['dav_name'] = $parent_container . 'oficina/';
        $aData['dav_displayname'] = 'oficina';
        $aData['description'] = _("pendientes de la oficina");

        $aCollection_id['oficina'] = $this->crearColeccion($aData);

        return $aCollection_id;
    }

    private function crearColeccion($aData)
    {
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

    private function grant($aData)
    {

        $by_principal = $aData['by_principal'];
        $to_principal = $aData['to_principal'];
        $by_collection = $aData['by_collection'];
        $privileges = $aData['privileges'];
        $is_group = $aData['is_group'];

        $pKey = ['to_principal' => $to_principal,];
        $oGrant = new Grant($pKey);

        $oGrant->setBy_principal($by_principal);
        $oGrant->setBy_collection($by_collection);
        $oGrant->setPrivileges($privileges);
        $oGrant->setIs_group($is_group);
        $oGrant->DBGuardar();

    }

    /**
     * Recupera el valor del principal_id del grupo de secretaria (scdl).
     *
     * @return integer
     */
    private function getPincipalSecretaria()
    {
        // Comprobar si existe el grupo y el usuario.
        return $this->existeSecretaria();
    }

    private function existeSecretaria()
    {
        $error_txt = '';
        $oficina = 'secretaria';
        $cargo = 'admin';
        $descripcion = "usuario y grupo para secretaria. Tiene permiso total para todos los calendarios de registro";

        $nom_grupo = $this->getNombreGrupo($oficina);
        $username = $this->getNombreUsuario($cargo);


        // mirar si existe el user
        if (($cUsers = $this->existeUser($username)) === FALSE) {
            // crear resource tipo persona
            //$aData['active'] = 't';
            //$aData['email_ok'] = null;
            //$aData['updated'] =  $str_ahora;
            //$aData['last_used'] = $str_ahora;
            //$aData['config_data'] = '';
            //$aData['date_format_type'] = 'E';
            //$aData['locale'] = 'es_ES';
            $aData['username'] = $username;
            $aData['password'] = '*5SU3Y5apO*{SSHA}SQ2FNnUuR9vvBASHMJ9NlaxR9dI1U1UzWTVhcE8='; // system
            $aData['fullname'] = $descripcion;
            $aData['email'] = '';


            $a_ids_cargo = $this->crearPersona($aData);
            $principal_id_person = $a_ids_cargo['principal_id'];
        } else {
            $user_no = $cUsers[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo (%s) en davical"), $username);
            } else {
                $principal_id_person = $cPrincipales[0]->getPrincipal_id();
            }

        }
        // asegurar que existe el grupo
        // Comprobar si existe el grupo, o crearlo.
        if (($cGroups = $this->existeUser($nom_grupo)) === FALSE) {
            $error_txt .= "\n";
            $error_txt .= sprintf(_("No existe un grupo para %s en davical"), $nom_grupo);
            $error_txt .= "\n";
            $error_txt .= _("creando...");
            $a_id = $this->crearGrupo($nom_grupo);
            $principal_id_grupo = $a_id['principal_id'];
        } else {
            $user_no = $cGroups[0]->getUser_no();
            $gesPrincipal = new GestorPrincipal();
            $cPrincipales = $gesPrincipal->getPrincipales(['user_no' => $user_no]);
            if (empty($cPrincipales)) {
                $error_txt .= "\n";
                $error_txt .= sprintf(_("No existe un principal del grupo para %s en davical"), $nom_grupo);
            } else {
                $principal_id_grupo = $cPrincipales[0]->getPrincipal_id();
            }
        }
        $this->addPerson2Group($principal_id_person, $principal_id_grupo);

        if (!empty($error_txt)) {
            $error_txt .= _("Problemas al crear secretaria en Davical");
            exit($error_txt);
        }
        return $principal_id_grupo;
    }

    /**
     * Añadir una persona al grupo.
     *
     * @param integer $member_id principal_id del usuario
     * @param integer $group_id principal_id del grupo
     */
    private function addPerson2Group($member_id, $group_id)
    {
        $oGroupMember = new GroupMember($member_id);
        $oGroupMember->setGroup_id($group_id);
        $oGroupMember->DBGuardar();
    }

    /**
     * Elimina un usuario de Davical (corresponde al cargo de tramity)
     * @param array $aDatosCargo
     * @return string
     */
    public function eliminarUser($aDatosCargo)
    {
        $error_txt = '';
        $cargo = $aDatosCargo['cargo'];
        // $password = $aDatosCargo['password']
        $username = $this->getNombreUsuario($cargo);

        if (($cUsers = $this->existeUser($username)) !== FALSE) {
            foreach ($cUsers as $oUser) {
                if ($oUser->DBEliminar() === FALSE) {
                    $error_txt .= _("hay un error, no se ha eliminado");
                    $error_txt .= "\n" . $oUser->getErrorTxt();
                }
            }
        }
        return $error_txt;
    }

    public function cambioNombreOficina($of_new, $of_old)
    {
        $of_new_mod = $this->getNombreRecursoPorNombreOficina($of_new);
        $of_old_mod = $this->getNombreRecursoPorNombreOficina($of_old);
        // modificar el usr correspondiente a la oficina:
        $oUserDavical = new User();
        $user_no = $oUserDavical->cambiarNombre($of_new_mod, $of_old_mod);

        // modificar el principal correspondiente a la oficina:
        $oPrincipal = new Principal();
        $oPrincipal->cambiarNombre($user_no, $of_new_mod);

        // modificar el usr correspondiente al grupo:
        $grupo_new = $this->getNombreGrupo($of_new);
        $grupo_old = $this->getNombreGrupo($of_old);

        $oUserDavical = new User();
        $user_no = $oUserDavical->cambiarNombre($grupo_new, $grupo_old);

        // modificar el principal correspondiente a la oficina:
        $oPrincipal = new Principal();
        $oPrincipal->cambiarNombre($user_no, $grupo_new);

        // modificar el collection correspondiente (2 => grupo y oficina):
        $oCollection = new Collection();
        $oCollection->cambiarNombre($of_new_mod, $of_old_mod);

        // calendar_item i caldav_data (el dav_name, se cambia al cambiarlo en collection)
        $oCalendar = new GestorCalendarItem();
        $oCalendar->cambiarOficinaUids($of_new_mod, $of_old_mod);

    }

    public function eliminarOficina($oficina)
    {
        $error_txt = '';
        $nom_oficina = $this->getNombreRecursoPorNombreOficina($oficina);
        $nom_grupo = $this->getNombreGrupo($oficina);
        // si No existe, no hacer nada.
        if ($this->existeUser($nom_oficina) === FALSE) {
            return TRUE;
        }
        // eliminar colecciones: registro y oficina
        $error_txt .= $this->deleteColecciones($oficina);
        // eliminar grupo tipo: vsm
        $error_txt .= $this->deleteGrupo($nom_grupo);
        // eliminar resource tipo: oficina_vsm
        $error_txt .= $this->deleteResource($nom_oficina);

        return $error_txt;
    }

    /**
     * Eliminar dos colecciones: una para el registro y otra para la oficina
     *
     * @param string $oficina
     * @return array con los valores de collection_id para 'registro' y 'oficina'.
     */
    private function deleteColecciones($nom_oficina)
    {
        $error_txt = '';
        $aData = [];
        $parent_container = "/" . $nom_oficina . "/";
        // registro
        if ($this->ambito == Cargo::AMBITO_DL) {
            $aData['dav_name'] = $parent_container . 'registro/';
            $aData['dav_displayname'] = 'registro';
            $error_txt .= $this->deleteColeccion($aData);
        }
        // oficina
        $aData['dav_name'] = $parent_container . 'oficina/';
        $aData['dav_displayname'] = 'oficina';

        $error_txt .= $this->deleteColeccion($aData);

        return $error_txt;
    }

    private function deleteColeccion($aData)
    {
        $error_txt = '';
        $dav_name = $aData['dav_name'];            // $parent_container.'oficina/'
        $displayname = $aData['dav_displayname'];  // 'oficina'

        $aWhere = ['dav_name' => $dav_name,
            'dav_displayname' => $displayname,
        ];
        $gesCollection = new GestorCollection();
        $cCollection = $gesCollection->getCollections($aWhere);
        foreach ($cCollection as $oCollection) {
            if ($oCollection->DBEliminar() === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oCollection->getErrorTxt();
            }
        }
        return $error_txt;
    }

    private function deleteGrupo($nom_grupo)
    {
        $error_txt = '';

        /* Hay que borrar el Principal y el RoleMember correspondiente al user_no del User
         * se supone que lo hace la BDR al borrar el user
         */
        $aWhere = ['username' => $nom_grupo];
        $gesUsers = new GestorUserDB();
        $cUsers = $gesUsers->getUsersDB($aWhere);
        foreach ($cUsers as $oUser) {
            if ($oUser->DBEliminar() === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oUser->getErrorTxt();
            }
        }
        return $error_txt;
    }

    private function deleteResource($nom_resource)
    {
        $error_txt = '';

        /* Hay que borrar el Principal y el RoleMember correspondiente al user_no del User
         * se supone que lo hace la BDR al borrar el user
         */
        // Hay que borrar un usuario
        $aWhere = ['username' => $nom_resource];
        $gesUsers = new GestorUserDB();
        $cUsers = $gesUsers->getUsersDB($aWhere);
        foreach ($cUsers as $oUser) {
            if ($oUser->DBEliminar() === FALSE) {
                $error_txt .= _("hay un error, no se ha eliminado");
                $error_txt .= "\n" . $oUser->getErrorTxt();
            }
        }
        return $error_txt;
    }

    public function getUsernameDavical($id_cargo)
    {
        // nombre normalizado del usuario y oficina:
        $oCargo = new Cargo($id_cargo);
        $cargo_role = $oCargo->getCargo();

        return $this->getNombreUsuario($cargo_role);
    }

    public function getUsernameDavicalSecretaria()
    {
        // nombre normalizado del usuario y oficina:
        $cargo = 'secretaria';

        return $this->getNombreUsuario($cargo);
    }

    /**
     * @return number
     */
    public function getAmbito()
    {
        return $this->ambito;
    }

    /**
     * @param number $ambito
     */
    public function setAmbito($ambito)
    {
        $this->ambito = $ambito;
    }


}