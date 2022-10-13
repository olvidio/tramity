<?php

namespace web;

/**
 * Classe per les dates. Afageix a la clase del php la vista amn num. romans.
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2010
 */
class DateTimeLocal extends \DateTime
{

    static public function createFromLocal($data, $type = '')
    {
        // Cambiar '-' por '/':
        $data = str_replace('-', '/', $data);
        $format = self::getFormat('/', $type);

        $extnd_dt = new static();
        $parent_dt = parent::createFromFormat($format, $data);

        if (!$parent_dt) {
            return false;
        }

        $extnd_dt->setTimestamp($parent_dt->getTimestamp());
        /* corregir en el caso que el año tenga dos digitos
         * No sirve para el siglo I (0-99) ;-) */
        $yy = $extnd_dt->format('y');
        $yyyy = $extnd_dt->format('Y');
        if (($yyyy - $yy) == 0) {
            $currentY4 = date('Y');
            $currentY2 = date('y');
            $currentMilenium = $currentY4 - $currentY2;

            $extnd_dt->add(new \DateInterval('P' . $currentMilenium . 'Y'));
        }

        return $extnd_dt;
    }

    /**
     * Devuelve el formato de fecha según el idioma del usuario (d/m/y, o m/d/Y)
     *
     * @param string $separador separador entre dia, mes año
     * @return string
     */
    static public function getFormat($separador = '/', $type = '')
    {
        $idioma = $_SESSION['session_auth']['idioma'];
        # Si no hemos encontrado ningún idioma que nos convenga, mostramos la web en el idioma por defecto
        if (!isset($idioma)) {
            $idioma = $_SESSION['oConfig']->getIdioma_default();
        }
        $a_idioma = explode('.', $idioma);
        $code_lng = $a_idioma[0];
        switch ($code_lng) {
            case 'en_US':
                $format = 'n' . $separador . 'j' . $separador . 'Y';
                break;
            default:
                $format = 'j' . $separador . 'n' . $separador . 'Y';
        }
        if (!empty($type) && ($type == 'timestamp' || $type == 'timestamptz')) {
            $format .= ' H:i:sP';
        }
        return $format;
    }

    static public function createFromFormat($format, $data, \DateTimeZone $TimeZone = NULL)
    {
        $extnd_dt = new static();
        $parent_dt = parent::createFromFormat($format, $data, $TimeZone);

        if (!$parent_dt) {
            return false;
        }
        $extnd_dt->setTimestamp($parent_dt->getTimestamp());
        return $extnd_dt;
    }

    public function getFechaLatin()
    {
        $mes_latin = $this->Meses_latin();

        $dia = parent::format('j'); //sin ceros iniciales
        $mes = parent::format('n'); //sin ceros iniciales
        $any = parent::format('Y');

        return "die " . $dia . " mense  " . $mes_latin[$mes] . "  anno  " . $any;
    }

    public static function Meses_latin()
    {
        return [
            '1' => 'ianuario',
            '2' => 'februario',
            '3' => 'martio',
            '4' => 'aprili',
            '5' => 'maio',
            '6' => 'iunio',
            '7' => 'iulio',
            '8' => 'augusto',
            '9' => 'septembri',
            '10' => 'octobri',
            '11' => 'novembri',
            '12' => 'decembri',
        ];
    }

    public function format($format)
    {
        $english = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $local = array_values(self::arrayDiasSemana());
        return str_replace($english, $local, parent::format($format));
    }

    /**
     *
     * creo un array con los dias de la semana
     *    primero el id (en inglés), después el nombre
     *
     */
    public static function arrayDiasSemana()
    {
        return [
            "MO" => _("lunes"),
            "TU" => _("martes"),
            "WE" => _("miércoles"),
            "TH" => _("jueves"),
            "FR" => _("viernes"),
            "SA" => _("sábado"),
            "SU" => _("domingo"),
        ];
    }

    /**
     * devuelve el mes en texto (según el idioma del usuario)
     *
     */
    public function getMesLocalTxt()
    {
        $mes_num = parent::format('n');
        $aMeses = self::Meses();
        return $aMeses[$mes_num];
    }

    public static function Meses()
    {
        return [
            '1' => _("enero"),
            '2' => _("febrero"),
            '3' => _("marzo"),
            '4' => _("abril"),
            '5' => _("mayo"),
            '6' => _("junio"),
            '7' => _("julio"),
            '8' => _("agosto"),
            '9' => _("septiembre"),
            '10' => _("octubre"),
            '11' => _("noviembre"),
            '12' => _("diciembre")
        ];
    }

    public function getIsoTime()
    {
        return parent::format('Y-m-d H:i:s');
    }

    public function getIso()
    {
        return parent::format('Y-m-d');
    }

    /**
     * Devuelve la fecha (+ Hora) en el formato local (según el idioma del usuario)
     *
     * @param string $separador (.-/)
     * @return string
     */
    public function getFromLocalHora($separador = '/')
    {
        $format = $this->getFormat($separador);
        $format .= ' H:i:s';
        return parent::format($format);
    }

    public function formatRoman()
    {
        $a_num_romanos = array('1' => "I", '2' => "II", '3' => "III", '4' => "IV", '5' => "V", '6' => "VI", '7' => "VII", '8' => "VIII", '9' => "IX",
            '10' => "X", '11' => "XI", '12' => "XII");
        $dia = parent::format('j');
        $mes = parent::format('n');
        $any = parent::format('y');
        return "$dia." . $a_num_romanos[$mes] . ".$any";
    }

    public function duracion($oDateDiff)
    {
        $interval = $this->diff($oDateDiff);
        $horas = $interval->format('%a') * 24 + $interval->format('%h') + $interval->format('%i') / 60 + $interval->format('%s') / 3600;
        return round($horas / 24, 2);
    }

    public function duracionAjustada($oDateDiff)
    {
        $interval = $this->diff($oDateDiff);
        $horas = $interval->format('%a') * 24 + $interval->format('%h') + $interval->format('%i') / 60 + $interval->format('%s') / 3600 + 12;
        $dias = $horas / 24;
        $e_dias = ($dias % $horas);
        $dec = round(($dias - $e_dias), 1);
        if ($dec > 0.1) {
            $dec = 0.5;
        } else {
            $dec = 0;
        }
        return ($e_dias + $dec);
    }

    /**
     * comprueba que no exista solape o vacios entre periodos.
     * oInicio y oFin deben ser objetos DatetimeLocal.
     *
     * @param array $cPeriodos ['oInicio','oFin','Descripcion']
     * @return boolean|string
     */
    public function comprobarSolapes($cPeriodos)
    {
        $i = 0;
        $error_txt = '';
        foreach ($cPeriodos as $aPeriodo) {
            $i++;
            $oF_ini = $aPeriodo['inicio'];
            $oF_fin = $aPeriodo['fin'];

            //Fecha fin periodo debe ser posterior a fecha inicio
            if ($oF_fin == $oF_ini) {
                $fecha = $oF_fin->getFromLocal();
                $error_txt .= empty($error_txt) ? '' : '<br>';
                $error_txt .= sprinitf(_("la fecha fin es igual a la fecha inicio en el periodo %s: %s"), $i, $fecha);
            }
            if ($oF_fin < $oF_ini) {
                $fecha = $oF_ini->getFromLocal();
                $error_txt .= empty($error_txt) ? '' : '<br>';
                $error_txt .= sprintf(_("la fecha fin es menor que la fecha inicio en el periodo %s: %s"), $i, $fecha);
            }

            // Siguiente
            if ($aPeriodoNext = next($cPeriodos)) {
                $oF_ini_next = $aPeriodoNext['inicio'];
                $interval = $oF_fin->diff($oF_ini_next);
                if ($interval->format('%r%d') > 1) {
                    $fecha = $oF_fin->getFromLocal();
                    $error_txt .= empty($error_txt) ? '' : '<br>';
                    $error_txt .= sprintf(_("dias libres cerca de %s"), $fecha);
                }
                if ($interval->format('%r%d') < 1) {
                    $fecha = $oF_fin->getFromLocal();
                    $error_txt .= empty($error_txt) ? '' : '<br>';
                    $error_txt .= sprintf(_("hay un solape cerca de %s"), $fecha);
                }
            }
        }
        if (empty($error_txt)) {
            return FALSE;
        } else {
            return $error_txt;
        }

    }

    /**
     * Devuelve la fecha en el formato local (según el idioma del usuario)
     *
     * @param string $separador (.-/)
     * @return string
     */
    public function getFromLocal($separador = '/')
    {
        $format = $this->getFormat($separador);
        return parent::format($format);
    }
}
