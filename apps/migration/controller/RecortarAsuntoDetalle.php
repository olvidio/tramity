<?php

// INICIO Cabecera global de URL de controlador *********************************

use web\DateTimeLocal;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oHoy = new DateTimeLocal();
$oFCorte = (new web\DateTimeLocal)->sub(interval: new DateInterval('P3Y'));
$f_corte_iso = $oFCorte->getIso();

$oDbl = $GLOBALS['oDBT'];
$centro = 'dlb';

$lista_palabras = getLlista();
foreach ($lista_palabras as $word => $replace) {
    //asunto
    $sql = "UPDATE $centro.entradas 
        SET asunto = regexp_replace(asunto, '$word', '$replace')
         WHERE asunto ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "1 Error de algún tipo..." . "<br>";
    }
    //asunto_entrada
    $sql = "UPDATE $centro.entradas 
        SET asunto_entrada = regexp_replace(asunto_entrada, '$word', '$replace')
         WHERE asunto_entrada ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "2 Error de algún tipo..." . "<br>";
    }

    //detalle
    $sql = "UPDATE $centro.entradas 
        SET detalle = regexp_replace(detalle, '$word', '$replace')
         WHERE detalle ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "3 Error de algún tipo..." . "<br>";
    }
}

echo "Entradas: fet!<br>";

foreach ($lista_palabras as $word => $replace) {
    //asunto
    $sql = "UPDATE $centro.escritos 
        SET asunto = regexp_replace(asunto, '$word', '$replace')
         WHERE asunto ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "4 Error de algún tipo..." . "<br>";
    }
    //detalle
    $sql = "UPDATE $centro.escritos 
        SET detalle = regexp_replace(detalle, '$word', '$replace')
         WHERE detalle ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "5 Error de algún tipo..." . "<br>";
    }
}

echo "Escritos: fet!<br>";

foreach ($lista_palabras as $word => $replace) {
    //asunto
    $sql = "UPDATE $centro.expedientes 
        SET asunto = regexp_replace(asunto, '$word', '$replace')
         WHERE asunto ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "6 Error de algún tipo..." . "<br>";
    }
    //entradilla
    $sql = "UPDATE $centro.expedientes 
        SET entradilla = regexp_replace(entradilla, '$word', '$replace')
         WHERE entradilla ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "7 Error de algún tipo..." . "<br>";
    }
}

echo "Expedientes: fet!<br>";

foreach ($lista_palabras as $word => $replace) {
    //asunto_entrada
    $sql = "UPDATE public.entradas_compartidas
        SET asunto_entrada = regexp_replace(asunto_entrada, '$word', '$replace')
         WHERE asunto_entrada ~* '$word' ";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "8 Error de algún tipo..." . "<br>";
    }
}

echo "Entradas compartidas: fet!<br>";


function getLlista()
{

    $aPalabras = [
        'abusos' => 'regalos',
        'apostolado' => 'actividad',
        'becas' => 'casas',
        'capellanes' => 'jardines',
        'cartas' => 'montañas',
        'compromiso vocacional' => 'excursión',
        'conciencia' => 'nube',
        'confianza' => 'batalla',
        'contables' => 'deportes',
        'dinero' => 'libros',
        'dispensa' => 'cartera',
        'económica' => 'regata',
        'económicas' => 'regatas',
        'entidades' => 'velas',
        'Estipendios' => 'patines',
        'exigencia' => 'comprensión',
        'financiación' => 'Balanza',
        'fiscales' => 'pájaros',
        'fondos' => 'experiencias',
        'fuero interno' => 'zapato',
        'fundación' => 'sendero',
        'gestoras ' => 'cuentas',
        'gobierno' => 'coordinación',
        'IESE' => 'Aragón',
        'ingresos' => 'materiales',
        'jurídicas' => 'turísticas',
        'jurídico' => 'turístico',
        'laborales' => 'locales',
        'miembros ' => 'vecinos',
        'Numerarias Auxiliares' => 'Personas',
        'Padre' => 'amigo',
        'profesional' => 'deportiva',
        'propietarias ' => 'mariposas',
        'recursos' => 'agendas',
        'siguen adelante' => 'suben',
        'sostenimiento económico' => 'cambio',
        'testamento' => 'recuerdo',
        'UIC' => 'Sevilla',
        'vocación' => 'deportiva',
        'vocaciones' => 'mejoras',
    ];

    return $aPalabras;
}