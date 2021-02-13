<?php
namespace davical\model;

use davical\model\entity\UserDB;
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
class User Extends UserDB {
    
    /* CONSTRUCTOR -------------------------------------------------------------- */

    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function xxguardar() {
        $Qusuario = (string) \filter_input(INPUT_POST, 'usuario');
        
        if (empty($Qusuario)) { echo _("debe poner un nombre"); }
        $Qid_usuario = (integer) \filter_input(INPUT_POST, 'id_usuario');
        $Qid_cargo = (integer) \filter_input(INPUT_POST, 'id_cargo');
        $Qemail = (string) \filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        
        $Qnom_usuario = (string) \filter_input(INPUT_POST, 'nom_usuario');
        $Qpassword = (string) \filter_input(INPUT_POST, 'password');
        $Qpass = (string) \filter_input(INPUT_POST, 'pass');
        
        $oUsuario = new UserDB(array('id_usuario' => $Qid_usuario));
        $oUsuario->DBCarregar();
        
        $this->setUser_no($aDades['user_no']);
        $this->setActive($aDades['active']);
        $this->setEmail_ok($aDades['email_ok']);
        $this->setJoined($aDades['joined']);
        $this->setUpdated($aDades['updated']);
        $this->setLast_used($aDades['last_used']);
        $this->setUsername($aDades['username']);
        $this->setPassword($aDades['password']);
        $this->setFullname($aDades['fullname']);
        $this->setEmail($aDades['email']);
        $this->setConfig_data($aDades['config_data']);
        $this->setDate_format_type($aDades['date_format_type']);
        $this->setLocale($aDades['locale']);
        
        
        $date_format_type = [ 
            'E' => 'European',
            'U' => 'US Format',
            'I' => 'ISO Format',
            ];
    
    }
}