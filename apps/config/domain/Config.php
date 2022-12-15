<?php

namespace config\domain;


use config\domain\repositories\ConfigSchemaPublicRepository;
use config\domain\repositories\ConfigSchemaRepository;

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
        $configSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $configSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
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
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getChat(): ?string
    {
        $parametro = 'chat';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getAmbito(): int
    {
        $parametro = 'ambito';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return (integer)$oConfigSchema->getValor();
    }

    public function getContador($sigla = '')
    {
        if ($sigla == 'cr') {
            return $this->getContador_cr();
        } elseif ($sigla == 'iese') {
            return $this->getContador_iese();
        } else {
            return $this->getContador_resto();
        }
    }

    public function getContador_cr()
    {
        $this->resetContador();
        $parametro = 'contador_cr';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador_cr() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $ConfigSchemaRepository->Guardar($oConfigSchema);

        return $valor_actual;
    }

    private function resetContador()
    {
        $any_actual = date('Y');
        $parametro = 'contador_any';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        $any_contador = $oConfigSchema->getValor();
        if ($any_actual > $any_contador) {
            $oConfigSchema->setValor($any_actual);
            $ConfigSchemaRepository->Guardar($oConfigSchema);
            // poner al inicio
            $valor = $this->getIni_contador_iese();
            $oConfigSchemaContador = $ConfigSchemaRepository->findById('contador_iese');
            if ($oConfigSchemaContador === null) {
                return '';
            }
            $oConfigSchemaContador->setValor($valor);
            $ConfigSchemaRepository->Guardar($oConfigSchemaContador);
            $valor = $this->getIni_contador_cr();
            $oConfigSchema = $ConfigSchemaRepository->findById('contador_cr');
            if ($oConfigSchema === null) {
                return '';
            }
            $oConfigSchemaContador->setValor($valor);
            $ConfigSchemaRepository->Guardar($oConfigSchemaContador);
            $valor = $this->getIni_contador();
            $oConfigSchema = $ConfigSchemaRepository->findById('contador');
            if ($oConfigSchema === null) {
                return '';
            }
            $oConfigSchemaContador->setValor($valor);
            $ConfigSchemaRepository->Guardar($oConfigSchemaContador);
            // actualizar el contador_any
            $oConfigSchema = $ConfigSchemaRepository->findById('contador_any');
            if ($oConfigSchema === null) {
                return '';
            }
            $oConfigSchemaContador->setValor($any_actual);
            $ConfigSchemaRepository->Guardar($oConfigSchemaContador);
        }
    }

    public function getIni_contador_iese()
    {
        $parametro = 'ini_contador_iese';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getIni_contador_cr()
    {
        $parametro = 'ini_contador_cr';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getIni_contador()
    {
        $parametro = 'ini_contador';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getContador_iese()
    {
        $this->resetContador();
        $parametro = 'contador_iese';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador_iese() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $ConfigSchemaRepository->Guardar($oConfigSchema);

        return $valor_actual;
    }

    public function getContador_resto()
    {
        $this->resetContador();
        $parametro = 'contador';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        $valor_actual = $oConfigSchema->getValor();
        $valor_actual = empty($valor_actual) ? $this->getIni_contador() : $valor_actual;
        $valor_nuevo = $valor_actual + 1;

        $oConfigSchema->setValor($valor_nuevo);
        $ConfigSchemaRepository->Guardar($oConfigSchema);

        return $valor_actual;
    }

    public function getBodyMail()
    {
        $parametro = 'bodyMail';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getSigla()
    {
        $parametro = 'sigla';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getLocalidad()
    {
        $parametro = 'localidad';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getServerEtherpad()
    {
        $parametro = 'server_etherpad';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getServerEthercalc()
    {
        $parametro = 'server_ethercalc';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getPeriodoEntradas()
    {
        $parametro = 'periodo_entradas';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return empty($oConfigSchema->getValor()) ? 15 : $oConfigSchema->getValor();
    }

    // prioridades:
    public function getPlazoUrgente()
    {
        $parametro = 'plazo_urgente';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getPlazoRapido()
    {
        $parametro = 'plazo_rapido';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getPlazoNormal()
    {
        $parametro = 'plazo_normal';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getPlazoError()
    {
        $parametro = 'plazo_error';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    // SMTP server:
    public function getSMTPSecure()
    {
        $parametro = 'smtp_secure';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getSMTPPort()
    {
        $parametro = 'smtp_port';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getSMTPAuth()
    {
        $parametro = 'smtp_auth';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getSMTPUser()
    {
        $parametro = 'smtp_user';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getSMTPPwd()
    {
        $parametro = 'smtp_pwd';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getFrom()
    {
        $parametro = 'from';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getReplyTo()
    {
        $parametro = 'reply_to';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getDock()
    {
        $parametro = 'dock';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getNomDock()
    {
        $parametro = 'nomdock';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getServerDavical()
    {
        $parametro = 'server_davical';
        $ConfigSchemaPublicRepository = new ConfigSchemaPublicRepository();
        $oConfigSchema = $ConfigSchemaPublicRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    // Parametros del scdl
    public function getPerm_distribuir()
    {
        $parametro = 'perm_distribuir';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    public function getPerm_aceptar()
    {
        $parametro = 'perm_aceptar';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

    // config default
    public function getPlataformaMantenimiento()
    {
        $parametro = 'plataforma_mantenimiento';
        $ConfigSchemaRepository = new ConfigSchemaRepository();
        $oConfigSchema = $ConfigSchemaRepository->findById($parametro);
        if ($oConfigSchema === null) {
            return '';
        }
        return $oConfigSchema->getValor();
    }

}