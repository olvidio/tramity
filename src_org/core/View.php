<?php

namespace core;

/**
 * Set
 *
 * Classe per a gestionar una col·lecció d'objectes.
 *
 * @package delegación
 * @subpackage model
 * @author
 * @version 1.0
 * @created 22/9/2010
 */
class View
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Namespace
     *
     * @var string
     */
    private $snamespace;


    /* CONSTRUCTOR -------------------------------------------------------------- */
    /**
     * Constructor de la classe.
     *
     *
     */
    public function __construct($namespace)
    {
        $this->snamespace = $namespace;
    }


    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    public function render($file, $variables = array()): void
    {

        extract($variables);

        ob_start();
        $dir_apps = '/src';
        $base_dir = $_SERVER['DOCUMENT_ROOT'] . $dir_apps;

        // reemplazo controller o model por view
        $patterns = array();
        $patterns[0] = '/controller/';
        $patterns[1] = '/model/';
        $replacements = array();
        $replacements[0] = 'view';
        $replacements[1] = 'view';
        $new_dir = preg_replace($patterns, $replacements, $this->snamespace);

        $new_dir = str_replace('\\', DIRECTORY_SEPARATOR, $new_dir);

        $fileName = $base_dir . DIRECTORY_SEPARATOR . $new_dir . DIRECTORY_SEPARATOR . $file;

        require $fileName;

        $out2 = ob_get_clean();

        echo $out2;
    }
}