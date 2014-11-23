<?php

namespace tantrum\Core;

class Module {

    private static $container = null;
    private static $listeners = null;
    private static $config = null;
    private static $cache = null;
    
    public final function __construct()
    {
        self::getContainer();
        self::getListeners();
        self::getConfig();
        self::getCache();
    }

    private static function getContainer()
    {
        if (self::$container === null) {
            self::$container = Container::init();
        }
        return self::$container;
    }

    private static function getListeners()
    {
        if (self::$listeners === null) {
            self::$listeners = ListenerCollection::init();
        }
        return self::$listeners;
    }

    private static function getConfig()
    {
        if (self::$config === null) {
            self::$config = Config::init();
        }
        return self::$config;
    }

    private static function getCache()
    {
        if (self::$cache === null) {
            self::$cache = Cache::init();
        }
        return self::$cache;
    }

    protected static function newInstance($class)
    {
        $args = func_get_args();
        unset($args[0]);
        return self::getContainer()->newInstance($class, $args);
    }

    public static function callListener($name)
    {
        $args = func_get_args();
        unset($args[0]);
        $listeners = self::$listeners;
        return $listeners::callListener($name, $args);
    }

    public static function getConfigOption($name)
    {
        $config = self::getConfig();
        return $config::get($name);
    }

    public static function setInCache($key, $value, $cacheLocally=false)
    {
        $cache = self::$cache;
        $cache::set($key, $value);
    }

    public static function getFromCache($key, $localCache=false)
    {
        $cache = self::$cache;
        return $cache::get($key);
    }
}