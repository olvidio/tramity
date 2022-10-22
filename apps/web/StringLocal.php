<?php

namespace web;

use Normalizer;

class StringLocal
{

    /**
     *
     * The function is mainly based on the following article:
     * http://ahinea.com/en/tech/accented-translate.html
     *
     * @param string $s
     * @return string|mixed
     */
    public static function toRFC952($original_string)
    {

        $s = self::lowerNormalized($original_string);
        // Quitar acentos:
        $s = self::sinAcentos($s);
        /* quitar caracteres '_',  para internet (rfc952):
        1. A "name" (Net, Host, Gateway, or Domain name) is a text string up
        to 24 characters drawn from the alphabet (A-Z), digits (0-9), minus
        sign (-), and period (.).
        */
        $s = str_replace("_", "", $s);

        if (empty($s)) {
            return $original_string;
        } else {
            return $s;
        }
    }

    /**
     * texto normalizado utf8 en minúsculas
     *
     * @param string $original_string
     * @return string
     */
    public static function lowerNormalized($original_string)
    {
        return mb_strtolower(self::normalizeUtf8String($original_string));
    }

    /**
     *
     * The function is mainly based on the following article:
     * http://ahinea.com/en/tech/accented-translate.html
     *
     * @param string $s
     * @return string|mixed
     */
    public static function normalizeUtf8String($original_string)
    {
        // Normalizer-class missing!
        if (!class_exists("Normalizer", $autoload = false)) {
            return $original_string;
        }
        $s = $original_string;
        // maps German (umlauts) and other European characters onto two characters before just removing diacritics
        $s = preg_replace('@\x{00c4}@u', "AE", $s);    // umlaut Ä => AE
        $s = preg_replace('@\x{00d6}@u', "OE", $s);    // umlaut Ö => OE
        $s = preg_replace('@\x{00dc}@u', "UE", $s);    // umlaut Ü => UE
        $s = preg_replace('@\x{00e4}@u', "ae", $s);    // umlaut ä => ae
        $s = preg_replace('@\x{00f6}@u', "oe", $s);    // umlaut ö => oe
        $s = preg_replace('@\x{00fc}@u', "ue", $s);    // umlaut ü => ue
        $s = preg_replace('@\x{00f1}@u', "ny", $s);    // ñ => ny
        $s = preg_replace('@\x{00ff}@u', "yu", $s);    // ÿ => yu


        // maps special characters (characters with diacritics) on their base-character followed by the diacritical mark
        // exmaple:  Ú => U´,  á => a`
        $s = Normalizer::normalize($s, Normalizer::FORM_D);


        $s = preg_replace('@\pM@u', "", $s);    // removes diacritics


        $s = preg_replace('@\x{00df}@u', "ss", $s);    // maps German ß onto ss
        $s = preg_replace('@\x{00c6}@u', "AE", $s);    // Æ => AE
        $s = preg_replace('@\x{00e6}@u', "ae", $s);    // æ => ae
        $s = preg_replace('@\x{0132}@u', "IJ", $s);    // ? => IJ
        $s = preg_replace('@\x{0133}@u', "ij", $s);    // ? => ij
        $s = preg_replace('@\x{0152}@u', "OE", $s);    // Œ => OE
        $s = preg_replace('@\x{0153}@u', "oe", $s);    // œ => oe

        $s = preg_replace('@\x{00d0}@u', "D", $s);    // Ð => D
        $s = preg_replace('@\x{0110}@u', "D", $s);    // Ð => D
        $s = preg_replace('@\x{00f0}@u', "d", $s);    // ð => d
        $s = preg_replace('@\x{0111}@u', "d", $s);    // d => d
        $s = preg_replace('@\x{0126}@u', "H", $s);    // H => H
        $s = preg_replace('@\x{0127}@u', "h", $s);    // h => h
        $s = preg_replace('@\x{0131}@u', "i", $s);    // i => i
        $s = preg_replace('@\x{0138}@u', "k", $s);    // ? => k
        $s = preg_replace('@\x{013f}@u', "L", $s);    // ? => L
        $s = preg_replace('@\x{0141}@u', "L", $s);    // L => L
        $s = preg_replace('@\x{0140}@u', "l", $s);    // ? => l
        $s = preg_replace('@\x{0142}@u', "l", $s);    // l => l
        $s = preg_replace('@\x{014a}@u', "N", $s);    // ? => N
        $s = preg_replace('@\x{0149}@u', "n", $s);    // ? => n
        $s = preg_replace('@\x{014b}@u', "n", $s);    // ? => n
        $s = preg_replace('@\x{00d8}@u', "O", $s);    // Ø => O
        $s = preg_replace('@\x{00f8}@u', "o", $s);    // ø => o
        $s = preg_replace('@\x{017f}@u', "s", $s);    // ? => s
        $s = preg_replace('@\x{00de}@u', "T", $s);    // Þ => T
        $s = preg_replace('@\x{0166}@u', "T", $s);    // T => T
        $s = preg_replace('@\x{00fe}@u', "t", $s);    // þ => t
        $s = preg_replace('@\x{0167}@u', "t", $s);    // t => t

        // remove all non-ASCii characters
        $s = preg_replace('@[^\0-\x80]@u', "", $s);

        // possible errors in UTF8-regular-expressions
        if (empty($s)) {
            return $original_string;
        } else {
            return $s;
        }
    }

    public static function sinAcentos($source)
    {

        // dirty solution to stop mb_convert_encoding from filling your string
        // with question marks whenever it encounters an illegal character for the target encoding.

        // detect the character encoding of the incoming file
        $encoding = mb_detect_encoding($source, "auto");
        // escape all of the question marks so we can remove artifacts from
        // the unicode conversion process
        $target = str_replace("?", "[question_mark]", $source);

        $target = mb_convert_encoding($target, 'UTF-8', $encoding);
        // remove any question marks that have been introduced because of illegal characters
        $target = str_replace("?", "", $target);
        // replace the token string "[question_mark]" with the symbol "?"
        $target = str_replace("[question_mark]", "?", $target);

        $target = preg_replace("/á|à|â|ã|ª/", "a", $target);
        $target = preg_replace("/Á|À|Â|Ã/", "A", $target);
        $target = preg_replace("/é|è|ê/", "e", $target);
        $target = preg_replace("/É|È|Ê/", "E", $target);
        $target = preg_replace("/í|ì|î/", "i", $target);
        $target = preg_replace("/Í|Ì|Î/", "I", $target);
        $target = preg_replace("/ó|ò|ô|õ|º/", "o", $target);
        $target = preg_replace("/Ó|Ò|Ô|Õ/", "O", $target);
        $target = preg_replace("/ú|ù|û/", "u", $target);
        $target = preg_replace("/Ú|Ù|Û/", "U", $target);
        $target = str_replace("/", "_", $target);
        $target = str_replace(".", "_", $target);
        $target = str_replace(" ", "_", $target);
        $target = str_replace("ñ", "n", $target);
        $target = str_replace("Ñ", "N", $target);

        $target = preg_replace('/[^a-zA-Z0-9_.-]/', '', $target);
        return $target;
    }
}