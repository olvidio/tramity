<?php

namespace core;

use Exception;
use jblond\TwigTrans\Translation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;


require_once(ConfigGlobal::$dir_libs . '/vendor/autoload.php');

/**
 *
 *
 * @package delegación
 * @subpackage model
 * @author
 * @version 1.0
 * @created 22/6/2020
 */
class ViewTwig extends Environment
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Namespace
     *
     * @var Twig\Loader\FilesystemLoader
     */
    private $loader;


    /* CONSTRUCTOR -------------------------------------------------------------- */
    /**
     * Constructor de la classe.
     *
     * param string  $dirname Es el directorio donde están las plantillas de twig
     * param array $paths $namespace => $path los possibles directorios donde buscar plantillas, son el namespace. (se antepone @).
     *
     * return \Twig\Environment
     */
    function __construct($dirname, array $paths = [])
    {

        $abs_dir = $this->setAbsolutePath($dirname);

        $loader = new FilesystemLoader($abs_dir);

        foreach ($paths as $namespace => $path) {
            $abs_dir = $this->setAbsolutePath($path);
            $loader->addPath($abs_dir, $namespace);
        }

        $dir_js = $this->getJsPath();
        $loader->addPath($dir_js, 'global_js');

        $options = [
            //'cache' => '/path/to/compilation_cache',
            'cache' => false,
            'debug' => true,
            'auto_reload' => true,
        ];
        $filter = new TwigFilter('trans', function (Environment $env, $context, $string) {
            return Translation::TransGetText($string, []);
        }, ['needs_context' => true, 'needs_environment' => true]);

        parent::__construct($loader, $options);
        // load the i18n extension for using the translation tag for twig
        // {% trans %}my string{% endtrans %}
        parent::addFilter($filter);
        parent::addExtension(new Translation());

    }

    private function setAbsolutePath($dirname)
    {
        //$dir_apps = ConfigGlobal::$web_path.'/apps';
        // en este caso ya esta en document_root
        $dir_apps = '/src';
        $base_dir = $_SERVER['DOCUMENT_ROOT'] . $dir_apps;

        // reemplazo controller o model por view
        $patterns = array();
        $patterns[0] = '/controller/';
        $patterns[1] = '/model/';
        $replacements = array();
        $replacements[0] = 'view';
        $replacements[1] = 'view';

        $new_dir = preg_replace($patterns, $replacements, $dirname);
        $new_dir = str_replace('\\', DIRECTORY_SEPARATOR, $new_dir);

        // dir_templates
        return $base_dir . DIRECTORY_SEPARATOR . $new_dir;
    }

    private function getJsPath()
    {
        $dir_apps = '';
        $base_dir = $_SERVER['DOCUMENT_ROOT'] . $dir_apps;
        $new_dir = 'js';

        // dir_templates
        return $base_dir . DIRECTORY_SEPARATOR . $new_dir;
    }

    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    public function renderizar($name, $context): void
    {
        try {
            $tpl = $this->load($name);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }

        echo $tpl->render($context);
    }

}