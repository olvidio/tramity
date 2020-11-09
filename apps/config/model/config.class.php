<?php
namespace config\model;
use config\model\entity\ConfigSchema;

/**
 * Classe 
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */
class Config {
   
    // conversion
    public static $replace  = array(
            'AE' => '&#0198;',
            'Ae' => '&#0198;',
            'ae' => '&#0230;',
            'aE' => '&#0230;',
            'OE' => '&#0338;',
            'Oe' => '&#0338;',
            'oe' => '&#0339;',
            'oE' => '&#0339;'
        );
        
    
    public function getIdioma_default() {
        $parametro = 'idioma_default';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getAmbito() {
        $parametro = 'ambito';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getIni_contador_cr() {
        $parametro = 'ini_contador_cr';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getIni_contador() {
        $parametro = 'ini_contador';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getContador_cr() {
        $this->resetContador();
        $parametro = 'contador_cr';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual)? $this->getIni_contador_cr() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;
        
        $oConfigSchema->setValor($valor_nuevo);
        $oConfigSchema->DBGuardar();
        
        return $valor_actual;
    }
    
    public function getContador_resto() {
        $this->resetContador();
        $parametro = 'contador';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual)? $this->getIni_contador() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;
        
        $oConfigSchema->setValor($valor_nuevo);
        $oConfigSchema->DBGuardar();
        
        return $valor_actual;
    }
    
    public function getContador($bCr) {
        if ($bCr) {
            return $this->getContador_cr();
        } else {
            return $this->getContador_resto(); 
        }
    }
    
    private function resetContador(){
        $any_actual = date('Y');
        $parametro = 'reset_contador';
        $oConfigSchema = new ConfigSchema($parametro);
        $any = $oConfigSchema->getValor();
        if ($any > $any_actual) {
            $oConfigSchema->setValor($any_actual);
            $oConfigSchema->DBGuardar();
            // poner al inicio
            $valor = $this->getIni_contador_cr();
            $oConfigSchemaContador = new ConfigSchema('contador_cr');
            $oConfigSchemaContador->setValor($valor);
            $oConfigSchemaContador->DBGuardar();
            $valor = $this->getIni_contador();
            $oConfigSchemaContador = new ConfigSchema('contador');
            $oConfigSchemaContador->setValor($valor);
            $oConfigSchemaContador->DBGuardar();
        }
    }
    
    public function getSigla() {
        $parametro = 'sigla';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getServerEtherpad() {
        $parametro = 'server_etherpad';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    public function getServerEthercalc() {
        $parametro = 'server_ethercalc';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    // prioridades:
    public function getPlazoUrgente() {
        $parametro = 'plazo_urgente';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getPlazoRapido() {
        $parametro = 'plazo_rapido';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getPlazoNormal() {
        $parametro = 'plazo_normal';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getPlazoError() {
        $parametro = 'plazo_error';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    // SMTP server:
    public function getSMTPSecure() {
        $parametro = 'smtp_secure';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getSMTPHost() {
        $parametro = 'smtp_host';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getSMTPPort() {
        $parametro = 'smtp_port';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getSMTPAuth() {
        $parametro = 'smtp_auth';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getSMTPUser() {
        $parametro = 'smtp_user';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getSMTPPwd() {
        $parametro = 'smtp_pwd';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getFrom() {
        $parametro = 'from';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    public function getReplyTo() {
        $parametro = 'reply_to';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }
    
    
}