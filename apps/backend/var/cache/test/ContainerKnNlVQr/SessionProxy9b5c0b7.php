<?php

namespace ContainerKnNlVQr;
include_once \dirname(__DIR__, 6).'/vendor/behat/mink/src/Session.php';

class SessionProxy9b5c0b7 extends \Behat\Mink\Session implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyProxyTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        'lazyObjectReal' => [self::class, 'lazyObjectReal', null],
        "\0".self::class."\0lazyObjectReal" => [self::class, 'lazyObjectReal', null],
        "\0".parent::class."\0".'driver' => [parent::class, 'driver', null],
        "\0".parent::class."\0".'page' => [parent::class, 'page', null],
        "\0".parent::class."\0".'selectorsHandler' => [parent::class, 'selectorsHandler', null],
        'driver' => [parent::class, 'driver', null],
        'page' => [parent::class, 'page', null],
        'selectorsHandler' => [parent::class, 'selectorsHandler', null],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('SessionProxy9b5c0b7', false)) {
    \class_alias(__NAMESPACE__.'\\SessionProxy9b5c0b7', 'SessionProxy9b5c0b7', false);
}
