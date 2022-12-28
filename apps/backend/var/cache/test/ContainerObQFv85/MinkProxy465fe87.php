<?php

namespace ContainerObQFv85;
include_once \dirname(__DIR__, 6).'/vendor/behat/mink/src/Mink.php';

class MinkProxy465fe87 extends \Behat\Mink\Mink implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyProxyTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        'lazyObjectReal' => [self::class, 'lazyObjectReal', null],
        "\0".self::class."\0lazyObjectReal" => [self::class, 'lazyObjectReal', null],
        "\0".parent::class."\0".'defaultSessionName' => [parent::class, 'defaultSessionName', null],
        "\0".parent::class."\0".'sessions' => [parent::class, 'sessions', null],
        'defaultSessionName' => [parent::class, 'defaultSessionName', null],
        'sessions' => [parent::class, 'sessions', null],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('MinkProxy465fe87', false)) {
    \class_alias(__NAMESPACE__.'\\MinkProxy465fe87', 'MinkProxy465fe87', false);
}
