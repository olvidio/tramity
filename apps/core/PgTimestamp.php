<?php

namespace core;

use DateTimeInterface;
use DateTimeZone;
use Exception;
use web\DateTimeLocal;

/**
 * @author dani
 *
 */
class PgTimestamp
{

    const TS_FORMAT = 'Y-m-d H:i:s.uP';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';

    /**
     * @var $data
     */
    var $data;
    var $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * fromPg
     *
     * Parse the output string from PostgreSQL and returns the converted value
     * into an according PHP representation.
     *
     * @return mixed $data  PHP representation of the data.
     */
    public function fromPg()
    {
        $data = trim($this->data);
        if ($data !== '') {
            if ($this->type === 'datetime_utc') { // pasar de utc a local
                $timeZone = $_SESSION['oConfig']->getTimeZone();
                $oDate = new DateTimeLocal($data);
                $oFecha = $oDate->setTimezone(new DateTimeZone($timeZone));
            } else {
                $oFecha = new DateTimeLocal($data);
            }
        } else {
            $oFecha = null;
        }
        return $oFecha;
    }

    /**
     * toPg
     *
     * Convert a PHP representation into the according Pg formatted string.
     *
     * @param string $type
     * @return string  $rta Pg converted string for input.
     */
    public function toPg($type)
    {
        $rta = null;
        if ($this->data !== null) {
            switch ($type) {
                case 'timestamp':
                case 'timestamptz':
                    $matches = [];
                    $string = $this->data;

                    $local_format = DateTimeLocal::getFormat('-', $type);
                    $format_E = 'j-n-Y H:i:sP';
                    $format_US = 'n-j-Y H:i:sP';
                    $pattern = '/^([0-9]{1,2})[-\/]([0-9]{1,2})[-\/]([0-9]{2,4})[T\s]([0-9]{2}):([0-9]{2}):*([0-9]{2}\.*[0-9]*){0,1}([+|-]([01][0-9]|[2][0-3])[:]*([0-5][0-9])*){0,1}$/';
                    if ($local_format === $format_E) {
                        preg_match($pattern, $string, $matches);
                        //list($full_str,$d,$m,$y,$h,$min,$s,$zone_full,$zone_h,$zone_m)=$matches;
                        // hay que evitar los errores 'Undefined offset'
                        $d = empty($matches[1]) ? '' : $matches[1];
                        $m = empty($matches[2]) ? '' : $matches[2];
                        $y = empty($matches[3]) ? '' : $matches[3];
                        $h = empty($matches[4]) ? '' : $matches[4];
                        $min = empty($matches[5]) ? '' : $matches[5];
                        $s = empty($matches[6]) ? '' : $matches[6];
                        $zone_full = empty($matches[7]) ? '' : $matches[7];
                        $zone_h = empty($matches[8]) ? '' : $matches[8];
                        $zond_m = empty($matches[9]) ? '' : $matches[9];
                    }
                    if ($local_format === $format_US) {
                        preg_match($pattern, $string, $matches);
                        //list($full_str,$d,$m,$y,$h,$min,$s,$zone_full,$zone_h,$zone_m)=$matches;
                        // hay que evitar los errores 'Undefined offset'
                        $m = empty($matches[1]) ? '' : $matches[1];
                        $d = empty($matches[2]) ? '' : $matches[2];
                        $y = empty($matches[3]) ? '' : $matches[3];
                        $h = empty($matches[4]) ? '' : $matches[4];
                        $min = empty($matches[5]) ? '' : $matches[5];
                        $s = empty($matches[6]) ? '' : $matches[6];
                        $zone_full = empty($matches[7]) ? '' : $matches[7];
                        $zone_h = empty($matches[8]) ? '' : $matches[8];
                        $zond_m = empty($matches[9]) ? '' : $matches[9];
                    }

                    // Ya lo pongo en ISO 
                    // ya tiene los segundos:
                    if (!empty($s)) {
                        $timestamp_with_seconds = "$y-$m-$d $h:$min:$s$zone_full";
                    } else {
                        // si faltan los segundos los añado (:00)
                        $timestamp_with_seconds = "$y-$m-$d $h:$min:00$zone_full";
                    }

                    $rta = sprintf("%s", $this->checkData($timestamp_with_seconds)->format(static::TS_FORMAT));
                    break;
                case 'date':
                case 'datetime':
                case 'datetime_utc':
                    $rta = sprintf("%s", $this->checkData($this->data)->format(static::DATE_FORMAT));
                    break;
                case 'time':
                    $rta = sprintf("%s", $this->checkData($this->data)->format(static::TIME_FORMAT));
                    break;
            }
        } else {
            $rta = sprintf("NULL::%s", $type);
        }
        return $rta;
    }

    /**
     * checkData
     *
     * Ensure a DateTime instance.
     *
     * @param mixed $data
     * @return DateTimeInterface|false|mixed|DateTimeLocal $data
     * @throws Exception
     */
    protected function checkData($data)
    {
        if (!$data instanceof DateTimeInterface) {
            try {
                switch ($this->type) {
                    case 'timestamp':
                    case 'timestamptz':
                        $data = new DateTimeLocal($data);
                        break;
                    default:
                        $data = DateTimeLocal::createFromLocal($data, $this->type);
                }
            } catch (Exception $e) {
                throw new Exception(
                    sprintf(
                        "Cannot convert data from invalid datetime representation '%s'.",
                        $data
                    ),
                    null,
                    $e
                );
            }
        }
        return $data;
    }

    /**
     * toPgStandardFormat
     *
     * Convert a PHP representation into short PostgreSQL format like used in
     * COPY values list.
     *
     * @return string $txt  PostgreSQL standard representation.
     */
    public function toPgStandardFormat()
    {
        return
            $this->data !== null
                ? $this->checkData($this->data)->format(static::TS_FORMAT)
                : null;
    }
}