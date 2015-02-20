<?php

namespace tantrum\Core;

class Cache
{
    private static $self = null;
    private static $cache = null;
    private static $cachedValues = array();

    protected final function __construct(){}

    public static function init()
    {
        if(is_null(static::$self)) {
            static::$self = new Cache();
        }
        return static::$self;
    }

    public static function set($key, $value)
    {
        self::init();
        self::$cachedValues[$key] = $value;
    }

    public static function get($key)
    {
        self::init();
        if(array_key_exists($key, self::$cachedValues)) {
            return self::$cachedValues[$key];
        }
        return null;
    }

    public static function flush()
    {
        self::$self = null;
    }
}