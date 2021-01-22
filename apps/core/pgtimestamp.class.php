<?php
namespace core;

use web;

/**
 * @author dani
 *
 */
class PgTimestamp {
    
    const TS_FORMAT = 'Y-m-d H:i:s.uP';
    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i:s';
    
   /**
    * @var $data
    */ 
    var $data;
    var $type;
    
    public function __construct($data,$type)  {
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
    public function fromPg() {
        $data = trim($this->data);
        if($data !== '') {
            $oFecha = new web\DateTimeLocal($data);
            //$fecha = $oFecha->createFromLocal($data);
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
     * @param  string  $type
     * @return string  $rta Pg converted string for input.
     */
    public function toPg($type) {
        $rta = null;
        if ($this->data !== null) {
            switch ($type) {
                case 'timestamp':
                    $matches = [];
                    $string = $this->data;
                    // comprobar si el string del timestamp tiene los segundos, o solo H:m
                    $pattern = '/((\d+)[-\/](\d+)[-\/](\d+))\s+(\d{2}):(\d{2}):*((\d{2})*)/i';
                    preg_match($pattern, $string, $matches);
                    // ya tiene los segundos:
                    if (!empty($matches[7])) {
                        $timestamp_with_seconds = $string;
                    } else {
                        // si faltan los segundos los añado (:00)
                        $replacement = '$1 $5:$6:00';
                        $timestamp_with_seconds = preg_replace($pattern, $replacement, $string);
                    }
                    
                    //$rta = sprintf("%s '%s'", $type, $this->checkData($this->data)->format(static::TS_FORMAT));
                    $rta = sprintf("%s", $this->checkData($timestamp_with_seconds)->format(static::TS_FORMAT));
                    break;
                case 'date':
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
     * toPgStandardFormat
     *
     * Convert a PHP representation into short PostgreSQL format like used in
     * COPY values list.
     *
     * @return string $txt  PostgreSQL standard representation.
     */
    public function toPgStandardFormat() {
        return
        $this->data !== null
        ? $this->checkData($this->data)->format(static::TS_FORMAT)
        : null
        ;
    }
    /**
     * checkData
     *
     * Ensure a DateTime instance.
     *
     * @param mixed $data
     * @throws object \Exception
     * @return $data web\DateTimeLocal
     */
    protected function checkData($data)
    {
        if (!$data instanceof \DateTimeInterface) {
            try {
                $data = web\DateTimeLocal::createFromLocal($data,$this->type);
            } catch (\Exception $e) {
                throw new \Exception(
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
}