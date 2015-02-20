<?php

namespace tantrum\Core;

class Module {

    public final function __construct(){}

    protected function newInstance($class)
    {
        $args = func_get_args();
        unset($args[0]);
        $container = Container::init();
        return $container->newInstance($class, $args);
    }

    protected function callListener($name)
    {
        $args = func_get_args();
        unset($args[0]);
        $listeners = ListenerCollection::init();
        return $listeners::callListener($name, $args);
    }

    protected function getConfigOption($name)
    {
        $config = Config::init();
        return $config::getDatabase($name);
    }

    protected static function setInCache($key, $value, $cacheLocally=false)
    {
        $cache = Cache::init();
        $cache::set($key, $value);
    }

    protected static function getFromCache($key, $localCache=false)
    {
        $cache = Cache::init();
        return $cache::get($key);
    }
}