<?php

namespace tantrum\Core;

use tantrum\Exception;

class Config
{
    private static $self = null;
    private static $configs = array();

    //TODO: support replication / root access here
    private static $keys = array(
        'databaseDriver',
        'databaseHost',
        'defaultSchema',
        'databaseUser',
        'databasePassword',
    );

    protected final function __construct(){}

    /**
     * Create a singleton instance
     * @return tantrum\Core\ListenerCollection
     */
    public static function init()
    {
        if (null === self::$self) {
           self::$self = new Config();
        }
        return self::$self;
    }

    public static function set(array $configs)
    {
        self::validateConfigOptions($configs);
        self::$configs = $configs;
    }

    public static function get($key)
    {
        if(!array_key_exists($key, self::$configs)) {
            throw new Exception\Exception($key.' is not a valid config option');
        }
        return self::$configs[$key];
    }

    protected static function validateConfigOptions($configs)
    {
        foreach(self::$keys as $key) {
            if(!array_key_exists($key, $configs)) {
                throw new Exception\Exception($key.' is a required config option');
            }
        }
        return true;
    }
}