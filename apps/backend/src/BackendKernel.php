<?php

declare(strict_types = 1);

namespace Tramity\Apps\Backend;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class BackendKernel extends Kernel
{
    use MicroKernelTrait;
    private const CONFIG_EXTS = '.{xml,yaml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    /*
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/services' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/services_' . $this->environment . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/services/*' . self::CONFIG_EXTS, 'glob');
    }
    */

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}');

        if (is_file($configDir.'/services.yaml')) {
            $container->import($configDir.'/services.yaml');
            $container->import($configDir.'/{services}_'.$this->environment.'.yaml');
        } else {
            $container->import($configDir.'/{services}.php');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        //$routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/');
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '');
    }
}