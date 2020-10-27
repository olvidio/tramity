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
    
    
}