<?php

namespace tomcroft\tantrum\Core;

use tomcroft\tantrum\Exception;

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

    public static function newInstance($class)
    {
        if(array_key_exists($class, self::$injectedModules)) {
            return self::$injectedModules[$class];
        }
        return new $class;
    }

    public static function injectInstance($key, $object)
    {
        if(php_sapi_name() !== 'cli')
        {
            throw new \Exception('Don\'t inject objects into the container unless you\'re testing!');
        }
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