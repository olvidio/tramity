<?php

namespace devel\controller;

use core\ServerConf;
use ReflectionMethod;

/**
 * programa per generar les classes a partir de la taula
 *
 */
/**
 * Para asegurar que inicia la sesión, y poder acceder a los permisos
 */
// INICIO Cabecera global de URL de controlador *********************************
require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
require_once("src_org/devel/controller/func_factory.php");
// FIN de  Cabecera global de URL de controlador ********************************

$Q_clase = (string)filter_input(INPUT_POST, 'clase');
$Q_clase_plural = (string)filter_input(INPUT_POST, 'clase_plural');
$Q_grupo = (string)filter_input(INPUT_POST, 'grupo');

if (empty($Q_clase)) {
    exit("Ha de dir quina clase");
}


$clase = !empty($Q_clase) ? $Q_clase : $tabla;
if (!empty($Q_clase_plural)) {
    $clase_plural = $Q_clase_plural;
} else {
    //plural de la clase
    if (preg_match('/[aeiou]$/', $clase)) {
        $clase_plural = $clase . 's';
    } else {
        $clase_plural = $clase . 'es';
    }
}

$gestor_base = 'get' . $clase_plural;
$gestor_query = 'get' . $clase_plural . 'Query';


/* buscar en el fichero gestor */
$gestor = "Gestor" . ucfirst($Q_clase);
$filenameOld = ServerConf::DIR . '/src/' . $Q_grupo . '/legacy/zzzGestor' . $Q_clase . 'Old.php';
if (!file_exists($filenameOld)) {
    exit("No encuentro el fichero $filenameOld");
}

/*
$content = file_get_contents($filenameOld);
$pattern = '/^function \s+' . $gestor . '/im';
*/


$class_name_old = $Q_grupo . '\\legacy\\zzzGestor' . $Q_clase . 'Old';

$Class = new $class_name_old();

$class_methods = get_class_methods($Class);

$a_metodos = [];
foreach ($class_methods as $method_name) {
    // descartar algunos:
    if ($method_name === $gestor_base) {
        continue;
    }
    if ($method_name === $gestor_query) {
        continue;
    }
    if ($method_name === 'getoDbl') {
        continue;
    }
    if ($method_name === 'setoDbl') {
        continue;
    }
    if ($method_name === 'getNomTabla') {
        continue;
    }
    if ($method_name[0] === 'z') {
        continue;
    }
    if ($method_name[0] === '_') {
        continue;
    }
    $a_metodos[] = $method_name;
}

foreach ($a_metodos as $name) {
    echo "$name<br>";
}


$txt_extra_repository = "\n";
$txt_extra_repository .= '/* -------------------- GESTOR EXTRA ---------------------------------------- */';
$txt_extra_repository .= "\n";

$txt_extra_interface = "\n";
$txt_extra_interface .= '/* -------------------- GESTOR EXTRA ---------------------------------------- */';
$txt_extra_interface .= "\n";

$txt_extra_pg_repository = "\n";
$txt_extra_pg_repository .= '/* -------------------- GESTOR EXTRA ---------------------------------------- */';
$txt_extra_pg_repository .= "\n";

foreach ($a_metodos as $method) {
    $func = new ReflectionMethod($Class, $method);
    $f = $func->getFileName();
    $start_line = $func->getStartLine() - 1;
    $end_line = $func->getEndLine();
    $length = $end_line - $start_line;
    $source = file_get_contents($f);
    $source = preg_split('/' . PHP_EOL . '/', $source);
    $body = implode(PHP_EOL, array_slice($source, $start_line, $length));

    $txt_extra_pg_repository .= $body;
    $txt_extra_pg_repository .= "\n\n";

    $name_function = $source[$start_line];

    if (str_contains($name_function, 'private')) {
        continue;
    }
    if (str_contains($name_function, 'protected')) {
        continue;
    }
    $name_function = str_replace('public', '', $name_function);
    $name_function = str_replace('function', '', $name_function);
    $name_function = trim($name_function);

    $txt_extra_interface .= "\n\t";
    $txt_extra_interface .= 'public function ' . $name_function . ';';
    $txt_extra_interface .= "\n";

    $txt_extra_repository .= "\n\t";
    $txt_extra_repository .= 'public function ' . $name_function;
    $txt_extra_repository .= "\n\t{";
    $txt_extra_repository .= "\n\t\t" . 'return $this->repository->' . $name_function . ';';
    $txt_extra_repository .= "\n\t}";
    $txt_extra_repository .= "\n";
}
// add final of class
$txt_extra_pg_repository .= "}";
$txt_extra_interface .= "}";
$txt_extra_repository .= "}";

// ------------------------------ PG REPOSITORY -----------------------------
$pg_clase = "Pg" . $Q_clase . "Repository";

$dir_infra = ServerConf::DIR . '/src/' . $Q_grupo . '/infrastructure';
$filename = $dir_infra . '/' . $pg_clase . '.php';
// borrar la llave del final de la clase:
$txt = file_get_contents($filename);
$end = strpos($txt, '}', -1);
$txt_recortado = substr($txt, 0, $end);

$txt_completo = $txt_recortado . $txt_extra_pg_repository;

// Write $somecontent to our opened file.
if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
if (fwrite($handle, $txt_completo) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote gestor to file ($filename)";
fclose($handle);

// ------------------------------ REPOSITORY INTERFACE -----------------------------

$clase_interface = $Q_clase . "RepositoryInterface";

$dir_repositories = ServerConf::DIR . '/src/' . $Q_grupo . '/domain/repositories';
$filename = $dir_repositories . '/' . $clase_interface . '.php';
// borrar la llave del final de la clase:
$txt = file_get_contents($filename);
$end = strpos($txt, '}', -1);
$txt_recortado = substr($txt, 0, $end);

$txt_completo = $txt_recortado . $txt_extra_interface;

if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
if (fwrite($handle, $txt_completo) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote gestor to file ($filename)";
fclose($handle);

// ------------------------------ REPOSITORY  -----------------------------

$clase_repository = $Q_clase . "Repository";

$dir_repositories = ServerConf::DIR . '/src/' . $Q_grupo . '/domain/repositories';
$filename = $dir_repositories . '/' . $clase_repository . '.php';
// borrar la llave del final de la clase:
$txt = file_get_contents($filename);
$end = strpos($txt, '}', -1);
$txt_recortado = substr($txt, 0, $end);

$txt_completo = $txt_recortado . $txt_extra_repository;

if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
if (fwrite($handle, $txt_completo) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote gestor to file ($filename)";
fclose($handle);

