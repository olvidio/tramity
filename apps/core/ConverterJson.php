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

use JsonException;
use stdClass;

class ConverterJson
{

    private string|array|stdClass|null $json;
    private bool $bArray;

    public function __construct($json, $bArray=FALSE)
    {
        $this->json = $json;
        $this->bArray = $bArray;
    }

    /**
     * @throws JsonException
     */
    public function fromPg(): stdClass|array|null
    {
        if ($this->json !== NULL && $this->json !== '[]') {
            $oJSON = json_decode($this->json, FALSE, 512, JSON_THROW_ON_ERROR);
        } else {
            $oJSON = new stdClass;
        }

        return $oJSON;
    }

    /**
     * @throws JsonException
     */
    public function toPg(): string
    {
        return json_encode($this->json, JSON_THROW_ON_ERROR);
    }

}
