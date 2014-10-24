<?php

namespace tantrum\Core;

class Module {

    private static $container = null;
    
    public final function __construct()
    {
        self::getContainer();
    }

    private static function getContainer()
    {
        if (self::$container === null) {
            self::$container = Container::init();
        }
        return self::$container;
    }

    public static function newInstance($class)
    {
        return self::getContainer()->newInstance($class);
    }

    public function injectInstance($object)
    {
        return self::getContainer()->injectInstance($class);
    }
}