<?php

namespace ContainerObQFv85;
include_once \dirname(__DIR__, 6).'/vendor/friends-of-behat/symfony-extension/src/Mink/MinkParameters.php';

class MinkParametersProxy303a72e extends \FriendsOfBehat\SymfonyExtension\Mink\MinkParameters implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyProxyTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        'lazyObjectReal' => [self::class, 'lazyObjectReal', null],
        "\0".self::class."\0lazyObjectReal" => [self::class, 'lazyObjectReal', null],
        "\0".parent::class."\0".'minkParameters' => [parent::class, 'minkParameters', null],
        'minkParameters' => [parent::class, 'minkParameters', null],
    ];

    #[\ReturnTypeWillChange] public function offsetExists($offset): bool
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->offsetExists(...\func_get_args());
        }

        return parent::offsetExists(...\func_get_args());
    }

    #[\ReturnTypeWillChange] public function offsetSet($offset, $value): void
    {
        if (isset($this->lazyObjectReal)) {
            $this->lazyObjectReal->offsetSet(...\func_get_args());
        } else {
            parent::offsetSet(...\func_get_args());
        }
    }

    #[\ReturnTypeWillChange] public function offsetUnset($offset): void
    {
        if (isset($this->lazyObjectReal)) {
            $this->lazyObjectReal->offsetUnset(...\func_get_args());
        } else {
            parent::offsetUnset(...\func_get_args());
        }
    }

    #[\ReturnTypeWillChange] public function count(): int
    {
        if (isset($this->lazyObjectReal)) {
            return $this->lazyObjectReal->count(...\func_get_args());
        }

        return parent::count(...\func_get_args());
    }
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('MinkParametersProxy303a72e', false)) {
    \class_alias(__NAMESPACE__.'\\MinkParametersProxy303a72e', 'MinkParametersProxy303a72e', false);
}
