<?php

namespace tantrum\Core;

class Cache
{
    private static $self = null;
    private static $cache = null;
    private static $cachedValues = array();

    protected final function __construct(){}

    /**
     * Create a singleton instance
     * @return tantrum\Core\Cache
     */
    public static function init()
    {
        if (null === self::$self) {
           self::$self = new Cache();
        }
        return self::$self;
    }

    public static function set($key, $value, $local = false)
    {
        $cache = self::$cache;
        if(!is_null(self::$cache) && $local === false) {
            return self::$cache->set($key, $value);
        } elseif($local === true) {
            self::$cachedValues[$key] = $value;
            return true;
        }
        return false;
    }

    public static function get($key)
    {
        $cache = self::$cache;
        if(!is_null($cache) && $cache->isHit($key)) {
            return self::$cache->get($key);
        } elseif(array_key_exists($key, self::$cachedValues)) {
            return self::$cachedValues[$key];
        }
        return null;
    }

    public static function flush()
    {
        self::$cache = null;
        self::$cachedValues = array();
        self::init();
    }
}