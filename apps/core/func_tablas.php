<?php

namespace core;

/**
 * Esta página sólo contiene funciones. Es para incluir en otras.
 *
 *
 * @package    delegacion
 * @subpackage    fichas
 * @author    Daniel Serrabou
 * @since        15/5/02.
 *
 */

/**
 * para convertir los arrays de php para introducir en el postgresql.
 *
 * @param array
 * @return string pg_array
 */
function array_php2pg($phpArray = [])
{
    if (!empty($phpArray) && is_array($phpArray)) {
        $phpArray_filtered = array_filter($phpArray);
    }
    // el join no va si el array esta vacio
    if (empty($phpArray_filtered)) {
        return "{}";
    } else {
        return "{" . join(",", $phpArray_filtered) . "}";
    }
}

/**
 * para convertir los arrays provinientes del postgresql a php.
 *
 * @param pg_array
 * @return array
 */
function array_pg2php($postgresArray)
{
    $str_csv = trim($postgresArray, "{}");
    if (empty($str_csv)) {
        $phpArray = [];
    } else {
        $phpArray = explode(',', $str_csv);
    }
    return $phpArray;
}

/**
 *
 *
 *
 */
function urlsafe_b64encode($string)
{
    $data = base64_encode($string);
    $data = str_replace(array('+', '/', '='), array('-', '_', '.'), $data);
    return $data;
}

function urlsafe_b64decode($string)
{
    $data = str_replace(array('-', '_', '.'), array('+', '/', '='), $string);
    return base64_decode($data);
}

/**
 * Para unificar los valores true ('t', 'true', 1, 'on...)
 *
 *
 * @author    Daniel Serrabou
 * @since        23/3/2020.
 *
 */
function is_true($val)
{
    if (is_string($val)) {
        $val = ($val == 't') ? 'true' : $val;
        $boolval = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    } else {
        $boolval = $val;
    }

    return $boolval;
}

/**
 * Para poner null en los valores vacios de un array
 *
 *
 * @author    Daniel Serrabou
 * @since        28/10/09.
 *
 */
function poner_null(&$valor)
{
    if (!$valor && $valor !== 0) { //admito que sea 0.
        $valor = NULL;
    }
    if ($valor === 'null') {
        $valor = NULL;
    }
}

/**
 * Para poner string empty en los valores null de un array,
 * necesario para la función http_build_query, que no pone
 * los parametros con valor null
 *
 * @author    Daniel Serrabou
 * @since        26/10/18.
 *
 */
function poner_empty_on_null(&$valor)
{
    if ($valor === NULL) {
        $valor = '';
    }
}


/**
 * Función para corregir la del php strnatcasecmp. Compara sin tener en cuenta los acentos. La uso para ordenar arrays.
 *
 */
function strsinacentocmp($str1, $str2)
{
    $acentos = array('Á', 'É', 'Í', 'Ó', 'Ú', 'À', 'È', 'Ì', 'Ò', 'Ù', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü', 'Â', 'Ê', 'Î', 'Ô', 'Û', 'Ñ',
        'á', 'é', 'í', 'ó', 'ú', 'à', 'è', 'ì', 'ò', 'ù', 'ä', 'ë', 'ï', 'ö', 'ü', 'â', 'ê', 'î', 'ô', 'û', 'ñ'
    );
    $sin = array('a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'nz',
        'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'nz'
    );

    $str1 = str_replace($acentos, $sin, $str1);
    $str2 = str_replace($acentos, $sin, $str2);
    return strnatcasecmp($str1, $str2);
}

/**
 * Función para corregir la del php strtoupper. No pone en mayúsculas las vocales acentuadas
 *
 */
function strtoupper_dlb($texto)
{
    $texto = strtoupper($texto);
    $minusculas = array("á", "é", "í", "ó", "ú", "à", "è", "ò", "ñ");
    $mayusculas = array("Á", "É", "Í", "Ó", "Ú", "À", "È", "Ò", "Ñ");

    return str_replace($minusculas, $mayusculas, $texto);
}


function cambiar_idioma($idioma = '')
{
    if (empty($idioma)) {
        // Si no está determinado en las preferencias, miro el del navegador
        if (empty($_SESSION['session_auth']['idioma'])) {
            // mirar el idioma del navegador
            if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) { # Verificamos que el visitante haya designado algún idioma
                $a_idiomas = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]); # Convertimos HTTP_ACCEPT_LANGUAGE en array
                /* Recorremos el array hasta que encontramos un idioma del visitante que coincida con los idiomas
                 en que está disponible nuestra web */
                for ($i = 0; $i < count($a_idiomas); $i++) {
                    if (!isset($idioma)) {
                        if (substr($a_idiomas[$i], 0, 2) == "ca") {
                            $idioma = "ca_ES.UTF-8";
                        }
                        if (substr($a_idiomas[$i], 0, 2) == "es") {
                            $idioma = "es_ES.UTF-8";
                        }
                        if (substr($a_idiomas[$i], 0, 2) == "en") {
                            $idioma = "en_US.UTF-8";
                        }
                        if (substr($a_idiomas[$i], 0, 2) == "de") {
                            $idioma = "de_DE.UTF-8";
                        }
                    }
                }
            }
        } else {
            $idioma = $_SESSION['session_auth']['idioma'];
        }
        # Si no hemos encontrado ningún idioma que nos convenga, mostramos la web en el idioma por defecto
        if (!isset($idioma)) {
            $idioma = $_SESSION['oConfig']->getIdioma_default();
        }
    }
    $idioma = str_replace('UTF-8', 'utf8', $idioma);
    $domain = "tramity";
    setlocale(LC_MESSAGES, "");
    putenv("LC_ALL=''");
    putenv("LANGUAGE=");

    setlocale(LC_MESSAGES, $idioma);
    putenv("LC_ALL=$idioma");
    putenv("LANG=$idioma");

    bindtextdomain($domain, ConfigGlobal::$dir_languages);
    textdomain($domain);
    bind_textdomain_codeset($domain, 'UTF-8');
}

