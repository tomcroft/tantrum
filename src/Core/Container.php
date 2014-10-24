<?php

/*
    TODO: This class knows too much, it should interact with collection objects which know what they're doing
 */

namespace tantrum\Core;

use tantrum\Exception;

class Container
{
    private static $injectedModules = array();
    private static $listeners = array();
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

    public static function addListener($name, $callback)
    {
        if(!is_callable($callback)) {
            throw new \Exception('Listener '.$name.' is not a valid callback');
        } elseif(array_key_exists($name, self::$listeners)) {
            throw new \Exception('Listener '.$name.' is already registered');
        }
        self::$listeners[$name] = $callback;
    }

    public function callListener($name)
    {
        if(!array_key_exists($name, self::$listeners)) {
            throw new \Exception('Listener '.$name.' is not registered');
        }
        $args = func_get_args();
        unset($args[0]);
        return call_user_func_array($listener, $args);
    }

    public static function flush()
    {
        self::$injectedModules = array();
        self::$listeners = array();
    }
}