<?php

namespace tantrum\Core;

use tantrum\Exception;

class Container
{
    private static $injectedModules = array();
    private static $self = null;
    
    protected final function __construct(){}

    public static function init()
    {
        if (null === self::$self) {
           self::$self = new Container();
        }
        return self::$self;
    }

    public static function newInstance($class, $args)
    {
        if(array_key_exists($class, self::$injectedModules)) {
            return self::$injectedModules[$class];
        }
        $reflection_class = new \ReflectionClass($class);
        return $reflection_class->newInstanceArgs($args);
    }

    public static function injectInstance($key, $object)
    {
        if(!array_key_exists($key, self::$injectedModules)) {
            self::$injectedModules[$key] = $object;
            return $object;
        } else {
            return self::$injectedModules[$key];
        }
    }

    public static function flush()
    {
        self::$injectedModules = array();
    }
}