<?php

namespace ContainerKnNlVQr;
include_once \dirname(__DIR__, 6).'/vendor/symfony/dependency-injection/ContainerInterface.php';

class ContainerInterfaceProxy2739a3b implements \Symfony\Component\DependencyInjection\ContainerInterface, \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyProxyTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        'lazyObjectReal' => [self::class, 'lazyObjectReal', null],
        "\0".self::class."\0lazyObjectReal" => [self::class, 'lazyObjectReal', null],
    ];

    public function initializeLazyObject(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal;
        }

        return $this;
    }

    public function set(string $id, ?object $service)
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->set(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::set()".');
    }

    public function get(string $id, int $invalidBehavior = \Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->get(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::get()".');
    }

    public function has(string $id): bool
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->has(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::has()".');
    }

    public function initialized(string $id): bool
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->initialized(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::initialized()".');
    }

    public function getParameter(string $name)
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->getParameter(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::getParameter()".');
    }

    public function hasParameter(string $name): bool
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->hasParameter(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::hasParameter()".');
    }

    public function setParameter(string $name, \UnitEnum|array|bool|float|int|null|string $value)
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->setParameter(...\func_get_args());
        }

        return throw new \BadMethodCallException('Cannot forward abstract method "Symfony\Component\DependencyInjection\ContainerInterface::setParameter()".');
    }
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('ContainerInterfaceProxy2739a3b', false)) {
    \class_alias(__NAMESPACE__.'\\ContainerInterfaceProxy2739a3b', 'ContainerInterfaceProxy2739a3b', false);
}
