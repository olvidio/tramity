<?php

namespace core;
class MyCrypt
{
    /*
     * Funcion para encriptar password
     *
     *
     */
    function encode($clear, $hashed = NULL)
    {
        $salt_len = 100;
        if (empty($hashed)) {
            for ($salt = '', $x = 0; $x++ < $salt_len; $salt .= bin2hex(chr(mt_rand(0, 255)))) ;   // make a new salt
        } else {
            $salt = substr($hashed, 0, $salt_len * 2);  //  extract existing salt
        }
        return $salt . hash('whirlpool', $salt . $clear);
    }
}
