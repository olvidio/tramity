<?php

namespace web;

use DateTime;
use DateTimeZone;

/**
 * Classe per les dates. Afageix a la clase del php la vista amn num. romans.
 *
 * @package delegación
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 26/11/2010
 */
class NullDateTimeLocal extends DateTime
{

    public static function createFromLocal($data)
    {
        return NULL;
    }

    public static function createFromFormat(string $format='', string $datetime='', DateTimeZone $timezone = NULL): DateTime|false
    {
        return FALSE;
    }

    public function getFromLocal()
    {
        return '';
    }

    public function getIsoTime()
    {
        return '';
    }

    public function getIso()
    {
        return '';
    }

    public function getFromLocalHora()
    {
        return '';
    }

    public function format($format): string
    {
        return '';
    }

    public function formatRoman()
    {
        return '';
    }

    public function duracion($oDateDiff)
    {
        return '';
    }

    public function duracionAjustada($oDateDiff)
    {
        return '';
    }
}
