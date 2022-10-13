<?php

namespace core;

/*
 * PgTimestamp
 *
 * Date and timestamp converter
 *
 * @package   Foundation
 * @copyright 2014 - 2017 Grégoire HUBERT
 * @author    Grégoire HUBERT <hubert.greg@gmail.com>
 * @license   X11 {@link http://opensource.org/licenses/mit-license.php}
 * @see       ConverterInterface
 */

class Converter
{

    var $Converter;
    var $type;

    public function __construct($type, $data)
    {
        $this->type = $type;
        switch ($type) {
            case 'date':
            case 'datetime':
                $this->Converter = new PgTimestamp($data, $type);
                break;
            case 'timestamp':
            case 'timestamptz':
                $this->Converter = new PgTimestamp($data, $type);
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
    }

    public function fromPg()
    {
        return $this->Converter->fromPg();
    }

    public function toCal()
    {
        $f_iso = $this->Converter->toPg($this->type);
        return str_replace('-', '', $f_iso);
    }

    public function toPg()
    {
        return $this->Converter->toPg($this->type);
    }
}
