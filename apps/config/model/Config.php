<?php

namespace config\model;

use config\model\entity\ConfigSchema;
use config\model\entity\ConfigSchemaPublic;

/**
 * Classe
 *
 * @package orbix
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 7/5/2019
 */
class Config
{

    // conversion
    public static $replace = array(
        'AE' => '&#0198;',
        'Ae' => '&#0198;',
        'ae' => '&#0230;',
        'aE' => '&#0230;',
        'OE' => '&#0338;',
        'Oe' => '&#0338;',
        'oe' => '&#0339;',
        'oE' => '&#0339;'
    );

    public function getMax_filesize_en_kilobytes()
    {
        $megas = $this->getMax_filesize();
        if (empty($megas)) {
            exit (_("Falta definir el tamaño máximo del fichero a subir en la configuración del servidor"));
        }
        return $megas * 1024;
    }

    public function getMax_filesize()
    {
        $parametro = 'max_filesize';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getMax_filesize_en_bytes()
    {
        $megas = $this->getMax_filesize();
        if (empty($megas)) {
            exit (_("Falta definir el tamaño máximo del fichero a subir en la configuración del servidor"));
        }
        return $megas * 1024 * 1024;
    }

    public function getIdioma_default()
    {
        $parametro = 'idioma_default';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getTimeZone()
    {
        $parametro = 'timezone';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor = $oConfigSchema->getValor();
        if (empty($valor)) {
            $valor = "Europe/Madrid";
        }
        return $valor;
    }

    public function getDistribuirTodos(): ?string
    {
        $parametro = 'distribuir_todos';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getChat(): ?string
    {
        $parametro = 'chat';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getAmbito(): int
    {
        $parametro = 'ambito';
        $oConfigSchema = new ConfigSchema($parametro);
        return (integer)$oConfigSchema->getValor();
    }

    public function getContador($id_lugar = '')
    {
        $oConfigSchema = new ConfigSchema('id_lugar_cr');
        $id_lugar_cr = (int)$oConfigSchema->getValor();

        $oConfigSchema = new ConfigSchema('id_lugar_cancilleria');
        $id_cancilleria = (int)$oConfigSchema->getValor();


        $oConfigSchema = new ConfigSchema('id_lugar_uden');
        $id_uden = (int)$oConfigSchema->getValor();

        switch ($id_lugar) {
            case $id_lugar_cr:
                return $this->getContador_cr();
            case $id_cancilleria:
            case $id_uden:
                return $this->getContador_cancilleria();
            default:
                return $this->getContador_resto();
        }
    }

    public function getContador_cr()
    {
        $this->resetContador();
        $parametro = 'contador_cr';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador_cr() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $oConfigSchema->DBGuardar();

        return $valor_actual;
    }

    private function resetContador()
    {
        $any_actual = date('Y');
        $parametro = 'contador_any';
        $oConfigSchema = new ConfigSchema($parametro);
        $any_contador = $oConfigSchema->getValor();
        if ($any_actual > $any_contador) {
            $oConfigSchema->setValor($any_actual);
            $oConfigSchema->DBGuardar();
            // poner al inicio
            $valor = $this->getIni_contador_cancilleria();
            $oConfigSchemaContador = new ConfigSchema('contador_cancilleria');
            $oConfigSchemaContador->setValor($valor);
            $oConfigSchemaContador->DBGuardar();

            $valor = $this->getIni_contador_cr();
            $oConfigSchemaContador = new ConfigSchema('contador_cr');
            $oConfigSchemaContador->setValor($valor);
            $oConfigSchemaContador->DBGuardar();

            $valor = $this->getIni_contador();
            $oConfigSchemaContador = new ConfigSchema('contador');
            $oConfigSchemaContador->setValor($valor);
            $oConfigSchemaContador->DBGuardar();

            // actualizar el contador_any
            $oConfigSchemaContador = new ConfigSchema('contador_any');
            $oConfigSchemaContador->setValor($any_actual);
            $oConfigSchemaContador->DBGuardar();
        }
    }

    public function getIni_contador_cancilleria()
    {
        $parametro = 'ini_contador_cancilleria';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getIni_contador_cr()
    {
        $parametro = 'ini_contador_cr';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getIni_contador()
    {
        $parametro = 'ini_contador';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getContador_cancilleria()
    {
        $this->resetContador();
        $parametro = 'contador_cancilleria';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador_cancilleria() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $oConfigSchema->DBGuardar();

        return $valor_actual;
    }

    public function getContador_resto()
    {
        $this->resetContador();
        $parametro = 'contador';
        $oConfigSchema = new ConfigSchema($parametro);
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $oConfigSchema->DBGuardar();

        return $valor_actual;
    }

    public function getBodyMail()
    {
        $parametro = 'bodyMail';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSigla()
    {
        $parametro = 'sigla';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getLocalidad()
    {
        $parametro = 'localidad';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getServerEtherpad()
    {
        $parametro = 'server_etherpad';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getServerEthercalc()
    {
        $parametro = 'server_ethercalc';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getPeriodoEntradas()
    {
        $parametro = 'periodo_entradas';
        $oConfigSchema = new ConfigSchema($parametro);
        return empty($oConfigSchema->getValor()) ? 15 : $oConfigSchema->getValor();
    }

    // prioridades:
    public function getPlazoUrgente()
    {
        $parametro = 'plazo_urgente';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getPlazoRapido()
    {
        $parametro = 'plazo_rapido';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getPlazoNormal()
    {
        $parametro = 'plazo_normal';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getPlazoError()
    {
        $parametro = 'plazo_error';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    // SMTP server:
    public function getSMTPSecure()
    {
        $parametro = 'smtp_secure';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSMTPHost()
    {
        $parametro = 'smtp_host';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSMTPPort()
    {
        $parametro = 'smtp_port';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSMTPAuth()
    {
        $parametro = 'smtp_auth';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSMTPUser()
    {
        $parametro = 'smtp_user';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getSMTPPwd()
    {
        $parametro = 'smtp_pwd';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getFrom()
    {
        $parametro = 'from';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getReplyTo()
    {
        $parametro = 'reply_to';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getDock()
    {
        $parametro = 'dock';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getNomDock()
    {
        $parametro = 'nomdock';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    public function getServerDavical()
    {
        $parametro = 'server_davical';
        $oConfigSchema = new ConfigSchemaPublic($parametro);
        return $oConfigSchema->getValor();
    }

    // Parametros del scdl
    public function getPerm_distribuir()
    {
        $parametro = 'perm_distribuir';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    public function getPerm_aceptar()
    {
        $parametro = 'perm_aceptar';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

    // config default
    public function getPlataformaMantenimiento()
    {
        $parametro = 'plataforma_mantenimiento';
        $oConfigSchema = new ConfigSchema($parametro);
        return $oConfigSchema->getValor();
    }

}